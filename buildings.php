<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('admin');
if (is_post()) {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'building' && trim($_POST['name'] ?? '')) {
        q('INSERT INTO buildings (name,address,floors,manager_id) VALUES (?,?,?,?)', [trim($_POST['name']), trim($_POST['address'] ?? ''), max(1, (int)$_POST['floors']), $_POST['manager_id'] ?: null]); flash('Building added.');
    } elseif ($act === 'flat') {
        [$photo, $perr] = save_flat_photo();
        if ($perr) flash($perr, 'err');
        else { q('INSERT INTO flats (building_id,unit,bedrooms,area_sqft,listing_type,price,amenities,photo) VALUES (?,?,?,?,?,?,?,?)',
          [(int)$_POST['building_id'], trim($_POST['unit']), (int)$_POST['bedrooms'], (int)$_POST['area'], in_array($_POST['type'], ['rent','sale','occupied'], true) ? $_POST['type'] : 'rent', (float)$_POST['price'], trim($_POST['amenities'] ?? ''), $photo]); flash('Flat added.'); }
    } elseif ($act === 'photo') {
        $id = (int)$_POST['id']; $old = q('SELECT photo FROM flats WHERE id=?', [$id])->fetchColumn();
        if (!empty($_POST['remove'])) { delete_photo($old ?: null); q('UPDATE flats SET photo=NULL WHERE id=?', [$id]); flash('Photo removed.'); }
        else { [$photo, $perr] = save_flat_photo();
            if ($perr) flash($perr, 'err'); elseif ($photo) { delete_photo($old ?: null); q('UPDATE flats SET photo=? WHERE id=?', [$photo, $id]); flash('Photo updated.'); } }
    } elseif ($act === 'del_flat') {
        $id = (int)$_POST['id']; delete_photo(q('SELECT photo FROM flats WHERE id=?', [$id])->fetchColumn() ?: null);
        q('DELETE FROM flats WHERE id=?', [$id]); flash('Flat removed.');
    }
    redirect('buildings.php');
}
$mgrs = q("SELECT id,name FROM users WHERE role='manager' AND status='active'")->fetchAll();
$blds = q("SELECT b.*, u.name mname, (SELECT COUNT(*) FROM flats WHERE building_id=b.id) nf, (SELECT COUNT(*) FROM users WHERE building_id=b.id AND role='resident' AND status='active') nr FROM buildings b LEFT JOIN users u ON u.id=b.manager_id ORDER BY b.name")->fetchAll();
$flats = q('SELECT f.*, b.name bname FROM flats f JOIN buildings b ON b.id=f.building_id ORDER BY b.name, f.unit')->fetchAll();
page_start('Buildings', 'buildings.php'); ?>
<div class="head"><div><h1>Buildings &amp; Flats</h1><p>Manage properties, listings and photos</p></div></div>
<div class="grid g2" style="margin-bottom:18px">
<form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="building"><h3>Add building</h3><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Address</label><input name="address" required></div>
<div class="row2"><div class="field"><label>Floors</label><input type="number" name="floors" value="5" min="1"></div><div class="field"><label>Manager</label><select name="manager_id"><option value="">—</option><?php foreach ($mgrs as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['name']) ?></option><?php endforeach; ?></select></div></div><button class="btn p">Add building</button></form>
<form class="card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="flat"><h3>Add flat / listing</h3><div class="row2"><div class="field"><label>Building</label><select name="building_id"><?php foreach ($blds as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Unit</label><input name="unit" placeholder="3A" required></div>
<div class="field"><label>Bedrooms</label><input type="number" name="bedrooms" value="2" min="1"></div><div class="field"><label>Area (sq ft)</label><input type="number" name="area" value="900"></div>
<div class="field"><label>Status</label><select name="type"><option value="rent">For rent</option><option value="sale">For sale</option><option value="occupied">Occupied</option></select></div><div class="field"><label>Price</label><input type="number" name="price" value="2000"></div></div>
<div class="field"><label>Amenities (comma separated)</label><input name="amenities" placeholder="Parking, Gym"></div>
<div class="field"><label>Photo (JPG, PNG or WebP · max 5 MB)</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div><button class="btn p">Add flat</button></form></div>
<div class="card tbl" style="margin-bottom:18px"><h3>Buildings</h3><table><tr><th>Name</th><th>Address</th><th>Floors</th><th>Flats</th><th>Residents</th><th>Manager</th></tr><?php foreach ($blds as $b): ?><tr><td><b><?= e($b['name']) ?></b></td><td><?= e($b['address']) ?></td><td><?= $b['floors'] ?></td><td><?= $b['nf'] ?></td><td><?= $b['nr'] ?></td><td><?= e($b['mname'] ?? '—') ?></td></tr><?php endforeach; ?></table></div>
<div class="card tbl"><h3>Flats</h3><table><tr><th>Photo</th><th>Flat</th><th>Beds</th><th>Area</th><th>Status</th><th>Price</th><th></th></tr><?php foreach ($flats as $f): ?><tr>
<td><?php if ($f['photo']): ?><img class="thumb" src="<?= e($f['photo']) ?>" alt=""><?php else: ?><span class="thumb" style="display:grid;place-items:center;color:#94a3b8"><?= icon('home', 18) ?></span><?php endif; ?></td>
<td><?= e($f['bname']) ?> · <?= e($f['unit']) ?></td><td><?= $f['bedrooms'] ?></td><td><?= number_format($f['area_sqft']) ?> ft²</td><td><?= badge($f['listing_type'] === 'occupied' ? 'normal' : ($f['listing_type'] === 'rent' ? 'resolved' : 'in_progress')) ?> <?= $f['listing_type'] ?></td><td><?= money($f['price']) ?></td>
<td><div style="display:flex;gap:6px;align-items:center">
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="photo"><input type="hidden" name="id" value="<?= $f['id'] ?>"><span class="btn sm filebtn"><?= icon('plus', 13) ?> <?= $f['photo'] ? 'Change' : 'Upload' ?><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()"></span></form>
<?php if ($f['photo']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="photo"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button class="btn sm" name="remove" value="1" title="Remove photo"><?= icon('x', 13) ?></button></form><?php endif; ?>
<form method="post" onsubmit="return confirm('Remove flat?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_flat"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button class="btn sm r"><?= icon('trash', 14) ?></button></form></div></td></tr><?php endforeach; ?></table></div>
<?php page_end();
