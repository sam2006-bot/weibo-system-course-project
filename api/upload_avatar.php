<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// 1. 检查登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

// 2. 检查是否有文件上传
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => '上传失败或未选择文件']);
    exit;
}

$file = $_FILES['avatar'];

// 3. 验证文件类型和大小
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file['tmp_name']);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => '只允许上传 JPG, PNG, GIF, WebP 格式的图片']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) { // 2MB
    echo json_encode(['success' => false, 'message' => '图片大小不能超过 2MB']);
    exit;
}

// 4. 准备保存路径
$upload_dir = __DIR__ . '/../uploads/avatars';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// 生成唯一文件名
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$target_path = $upload_dir . '/' . $filename;
$db_path = 'uploads/avatars/' . $filename; // 存入数据库的相对路径

// 5. 移动文件并更新数据库
if (move_uploaded_file($file['tmp_name'], $target_path)) {
    try {
        // 更新用户表
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$db_path, $_SESSION['user_id']]);

        // 更新 Session 中的头像（如果有存的话，或者前端直接用返回的新路径）
        echo json_encode(['success' => true, 'avatar_url' => $db_path]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '数据库更新失败']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '保存文件失败']);
}
