<?php
require_once __DIR__ . '/models/website/voucher_model.php';

$model = new VoucherModel();

echo "Testing Voucher Filters (Post-Fix)...\n";

$filters = ['all', 'active', 'expiring', 'upcoming'];

foreach ($filters as $filter) {
    echo "--------------------------------------------------\n";
    echo "Filter: $filter\n";
    try {
        $vouchers = $model->getVoucherByFilter($filter);
        if (empty($vouchers)) {
            echo "No vouchers found for this filter.\n";
        } else {
            echo "Found " . count($vouchers) . " vouchers.\n";
            foreach ($vouchers as $v) {
                // Formatting for readability
                echo " - [{$v['VoucherID']}] {$v['Code']} | DB Status: {$v['VoucherStatus']} | Start: {$v['StartDate']} | Dyn Status: " . ($v['DynamicStatus'] ?? 'N/A') . "\n";
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "--------------------------------------------------\n";
echo "Testing getActiveVouchers() (SSR Default)...\n";
$all = $model->getActiveVouchers();
echo "Found " . count($all) . " vouchers in SSR list.\n";
foreach ($all as $v) {
    echo " - [{$v['VoucherID']}] {$v['Code']} | DB Status: {$v['VoucherStatus']} | Start: {$v['StartDate']}\n";
}

echo "Done.\n";
