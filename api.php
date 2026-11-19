<?php // JSON endpoints for chat
require_once 'includes/bootstrap.php';
header('Content-Type: application/json');
$u = user(); if (!$u) { http_response_code(401); exit('{"error":"auth"}'); }
$me = $u['id'];
if (is_post()) {
    csrf_check(); $to = (int)($_POST['to'] ?? 0); $body = trim($_POST['body'] ?? '');
    if ($body === '' || !q("SELECT id FROM users WHERE id=? AND status='active'", [$to])->fetch()) { http_response_code(422); exit('{"error":"invalid"}'); }
    q('INSERT INTO messages (sender_id,receiver_id,body) VALUES (?,?,?)', [$me, $to, strw($body, 2000)]);
    exit(json_encode(['ok' => true]));
}
$with = (int)($_GET['with'] ?? 0); $after = (int)($_GET['after'] ?? 0);
q('UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=?', [$with, $me]);
$rows = q('SELECT id,sender_id,body,created_at FROM messages WHERE id>? AND ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) ORDER BY id', [$after, $me, $with, $with, $me])->fetchAll();
echo json_encode(array_map(fn($m) => ['id' => (int)$m['id'], 'mine' => (int)$m['sender_id'] === $me, 'body' => $m['body'], 'time' => date('g:i A', strtotime($m['created_at']))], $rows));