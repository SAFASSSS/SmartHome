<?php require_once 'includes/bootstrap.php'; require_once 'includes/layout.php';
$u = require_login('resident', 'manager', 'technician'); $me = $u['id'];
$contacts = q("SELECT u.id,u.name,u.role,
  (SELECT body FROM messages m WHERE (m.sender_id=u.id AND m.receiver_id=:m) OR (m.sender_id=:m2 AND m.receiver_id=u.id) ORDER BY m.id DESC LIMIT 1) last_msg,
  (SELECT COUNT(*) FROM messages m WHERE m.sender_id=u.id AND m.receiver_id=:m3 AND m.is_read=0) unread
  FROM users u WHERE u.status='active' AND u.role IN ('resident','manager','technician') AND u.id<>:m4 ORDER BY (unread>0) DESC, u.name", ['m' => $me, 'm2' => $me, 'm3' => $me, 'm4' => $me])->fetchAll();
$with = (int)($_GET['with'] ?? ($contacts[0]['id'] ?? 0)); $peer = null;
foreach ($contacts as $c) if ($c['id'] == $with) $peer = $c;
page_start('Chat', 'chat.php'); ?>
<div class="chat"><div class="card side"><?php foreach ($contacts as $c): ?><a class="contact <?= $c['id'] == $with ? 'on' : '' ?>" href="?with=<?= $c['id'] ?>"><span class="avatar r-<?= $c['role'] ?>"><?= e(strtoupper($c['name'][0])) ?></span>
<div style="flex:1;min-width:0"><b><?= e($c['name']) ?></b><br><small class="muted" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block"><?= e($c['last_msg'] ?? ucfirst($c['role'])) ?></small></div><?php if ($c['unread']): ?><span class="tag" style="background:var(--pri);color:#fff"><?= $c['unread'] ?></span><?php endif; ?></a><?php endforeach; if (!$contacts) echo '<p class="empty">No contacts yet.</p>'; ?></div>
<div class="card pane"><?php if ($peer): ?><div style="padding:14px 18px;border-bottom:1px solid var(--line)"><b><?= e($peer['name']) ?></b><br><small class="muted"><?= ucfirst($peer['role']) ?></small></div>
<div class="msgs" id="msgs" data-with="<?= $peer['id'] ?>"></div>
<form class="composer" id="cf"><input type="hidden" id="csrf" value="<?= csrf() ?>"><input id="cb" placeholder="Type a message…" autocomplete="off"><button aria-label="Send"><?= icon('send', 16) ?></button></form>
<?php else: ?><p class="empty">Select a contact to start chatting.</p><?php endif; ?></div></div>
<?php page_end();
