<?php require_once 'includes/bootstrap.php';
if (!isset($_GET['force']) && !empty($_COOKIE['sn_onboarded'])) redirect('login.php');
setcookie('sn_onboarded', '1', time() + 30 * 86400, '/');
$slides = [['clipboard','Maintenance Made Simple','Submit and track maintenance complaints in real-time. Get updates at every step from submission to resolution.'],
['card','Billing, Clearly','Monthly utility bills with a full breakdown, and one-tap payment tracking.'],['bell','Stay in the Loop','Building notices, emergency alerts and direct chat with your manager.']];
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Welcome · SmartNest</title>
<link rel="stylesheet" href="assets/css/app.css"></head>
<body class="auth"><div class="authbox" id="ob">
<?php foreach ($slides as $i => [$ic,$t,$d]): ?>
<div class="slide" style="<?= $i ? 'display:none' : '' ?>"><div class="logo" style="width:68px;height:68px;border-radius:20px"><?= icon($ic, 30) ?></div>
<h1 style="font-size:24px"><?= $t ?></h1><p style="max-width:380px;margin:10px auto 0"><?= $d ?></p></div>
<?php endforeach; ?>
<div style="display:flex;gap:6px;justify-content:center;margin:26px 0" id="dots"><?php foreach ($slides as $i => $_): ?><i style="height:4px;width:<?= $i ? 14 : 30 ?>px;border-radius:9px;background:<?= $i ? '#d5dbe8' : '#0b4dff' ?>;transition:.2s"></i><?php endforeach; ?></div>
<div style="display:flex;gap:18px;justify-content:center;align-items:center"><a class="muted" href="login.php">Skip</a><button class="btn p" id="next">Next →</button></div></div>
<script>
let i=0;const s=document.querySelectorAll('.slide'),d=document.querySelectorAll('#dots i');
document.getElementById('next').onclick=()=>{ if(i==s.length-1){location='login.php';return}
 s[i].style.display='none';d[i].style.cssText+='width:14px;background:#d5dbe8';i++;s[i].style.display='';d[i].style.cssText+='width:30px;background:#0b4dff';
 if(i==s.length-1)document.getElementById('next').textContent='Get Started →';};
</script></body></html>
