<?php
// admin/includes/db.php

// Load config trước
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}

// Hàm helper để kiểm tra bảng
function checkTableExists($pdo, $tableName) {
    $check = $pdo->query("SHOW TABLES LIKE '$tableName'");
    return $check->rowCount() > 0;
}

// Kiểm tra và tạo tài khoản admin nếu cần
if (checkTableExists($pdo, 'ACCOUNT')) {
    $checkAdmin = $pdo->query("SELECT * FROM ACCOUNT WHERE Email = 'admin@example.com'");
    if ($checkAdmin->rowCount() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        try {
            $pdo->exec("INSERT INTO ACCOUNT (AccountID, Email, Password, AccountStatus) 
                        VALUES ('ADMIN001', 'admin@example.com', '$hashedPassword', 'ACTIVE')");
        } catch (Exception $e) {
            // Bỏ qua nếu có lỗi
        }
    }
}
?>