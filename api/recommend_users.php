<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '请先登录']));
}

$current_user_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
$limit = max(1, min(6, $limit));

$sql = "SELECT u.id, u.username, u.avatar, u.created_at
        FROM users u
        WHERE u.id != ?
          AND u.id NOT IN (SELECT following_id FROM follows WHERE follower_id = ?)
        ORDER BY RAND()
        LIMIT ?";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(1, $current_user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $current_user_id, PDO::PARAM_INT);
$stmt->bindValue(3, $limit, PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'users' => $users
]);
