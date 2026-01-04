<?php
$profile_id = 0;
if (isset($_GET['profile_id'])) {
    $profile_id = intval($_GET['profile_id']);
} elseif (isset($_GET['id'])) {
    $profile_id = intval($_GET['id']);
}

$redirect = 'index.php';
if ($profile_id > 0) {
    $redirect .= '?profile_id=' . $profile_id;
}

header('Location: ' . $redirect);
exit;
