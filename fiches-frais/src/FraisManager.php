<?php
require_once __DIR__ . '/Database.php';

class FraisManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::get();
    }

    /**
     * Retourne tous les types de forfait (repas, nuitée…)
     * @return array
     */
    public function getForfaits(): array {
        return $this->pdo->query("SELECT * FROM frais_forfait")->fetchAll();
    }

    /**
     * Récupère l’ID d’une fiche existante ou la crée si nécessaire
     * @param string $visId  Identifiant du visiteur
     * @param string $mois   Mois (ENUM)
     * @param string $annee  Année (4 chiffres)
     * @return int           ID de la fiche
     */
    /**
 * Récupère l’ID d’une fiche existante ou la crée si nécessaire
 * en fournissant explicitement des 0 pour montant et justificatifs.
 */
public function getOrCreateFiche(string $visId, string $mois, string $annee): int {
    // Si la fiche existe déjà…
    $stmt = $this->pdo->prepare(
        "SELECT FFR_ID
         FROM fiche_frais
         WHERE VIS_ID = ? AND FRR_MOIS = ? AND FRR_ANNEE = ?"
    );
    $stmt->execute([$visId, $mois, $annee]);
    if ($row = $stmt->fetch()) {
        return (int)$row['FFR_ID'];
    }

    // Sinon on crée la fiche en précisant 0 pour montant et nb justificatifs
    $stmt = $this->pdo->prepare(
        "INSERT INTO fiche_frais
         (VIS_ID, ETA_ID, FRR_MOIS, FRR_ANNEE, FRR_MONTANT_VALIDE, FRR_NB_JUSTIFICATIFS, FRR_DATE_MODIF)
         VALUES (?, 'CR', ?, ?, 0, 0, CURDATE())"
    );
    $stmt->execute([$visId, $mois, $annee]);

    return (int)$this->pdo->lastInsertId();
}

    /**
     * Ajoute ou met à jour une ligne de forfait, puis recalcule le montant validé
     * @param int    $ffrId  ID de la fiche
     * @param string $forId  Code du forfait (ETP, KM…)
     * @param int    $qte    Quantité
     */
    public function addLigneForfait(int $ffrId, string $forId, int $qte) {
        // REPLACE INTO pour upsert
        $stmt = $this->pdo->prepare(
            "REPLACE INTO ligne_frais_forfait (FFR_ID, FOR_ID, LIG_QTE)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$ffrId, $forId, $qte]);
        $this->recalcMontantValide($ffrId);
    }

    /**
     * Recalcule et met à jour le champ FRR_MONTANT_VALIDE de la fiche
     * @param int $ffrId
     */
    private function recalcMontantValide(int $ffrId) {
        $sql = <<<SQL
SELECT SUM(l.LIG_QTE * f.FOR_MONTANT) AS total
FROM ligne_frais_forfait l
JOIN frais_forfait f ON f.FOR_ID = l.FOR_ID
WHERE l.FFR_ID = ?
SQL;
        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute([$ffrId]);
        $total = (float)$stmt->fetchColumn();

        $upd = $this->pdo->prepare(
            "UPDATE fiche_frais
             SET FRR_MONTANT_VALIDE = ?, FRR_DATE_MODIF = CURDATE()
             WHERE FFR_ID = ?"
        );
        $upd->execute([$total, $ffrId]);
    }

    /**
     * Ajoute une ligne de frais hors-forfait
     * @param int    $ffrId
     * @param string $dte       Date au format YYYY-MM-JJ
     * @param string $libelle   Libellé
     * @param float  $montant   Montant
     */
    public function addHorsForfait(int $ffrId, string $dte, string $libelle, float $montant) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ligne_frais_horsforfait
             (FFR_ID, DTE, LIBELLE, MONTANT, ETA_ID)
             VALUES (?, ?, ?, ?, 'CR')"
        );
        $stmt->execute([$ffrId, $dte, $libelle, $montant]);
    }

    /**
 * Récupère le détail (forfait + hors-forfait) d’une fiche
 * et son montant total validé (FRR_MONTANT_VALIDE).
 */
public function getDetailFiche(int $ffrId): array {
    // 1) Forfaits
    $stmt = $this->pdo->prepare(
      "SELECT l.FOR_ID, l.LIG_QTE, f.FOR_LIB, f.FOR_MONTANT
       FROM ligne_frais_forfait l
       JOIN frais_forfait f USING (FOR_ID)
       WHERE l.FFR_ID = ?"
    );
    $stmt->execute([$ffrId]);
    $forfaits = $stmt->fetchAll();

    // 2) Hors-forfait
    $stmt = $this->pdo->prepare(
      "SELECT * FROM ligne_frais_horsforfait WHERE FFR_ID = ?"
    );
    $stmt->execute([$ffrId]);
    $horsforfait = $stmt->fetchAll();

    // 3) Total validé stocké en base
    $stmt = $this->pdo->prepare(
      "SELECT FRR_MONTANT_VALIDE FROM fiche_frais WHERE FFR_ID = ?"
    );
    $stmt->execute([$ffrId]);
    $total = (float)$stmt->fetchColumn();

    return [
      'forfaits'    => $forfaits,
      'horsforfait' => $horsforfait,
      'total'       => $total,
    ];
}

    /**
     * Retourne les fiches pour un état donné (CR = saisie en cours, VA = validée…)
     * @param string $statut  Code ETA_ID
     * @return array
     */
    public function getFichesByStatut(string $statut): array {
        $stmt = $this->pdo->prepare(
            "SELECT f.*, v.VIS_NOM, v.VIS_PRENOM
             FROM fiche_frais f
             JOIN visiteur v USING (VIS_ID)
             WHERE ETA_ID = ?
             ORDER BY FRR_ANNEE DESC, FRR_MOIS DESC"
        );
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    /**
     * Met à jour l'état d'une fiche (VA, NV, RB)
     * @param int    $ffrId
     * @param string $etat   Nouveau code ETA_ID
     */
    public function updateStatut(int $ffrId, string $etat) {
        $stmt = $this->pdo->prepare(
            "UPDATE fiche_frais
             SET ETA_ID = ?, FRR_DATE_MODIF = CURDATE()
             WHERE FFR_ID = ?"
        );
        $stmt->execute([$etat, $ffrId]);
    }
}
