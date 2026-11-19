<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login(); $role = $u['role']; $uid = $u['id'];
function stat_card($ic, $c, $val, $label, $tag = '') { return '<div class="card stat"><div class="top"><span class="ico ' . $c . '">' . icon($ic, 19) . '</span>' . ($tag ? '<span class="tag">' . $tag . '</span>' : '') . '</div><b>' . $val . '</b><small>' . $label . '</small></div>'; }
$cnt = fn($sql, $a = []) => (int)q($sql, $a)->fetchColumn();
$month = date('Y-m');
page_start('Dashboard', 'dashboard.php');

if ($role === 'admin' || $role === 'manager'):
    $open = $cnt("SELECT COUNT(*) FROM complaints WHERE status<>'resolved'");
    $resolved = $cnt("SELECT COUNT(*) FROM complaints WHERE status='resolved' AND DATE_FORMAT(updated_at,'%Y-%m')=?", [$month]);
    $rev = (float)q("SELECT COALESCE(SUM(total),0) FROM bills WHERE status='paid' AND month=?", [$month])->fetchColumn();
    $billed = (float)q('SELECT COALESCE(SUM(total),0) FROM bills WHERE month=?', [$month])->fetchColumn();
    $rate = $billed ? round($rev / $billed * 100, 1) : 0;
    $months = months_back(7); $from = array_key_first($months) . '-01';
    $recv = q("SELECT DATE_FORMAT(created_at,'%Y-%m') ym, COUNT(*) FROM complaints WHERE created_at >= ? GROUP BY ym", [$from])->fetchAll(PDO::FETCH_KEY_PAIR);
    $solv = q("SELECT DATE_FORMAT(updated_at,'%Y-%m') ym, COUNT(*) FROM complaints WHERE status='resolved' AND updated_at >= ? GROUP BY ym", [$from])->fetchAll(PDO::FETCH_KEY_PAIR);
    $trendCfg = ['labels' => array_values($months), 'series' => [
        ['name' => 'Received', 'color' => '#ef4444', 'values' => array_map(fn($k) => (int)($recv[$k] ?? 0), array_keys($months))],
        ['name' => 'Resolved', 'color' => '#10b981', 'values' => array_map(fn($k) => (int)($solv[$k] ?? 0), array_keys($months))]]];
    $prio = q("SELECT priority, COUNT(*) FROM complaints WHERE status<>'resolved' GROUP BY priority")->fetchAll(PDO::FETCH_KEY_PAIR);
    $donutCfg = ['center' => 'Open', 'items' => [
        ['label' => 'Urgent', 'value' => (int)($prio['urgent'] ?? 0), 'color' => '#ef4444'],
        ['label' => 'Normal', 'value' => (int)($prio['normal'] ?? 0), 'color' => '#0b4dff'],
        ['label' => 'Low', 'value' => (int)($prio['low'] ?? 0), 'color' => '#10b981']]];
    $recent = q('SELECT c.*, u.name FROM complaints c JOIN users u ON u.id=c.resident_id ORDER BY c.created_at DESC LIMIT 5')->fetchAll(); ?>
  <div class="head"><div><h1><?= $role === 'admin' ? 'System Overview' : 'Manager Overview' ?></h1><p><?= date('F Y') ?> — All Buildings</p></div><a class="btn p" href="reports.php"><?= icon('download', 15) ?> Export Report</a></div>
  <div class="grid g4" style="margin-bottom:16px">
    <?= stat_card('building', 'c-blue', $cnt('SELECT COUNT(*) FROM buildings'), 'Total Buildings') ?>
    <?= stat_card('users', 'c-green', $cnt("SELECT COUNT(*) FROM users WHERE role='resident' AND status='active'"), 'Total Residents') ?>
    <?= stat_card('users', 'c-purple', $cnt("SELECT COUNT(*) FROM users WHERE role='manager' AND status='active'"), 'Managers') ?>
    <?= stat_card('wrench', 'c-orange', $cnt("SELECT COUNT(*) FROM users WHERE role='technician' AND status='active'"), 'Technicians') ?>
    <?= stat_card('alert', 'c-red', $open, 'Open Complaints') ?>
    <?= stat_card('check-c', 'c-green', $resolved, 'Resolved (' . date('M') . ')') ?>
    <?= stat_card('dollar', 'c-blue', money($rev), 'Monthly Revenue') ?>
    <?= stat_card('trend', 'c-indigo', $rate . '%', 'Collection Rate') ?>
  </div>
  <div class="grid g21"><div class="card"><h3>Complaint Resolution Trends</h3><div class="chart" data-chart="<?= e(json_encode($trendCfg)) ?>"></div></div>
  <div class="card"><h3>Priority Distribution</h3><div data-donut="<?= e(json_encode($donutCfg)) ?>"></div></div></div>
  <div class="card tbl" style="margin-top:16px"><h3>Recent complaints</h3><table><tr><th>Ticket</th><th>Resident</th><th>Category</th><th>Priority</th><th>Status</th><th>Filed</th></tr>
  <?php foreach ($recent as $c): ?><tr><td class="mono"><?= code($c['id']) ?></td><td><?= e($c['name']) ?></td><td><?= e($c['category']) ?></td><td><?= badge($c['priority']) ?></td><td><?= badge($c['status']) ?></td><td class="muted"><?= ago($c['created_at']) ?></td></tr><?php endforeach; ?></table></div>

