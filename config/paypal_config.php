<?php
// ============================================================
//  PAYPAL SANDBOX CONFIGURATION
//  File: /Candy-Crunch-Website/config/paypal_config.php
// ============================================================

// -------------------------------------------------------
//  PayPal Sandbox Credentials
//  Lấy từ: https://developer.paypal.com/dashboard/applications/sandbox
//  ⚠️ THAY THẾ bằng Client ID và Secret của bạn
// -------------------------------------------------------
define('PAYPAL_CLIENT_ID',     'AUj63_sC43BYyoQ47oGLrPhpkcRkkn1l25gWprqVDI2jVWNAIMMT7NWYlzYB759Wzxy6eBnmhxIHiAuC');
define('PAYPAL_CLIENT_SECRET', 'ED4YmMphiF93G2r-t-_ZarWD7URrQ_Vk5ibksSU4Gjmt1Yz2gUNr6bLNk9bo9K1Djj_oH8j845uB47MP');

// -------------------------------------------------------
//  PayPal API Base URL
//  Sandbox: https://api-m.sandbox.paypal.com
//  Live:    https://api-m.paypal.com
// -------------------------------------------------------
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');

// -------------------------------------------------------
//  Currency & Exchange Rate
// -------------------------------------------------------
define('PAYPAL_CURRENCY',  'USD');
define('VND_TO_USD_RATE',  25000); // 1 USD ≈ 25,000 VND

// -------------------------------------------------------
//  Return & Cancel URLs
//  PayPal sẽ redirect user về đây sau khi approve/cancel
// -------------------------------------------------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl  = $protocol . '://' . $host . '/Candy-Crunch-Website';

define('PAYPAL_RETURN_URL', $baseUrl . '/controllers/website/PaypalController.php?action=capture');
define('PAYPAL_CANCEL_URL', $baseUrl . '/controllers/website/PaypalController.php?action=cancel');
