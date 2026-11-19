<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('admin', 'manager');
$sets = [
 'complaints' => ['Complaint Summary', 'Status and priority by category', 'clipboard', 'c-red', ['Category','Total','Open','Resolved','Urgent'], "SELECT category, COUNT(*), SUM(status<>'resolved'), SUM(status='resolved'), SUM(priority='urgent') FROM complaints GROUP BY category"],
 'maintenance' => ['Maintenance Performance', 'Technician performance and task completion', 'wrench', 'c-orange', ['Technician','Assigned','Resolved'], "SELECT u.name, COUNT(c.id), SUM(c.status='resolved') FROM users u LEFT JOIN complaints c ON c.technician_id=u.id WHERE u.role='technician' GROUP BY u.id"],
 'revenue' => ['Revenue Collection', 'Monthly income statements and payment trends', 'dollar', 'c-purple', ['Month','Billed','Collected','Pending'], "SELECT month, SUM(total), SUM(CASE WHEN status='paid' THEN total ELSE 0 END), SUM(CASE WHEN status='pending' THEN total ELSE 0 END) FROM bills GROUP BY month ORDER BY month"],
 'residents' => ['Resident Directory', 'Active residents by building', 'users', 'c-blue', ['Name','Email','Phone','Building','Flat'], "SELECT u.name,u.email,u.phone,b.name,u.unit FROM users u LEFT JOIN buildings b ON b.id=u.building_id WHERE u.role='resident' AND u.status='active' ORDER BY b.name,u.unit"],
];
$data = fn($k) => q($sets[$k][5])->fetchAll(PDO::FETCH_NUM);
if (isset($_GET['export'], $sets[$_GET['export']])) {
    $k = $_GET['export']; header('Content-Type: text/csv'); header("Content-Disposition: attachment; filename=smartnest-$k-" . date('Ymd') . '.csv');
    $o = fopen('php://output', 'w'); fputcsv($o, $sets[$k][4]);
    foreach ($data($k) as $r) fputcsv($o, array_map(fn($c) => preg_match('/^[=+\-@]/', (string)$c) ? "'" . $c : $c, $r)); exit;
}
$prev = isset($_GET['preview'], $sets[$_GET['preview']]) ? $_GET['preview'] : null;
$months = months_back(7);
$bm = q("SELECT month, SUM(CASE WHEN status='paid' THEN total ELSE 0 END) c, SUM(CASE WHEN status='pending' THEN total ELSE 0 END) p FROM bills GROUP BY month")->fetchAll(PDO::FETCH_UNIQUE);
$revCfg = ['prefix' => '$', 'labels' => array_values($months), 'series' => [
    ['name' => 'Collected', 'color' => '#10b981', 'values' => array_map(fn($k) => (float)($bm[$k]['c'] ?? 0), array_keys($months))],
    ['name' => 'Pending', 'color' => '#f59e0b', 'values' => array_map(fn($k) => (float)($bm[$k]['p'] ?? 0), array_keys($months))]]];
page_start('Reports', 'reports.php'); ?>
<div class="head"><div><h1>Reports</h1><p>Preview, print or export as CSV</p></div></div>
<div class="grid g2"><?php foreach ($sets as $k => $s): ?><div class="card"><span class="avatar <?= $s[3] ?>" style="border-radius:12px;margin-bottom:12px"><?= icon($s[2], 18) ?></span><h3 style="margin:0"><?= $s[0] ?></h3><p class="muted" style="margin-bottom:14px"><?= $s[1] ?></p>
<a class="btn sm" href="?preview=<?= $k ?>">Preview</a> <button class="btn sm p" onclick="window.print()">Export PDF</button> <a class="btn sm" href="?export=<?= $k ?>">CSV</a></div><?php endforeach; ?></div>
<?php if ($prev): ?><div class="card tbl" style="margin-top:16px"><h3><?= $sets[$prev][0] ?></h3><table><tr><?php foreach ($sets[$prev][4] as $h): ?><th><?= $h ?></th><?php endforeach; ?></tr><?php foreach ($data($prev) as $r): ?><tr><?php foreach ($r as $c): ?><td><?= e($c) ?></td><?php endforeach; ?></tr><?php endforeach; ?></table></div><?php endif; ?>
<div class="card" style="margin-top:16px"><h3>Revenue Collection — Last 7 Months</h3><div data-chart="<?= e(json_encode($revCfg)) ?>"></div></div>
<?php page_end();
