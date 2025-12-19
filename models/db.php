<?php
// config/database.php

$host = 'localhost';       
$dbname = 'Candy_Crunch'; 
$username = 'root';        
$password = '';

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

    // Kết nối thành công
    echo "Đã kết nối thành công db Candy Crunch";

} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
