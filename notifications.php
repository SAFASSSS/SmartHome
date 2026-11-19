<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login();
if (is_post()) { csrf_check(); q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$u['id']]); redirect('notifications.php'); }
$rows = q('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 60', [$u['id']])->fetchAll();
$un = count(array_filter($rows, fn($r) => !$r['is_read']));
page_start('Notifications', 'notifications.php'); ?>
<div class="head"><div><h1>Notifications</h1><p><?= $un ?> unread</p></div><form method="post"><?= csrf_field() ?><button class="btn sm">Mark all read</button></form></div>
<div style="max-width:760px"><?php foreach ($rows as $n): ?><div class="ntf <?= $n['is_read'] ? '' : 'new' ?>"><span class="avatar c-blue" style="background:#e6eeff;color:var(--pri)"><?= icon('bell', 16) ?></span><div style="flex:1"><?= e($n['message']) ?><br><small class="muted"><?= ago($n['created_at']) ?></small></div></div>
<?php endforeach; if (!$rows) echo '<div class="card empty">You are all caught up.</div>'; ?></div>
<?php page_end();
