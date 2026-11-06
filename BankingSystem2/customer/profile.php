<?php
require_once __DIR__ . '/../config/database.php';
require_customer();
$uid = $_SESSION['user']['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['full_name'] ?? '');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Please enter a valid name.';
        else {
            $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?')->execute([$name, $uid]);
            $_SESSION['user']['name'] = $name;
            flash('Profile updated.'); redirect('customer/profile.php');
        }
    } elseif (($_POST['action'] ?? '') === 'password') {
        $st = $pdo->prepare('SELECT password FROM users WHERE id = ?'); $st->execute([$uid]);
        if (!password_verify($_POST['current'] ?? '', $st->fetchColumn())) $errors[] = 'Current password is incorrect.';
        elseif (strlen($_POST['new'] ?? '') < 8)                          $errors[] = 'New password must be at least 8 characters.';
        elseif (($_POST['new'] ?? '') !== ($_POST['confirm'] ?? ''))      $errors[] = 'New passwords do not match.';
        else {
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $uid]);
            session_regenerate_id(true);
            flash('Password changed.'); redirect('customer/profile.php');
        }
    }
}
$st = $pdo->prepare('SELECT u.*, a.account_number FROM users u JOIN accounts a ON a.user_id = u.id WHERE u.id = ?');
$st->execute([$uid]); $me = $st->fetch();

$pageTitle = 'Profile';
include __DIR__ . '/../config/header.php';
?>
<h2>My profile</h2>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<div class="grid two">
  <div class="card">
    <h3>Details</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="profile">
      <label>Full name <input name="full_name" value="<?= e($me['full_name']) ?>" required></label>
      <label>Email <input value="<?= e($me['email']) ?>" disabled></label>
      <label>Account number <input value="<?= e($me['account_number']) ?>" disabled></label>
      <button class="btn" type="submit">Save</button>
    </form>
  </div>
  <div class="card">
    <h3>Change password</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <label>Current password <input type="password" name="current" required></label>
      <label>New password <input type="password" name="new" minlength="8" required></label>
      <label>Confirm new password <input type="password" name="confirm" minlength="8" required></label>
      <button class="btn" type="submit">Update password</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
