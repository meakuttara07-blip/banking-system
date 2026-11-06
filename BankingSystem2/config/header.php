<?php $u = $_SESSION['user'] ?? null; $flash = flash(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Bank') ?> · SecureBank</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?>/index.php">🏦 SecureBank</a>
  <nav>
  <?php if ($u): ?>
    <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
    <?php if ($u['role'] === 'admin'): ?>
      <a href="<?= BASE_URL ?>/admin/customers.php">Customers</a>
      <a href="<?= BASE_URL ?>/admin/accounts.php">Accounts</a>
      <a href="<?= BASE_URL ?>/admin/transactions.php">Transactions</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/customer/deposit.php">Deposit</a>
      <a href="<?= BASE_URL ?>/customer/withdraw.php">Withdraw</a>
      <a href="<?= BASE_URL ?>/customer/transfer.php">Transfer</a>
      <a href="<?= BASE_URL ?>/customer/history.php">History</a>
      <a href="<?= BASE_URL ?>/customer/profile.php">Profile</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/logout.php">Logout (<?= e(explode(' ', $u['name'])[0]) ?>)</a>
  <?php else: ?>
    <a href="<?= BASE_URL ?>/login.php">Login</a>
    <a href="<?= BASE_URL ?>/register.php">Open account</a>
  <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if ($flash): ?><div class="alert <?= e($flash[1]) ?>"><?= e($flash[0]) ?></div><?php endif; ?>
