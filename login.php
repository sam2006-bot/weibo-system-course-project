<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$error = '';
$success_msg = '';

// 检查是否有注册成功的标志
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_msg = "注册成功，请登录您的账号";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 微博</title>
    <!-- 添加时间戳 ?v=... 强制浏览器重新加载 CSS，解决样式不更新的问题 -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- 引入图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="auth-body">

    <div class="auth-card-modern">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fab fa-weibo"></i>
            </div>
            <h2 class="auth-title">欢迎回来</h2>
            <p class="auth-subtitle">登录你的微博账号，发现新鲜事</p>
        </div>

        <form method="POST" class="auth-form">

            <!-- 注册成功提示 -->
            <?php if ($success_msg): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <!-- 错误提示 -->
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="modern-input-group">
                <input type="text" name="username" class="modern-input" placeholder="请输入用户名" required autocomplete="off">
                <i class="fas fa-user"></i>
            </div>

            <div class="modern-input-group">
                <input type="password" name="password" class="modern-input" placeholder="请输入密码" required>
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="btn modern-btn">立即登录</button>

            <div class="auth-links">
                <a href="register.php">注册新账号</a>
                <span style="color: #ddd;">|</span>
                <a href="index.php">返回首页</a>
            </div>
        </form>
    </div>

</body>

</html>