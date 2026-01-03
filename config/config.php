<?php
/**
 * Application Configuration
 */

// Load database config
require_once __DIR__ . '/database.php';

// Application settings
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!file_exists(UPLOAD_PATH . '/products')) {
    mkdir(UPLOAD_PATH . '/products', 0777, true);
}
if (!file_exists(UPLOAD_PATH . '/banners')) {
    mkdir(UPLOAD_PATH . '/banners', 0777, true);
}

// Session configuration
session_name($_ENV['SESSION_NAME'] ?? getenv('SESSION_NAME') ?: 'PC_STORE_SESSION');
session_start();

// Error reporting (disable in production)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

