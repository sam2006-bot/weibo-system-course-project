<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '请先登录']));
}

$post_id = $_POST['post_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($post_id) {
    // 检查是否已经点赞
    $check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $check->execute([$user_id, $post_id]);

    if ($check->rowCount() > 0) {
        // 已赞 -> 取消赞
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $action = 'unliked';
    } else {
        // 未赞 -> 点赞
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
        $action = 'liked';
    }

    // 获取最新点赞数
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $countStmt->execute([$post_id]);
    $newCount = $countStmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'new_count' => $newCount]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
