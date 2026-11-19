<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login(); $admin = in_array($u['role'], ['admin', 'manager'], true);
$types = ['Notice', 'Maintenance', 'Event', 'Emergency'];
if (is_post() && $admin) {
    csrf_check(); $act = $_POST['action'] ?? '';
    $t = trim($_POST['title'] ?? ''); $b = trim($_POST['body'] ?? '');
    $ty = in_array($_POST['type'] ?? '', $types, true) ? $_POST['type'] : 'Notice';
    if ($act === 'create') {
        if ($t && $b) {
            q('INSERT INTO notices (title,body,type,created_by) VALUES (?,?,?,?)', [$t, $b, $ty, $u['id']]);
            foreach (q("SELECT id FROM users WHERE status='active' AND id<>?", [$u['id']])->fetchAll() as $r) notify((int)$r['id'], ($ty === 'Emergency' ? 'Emergency: ' : 'New notice: ') . $t);
            flash('Notice published to all users.');
        } else flash('Title and message are required.', 'err');
    } elseif ($act === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($t && $b && q('SELECT id FROM notices WHERE id=?', [$id])->fetch()) {
            q('UPDATE notices SET title=?, body=?, type=? WHERE id=?', [$t, $b, $ty, $id]);
            if (!empty($_POST['renotify'])) foreach (q("SELECT id FROM users WHERE status='active' AND id<>?", [$u['id']])->fetchAll() as $r) notify((int)$r['id'], 'Notice updated: ' . $t);
            flash('Notice updated.');
        } else { flash('Title and message are required.', 'err'); redirect('notices.php?edit=' . $id); }
    } elseif ($act === 'delete') { q('DELETE FROM notices WHERE id=?', [(int)$_POST['id']]); flash('Notice deleted.'); }
    redirect('notices.php');
}
$edit = $admin && isset($_GET['edit']) ? q('SELECT * FROM notices WHERE id=?', [(int)$_GET['edit']])->fetch() : null;
$rows = q('SELECT * FROM notices ORDER BY created_at DESC')->fetchAll();
page_start('Notices', 'notices.php'); ?>
<div class="head"><div><h1><?= $admin ? 'Notice Broadcasting' : 'Notices &amp; Announcements' ?></h1><p><?= $admin ? 'Create, edit and publish notices to residents' : 'Important building updates and alerts' ?></p></div>
<?php if ($admin && !$edit): ?><button class="btn p" onclick="document.getElementById('nf').hidden=!document.getElementById('nf').hidden"><?= icon('plus', 15) ?> Create Notice</button><?php endif; ?></div>
<?php if ($admin): ?><form id="nf" <?= $edit ? '' : 'hidden' ?> class="card" method="post" style="margin-bottom:18px"><?= csrf_field() ?>
<input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><h3>Edit notice</h3><?php endif; ?>
<div class="row2"><div class="field"><label>Title</label><input name="title" value="<?= e($edit['title'] ?? '') ?>" required></div>
<div class="field"><label>Type</label><select name="type"><?php foreach ($types as $t): ?><option <?= ($edit['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div></div>
<div class="field"><label>Message</label><textarea name="body" required><?= e($edit['body'] ?? '') ?></textarea></div>
<?php if ($edit): ?><label style="text-transform:none;letter-spacing:0;font-weight:500;display:flex;gap:8px;align-items:center"><input type="checkbox" name="renotify" value="1" style="width:auto"> Notify everyone about this update</label><br><?php endif; ?>
<button class="btn p"><?= $edit ? 'Save changes' : 'Publish' ?></button> <?php if ($edit): ?><a class="btn" href="notices.php">Cancel</a><?php endif; ?></form><?php endif; ?>
<div style="max-width:860px"><?php foreach ($rows as $n): ?><div class="notice n-<?= strtolower($n['type']) ?>"><div style="display:flex;justify-content:space-between;align-items:center"><span><?= badge($n['type']) ?> <small class="muted"><?= date('M j, Y', strtotime($n['created_at'])) ?></small></span>
<?php if ($admin): ?><span style="display:flex;gap:6px"><a class="btn sm" href="notices.php?edit=<?= $n['id'] ?>" title="Edit"><?= icon('settings', 14) ?> Edit</a>
<form method="post" onsubmit="return confirm('Delete this notice?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $n['id'] ?>"><button class="btn sm r" title="Delete"><?= icon('trash', 14) ?></button></form></span><?php endif; ?></div>
<h4><?= $n['type'] === 'Emergency' ? '⚠️ ' : '' ?><?= e($n['title']) ?></h4><p><?= nl2br(e($n['body'])) ?></p></div>
<?php endforeach; if (!$rows) echo '<div class="card empty">No notices yet.</div>'; ?></div>
<?php page_end();
