<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// 权限检查
check_admin();

// 处理删除微博
if (isset($_GET['delete_post'])) {
    $pid = $_GET['delete_post'];
    $img_stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = ?");
    $img_stmt->execute([$pid]);
    $image_path = $img_stmt->fetchColumn();
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$pid]);
    if (!empty($image_path)) {
        $full_path = __DIR__ . '/../' . $image_path;
        if (is_file($full_path)) {
            unlink($full_path);
        }
    }
    header("Location: index.php?msg=deleted");
    exit;
}

// 获取统计数据
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$postCount = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();

// 获取所有微博列表
$posts = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>后台管理系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .admin-table th {
            background-color: #f2f2f2;
        }

        .sidebar-admin {
            background: #333;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }

        .sidebar-admin a {
            color: #ccc;
            display: block;
            padding: 10px 0;
            text-decoration: none;
        }

        .sidebar-admin a:hover {
            color: #fff;
        }
    </style>
</head>

<body style="display: flex;">

    <div class="sidebar-admin" style="width: 200px;">
        <h3>Weibo Admin</h3>
        <a href="../index.php">返回前台</a>
        <hr>
        <a href="index.php" style="color: white;">微博管理</a>
        <a href="manage_users.php">用户管理</a>
    </div>

    <div style="flex: 1; padding: 20px;">
        <h2>微博内容管理</h2>

        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="card" style="flex: 1; text-align: center;">
                <h3>用户总数</h3>
                <p style="font-size: 24px; color: var(--primary-color);"><?php echo $userCount; ?></p>
            </div>
            <div class="card" style="flex: 1; text-align: center;">
                <h3>微博总数</h3>
                <p style="font-size: 24px; color: var(--primary-color);"><?php echo $postCount; ?></p>
            </div>
        </div>

        <div class="card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>发布者</th>
                        <th>内容</th>
                        <th>发布时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td><?php echo h($post['username']); ?></td>
                            <td><?php echo mb_substr(h($post['content']), 0, 50) . '...'; ?></td>
                            <td><?php echo $post['created_at']; ?></td>
                            <td>
                                <a href="index.php?delete_post=<?php echo $post['id']; ?>" onclick="return confirm('确定删除这条微博吗？')" style="color: red;">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>