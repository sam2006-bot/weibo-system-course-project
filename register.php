<?php
require_once 'includes/db_connect.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 简单验证
    if (strlen($password) < 6) {
        $msg = "密码长度至少6位";
    } else {
        // 检查用户名是否存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $msg = "用户名已存在";
        } else {
            // 注册
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hash])) {
                header("Location: login.php");
                exit;
            } else {
                $msg = "注册失败";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>注册 - 微博</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }

        .auth-form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="auth-container card">
        <h2>新用户注册</h2>
        <?php if ($msg): ?><p style="color: red;"><?php echo $msg; ?></p><?php endif; ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">注册</button>
        </form>
        <p>已有账号？<a href="login.php">去登录</a></p>
    </div>
</body>

</html>