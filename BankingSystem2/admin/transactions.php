<?php
require_once __DIR__ . '/../config/database.php';
require_admin();

$type = $_GET['type'] ?? '';
$valid = ['deposit', 'withdraw', 'transfer_in', 'transfer_out'];
$sql = 'SELECT t.*, a.account_number, u.full_name FROM transactions t
        JOIN accounts a ON a.id = t.account_id JOIN users u ON u.id = a.user_id';
$args = [];
if (in_array($type, $valid, true)) { $sql .= ' WHERE t.type = ?'; $args[] = $type; }
$st = $pdo->prepare($sql . ' ORDER BY t.id DESC LIMIT 300'); $st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'All transactions';
include __DIR__ . '/../config/header.php';
?>
<h2>All transactions</h2>
<form method="get" class="inline">
  <select name="type" onchange="this.form.submit()">
    <option value="">All types</option>
    <?php foreach ($valid as $v): ?>
      <option value="<?= $v ?>" <?= $type === $v ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $v))) ?></option>
    <?php endforeach; ?>
  </select>
</form>
<div class="card table-wrap">
<table>
  <tr><th>#</th><th>Date</th><th>Customer</th><th>Account</th><th>Type</th><th>Amount</th><th>Balance after</th></tr>
  <?php foreach ($rows as $t): ?>
  <tr>
    <td><?= (int)$t['id'] ?></td><td><?= e($t['created_at']) ?></td><td><?= e($t['full_name']) ?></td>
    <td><?= e($t['account_number']) ?></td>
    <td><?= e(ucwords(str_replace('_', ' ', $t['type']))) ?></td>
    <td><?= money($t['amount']) ?></td><td><?= money($t['balance_after']) ?></td>
  </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="muted">No transactions.</td></tr><?php endif; ?>
</table>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
