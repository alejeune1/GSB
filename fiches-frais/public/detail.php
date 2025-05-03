<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'visiteur') {
    header('Location: login.php'); exit;
}
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/FraisManager.php';

$ffrId = (int)($_GET['ffr_id'] ?? 0);
if (!$ffrId) {
    header('Location: list.php'); exit;
}

$pdo = Database::get();

// Lignes forfait
$stmt = $pdo->prepare(
  "SELECT l.FOR_ID, l.LIG_QTE, f.FOR_LIB, f.FOR_MONTANT
   FROM ligne_frais_forfait l
   JOIN frais_forfait f ON f.FOR_ID = l.FOR_ID
   WHERE l.FFR_ID = ?"
);
$stmt->execute([$ffrId]);
$forfaits = $stmt->fetchAll();

// Lignes hors-forfait
$stmt = $pdo->prepare(
  "SELECT * FROM ligne_frais_horsforfait WHERE FFR_ID = ?"
);
$stmt->execute([$ffrId]);
$horsforfait = $stmt->fetchAll();

// Montant total
$stmt = $pdo->prepare("SELECT FRR_MONTANT_VALIDE FROM fiche_frais WHERE FFR_ID = ?");
$stmt->execute([$ffrId]);
$total = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Détail fiche <?= $ffrId ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
  <h1>Détail de la fiche <?= $ffrId ?></h1>

  <h2>Forfait</h2>
  <ul>
    <?php foreach($forfaits as $l): ?>
      <li><?= "{$l['FOR_LIB']} : {$l['LIG_QTE']} × {$l['FOR_MONTANT']} € = " .
           ($l['LIG_QTE'] * $l['FOR_MONTANT']) ?> €</li>
    <?php endforeach; ?>
  </ul>

  <h2>Hors-forfait</h2>
  <ul>
    <?php foreach($horsforfait as $h): ?>
      <li><?= "{$h['DTE']} – {$h['LIBELLE']} : {$h['MONTANT']} €" ?></li>
    <?php endforeach; ?>
  </ul>

  <p><strong>Total validé :</strong> <?= $total ?> €</p>

  <p><a href="list.php" class="btn">← Retour</a></p>
  <p><a href="logout.php" class="btn secondary">Se déconnecter</a></p>
</div>
</body>
</html>
