<?php
// config/database.php

$host = 'localhost';        // hoặc localhost
$dbname = 'candy_crunch2'; // ĐÚNG tên database bạn đang dùng
$username = 'root';        
$password = 'giangne060705';            // XAMPP mặc định

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
