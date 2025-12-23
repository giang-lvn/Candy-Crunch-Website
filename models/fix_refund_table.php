<?php
/**
 * Script chạy một lần để sửa bảng REFUND
 * Truy cập: http://localhost/Candy-Crunch-Website/models/fix_refund_table.php
 */

// Database connection
$host = 'localhost';
$dbname = 'candy_crunch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Fixing REFUND Table...</h2>";
    
    // 1. Drop foreign key if exists
    try {
        $pdo->exec("ALTER TABLE REFUND DROP FOREIGN KEY refund_ibfk_1");
        echo "<p style='color:green'>✓ Dropped foreign key constraint</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>⚠ Foreign key not found or already dropped: " . $e->getMessage() . "</p>";
    }
    
    // 2. Add RefundMethod column if not exists
    try {
        $pdo->exec("ALTER TABLE REFUND ADD COLUMN RefundMethod VARCHAR(100) AFTER RefundDescription");
        echo "<p style='color:green'>✓ Added RefundMethod column</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>⚠ RefundMethod column already exists or error: " . $e->getMessage() . "</p>";
    }
    
    // 3. Show table structure
    echo "<h3>REFUND Table Structure:</h3>";
    $result = $pdo->query("DESCRIBE REFUND");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>" . htmlspecialchars($val ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Show existing data
    echo "<h3>Existing REFUND Data:</h3>";
    $result = $pdo->query("SELECT * FROM REFUND");
    $data = $result->fetchAll(PDO::FETCH_ASSOC);
    if (count($data) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        foreach (array_keys($data[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars($val ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in REFUND table yet.</p>";
    }
    
    // 5. List all OrderIDs in ORDERS table
    echo "<h3>Available Order IDs:</h3>";
    $result = $pdo->query("SELECT OrderID, CustomerID, OrderStatus FROM ORDERS ORDER BY OrderID DESC LIMIT 10");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>OrderID</th><th>CustomerID</th><th>Status</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>" . htmlspecialchars($val ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color:green'>Done! You can now test the return form.</h2>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}
