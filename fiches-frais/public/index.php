<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'visiteur') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../src/FraisManager.php';
$fm = new FraisManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Création ou récupération de la fiche
    $fid = $fm->getOrCreateFiche(
        $_SESSION['user_id'],
        $_POST['mois'],
        $_POST['annee']
    );
    // Ajout des lignes forfait
    foreach ($_POST['forfait'] as $forId => $qte) {
        $fm->addLigneForfait($fid, $forId, (int)$qte);
    }
    header('Location: index.php');
    exit;
}

$forfaits = $fm->getForfaits();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Saisie des frais</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="title">Gestion des frais – Saisie</div>
  <form method="post" class="container">
    <label>Mois :</label>
    <select name="mois" required>
      <?php foreach (['JANVIER','FEVRIER','MARS','AVRIL','MAI','JUIN','JUILLET','AOUT','SEPTEMBRE','OCTOBRE','NOVEMBRE','DECEMBRE'] as $m): ?>
        <option><?= $m ?></option>
      <?php endforeach; ?>
    </select>
    <label>Année :</label>
    <input type="text" name="annee" pattern="\d{4}" required>

    <h2>Frais au forfait</h2>
    <?php foreach($forfaits as $f): ?>
      <label><?= htmlspecialchars($f['FOR_LIB']) ?> (<?= $f['FOR_MONTANT'] ?> €)</label>
      <input type="number" name="forfait[<?= $f['FOR_ID'] ?>]" min="0" value="0">
    <?php endforeach; ?>

    <button type="submit" class="submit-button">Enregistrer</button>
  </form>
  <p><a href="list.php" class="btn">Voir mes fiches →</a></p>
  <p><a href="logout.php" class="btn secondary">Se déconnecter</a></p>
</body>
</html>
