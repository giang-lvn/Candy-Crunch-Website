<?php
// views/reports.php
$period = $_GET['period'] ?? 'month';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Lấy doanh thu theo khoảng thời gian
$sql = "SELECT DATE(o.OrderDate) as order_date, 
               COUNT(*) as order_count,
               SUM(s.OriginalPrice * od.OrderQuantity) as revenue
        FROM ORDERS o
        JOIN ORDER_DETAIL od ON o.OrderID = od.OrderID