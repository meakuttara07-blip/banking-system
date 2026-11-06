<?php
require_once __DIR__ . '/../config/database.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$sql = "SELECT u.id, u.full_name, u.email, u.created_at, a.account_number, a.balance, a.status
        FROM users u LEFT JOIN accounts a ON a.user_id = u.id WHERE u.role = 'customer'";
$args = [];
if ($q !== '') { $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR a.account_number LIKE ?)'; $args = ["%$q%", "%$q%", "%$q%"]; }
$st = $pdo->prepare($sql . ' ORDER BY u.id DESC LIMIT 500'); $st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'Customers';
include __DIR__ . '/../config/header.php';
?>
<h2>Customers</h2>
<form method="get" class="inline">
  <input name="q" value="<?= e($q) ?>" placeholder="Search name, email or account #">
  <button class="btn" type="submit">Search</button>
</form>
<div class="card table-wrap">
<table>
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Account</th><th>Balance</th><th>Status</th><th>Joined</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['email']) ?></td>
    <td><?= e($r['account_number']) ?></td><td><?= money($r['balance']) ?></td>
    <td><span class="badge <?= $r['status'] === 'active' ? 'green' : 'red' ?>"><?= e($r['status']) ?></span></td>
    <td><?= e($r['created_at']) ?></td>
  </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="muted">No customers found.</td></tr><?php endif; ?>
</table>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