<?php elseif ($role === 'technician'):
    $mine = q("SELECT * FROM complaints WHERE technician_id=? AND status<>'resolved' ORDER BY priority='urgent' DESC, created_at", [$uid])->fetchAll(); ?>
  <div class="head"><div><h1>Hello, <?= e(explode(' ', $u['name'])[0]) ?></h1><p>Your work queue</p></div><a class="btn p" href="tickets.php">Open tickets</a></div>
  <div class="grid g3" style="margin-bottom:16px">
    <?= stat_card('wrench', 'c-orange', count($mine), 'Active tickets') ?>
    <?= stat_card('alert', 'c-red', $cnt("SELECT COUNT(*) FROM complaints WHERE technician_id=? AND priority='urgent' AND status<>'resolved'", [$uid]), 'Urgent') ?>
    <?= stat_card('check-c', 'c-green', $cnt("SELECT COUNT(*) FROM complaints WHERE technician_id=? AND status='resolved'", [$uid]), 'Completed') ?></div>
  <div class="card list"><h3>Assigned to you</h3><?php foreach ($mine as $c): ?><div class="it"><div><span class="mono"><?= code($c['id']) ?></span> <?= e($c['category']) ?><br><small><?= e(trimw($c['description'], 90, '…')) ?></small></div><div><?= badge($c['priority']) ?> <?= badge($c['status']) ?></div></div><?php endforeach; if (!$mine) echo '<p class="empty">Nothing assigned — nice work.</p>'; ?></div>

<?php else: // resident
    $mine = q('SELECT * FROM complaints WHERE resident_id=? ORDER BY created_at DESC LIMIT 4', [$uid])->fetchAll();
    $notices = q('SELECT * FROM notices ORDER BY created_at DESC LIMIT 4')->fetchAll(); ?>
  <div class="hero-band"><h1>Welcome home, <?= e(explode(' ', $u['name'])[0]) ?></h1><p>Everything for your flat in one place.</p>
    <a class="btn" href="complaints.php">Submit Complaint</a> <a class="btn w" href="billing.php">Pay Bill</a> <a class="btn" href="chat.php">Contact Manager</a></div>
  <div class="grid g4" style="margin-bottom:16px">
    <?= stat_card('clipboard', 'c-red', $cnt("SELECT COUNT(*) FROM complaints WHERE resident_id=? AND status<>'resolved'", [$uid]), 'Active Complaints') ?>
    <?= stat_card('card', 'c-orange', $cnt("SELECT COUNT(*) FROM bills WHERE resident_id=? AND status='pending'", [$uid]), 'Pending Bills') ?>
    <?= stat_card('check-c', 'c-green', $cnt("SELECT COUNT(*) FROM bills WHERE resident_id=? AND status='paid'", [$uid]), 'Paid Bills') ?>
    <?= stat_card('bell', 'c-blue', $cnt('SELECT COUNT(*) FROM notices WHERE created_at > DATE_SUB(NOW(), INTERVAL 14 DAY)'), 'New Notices') ?></div>
  <div class="grid g2"><div class="card list"><h3>My Complaints <a href="complaints.php" style="float:right;font-size:12px;color:var(--pri)">View all</a></h3>
    <?php foreach ($mine as $c): ?><div class="it"><div><span class="mono"><?= code($c['id']) ?></span><br><?= e(trimw($c['description'], 60, '…')) ?><br><?= badge($c['priority']) ?> <small><?= date('M j', strtotime($c['created_at'])) ?></small></div><div><?= badge($c['status']) ?></div></div><?php endforeach; if (!$mine) echo '<p class="empty">No complaints yet.</p>'; ?></div>
  <div class="card list"><h3>Latest Notices <a href="notices.php" style="float:right;font-size:12px;color:var(--pri)">View all</a></h3>
    <?php foreach ($notices as $n): ?><div class="it"><div><?= e($n['title']) ?><br><small><?= date('M j, Y', strtotime($n['created_at'])) ?></small></div><?= badge($n['type']) ?></div><?php endforeach; ?></div></div>
<?php endif; page_end();