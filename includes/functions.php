<?php
// includes/functions.php

// 检查是否登录
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php"); // 假设根目录在 weibo-system/ 下，根据实际情况调整路径
        exit;
    }
}

// 检查是否是管理员
function check_admin()
{
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        die("无权访问");
    }
}

// XSS 过滤
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 格式化时间
function time_ago($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    return date('Y-m-d H:i', $time);
}
