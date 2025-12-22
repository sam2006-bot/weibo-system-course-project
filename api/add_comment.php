<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false]));
}

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '请先登录']));
}

$post_id = $_POST['post_id'] ?? 0;
$content = trim($_POST['content'] ?? '');

if ($post_id && $content) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    if ($stmt->execute([$post_id, $_SESSION['user_id'], $content])) {
        echo json_encode([
            'success' => true,
            'username' => $_SESSION['username'],
            'content' => htmlspecialchars($content)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '评论失败']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '参数错误']);
}
