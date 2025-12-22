<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$content = trim($_POST['content'] ?? '');
$image_path = null;
$has_image = isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($content === '' && !$has_image) {
    echo json_encode(['success' => false, 'message' => '内容或图片不能为空']);
    exit;
}

if ($has_image) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '图片上传失败']);
        exit;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => '图片大小不能超过 5MB']);
        exit;
    }

    $tmp_name = $_FILES['image']['tmp_name'];
    $image_info = getimagesize($tmp_name);
    if ($image_info === false) {
        echo json_encode(['success' => false, 'message' => '只支持图片文件']);
        exit;
    }

    $mime_to_ext = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $mime = $image_info['mime'] ?? '';
    if (!isset($mime_to_ext[$mime])) {
        echo json_encode(['success' => false, 'message' => '仅支持 JPG/PNG/GIF/WebP 格式']);
        exit;
    }

    $upload_dir = __DIR__ . '/../uploads/posts';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => '创建上传目录失败']);
            exit;
        }
    }

    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $mime_to_ext[$mime];
    $target_path = $upload_dir . '/' . $filename;
    if (!move_uploaded_file($tmp_name, $target_path)) {
        echo json_encode(['success' => false, 'message' => '保存图片失败']);
        exit;
    }

    $image_path = 'uploads/posts/' . $filename;
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $content, $image_path]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($image_path) {
        $full_path = __DIR__ . '/../' . $image_path;
        if (is_file($full_path)) {
            unlink($full_path);
        }
    }
    echo json_encode(['success' => false, 'message' => 'Database Error']);
}
