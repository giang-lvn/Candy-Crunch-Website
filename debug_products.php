<?php
require_once 'models/db.php';

$sql = "SELECT ProductID FROM PRODUCT LIMIT 10";
$stmt = $db->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($products);
