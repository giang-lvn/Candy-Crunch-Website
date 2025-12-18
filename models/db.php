<?php
// config/database.php

$host = 'localhost';        // hoặc localhost
$dbname = ''; // ĐÚNG tên database bạn đang dùng
$username = '';        
$password = '';            // XAMPP mặc định

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
