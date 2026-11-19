<?php
require_once 'includes/bootstrap.php';
$s = [
 'flats' => (int)q("SELECT COUNT(*) FROM flats WHERE listing_type<>'occupied'")->fetchColumn(),
 'buildings' => (int)q('SELECT COUNT(*) FROM buildings')->fetchColumn(),
 'residents' => (int)q("SELECT COUNT(*) FROM users WHERE role='resident' AND status='active'")->fetchColumn(),
];
$featured = q("SELECT f.*, b.name bname FROM flats f JOIN buildings b ON b.id=f.building_id WHERE f.listing_type<>'occupied' ORDER BY f.id LIMIT 3")->fetchAll();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartNest · Find Your Dream Flat</title>

<link rel="stylesheet" href="assets/css/app.css"></head>
<body class="land">
<nav class="top">
  <a class="brand" href="index.php"><span class="logo"><?= icon('building') ?></span>SmartNest</a>
  <a class="l" href="index.php">Home</a><a class="l" href="listings.php">Browse Flats</a><a class="l" href="#amenities">Amenities</a><a class="l" href="contact.php">Contact</a>
  <?php if (user()): ?><a class="btn p" href="dashboard.php">Dashboard</a>
  <?php else: ?><a class="l" href="onboarding.php">Login</a><a class="btn p" href="contact.php">Book a Tour</a><?php endif; ?>
</nav>
<section class="hero">
  <span class="pill"><?= $s['flats'] ?> verified listings available now</span>
  <h1>Find Your <em>Dream Flat</em></h1>
  <p class="sub">Discover premium apartments for rent and sale in Greenfield — curated, verified, and move-in ready.</p>
  <form class="searchbox" action="listings.php">
    <div class="grid">
      <div><label>Type</label><select name="type"><option value="">Rent or Sale</option><option value="rent">For Rent</option><option value="sale">For Sale</option></select></div>
      <div><label>Bedrooms</label><select name="beds"><option value="">Any</option><option>1</option><option>2</option><option>3</option></select></div>
      <div><label>Max budget</label><select name="max"><option value="">Any Budget</option><option value="2500">$2,500 / mo</option><option value="4000">$4,000 / mo</option></select></div>
    </div>
    <button class="btn p block" style="margin-top:16px;padding:14px"><?= icon('search', 16) ?> Search Available Flats</button>
  </form>
  <div class="hstats"><span><?= $s['flats'] ?>+ Listings</span><span><?= $s['buildings'] ?> Buildings</span><span><?= $s['residents'] ?>+ Happy Residents</span><span>Verified Properties</span></div>
</section>
<section class="sec" id="amenities">
  <h2>Everything a modern building needs</h2><p>One platform for residents, managers, technicians and admins.</p>
  <div class="grid g3">
    <?php foreach ([['wrench','c-orange','Smart Maintenance','Submit complaints, track technicians and get updates until resolution.'],['card','c-blue','Transparent Billing','Utility bills generated from live rates with instant payment tracking.'],['bell','c-green','Instant Notices','Building-wide announcements and emergency alerts in real time.'],['chat','c-purple','Built-in Chat','Message your manager or technician directly.'],['users','c-indigo','Role-based Access','Tailored dashboards for every role, with admin approvals.'],['chart','c-red','Reports & Insights','Revenue, complaints and performance exportable to CSV.']] as [$i,$c,$t,$d]): ?>
      <div class="card feat"><div class="ico <?= $c ?>"><?= icon($i, 22) ?></div><h3><?= $t ?></h3><p><?= $d ?></p></div>
    <?php endforeach; ?>
  </div>
</section>
<section class="sec" style="padding-top:0"><h2>Featured flats</h2><p>Hand-picked homes ready for you.</p>
  <div class="grid g3"><?php foreach ($featured as $f): ?>
    <div class="card flat"><div class="img"><?= flat_img($f) ?><span class="lbl <?= $f['listing_type'] ?>">For <?= ucfirst($f['listing_type']) ?></span></div>
    <div class="body"><h3><?= e($f['bname']) ?> · Flat <?= e($f['unit']) ?></h3><p class="muted"><?= $f['bedrooms'] ?> bed · <?= number_format($f['area_sqft']) ?> sq ft</p>
    <div class="price"><?= money($f['price']) ?> <small><?= $f['listing_type'] === 'rent' ? '/month' : '' ?></small></div>
    <a class="btn p block" style="margin-top:12px" href="contact.php?flat=<?= $f['id'] ?>">Contact</a></div></div>
  <?php endforeach; ?></div>
</section>
<footer class="foot">© <?= date('Y') ?> SmartNest · Smart Apartment Management System</footer>
</body></html>
