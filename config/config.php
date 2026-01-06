<?php
/**
 * Application Configuration
 */

// Start output buffering to prevent headers already sent errors
if (!ob_get_level()) {
    ob_start();
}

// Load database config first
require_once __DIR__ . '/database.php';

// Application settings
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
// Upload path - lưu trực tiếp vào /uploads (ngoài public) để dễ truy cập
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Create upload directories if they don't exist (silent failure in Docker if already created)
// In Docker, these directories are created in Dockerfile, so we suppress errors here
@mkdir(UPLOAD_PATH, 0777, true);
@mkdir(UPLOAD_PATH . '/products', 0777, true);
@mkdir(UPLOAD_PATH . '/banners', 0777, true);

// Session configuration (must be called before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_name($_ENV['SESSION_NAME'] ?? getenv('SESSION_NAME') ?: 'PC_STORE_SESSION');
    session_start();
}

// Error reporting (configure based on environment)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

