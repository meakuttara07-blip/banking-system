<?php
require_once __DIR__ . '/../config/database.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'frozen' ? 'frozen' : 'active';
    $pdo->prepare('UPDATE accounts SET status = ? WHERE id = ?')->execute([$status, $id]);
    flash("Account #$id is now $status.");
    redirect('admin/accounts.php');
}
$rows = $pdo->query('SELECT a.*, u.full_name, u.email FROM accounts a JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 500')->fetchAll();

$pageTitle = 'Accounts';
include __DIR__ . '/../config/header.php';
?>
<h2>Accounts</h2>
<div class="card table-wrap">
<table>
  <tr><th>Account #</th><th>Owner</th><th>Balance</th><th>Status</th><th>Opened</th><th></th></tr>
  <?php foreach ($rows as $r): $next = $r['status'] === 'active' ? 'frozen' : 'active'; ?>
  <tr>
    <td><?= e($r['account_number']) ?></td>
    <td><?= e($r['full_name']) ?><br><small class="muted"><?= e($r['email']) ?></small></td>
    <td><?= money($r['balance']) ?></td>
    <td><span class="badge <?= $r['status'] === 'active' ? 'green' : 'red' ?>"><?= e($r['status']) ?></span></td>
    <td><?= e($r['created_at']) ?></td>
    <td>
      <form method="post" data-confirm="<?= $next === 'frozen' ? 'Freeze' : 'Unfreeze' ?> this account?">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <input type="hidden" name="status" value="<?= $next ?>">
        <button class="btn btn-sm <?= $next === 'frozen' ? 'btn-danger' : '' ?>"><?= $next === 'frozen' ? 'Freeze' : 'Unfreeze' ?></button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
