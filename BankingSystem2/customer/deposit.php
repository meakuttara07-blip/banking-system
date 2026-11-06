<?php
require_once __DIR__ . '/../config/database.php';
require_customer();
$acct = my_account($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $error = process_transaction($pdo, $_SESSION['user']['id'], 'deposit', trim($_POST['amount'] ?? ''), '', $_POST['description'] ?? '');
    if (!$error) { flash('Deposit successful.'); redirect('customer/deposit.php'); }
}
$pageTitle = 'Deposit';
include __DIR__ . '/../config/header.php';
?>
<div class="card narrow">
  <h2>Deposit</h2>
  <p class="muted">Current balance: <strong><?= money($acct['balance']) ?></strong></p>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-confirm="Confirm this deposit?">
    <?= csrf_field() ?>
    <label>Amount (USD) <input name="amount" inputmode="decimal" placeholder="0.00" required autofocus></label>
    <label>Note (optional) <input name="description" maxlength="255"></label>
    <button class="btn" type="submit">Deposit</button>
  </form>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
