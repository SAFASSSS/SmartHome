<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login();
if (is_post()) {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'profile') {
        $n = trim($_POST['name'] ?? '');
        if ($n) { q('UPDATE users SET name=?, phone=? WHERE id=?', [$n, trim($_POST['phone'] ?? ''), $u['id']]); $_SESSION['user']['name'] = $n; flash('Profile saved.'); } else flash('Name is required.', 'err');
    } elseif ($act === 'password') {
        $row = q('SELECT password_hash FROM users WHERE id=?', [$u['id']])->fetch();
        if (!password_verify($_POST['current'] ?? '', $row['password_hash'])) flash('Current password is incorrect.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 8 || $_POST['new'] !== ($_POST['confirm'] ?? '')) flash('New passwords must match and be 8+ characters.', 'err');
        else { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); flash('Password updated.'); }
    } elseif ($act === 'delete' && $u['role'] !== 'admin') { q('DELETE FROM users WHERE id=?', [$u['id']]); $_SESSION = []; session_destroy(); redirect('index.php'); }
    redirect('settings.php');
}
$me = q('SELECT * FROM users WHERE id=?', [$u['id']])->fetch();
page_start('Settings', 'settings.php'); ?>
<div class="head"><div><h1>Settings</h1><p>Manage your account</p></div></div>
<div style="max-width:560px;display:grid;gap:16px">
<form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile"><h3>Profile</h3><div class="field"><label>Name</label><input name="name" value="<?= e($me['name']) ?>" required></div>
<div class="field"><label>Email</label><input value="<?= e($me['email']) ?>" disabled></div><div class="field"><label>Phone</label><input name="phone" value="<?= e($me['phone']) ?>"></div><button class="btn d">Save changes</button></form>
<form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="password"><h3>Security</h3><div class="field"><label>Current password</label><input type="password" name="current" required></div>
<div class="field"><label>New password</label><input type="password" name="new" minlength="8" required></div><div class="field"><label>Confirm new password</label><input type="password" name="confirm" required></div><button class="btn d">Update Password</button></form>
<?php if ($u['role'] !== 'admin'): ?><form class="card" method="post" style="background:#fff5f5;border-color:#fecaca" onsubmit="return confirm('Permanently delete your account?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><h3 style="color:#b91c1c">Danger Zone</h3><p style="color:#b91c1c;margin-bottom:12px">Permanently delete your account and all associated data.</p><button class="btn r">Delete Account</button></form><?php endif; ?></div>
<?php page_end();
