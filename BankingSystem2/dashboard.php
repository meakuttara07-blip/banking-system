<?php
require_once __DIR__ . '/config/database.php';
require_login();
$pageTitle = 'Dashboard';
$isAdmin = $_SESSION['user']['role'] === 'admin';

if ($isAdmin) {
    $stats = [
        'Customers'          => $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
        'Accounts'           => $pdo->query('SELECT COUNT(*) FROM accounts')->fetchColumn(),
        'Total deposits'     => money($pdo->query('SELECT COALESCE(SUM(balance),0) FROM accounts')->fetchColumn()),
        'Transactions today' => $pdo->query('SELECT COUNT(*) FROM transactions WHERE DATE(created_at)=CURDATE()')->fetchColumn(),
    ];
} else {
    $acct = my_account($pdo);
    $st = $pdo->prepare('SELECT * FROM transactions WHERE account_id = ? ORDER BY id DESC LIMIT 5');
    $st->execute([$acct['id']]);
    $recent = $st->fetchAll();
}
include __DIR__ . '/config/header.php';
?>
<h2>Welcome, <?= e($_SESSION['user']['name']) ?></h2>

<?php if ($isAdmin): ?>
  <div class="grid">
    <?php foreach ($stats as $label => $val): ?>
      <div class="card stat"><span class="muted"><?= e($label) ?></span><strong><?= e($val) ?></strong></div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="card balance">
    <span class="muted">Account <?= e($acct['account_number']) ?>
      <?php if ($acct['status'] === 'frozen'): ?><span class="badge red">Frozen</span><?php endif; ?></span>
    <strong><?= money($acct['balance']) ?></strong>
    <div>
      <a class="btn" href="customer/deposit.php">Deposit</a>
      <a class="btn btn-outline" href="customer/withdraw.php">Withdraw</a>
      <a class="btn btn-outline" href="customer/transfer.php">Transfer</a>
    </div>
  </div>
  <h3>Recent activity</h3>
  <div class="card">
    <?php if (!$recent): ?><p class="muted">No transactions yet.</p><?php else: ?>
    <table>
      <tr><th>Date</th><th>Type</th><th>Amount</th><th>Balance</th></tr>
      <?php foreach ($recent as $t): $in = in_array($t['type'], ['deposit', 'transfer_in']); ?>
      <tr>
        <td><?= e($t['created_at']) ?></td>
        <td><?= e(ucwords(str_replace('_', ' ', $t['type']))) ?></td>
        <td class="<?= $in ? 'pos' : 'neg' ?>"><?= $in ? '+' : '−' ?><?= money($t['amount']) ?></td>
        <td><?= money($t['balance_after']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php include __DIR__ . '/config/footer.php'; ?>
