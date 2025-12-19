<?php
// config/database.php

<<<<<<< Updated upstream
$host = 'localhost';        // hoặc localhost
$dbname = ''; // ĐÚNG tên database bạn đang dùng
$username = '';        
$password = '';            // XAMPP mặc định
=======
$host = 'localhost';       
$dbname = 'candy_crunch2'; 
$username = 'root';        
$password = 'giangne060705';            
>>>>>>> Stashed changes

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
