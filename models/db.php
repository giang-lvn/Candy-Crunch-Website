<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "Candy_Crunch"; //Tên db là Candy_Crunch

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu kết nối thất bại
    die("Connection failed: " . $conn->connect_error);
}