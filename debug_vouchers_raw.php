<?php
require_once __DIR__ . '/models/db.php';

global $db;
$stmt = $db->query("SELECT * FROM VOUCHER");
$vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total Vouchers: " . count($vouchers) . "\n";
foreach ($vouchers as $v) {
    echo "ID: {$v['VoucherID']} | Code: {$v['Code']} | Status: {$v['VoucherStatus']} | Start: {$v['StartDate']} | End: {$v['EndDate']}\n";
}
