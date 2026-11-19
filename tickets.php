<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('technician');
if (is_post()) {
    csrf_check(); $id = (int)$_POST['id']; $st = $_POST['status'] ?? '';
    $c = q('SELECT * FROM complaints WHERE id=? AND technician_id=?', [$id, $u['id']])->fetch();
    if ($c && in_array($st, ['assigned','in_progress','resolved'], true)) {
        q('UPDATE complaints SET status=?, repair_notes=? WHERE id=?', [$st, trim($_POST['notes'] ?? ''), $id]);
        notify((int)$c['resident_id'], 'Complaint ' . code($id) . ' is now ' . str_replace('_', ' ', $st) . '.');
        flash('Ticket updated.');
    }
    redirect('tickets.php');
}
$rows = q('SELECT c.*, r.name rname FROM complaints c JOIN users r ON r.id=c.resident_id WHERE c.technician_id=? ORDER BY c.status=\'resolved\', c.priority=\'urgent\' DESC, c.created_at', [$u['id']])->fetchAll();
page_start('My Tickets', 'tickets.php'); ?>
<div class="head"><div><h1>Assigned Tickets</h1><p>Update ticket status and submit repair notes</p></div></div>
<div class="grid g3"><?php foreach ($rows as $c): ?><form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>">
<div style="display:flex;justify-content:space-between"><span class="mono"><?= code($c['id']) ?></span><?= badge($c['priority']) ?></div>
<h3 style="margin:8px 0 4px"><?= e($c['category']) ?></h3><p class="muted"><?= e($c['description']) ?></p><p class="muted" style="margin:8px 0"><small>Resident: <?= e($c['rname']) ?> · Filed <?= date('M j', strtotime($c['created_at'])) ?></small></p>
<?php if ($c['status'] === 'resolved'): ?><?= badge('resolved') ?><?php else: ?><div class="field"><select name="status"><?php foreach (['assigned' => 'Assigned', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $k => $v): ?><option value="<?= $k ?>" <?= $c['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="field"><textarea name="notes" style="min-height:60px" placeholder="Repair notes…"><?= e($c['repair_notes']) ?></textarea></div><button class="btn p block sm">Update →</button><?php endif; ?></form>
<?php endforeach; if (!$rows) echo '<div class="card empty" style="grid-column:1/-1">No tickets assigned yet.</div>'; ?></div>
<?php page_end();
