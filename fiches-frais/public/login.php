<?php
session_start();
require_once __DIR__ . '/../src/Database.php';

if (!empty($_SESSION['user_id'])) {
    // Redirige selon le rôle
    header('Location: ' . ($_SESSION['role'] === 'visiteur' ? 'index.php' : 'validation.php'));
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = $_POST['login']    ?? '';
    $password = $_POST['password'] ?? '';

    $pdo  = Database::get();
    $stmt = $pdo->prepare("SELECT id, password, role FROM USER WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        header('Location: ' . ($user['role'] === 'visiteur' ? 'index.php' : 'validation.php'));
        exit;
    } else {
        $msg = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Connexion</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
  <form method="post" class="container">
    <h1>Connexion</h1>
    <?php if ($msg): ?><p style="color:red"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <label>Login :</label>
    <input type="text" name="login" required>
    <label>Mot de passe :</label>
    <input type="password" name="password" required>
    <button type="submit" class="submit-button">Se connecter</button>
  </form>
</body>
</html>
