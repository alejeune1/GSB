<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'visiteur') {
    header('Location: login.php'); exit;
}
require_once __DIR__ . '/../src/Database.php';

$pdo  = Database::get();
$stmt = $pdo->prepare(
    "SELECT f.FFR_ID,
            f.FRR_MOIS,
            f.FRR_ANNEE,
            f.FRR_MONTANT_VALIDE,
            f.ETA_ID,
            e.ETA_LIB
       FROM fiche_frais f
       JOIN etat        e ON e.ETA_ID = f.ETA_ID
      WHERE f.VIS_ID = ?
   ORDER BY f.FRR_ANNEE DESC, f.FRR_MOIS DESC"
  );  
$stmt->execute([$_SESSION['user_id']]);
$fiches = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Mes fiches</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
  <h1>Mes fiches de frais</h1>
  <table>
  <tr><th>Mois/Année</th><th>Montant</th><th>État</th><th>Actions</th></tr>
  <?php foreach($fiches as $f): ?>
  <tr>
    <td><?= "{$f['FRR_MOIS']}/{$f['FRR_ANNEE']}" ?></td>
    <td><?= number_format($f['FRR_MONTANT_VALIDE'],2,',',' ') ?> €</td>
    <!-- Avant : <?= $f['ETA_ID'] ?> -->
    <td><?= htmlspecialchars($f['ETA_LIB']) ?></td>
    <td><a href="detail.php?ffr_id=<?= $f['FFR_ID'] ?>" class="btn">Voir →</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<p><a href="index.php" class="btn">← Nouvelle saisie</a></p>
<p><a href="logout.php" class="btn secondary">Se déconnecter</a></p>
</div>
</body>
</html>
