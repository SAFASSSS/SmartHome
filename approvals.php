<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('admin');
if (is_post()) {
    csrf_check(); $id = (int)$_POST['id']; $ok = ($_POST['action'] ?? '') === 'approve';
    q('UPDATE users SET status=? WHERE id=? AND role<>?', [$ok ? 'active' : 'rejected', $id, 'admin']);
    notify($id, $ok ? 'Your account has been approved. Welcome to SmartNest!' : 'Your registration was declined.');
    flash($ok ? 'User approved.' : 'User rejected.'); redirect('approvals.php');
}
$pending = q("SELECT u.*, b.name bname FROM users u LEFT JOIN buildings b ON b.id=u.building_id WHERE u.status='pending' ORDER BY u.created_at")->fetchAll();
$all = q("SELECT * FROM users WHERE status<>'pending' ORDER BY created_at DESC LIMIT 50")->fetchAll();
page_start('User Approvals', 'approvals.php'); ?>
<div class="head"><div><h1>User Approvals</h1><p><?= count($pending) ?> pending registration(s)</p></div></div>
<div class="card tbl" style="margin-bottom:18px"><h3>Pending</h3><table><tr><th>Name</th><th>Email</th><th>Role</th><th>Building / Flat</th><th>Applied</th><th></th></tr>
<?php foreach ($pending as $p): ?><tr><td><b><?= e($p['name']) ?></b></td><td><?= e($p['email']) ?></td><td><?= e(ucfirst($p['role'])) ?></td><td><?= e($p['bname'] ?? '—') ?> <?= e($p['unit']) ?></td><td class="muted"><?= ago($p['created_at']) ?></td>
<td><form method="post" style="display:flex;gap:6px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button name="action" value="approve" class="btn sm g"><?= icon('check', 14) ?> Approve</button><button name="action" value="reject" class="btn sm r"><?= icon('x', 14) ?></button></form></td></tr>
<?php endforeach; if (!$pending) echo '<tr><td colspan="6" class="empty">No pending approvals 🎉</td></tr>'; ?></table></div>
<div class="card tbl"><h3>All users</h3><table><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr><?php foreach ($all as $a): ?><tr><td><?= e($a['name']) ?></td><td><?= e($a['email']) ?></td><td><?= e(ucfirst($a['role'])) ?></td><td><?= badge($a['status']) ?></td></tr><?php endforeach; ?></table></div>
<?php page_end();
