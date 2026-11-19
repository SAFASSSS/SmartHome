<?php require_once 'includes/bootstrap.php';
$err = ''; $blds = q('SELECT id,name FROM buildings ORDER BY name')->fetchAll();
if (is_post()) {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pw = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['resident','technician','manager'], true) ? $_POST['role'] : 'resident';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter your name and a valid email.';
    elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
    elseif (q('SELECT id FROM users WHERE email=?', [$email])->fetch()) $err = 'That email is already registered.';
    else {
        q('INSERT INTO users (name,email,phone,password_hash,role,status,building_id,unit,specialty) VALUES (?,?,?,?,?,?,?,?,?)',
          [$name, $email, trim($_POST['phone'] ?? ''), password_hash($pw, PASSWORD_DEFAULT), $role, 'pending', $_POST['building_id'] ?: null, trim($_POST['unit'] ?? '') ?: null, trim($_POST['specialty'] ?? '') ?: null]);
        notify_role('admin', "New $role registration awaiting approval: $name");
        flash('Account created! An administrator will approve it shortly.');
        redirect('login.php');
    }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create account · SmartNest</title>
<link rel="stylesheet" href="assets/css/app.css"></head>
<body class="auth"><div class="authbox">
<div class="logo"><?= icon('building', 24) ?></div><h1>Create account</h1><p>Join SmartNest — admin approval required</p>
<form class="card" method="post"><?= csrf_field() ?>
<?php if ($err): ?><div class="alert a-err"><?= e($err) ?></div><?php endif; ?>
<div class="field"><label>Full name</label><input name="name" value="<?= e($_POST['name'] ?? '') ?>" required></div>
<div class="row2"><div class="field"><label>Email</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div><div class="field"><label>Phone</label><input name="phone"></div></div>
<div class="row2"><div class="field"><label>I am a</label><select name="role"><option value="resident">Resident</option><option value="technician">Technician</option><option value="manager">Building Manager</option></select></div>
<div class="field"><label>Building</label><select name="building_id"><option value="">—</option><?php foreach ($blds as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div></div>
<div class="row2"><div class="field"><label>Flat no. (residents)</label><input name="unit" placeholder="4B"></div><div class="field"><label>Specialty (technicians)</label><input name="specialty" placeholder="Plumbing"></div></div>
<div class="field"><label>Password</label><input type="password" name="password" minlength="8" required></div>
<button class="btn p block" style="padding:13px">Create Account</button></form>
<a class="muted" style="display:block;margin-top:18px" href="login.php">← Back to sign in</a></div></body></html>
