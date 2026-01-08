<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => '非法请求']));
}

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '请先登录']));
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    exit(json_encode(['success' => false, 'message' => '无权操作']));
}

$comment_id = intval($_POST['comment_id'] ?? 0);
if (!$comment_id) {
    exit(json_encode(['success' => false, 'message' => '参数错误']));
}

$stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'comment_id' => $comment_id]);
} else {
    echo json_encode(['success' => false, 'message' => '评论不存在或已删除']);
}
