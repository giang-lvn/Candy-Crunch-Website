<?php
require_once 'models/db.php';

$sql = "SELECT SKUID FROM SKU LIMIT 10";
$stmt = $db->query($sql);
$skus = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($skus);
