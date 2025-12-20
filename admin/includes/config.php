<?php
// admin/includes/config.php

// Đường dẫn cơ sở
define('BASE_URL', 'http://localhost/CodePTUD/admin/');
define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_NAME', 'PTUD_Final');
define('DB_USER', 'root');
define('DB_PASS', '');

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug mode
define('DEBUG', true);
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>