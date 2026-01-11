# weibo-system-course-project
这是一个基于原生 PHP + MySQL 开发的现代化仿微博/X（Twitter）社交系统。本项目是 HTML5/Web 开发课程的期末作业，旨在实现一个功能完善、界面美观且交互流畅的社交平台。

## ✨ 项目特色

### 🎨 界面与交互 (UI/UX)
现代化设计风格：采用类似 X (Twitter) 的 "Shell" 布局，响应式设计，适配桌面端与移动端。

沉浸式体验：毛玻璃效果导航栏、动态背景（个人主页）、丝滑的 CSS 动画。

九宫格图片展示：智能适配 1 张、2 张、4 张及 9 张图片的布局显示。

无刷新交互 (Ajax)：发布微博、点赞、关注、评论、加载推荐用户均采用 Ajax 异步请求，无需刷新页面。

## 🚀 核心功能
### 用户系统：

用户注册与登录（全新设计的 Auth 页面）。

个人主页：展示用户信息、统计数据（微博数、获赞数）、个性化头像上传。

关注机制：由于有了关注系统，首页支持 "为你推荐" (随机) 和 "正在关注" (关注流) 双 Tab 切换。

### 内容创作：

支持多图上传（单次最多 9 张）。

发布纯文本或图文微博。

### 社交互动：

点赞/取消点赞（实时数字更新）。

评论功能（支持展开/收起评论区）。

侧边栏推荐关注用户（换一换功能）。

全局内容搜索。

### 后台管理：

管理员专属仪表盘。

数据统计（用户总数、微博总数）。

内容审核（删除违规微博、删除评论）。

用户管理（删除违规用户）。

## 🛠️ 技术栈

后端：PHP 7.4+ (使用 PDO 扩展进行数据库操作)

前端：HTML5, CSS3 (Flexbox/Grid, CSS Variables), Vanilla JavaScript (原生 JS, Fetch API)

数据库：MySQL 5.7+ / MariaDB

服务器：Apache / Nginx

## 📂 目录结构

```text
weibo-system/
├── admin/                  # 管理员后台模块
│   ├── index.php           # 后台首页 & 微博管理
│   └── manage_users.php    # 用户管理
├── api/                    # Ajax 接口 (RESTful 风格)
│   ├── post_weibo.php      # 发布微博 (支持多图)
│   ├── like_weibo.php      # 点赞/取消赞
│   ├── follow_user.php     # 关注/取关
│   ├── add_comment.php     # 发布评论
│   └── recommend_users.php # 获取推荐用户
│   └── ...
├── assets/                 # 静态资源
│   ├── css/style.css       # 核心样式表
│   ├── js/main.js          # 核心交互逻辑
│   └── images/             # 默认头像与图标
├── config/                 # 配置文件目录
│   └── config.php          # (需自行创建) 数据库配置
├── includes/               # 公共组件
│   ├── db_connect.php      # 数据库连接
│   └── functions.php       # 通用辅助函数
├── sql/                    # 数据库文件
│   └── weibo_db.sql        # 初始数据库结构导入文件
├── uploads/                # 上传文件存储目录
│   ├── avatars/            # 用户头像
│   └── posts/              # 微博配图
├── index.php               # 前台首页 (Feed流)
├── login.php               # 登录页
├── register.php            # 注册页
└── profile.php             # 个人主页跳转逻辑
```

## ⚡ 快速开始
1. 环境准备
确保您的本地环境（如 XAMPP, WAMP, MAMP 或 Docker）已安装 PHP 和 MySQL。

2. 数据库配置
进入 MySQL 数据库管理工具（如 phpMyAdmin）。

创建一个新的数据库，例如命名为 weibo_system。

导入项目根目录下的 sql/weibo_db.sql 文件。

注意：该 SQL 文件包含初始表结构和一个默认管理员账号。

3. 项目配置
在 config/ 目录下创建一个名为 config.php 的文件（如果在 .gitignore 中被忽略），并填入您的数据库信息：


```php
<?php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'weibo_system');
define('DB_USER', 'root');      // 您的数据库用户名
define('DB_PASS', '');          // 您的数据库密码
define('DB_CHARSET', 'utf8mb4');

// 定义默认头像路径
define('DEFAULT_AVATAR_PATH', 'assets/images/default-avatar.svg');
?>
注意：如果 config 文件夹不存在，您可以直接修改 includes/db_connect.php 中的数据库连接参数。
```

4. 运行项目

将项目文件夹放置在 Web 服务器的根目录（如 htdocs 或 www）下，在浏览器访问： http://localhost/weibo-system/

## 🔐 默认账号

管理员账号 (拥有后台管理权限):

用户名: admin

密码: admin123

普通用户:

您可以在注册页面 (register.php) 自行注册新账号。

## 📸 功能展示

首页 Feed 流：查看关注人的动态或系统推荐内容。

发布器：点击图片图标可选择多张图片，支持预览。

个人主页：点击头像进入，支持点击封面图更换（如有开发）或点击头像更换头像。

后台管理：使用管理员账号登录后，在侧边栏点击 "管理后台" 进入。

## 📝 注意事项

上传的图片会存储在 uploads/ 目录下，请确保服务器对该目录有写入权限。

项目使用了 session，请确保 PHP 配置中 session.save_path 可写。

为了获得最佳体验，请使用 Chrome, Edge 或 Firefox 等现代浏览器。