<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => '请求方式错误']));
}

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '请先登录']));
}

$current_user_id = $_SESSION['user_id'];
$target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($target_user_id <= 0 || $target_user_id === $current_user_id) {
    exit(json_encode(['success' => false, 'message' => '参数错误']));
}

$user_check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$user_check->execute([$target_user_id]);
if (!$user_check->fetchColumn()) {
    exit(json_encode(['success' => false, 'message' => '用户不存在']));
}

$check = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
$check->execute([$current_user_id, $target_user_id]);

if ($check->rowCount() > 0) {
    $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$current_user_id, $target_user_id]);
    $is_following = false;
    $action = 'unfollowed';
} else {
    $stmt = $pdo->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
    $stmt->execute([$current_user_id, $target_user_id]);
    $is_following = true;
    $action = 'followed';
}

echo json_encode([
    'success' => true,
    'action' => $action,
    'is_following' => $is_following
]);
