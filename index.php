<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// 处理搜索
$whereClause = "";
$params = [];
if (isset($_GET['q'])) {
    $keyword = trim($_GET['q']);
    $whereClause = "WHERE content LIKE ?";
    $params[] = "%$keyword%";
}

// 获取微博列表 (联表查询：包含用户信息、点赞数、当前用户是否点赞)
$current_user_id = $_SESSION['user_id'] ?? 0;
$sql = "SELECT p.*, u.username, u.avatar, 
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        $whereClause
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge([$current_user_id], $params));
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>微博系统 - 首页</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome 图标库 (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo"><i class="fab fa-weibo"></i> 微博</a>
            <div class="search-box">
                <form action="index.php" method="GET">
                    <input type="text" name="q" placeholder="搜索微博..." value="<?php echo isset($_GET['q']) ? h($_GET['q']) : ''; ?>" style="padding: 5px; border-radius: 15px; border: 1px solid #ccc;">
                </form>
            </div>
            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>欢迎, <?php echo h($_SESSION['username']); ?></span>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin/index.php" style="margin-left: 10px;">管理后台</a>
                    <?php endif; ?>
                    <a href="logout.php" style="margin-left: 10px; color: #fa7d3c;">退出</a>
                <?php else: ?>
                    <a href="login.php">登录</a> | <a href="register.php">注册</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- 左侧/主体内容 -->
        <div class="main-content">

            <!-- 发布框 (仅登录可见) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="card">
                    <div class="publish-box">
                        <p>有什么新鲜事想告诉大家？</p>
                        <textarea id="weibo-content" rows="3" placeholder="分享你的想法..."></textarea>
                        <div class="publish-tools">
                            <label class="upload-label">
                                <i class="far fa-image"></i> 图片
                                <input type="file" id="weibo-image" accept="image/*">
                            </label>
                            <span class="upload-tip">支持 JPG/PNG/GIF/WebP，5MB以内</span>
                        </div>
                        <div style="margin-top: 10px; text-align: right;">
                            <button id="publish-btn" class="btn">发布</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 微博列表 -->
            <div class="card">
                <?php if (empty($posts)): ?>
                    <p style="text-align: center; color: #999;">暂时没有内容</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="weibo-item">
                            <div class="weibo-header">
                                <div class="avatar"></div> <!-- 默认头像占位 -->
                                <div class="user-info">
                                    <span class="username"><?php echo h($post['username']); ?></span>
                                    <span class="time"><?php echo time_ago($post['created_at']); ?></span>
                                </div>
                            </div>
                            <?php if (!empty(trim($post['content'] ?? ''))): ?>
                                <div class="weibo-content">
                                    <?php echo h($post['content']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($post['image_path'] ?? '')): ?>
                                <div class="weibo-image">
                                    <img src="<?php echo h($post['image_path']); ?>" alt="微博图片">
                                </div>
                            <?php endif; ?>
                            <div class="weibo-footer">
                                <span class="action-btn comment-toggle-btn" data-id="<?php echo $post['id']; ?>">
                                    <i class="far fa-comment"></i> 评论
                                </span>
                                <span class="action-btn like-btn <?php echo $post['is_liked'] ? 'active' : ''; ?>" data-id="<?php echo $post['id']; ?>">
                                    <i class="far fa-thumbs-up"></i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                                </span>
                            </div>

                            <!-- 评论区域 (Ajax 加载或预加载) -->
                            <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="comment-input-group">
                                        <input type="text" class="comment-input" placeholder="写下你的评论...">
                                        <button class="btn submit-comment-btn" data-id="<?php echo $post['id']; ?>">评论</button>
                                    </div>
                                <?php endif; ?>
                                <div class="comment-list" id="comment-list-<?php echo $post['id']; ?>">
                                    <!-- PHP 预加载部分评论 -->
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

        <!-- 右侧侧边栏 -->
        <div class="sidebar">
            <div class="card">
                <h3>热门话题</h3>
                <ul style="padding-left: 20px; color: #fa7d3c;">
                    <li>#HTML5课程设计#</li>
                    <li>#Web开发#</li>
                    <li>#PHP是世界上最好的语言#</li>
                </ul>
            </div>
            <div class="card">
                <h3>关于系统</h3>
                <p style="font-size: 12px; color: #666;">
                    这是一个基于 PHP + MySQL 的简易微博系统。<br>
                    包含了发布、点赞、评论、搜索等核心功能。<br>
                    后台可进行用户与内容管理。
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>