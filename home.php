<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$flats = q("SELECT f.*, b.name bname FROM flats f JOIN buildings b ON b.id=f.building_id WHERE f.listing_type<>'occupied' ORDER BY f.id LIMIT 4")->fetchAll();
$facs = [['car','Parking','c-blue'],['elevator','Elevator','c-purple'],['lock','Security','c-red'],['zap','Generator','c-orange'],['dumbbell','Gym','c-green'],['roof','Rooftop','c-green']];
page_start('SmartNest', 'home.php'); ?>
<div class="banner"><h1>Welcome to SmartNest</h1><p>Premium apartments with modern amenities in Greenfield.</p><a class="btn w" href="listings.php">Browse Listings →</a></div>
<h3 class="sech">Community Facilities</h3>
<div class="facs"><?php foreach ($facs as [$i, $n, $c]): ?><div class="fac <?= $c ?>"><span><?= icon($i, 20) ?></span><b><?= $n ?></b></div><?php endforeach; ?></div>
<div style="display:flex;justify-content:space-between;align-items:center;margin:26px 0 12px"><h3 class="sech" style="margin:0">Available Flats</h3><a href="listings.php" style="color:var(--pri);font-weight:700">View all →</a></div>
<div class="grid g2"><?php foreach ($flats as $f): ?>
<div class="card flat"><div class="img"><?= flat_img($f) ?><span class="lbl <?= $f['listing_type'] ?>">For <?= ucfirst($f['listing_type']) ?></span></div>
<div class="body"><h3 style="margin-bottom:2px"><?= e($f['bname']) ?> · Flat <?= e($f['unit']) ?></h3><p class="muted"><?= $f['bedrooms'] ?> bed · <?= number_format($f['area_sqft']) ?> sq ft</p>
<div class="price" style="margin-top:6px"><?= money($f['price']) ?> <small><?= $f['listing_type'] === 'rent' ? '/month' : '' ?></small></div>
<div class="chips"><?php foreach (array_filter(array_map('trim', explode(',', (string)$f['amenities']))) as $am): ?><span><?= e($am) ?></span><?php endforeach; ?></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px"><a class="btn" href="listings.php">View Details</a><a class="btn p" href="contact.php?flat=<?= $f['id'] ?>">Contact</a></div></div></div>
<?php endforeach; if (!$flats) echo '<div class="card empty" style="grid-column:1/-1">No listings yet.</div>'; ?></div>
<?php if (!user()): ?><div class="card" style="margin-top:22px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap"><div><b>Already a resident or staff member?</b><br><span class="muted">Sign in to manage complaints, bills and notices.</span></div><a class="btn p" href="login.php">Sign In</a></div><?php endif; ?>
<?php page_end();
