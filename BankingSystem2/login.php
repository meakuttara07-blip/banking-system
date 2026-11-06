<?php
require_once __DIR__ . '/config/database.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $st->execute([$email]);
    $user = $st->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'name' => $user['full_name'], 'role' => $user['role']];
        redirect('dashboard.php');
    }
    $error = 'Invalid email or password.';
}
$pageTitle = 'Log in';
include __DIR__ . '/config/header.php';
?>
<div class="card narrow">
  <h2>Log in</h2>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Email <input type="email" name="email" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn" type="submit">Log in</button>
  </form>
  <p class="muted">New here? <a href="register.php">Open an account</a></p>
</div>
<?php include __DIR__ . '/config/footer.php'; ?>
