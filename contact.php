<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$flatId = (int)($_GET['flat'] ?? 0); $flat = $flatId ? q('SELECT f.*,b.name bname FROM flats f JOIN buildings b ON b.id=f.building_id WHERE f.id=?', [$flatId])->fetch() : null;
if (is_post()) {
    csrf_check(); $em = trim($_POST['email'] ?? '');
    if (trim($_POST['name'] ?? '') && filter_var($em, FILTER_VALIDATE_EMAIL) && trim($_POST['message'] ?? '')) {
        q('INSERT INTO inquiries (flat_id,name,email,phone,message) VALUES (?,?,?,?,?)', [$flatId ?: null, trim($_POST['name']), $em, trim($_POST['phone'] ?? ''), trim($_POST['message'])]);
        notify_role('manager', 'New flat inquiry from ' . trim($_POST['name']));
        flash('Thanks! Our team will get back to you shortly.'); redirect('contact.php');
    }
    flash('Please fill in name, a valid email and a message.', 'err');
}
page_start('Contact', 'contact.php'); ?>
<div class="head"><div><h1>Contact us</h1><p><?= $flat ? 'Enquiring about ' . e($flat['bname']) . ' · Flat ' . e($flat['unit']) : 'Book a tour or ask us anything' ?></p></div></div>
<form class="card" method="post" style="max-width:640px"><?= csrf_field() ?><div class="row2"><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Phone</label><input name="phone"></div></div>
<div class="field"><label>Email</label><input type="email" name="email" required></div><div class="field"><label>Message</label><textarea name="message" required><?= $flat ? 'I am interested in ' . e($flat['bname']) . ' Flat ' . e($flat['unit']) . '. Please contact me to arrange a tour.' : '' ?></textarea></div>
<button class="btn p"><?= icon('send', 15) ?> Send message</button></form>
<?php page_end();
