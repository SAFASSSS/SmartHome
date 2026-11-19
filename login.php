<?php require_once 'includes/bootstrap.php';
if (user()) redirect('dashboard.php');
$err = '';
if (is_post()) {
    csrf_check();
    $role = $_POST['role'] ?? ''; $email = trim($_POST['email'] ?? '');
    $u = q('SELECT * FROM users WHERE (email=? OR phone=?) AND role=?', [$email, $email, $role])->fetch();
    if (!$u || !password_verify($_POST['password'] ?? '', $u['password_hash'])) $err = 'Invalid credentials for the selected role.';
    elseif ($u['status'] === 'pending') $err = 'Your account is awaiting admin approval.';
    elseif ($u['status'] === 'rejected') $err = 'Your registration was declined. Please contact management.';
    else {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role'], 'building_id' => $u['building_id']];
        redirect('dashboard.php');
    }
}
$sel = $_POST['role'] ?? 'admin';
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in · SmartNest</title>
<link rel="stylesheet" href="assets/css/app.css"></head>
<body class="auth"><div class="authbox">
<div class="logo"><?= icon('building', 24) ?></div><h1>Welcome back</h1><p>Sign in to SmartNest</p>
<form class="card" method="post"><?= csrf_field() ?>
<?php if ($f = flash()): ?><div class="alert a-ok"><?= e($f[0]) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert a-err"><?= e($err) ?></div><?php endif; ?>
<label>Role</label><div class="roles"><?php foreach (['admin'=>'Admin','manager'=>'Bldg Manager','technician'=>'Technician','resident'=>'Resident'] as $k => $v): ?>
<input type="radio" name="role" id="r-<?= $k ?>" value="<?= $k ?>" <?= $sel === $k ? 'checked' : '' ?>><label for="r-<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div>
<div class="field"><label>Email / Phone</label><input name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="sarah@example.com" required></div>
<div class="field"><label>Password</label><input type="password" name="password" placeholder="••••••••" required></div>
<div class="between"><label style="margin:0;text-transform:none;letter-spacing:0;font-weight:500"><input type="checkbox" style="width:auto"> Remember me</label><a href="contact.php">Forgot password?</a></div>
<button class="btn p block" style="padding:13px">Sign In</button>
<a class="btn block" style="margin-top:10px" href="register.php">Create Account</a></form>
<a class="muted" style="display:block;margin-top:18px" href="home.php">Continue as Guest →</a>
</div></body></html>
