<?php
// models/db.php

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
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Kết nối thành công (không echo để tránh ảnh hưởng JSON response)
    // echo "Đã kết nối thành công db Candy Crunch";

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}
?>