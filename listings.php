<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$type = $_GET['type'] ?? ''; $beds = (int)($_GET['beds'] ?? 0); $max = (int)($_GET['max'] ?? 0); $kw = trim($_GET['q'] ?? '');
$sql = "SELECT f.*, b.name bname FROM flats f JOIN buildings b ON b.id=f.building_id WHERE f.listing_type<>'occupied'"; $a = [];
if (in_array($type, ['rent','sale'], true)) { $sql .= ' AND f.listing_type=?'; $a[] = $type; }
if ($beds) { $sql .= ' AND f.bedrooms=?'; $a[] = $beds; }
if ($max) { $sql .= ' AND f.price<=?'; $a[] = $max; }
if ($kw !== '') { $sql .= ' AND (b.name LIKE ? OR f.unit LIKE ? OR f.amenities LIKE ?)'; array_push($a, "%$kw%", "%$kw%", "%$kw%"); }
$flats = q($sql . ' ORDER BY f.price', $a)->fetchAll();
page_start('Available Flats', 'listings.php'); ?>
<div class="head"><div><h1>Available Flats</h1><p><?= count($flats) ?> properties listed</p></div></div>
<form class="card filters" method="get"><div><label>Type</label><select name="type"><option value="">All</option><option value="rent" <?= $type === 'rent' ? 'selected' : '' ?>>For Rent</option><option value="sale" <?= $type === 'sale' ? 'selected' : '' ?>>For Sale</option></select></div>
<div><label>Bedrooms</label><select name="beds"><option value="">Any</option><?php for ($i = 1; $i <= 4; $i++): ?><option <?= $beds === $i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></div>
<div style="flex:1"><label>Max price: <b id="mv"><?= $max ? money($max) : 'any' ?></b></label><input type="range" name="max" min="0" max="500000" step="500" value="<?= $max ?>" oninput="mv.textContent=this.value>0?'$'+(+this.value).toLocaleString():'any'"></div>
<button class="btn p"><?= icon('search', 15) ?> Apply</button></form>
<div class="grid g3"><?php foreach ($flats as $f): ?>
<div class="card flat"><div class="img"><?= flat_img($f) ?><span class="lbl <?= $f['listing_type'] ?>">For <?= ucfirst($f['listing_type']) ?></span></div>
<div class="body"><h3 style="margin-bottom:2px"><?= e($f['bname']) ?> · Flat <?= e($f['unit']) ?></h3><p class="muted"><?= icon('pin', 13) ?> <?= $f['bedrooms'] ?> bed · <?= number_format($f['area_sqft']) ?> sq ft</p>
<div class="price" style="margin-top:8px"><?= money($f['price']) ?> <small><?= $f['listing_type'] === 'rent' ? '/month' : '' ?></small></div>
<div class="chips"><?php foreach (array_filter(array_map('trim', explode(',', (string)$f['amenities']))) as $am): ?><span><?= e($am) ?></span><?php endforeach; ?></div>
<a class="btn p block" href="contact.php?flat=<?= $f['id'] ?>">Contact</a></div></div>
<?php endforeach; if (!$flats): ?><div class="card empty" style="grid-column:1/-1">No flats match your filters.</div><?php endif; ?></div>
<?php page_end();
