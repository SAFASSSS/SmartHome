<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('admin');
if (is_post()) { csrf_check(); foreach ($_POST['rate'] ?? [] as $item => $v) if (is_numeric($v) && $v >= 0) q('UPDATE utility_rates SET rate=? WHERE item=?', [$v, $item]); flash('Utility rates updated.'); redirect('rates.php'); }
$rows = q('SELECT * FROM utility_rates')->fetchAll();
page_start('Utility Rates', 'rates.php'); ?>
<div class="head"><div><h1>Utility Rates</h1><p>Rates used when managers generate monthly bills</p></div></div>
<form class="card" method="post" style="max-width:520px"><?= csrf_field() ?><?php foreach ($rows as $r): ?><div class="field"><label><?= e($r['label']) ?> ($ <?= e($r['unit']) ?>)</label><input type="number" step="0.01" min="0" name="rate[<?= e($r['item']) ?>]" value="<?= e($r['rate']) ?>"></div><?php endforeach; ?><button class="btn p">Save rates</button></form>
<?php page_end();
