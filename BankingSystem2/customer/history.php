<?php
require_once __DIR__ . '/../config/database.php';
require_customer();
$acct = my_account($pdo);

$filter = $_GET['type'] ?? '';
$valid  = ['deposit', 'withdraw', 'transfer_in', 'transfer_out'];
$sql = 'SELECT t.*, a.account_number AS other FROM transactions t
        LEFT JOIN accounts a ON a.id = t.related_account_id WHERE t.account_id = ?';
$args = [$acct['id']];
if (in_array($filter, $valid, true)) { $sql .= ' AND t.type = ?'; $args[] = $filter; }
$sql .= ' ORDER BY t.id DESC LIMIT 200';
$st = $pdo->prepare($sql); $st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'History';
include __DIR__ . '/../config/header.php';
?>
<h2>Transaction history</h2>
<form method="get" class="inline">
  <select name="type" onchange="this.form.submit()">
    <option value="">All types</option>
    <?php foreach ($valid as $v): ?>
      <option value="<?= $v ?>" <?= $filter === $v ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $v))) ?></option>
    <?php endforeach; ?>
  </select>
</form>
<div class="card table-wrap">
<?php if (!$rows): ?><p class="muted">No transactions found.</p><?php else: ?>
<table>
  <tr><th>Date</th><th>Type</th><th>Counterparty</th><th>Note</th><th>Amount</th><th>Balance</th></tr>
  <?php foreach ($rows as $t): $in = in_array($t['type'], ['deposit', 'transfer_in']); ?>
  <tr>
    <td><?= e($t['created_at']) ?></td>
    <td><?= e(ucwords(str_replace('_', ' ', $t['type']))) ?></td>
    <td><?= e($t['other'] ?? '—') ?></td>
    <td><?= e($t['description'] ?: '—') ?></td>
    <td class="<?= $in ? 'pos' : 'neg' ?>"><?= $in ? '+' : '−' ?><?= money($t['amount']) ?></td>
    <td><?= money($t['balance_after']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
