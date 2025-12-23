<?php
/**
 * Script cập nhật tất cả OrderStatus từ "Canceled" sang "Cancelled"
 * Truy cập: http://localhost/Candy-Crunch-Website/models/fix_order_status.php
 */

$host = 'localhost';
$dbname = 'candy_crunch';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Đồng bộ trạng thái đơn hàng...</h2>";
    
    // Đếm số đơn hàng có status "Canceled"
    $countStmt = $pdo->query("SELECT COUNT(*) FROM ORDERS WHERE OrderStatus = 'Canceled'");
    $count = $countStmt->fetchColumn();
    
    echo "<p>Tìm thấy <strong>$count</strong> đơn hàng có trạng thái 'Canceled'.</p>";
    
    if ($count > 0) {
        // Cập nhật từ "Canceled" sang "Cancelled"
        $updateStmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = 'Cancelled' WHERE OrderStatus = 'Canceled'");
        $updateStmt->execute();
        
        echo "<p style='color:green'>✓ Đã cập nhật $count đơn hàng từ 'Canceled' sang 'Cancelled'.</p>";
    } else {
        echo "<p style='color:orange'>⚠ Không cần cập nhật.</p>";
    }
    
    // Hiển thị thống kê các trạng thái đơn hàng
    echo "<h3>Thống kê trạng thái đơn hàng hiện tại:</h3>";
    $statsStmt = $pdo->query("SELECT OrderStatus, COUNT(*) as Total FROM ORDERS GROUP BY OrderStatus ORDER BY Total DESC");
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Trạng thái</th><th>Số lượng</th></tr>";
    while ($row = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['OrderStatus']) . "</td>";
        echo "<td style='text-align: center;'>" . $row['Total'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color:green; margin-top: 20px;'>Hoàn tất!</h2>";
    echo "<p><a href='/Candy-Crunch-Website/admin/index.php'>Quay về Admin Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}
