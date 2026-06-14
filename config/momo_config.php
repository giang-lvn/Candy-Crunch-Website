<?php
// ============================================================
//  MOMO SANDBOX CONFIGURATION
//  File: /Candy-Crunch-Website/config/momo_config.php
// ============================================================

define('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
define('MOMO_ACCESS_KEY',   'klm05TvNBzhg7h7j');
define('MOMO_SECRET_KEY',   'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');
define('MOMO_ENDPOINT',     'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_REQUEST_TYPE', 'payWithMethod');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl  = $protocol . '://' . $host . '/Candy-Crunch-Website';

define('MOMO_REDIRECT_URL', $baseUrl . '/controllers/website/MomoController.php?action=return');
define('MOMO_IPN_URL',      $baseUrl . '/controllers/website/MomoController.php?action=ipn');
