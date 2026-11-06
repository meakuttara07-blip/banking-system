<?php
require_once __DIR__ . '/config/database.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$errors = []; $name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = 'Enter a valid email address.';
    if (strlen($pass) < 8)                               $errors[] = 'Password must be at least 8 characters.';
    if ($pass !== $pass2)                                $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $st = $pdo->prepare('SELECT 1 FROM users WHERE email = ?'); $st->execute([$email]);
        if ($st->fetch()) $errors[] = 'That email is already registered.';
    }
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO users (full_name,email,password) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $uid = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO accounts (user_id,account_number) VALUES (?,?)')
                ->execute([$uid, new_account_number($pdo)]);
            $pdo->commit();
            flash('Account created! Please log in.');
            redirect('login.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $errors[] = 'Could not create account. Please try again.';
        }
    }
}
$pageTitle = 'Open an account';
include __DIR__ . '/config/header.php';
?>
<div class="card narrow">
  <h2>Open an account</h2>
  <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <label>Full name <input name="full_name" value="<?= e($name) ?>" required></label>
    <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Password <input type="password" name="password" minlength="8" required></label>
    <label>Confirm password <input type="password" name="confirm" minlength="8" required></label>
    <button class="btn" type="submit">Create account</button>
  </form>
  <p class="muted">Already a customer? <a href="login.php">Log in</a></p>
</div>
<?php include __DIR__ . '/config/footer.php'; ?>
