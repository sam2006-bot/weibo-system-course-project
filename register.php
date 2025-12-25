<?php
require_once 'includes/db_connect.php';

$msg = '';
$error = false;

// 初始化变量，用于在表单中回显
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_val = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 简单验证
    if (strlen($username_val) < 2) {
        $msg = "用户名太短";
        $error = true;
    } elseif (strlen($password) < 6) {
        $msg = "密码长度至少6位";
        $error = true;
    } elseif ($password !== $confirm_password) {
        $msg = "两次输入的密码不一致";
        $error = true;
    } else {
        // 检查用户名是否存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username_val]);
        if ($stmt->fetch()) {
            $msg = "该用户名已被注册";
            $error = true;
        } else {
            // 注册
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username_val, $hash])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $msg = "系统错误，注册失败";
                $error = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 微博</title>
    <!-- 添加时间戳参数，强制浏览器重新加载最新 CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="auth-body">

    <div class="auth-card-modern">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fab fa-weibo"></i>
            </div>
            <h2 class="auth-title">加入我们</h2>
            <p class="auth-subtitle">创建一个新账号，开启分享之旅</p>
        </div>

        <form method="POST" class="auth-form">

            <?php if ($msg): ?>
                <div class="error-message" style="<?php echo $error ? '' : 'background-color:#f6ffed; border-color:#b7eb8f; color:#52c41a;'; ?>">
                    <i class="fas <?php echo $error ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="modern-input-group">
                <!-- 添加 value 属性回显用户名 -->
                <input type="text" name="username" class="modern-input" placeholder="设置用户名 (至少2位)" required autocomplete="off" value="<?php echo htmlspecialchars($username_val); ?>">
                <i class="fas fa-user"></i>
            </div>

            <div class="modern-input-group">
                <input type="password" name="password" class="modern-input" placeholder="设置密码 (至少6位)" required>
                <i class="fas fa-lock"></i>
            </div>

            <!-- 新增确认密码字段 -->
            <div class="modern-input-group">
                <input type="password" name="confirm_password" class="modern-input" placeholder="确认密码" required>
                <i class="fas fa-check-circle"></i>
            </div>

            <button type="submit" class="btn modern-btn">立即注册</button>

            <div class="auth-links">
                <a href="login.php">已有账号？去登录</a>
                <span style="color: #ddd;">|</span>
                <a href="index.php">返回首页</a>
            </div>
        </form>
    </div>

</body>

</html>