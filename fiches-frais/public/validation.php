<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'comptable') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../src/FraisManager.php';
$fm = new FraisManager();

// 1) Traitement de la mise à jour (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ffr_id'], $_POST['etat'])) {
    $fm->updateStatut((int)$_POST['ffr_id'], $_POST['etat']);
    // Après MAJ, on redirige en GET pour voir la fiche toujours sélectionnée
    header('Location: validation.php?ffr_id=' . (int)$_POST['ffr_id']);
    exit;
}

// 2) Lecture des fiches en cours (CR)
$fiches = $fm->getFichesByStatut('CR');

// 3) Quel ID est sélectionné en GET ?
$selectedId = isset($_GET['ffr_id']) ? (int)$_GET['ffr_id'] : null;

// 4) Si on a un ID, on va chercher aussi le détail
$detail = null;
if ($selectedId) {
    // On récupère forfaits et hors-forfait pour cette fiche
    $detail = $fm->getDetailFiche($selectedId);
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Validation des fiches</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="container">
  <h1>Validation des fiches</h1>

  <!-- Sélection en GET -->
  <form method="get" action="validation.php">
    <label>Fiche à traiter :</label>
    <select name="ffr_id" onchange="this.form.submit()">
      <option value="">-- Choisir --</option>
      <?php foreach($fiches as $f): ?>
        <option value="<?= $f['FFR_ID'] ?>"
          <?= $selectedId === (int)$f['FFR_ID'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($f['VIS_NOM'].' '.$f['VIS_PRENOM'].' – '.$f['FRR_MOIS'].'/'.$f['FRR_ANNEE']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if ($detail): ?>
    <!-- Affichage du détail de la fiche sélectionnée -->
    <h2>Détail de la fiche n°<?= $selectedId ?></h2>
    <h3>Forfaits</h3>
    <ul>
      <?php foreach($detail['forfaits'] as $l): ?>
        <li>
          <?= "{$l['FOR_LIB']} : {$l['LIG_QTE']} × {$l['FOR_MONTANT']}€ = " .
             ($l['LIG_QTE'] * $l['FOR_MONTANT']) ?> €
        </li>
      <?php endforeach; ?>
    </ul>

    <h3>Hors-forfait</h3>
    <ul>
      <?php foreach($detail['horsforfait'] as $h): ?>
        <li><?= "{$h['DTE']} – {$h['LIBELLE']} : {$h['MONTANT']}€" ?></li>
      <?php endforeach; ?>
    </ul>

    <?php if ($detail): ?>
  <!-- ... vos <ul> Forfait et Hors-forfait ... -->

  <h3>Total :</h3>
  <p><strong><?= number_format($detail['total'], 2, ',', ' ') ?> €</strong></p>

  <!-- Formulaire de mise à jour d'état ... -->
<?php endif; ?>

    <!-- Formulaire de mise à jour de l'état -->
    <form method="post" action="validation.php">
      <input type="hidden" name="ffr_id" value="<?= $selectedId ?>">
      <h2>Nouvel état :</h2>
      <label><input type="radio" name="etat" value="VA" checked> Validée</label><br>
      <label><input type="radio" name="etat" value="NV"> Non validée</label><br>
      <label><input type="radio" name="etat" value="RB"> Remboursée</label><br>
      <button type="submit" class="submit-button">Mettre à jour</button>
    </form>
  <?php endif; ?>

  <p><a href="logout.php" class="btn secondary">Se déconnecter</a></p>
</body>
</html>
