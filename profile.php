<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// 获取要查看的用户 ID，默认为当前登录用户
$view_user_id = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['user_id'] ?? 0);

if ($view_user_id === 0) {
    header("Location: login.php");
    exit;
}

// 获取用户信息
$stmt = $pdo->prepare("SELECT id, username, avatar, created_at FROM users WHERE id = ?");
$stmt->execute([$view_user_id]);
$user_info = $stmt->fetch();

if (!$user_info) {
    die("用户不存在");
}

// 判断是否是查看自己的主页
$is_own_profile = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $view_user_id);

// 获取该用户发布的微博
// 注意：这里需要联表查询出当前登录用户是否点赞了这些微博 (is_liked)
$current_user_id = $_SESSION['user_id'] ?? 0;
$stmt_posts = $pdo->prepare("
    SELECT p.*, u.username, u.avatar, 
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt_posts->execute([$current_user_id, $view_user_id]);
$posts = $stmt_posts->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($user_info['username']); ?> 的主页 - 微博</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <!-- 导航栏 -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo"><i class="fab fa-weibo"></i> 微博</a>
            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="nav-avatar-link">
                        <span>欢迎, <?php echo h($_SESSION['username']); ?></span>
                    </a>
                    <a href="index.php" style="margin-left: 10px;">返回首页</a>
                    <a href="logout.php" style="margin-left: 10px; color: #fa7d3c;">退出</a>
                <?php else: ?>
                    <a href="login.php">登录</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">

            <!-- 用户信息卡片 -->
            <div class="card profile-header">
                <div class="profile-avatar-container">
                    <img src="<?php echo $user_info['avatar'] ? h($user_info['avatar']) : 'assets/images/default-avatar.png'; ?>"
                        alt="头像" class="profile-avatar-large" id="profile-avatar-img"
                        onerror="this.src='https://via.placeholder.com/100?text=User'">

                    <?php if ($is_own_profile): ?>
                        <div class="avatar-upload-overlay" onclick="document.getElementById('avatar-input').click()">
                            <i class="fas fa-camera"></i> 更换
                        </div>
                        <input type="file" id="avatar-input" style="display: none;" accept="image/*">
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h2><?php echo h($user_info['username']); ?></h2>
                    <p style="color: #808080; font-size: 14px;">
                        加入时间：<?php echo date('Y年m月d日', strtotime($user_info['created_at'])); ?>
                    </p>
                    <p>共发布 <?php echo count($posts); ?> 条微博</p>
                </div>
            </div>

            <!-- 微博列表 -->
            <div class="card">
                <h3>我的动态</h3>
                <?php if (empty($posts)): ?>
                    <p style="text-align: center; color: #999; padding: 20px;">这个人很懒，什么都没写。</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="weibo-item">
                            <div class="weibo-header">
                                <img src="<?php echo $user_info['avatar'] ? h($user_info['avatar']) : 'assets/images/default-avatar.png'; ?>"
                                    class="avatar"
                                    onerror="this.src='https://via.placeholder.com/50?text=User'">
                                <div class="user-info">
                                    <span class="username"><?php echo h($post['username']); ?></span>
                                    <span class="time"><?php echo time_ago($post['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="weibo-content">
                                <?php echo h($post['content']); ?>
                            </div>

                            <!-- 图片展示逻辑修改：支持九宫格 -->
                            <?php
                            $imgs = [];
                            $db_path = $post['image_path'] ?? '';
                            if (!empty($db_path)) {
                                $json_imgs = json_decode($db_path, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($json_imgs)) {
                                    $imgs = $json_imgs;
                                } else {
                                    $imgs = [$db_path];
                                }
                            }
                            ?>
                            <?php if (!empty($imgs)): ?>
                                <div class="weibo-image-grid grid-<?php echo count($imgs); ?>">
                                    <?php foreach ($imgs as $img_url): ?>
                                        <div class="grid-item">
                                            <img src="<?php echo h($img_url); ?>" alt="图片" loading="lazy">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- 交互按钮区域 -->
                            <div class="weibo-footer">
                                <span class="action-btn comment-toggle-btn" data-id="<?php echo $post['id']; ?>">
                                    <i class="far fa-comment"></i> 评论
                                </span>
                                <span class="action-btn like-btn <?php echo $post['is_liked'] ? 'active' : ''; ?>" data-id="<?php echo $post['id']; ?>">
                                    <i class="far fa-thumbs-up"></i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                                </span>
                            </div>

                            <!-- 评论区域 -->
                            <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="comment-input-group">
                                        <input type="text" class="comment-input" placeholder="写下你的评论...">
                                        <button class="btn submit-comment-btn" data-id="<?php echo $post['id']; ?>">评论</button>
                                    </div>
                                <?php endif; ?>
                                <div class="comment-list" id="comment-list-<?php echo $post['id']; ?>">
                                    <?php
                                    $stmt_c = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC LIMIT 5");
                                    $stmt_c->execute([$post['id']]);
                                    $comments = $stmt_c->fetchAll();
                                    foreach ($comments as $comment):
                                    ?>
                                        <div style="border-top: 1px dashed #eee; padding: 5px 0; font-size: 13px;">
                                            <span style="color:#fa7d3c"><?php echo h($comment['username']); ?>:</span>
                                            <?php echo h($comment['content']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar">
            <div class="card">
                <h3>关于用户</h3>
                <p>这里是 <?php echo h($user_info['username']); ?> 的个人空间。</p>
            </div>
        </div>
    </div>

    <!-- 必须引入 main.js 才能使用点赞和评论功能 -->
    <script src="assets/js/main.js"></script>

    <script>
        // 头像上传逻辑 (保持不变)
        const avatarInput = document.getElementById('avatar-input');
        if (avatarInput) {
            avatarInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('avatar', file);

                // 显示上传中...
                const img = document.getElementById('profile-avatar-img');
                const oldSrc = img.src;
                img.style.opacity = '0.5';

                fetch('api/upload_avatar.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        img.style.opacity = '1';
                        if (data.success) {
                            img.src = data.avatar_url;
                            alert('头像更换成功！');
                        } else {
                            alert(data.message || '上传失败');
                            img.src = oldSrc;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('网络错误');
                        img.style.opacity = '1';
                    });
            });
        }
    </script>
</body>

</html>