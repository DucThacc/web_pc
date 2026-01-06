<?php
require_once __DIR__ . '/config/config.php';

// Xóa tất cả session variables
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Redirect về trang login
header('Location: /login');
exit;

