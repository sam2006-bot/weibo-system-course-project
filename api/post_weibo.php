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
// 检查是否有文件上传 (注意这里前端发来的是 images[] 数组)
$has_files = isset($_FILES['images']) && !empty($_FILES['images']['name'][0]);

if ($content === '' && !$has_files) {
    echo json_encode(['success' => false, 'message' => '内容或图片不能为空']);
    exit;
}

$saved_image_paths = [];

if ($has_files) {
    $files = $_FILES['images'];
    $file_count = count($files['name']);

    if ($file_count > 9) {
        echo json_encode(['success' => false, 'message' => '最多只能上传 9 张图片']);
        exit;
    }

    $upload_dir = __DIR__ . '/../uploads/posts';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => '创建上传目录失败']);
            exit;
        }
    }

    $mime_to_ext = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    // 循环处理每一张图片
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // 跳过错误的图片
        }

        if ($files['size'][$i] > 5 * 1024 * 1024) {
            // 如果单张超过5MB，跳过
            continue;
        }

        $tmp_name = $files['tmp_name'][$i];
        $image_info = getimagesize($tmp_name);
        if ($image_info === false) continue;

        $mime = $image_info['mime'] ?? '';
        if (!isset($mime_to_ext[$mime])) continue;

        // 生成唯一文件名
        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $i . '.' . $mime_to_ext[$mime];
        $target_path = $upload_dir . '/' . $filename;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $saved_image_paths[] = 'uploads/posts/' . $filename;
        }
    }
}

// 如果一张都没传成功，但用户本意是传图且没写字
if ($has_files && empty($saved_image_paths) && $content === '') {
    echo json_encode(['success' => false, 'message' => '图片上传失败，请检查格式或大小']);
    exit;
}

// 将路径数组转换为 JSON 字符串存储
$image_path_json = !empty($saved_image_paths) ? json_encode($saved_image_paths) : null;

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $content, $image_path_json]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // 简单的错误回滚：删除已上传的文件
    foreach ($saved_image_paths as $path) {
        @unlink(__DIR__ . '/../' . $path);
    }
    echo json_encode(['success' => false, 'message' => 'Database Error']);
}
