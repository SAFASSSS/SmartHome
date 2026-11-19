<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('resident', 'manager'); $isRes = $u['role'] === 'resident';
$rates = q('SELECT item,rate FROM utility_rates')->fetchAll(PDO::FETCH_KEY_PAIR);
$f = fn($k) => max(0, (float)($_POST[$k] ?? 0));
if (is_post()) {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'pay' && $isRes) {
        $n = q("UPDATE bills SET status='paid', paid_at=NOW() WHERE id=? AND resident_id=? AND status='pending'", [(int)$_POST['id'], $u['id']])->rowCount();
        if ($n) { notify_role('manager', "Payment received from {$u['name']}"); flash('Payment recorded. Thank you!'); }
    } elseif ($act === 'generate' && !$isRes) {
        $el = $f('electricity') * $rates['electricity']; $wa = $f('water') * $rates['water']; $ga = $f('gas') * $rates['gas'];
        $sv = $f('service'); $mt = $f('maintenance'); $ot = $f('other'); $tot = $el + $wa + $ga + $sv + $mt + $ot; $month = date('Y-m');
        $targets = q("SELECT id FROM users WHERE role='resident' AND status='active'" . ($_POST['building_id'] ? ' AND building_id=' . (int)$_POST['building_id'] : ''))->fetchAll();
        foreach ($targets as $t) {
            q('INSERT INTO bills (resident_id,month,electricity,water,gas,service,maintenance,other,total) VALUES (?,?,?,?,?,?,?,?,?)', [$t['id'], $month, $el, $wa, $ga, $sv, $mt, $ot, $tot]);
            notify((int)$t['id'], date('F Y') . ' utility bill is ready. Amount: ' . money($tot));
        }
        flash('Generated and sent ' . count($targets) . ' bill(s).');
    }
    redirect('billing.php' . ($isRes ? '' : '?tab=' . ($act === 'generate' ? 'track' : 'track')));
}
$tab = $_GET['tab'] ?? 'gen';
page_start('Billing', 'billing.php');
if ($isRes): $bills = q('SELECT * FROM bills WHERE resident_id=? ORDER BY created_at DESC', [$u['id']])->fetchAll(); ?>
<div class="head"><div><h1>My Bills</h1><p>Utility statements and payments</p></div></div>
<div class="grid g2"><?php foreach ($bills as $b): ?><div class="card"><div style="display:flex;justify-content:space-between"><h3><?= date('F Y', strtotime($b['month'] . '-01')) ?></h3><?= badge($b['status']) ?></div>
<?php foreach (['electricity','water','gas','service','maintenance','other'] as $k): if ($b[$k] > 0): ?><div class="sum"><span><?= ucfirst($k) ?></span><span><?= money($b[$k]) ?></span></div><?php endif; endforeach; ?>
<div class="sum t"><span>Total</span><span><?= money($b['total']) ?></span></div>
<?php if ($b['status'] === 'pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="pay"><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn p block" style="margin-top:10px">Pay <?= money($b['total']) ?></button></form><?php endif; ?></div>
<?php endforeach; if (!$bills) echo '<div class="card empty" style="grid-column:1/-1">No bills yet.</div>'; ?></div>
<?php else: $blds = q('SELECT id,name FROM buildings')->fetchAll(); $all = q('SELECT b.*, u.name, u.unit FROM bills b JOIN users u ON u.id=b.resident_id ORDER BY b.created_at DESC LIMIT 100')->fetchAll(); ?>
<div class="head"><div><h1>Monthly Billing</h1><p>Generate and track utility bills</p></div><a class="btn g" href="reports.php?export=revenue"><?= icon('download', 15) ?> Download Report</a></div>
<div class="tabs"><a href="?tab=gen" class="<?= $tab === 'gen' ? 'on' : '' ?>">Generate Bills</a><a href="?tab=track" class="<?= $tab === 'track' ? 'on' : '' ?>">Payment Tracking</a></div>
<?php if ($tab === 'gen'): ?>
<div class="grid g21"><form class="card" method="post" id="bf"><?= csrf_field() ?><input type="hidden" name="action" value="generate"><h3>Generate Monthly Bill</h3>
<div class="row2"><div class="field"><label>Electricity (units)</label><input type="number" step="any" min="0" name="electricity" value="140" data-rate="<?= $rates['electricity'] ?>"></div><div class="field"><label>Water (m³)</label><input type="number" step="any" min="0" name="water" value="28" data-rate="<?= $rates['water'] ?>"></div>
<div class="field"><label>Gas (m³)</label><input type="number" step="any" min="0" name="gas" value="18" data-rate="<?= $rates['gas'] ?>"></div><div class="field"><label>Service charge</label><input type="number" step="any" min="0" name="service" value="1500" data-rate="1"></div>
<div class="field"><label>Maintenance fee</label><input type="number" step="any" min="0" name="maintenance" value="200" data-rate="1"></div><div class="field"><label>Other charges</label><input type="number" step="any" min="0" name="other" value="50" data-rate="1"></div></div>
<div class="field"><label>Target</label><select name="building_id"><option value="">All buildings</option><?php foreach ($blds as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
<button class="btn p block" onclick="return confirm('Send bills to all selected residents?')">Generate &amp; Send Bills</button></form>
<div class="card"><h3>Bill Summary Preview</h3><div id="pv"></div><div class="sum t"><span>Total</span><span id="tot">$0</span></div></div></div>
<?php else: ?><div class="card tbl"><table><tr><th>Resident</th><th>Month</th><th>Total</th><th>Status</th><th>Paid on</th></tr>
<?php foreach ($all as $b): ?><tr><td><?= e($b['name']) ?><?= $b['unit'] ? ' · ' . e($b['unit']) : '' ?></td><td><?= e($b['month']) ?></td><td><b><?= money($b['total']) ?></b></td><td><?= badge($b['status']) ?></td><td class="muted"><?= $b['paid_at'] ? date('M j, Y', strtotime($b['paid_at'])) : '—' ?></td></tr><?php endforeach; if (!$all) echo '<tr><td colspan="5" class="empty">No bills generated yet.</td></tr>'; ?></table></div><?php endif; endif;
page_end();
