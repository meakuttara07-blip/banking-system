<?php
require_once __DIR__ . '/../config/database.php';
require_customer();
$acct = my_account($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $to = preg_replace('/\D/', '', $_POST['to_account'] ?? '');
    $error = process_transaction($pdo, $_SESSION['user']['id'], 'transfer', trim($_POST['amount'] ?? ''), $to, $_POST['description'] ?? '');
    if (!$error) { flash('Transfer completed.'); redirect('customer/transfer.php'); }
}
$pageTitle = 'Transfer';
include __DIR__ . '/../config/header.php';
?>
<div class="card narrow">
  <h2>Transfer money</h2>
  <p class="muted">From account <?= e($acct['account_number']) ?> · Balance: <strong><?= money($acct['balance']) ?></strong></p>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-confirm="Send this transfer? It cannot be undone.">
    <?= csrf_field() ?>
    <label>Recipient account number <input name="to_account" maxlength="10" pattern="\d{10}" placeholder="10 digits" required></label>
    <label>Amount (USD) <input name="amount" inputmode="decimal" placeholder="0.00" required></label>
    <label>Note (optional) <input name="description" maxlength="255"></label>
    <button class="btn" type="submit">Send</button>
  </form>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
