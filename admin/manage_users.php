<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

check_admin();

// 处理删除用户
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    // 防止删除自己
    if ($uid != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uid]);
    }
    header("Location: manage_users.php");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>用户管理 - 后台</title>
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
        <a href="index.php">微博管理</a>
        <a href="manage_users.php" style="color: white;">用户管理</a>
    </div>

    <div style="flex: 1; padding: 20px;">
        <h2>注册用户管理</h2>

        <div class="card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>角色</th>
                        <th>注册时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo h($user['username']); ?></td>
                            <td>
                                <?php if ($user['role'] == 'admin'): ?>
                                    <span style="background: #fa7d3c; color: white; padding: 2px 5px; border-radius: 4px; font-size: 12px;">管理员</span>
                                <?php else: ?>
                                    普通用户
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <a href="manage_users.php?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('确定删除该用户吗？所有关联数据也会被删除。')" style="color: red;">删除用户</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>