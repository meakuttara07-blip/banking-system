<?php
require_once __DIR__ . '/config/database.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
$pageTitle = 'Welcome';
include __DIR__ . '/config/header.php';
?>
<section class="hero">
  <h1>Banking made simple.</h1>
  <p>Open an account in seconds. Deposit, withdraw and transfer money securely.</p>
  <a class="btn" href="register.php">Open an account</a>
  <a class="btn btn-outline" href="login.php">Log in</a>
</section>
<?php include __DIR__ . '/config/footer.php'; ?>
