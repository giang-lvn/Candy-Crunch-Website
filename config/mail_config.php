<?php
// ============================================================
//  Cấu hình gửi email — Candy Crunch
// ============================================================
//
// KHÔNG phụ thuộc Laragon / Apache / XAMPP.
// Website chỉ cần PHP + kết nối tới một máy chủ SMTP (Gmail, Mailpit, v.v.).
//
// Chạy localhost với Apache/XAMPP/Laragon đều được — miễn là:
//   1. MAIL_ENABLED = true
//   2. MAIL_HOST:PORT đang chạy và chấp nhận kết nối
//
// Gợi ý:
//   - Dev (bất kỳ localhost): Mailpit/Mailhog cài riêng → 127.0.0.1:1025
//   - Gửi email thật mọi môi trường: Gmail / Brevo / SendGrid SMTP
//
// Tùy chỉnh từng máy: copy mail_config.local.php.example → mail_config.local.php

$mailConfig = [
    'MAIL_ENABLED'     => true,
    'MAIL_DEBUG'       => true,
    'MAIL_USE_SMTP'    => true,

    // --- SMTP mặc định: Mailpit (test local) — bật Mailpit trước khi đặt hàng ---
    // Gmail: copy mail_config.local.php.example → mail_config.local.php và điền App Password
    'MAIL_HOST'        => '127.0.0.1',
    'MAIL_PORT'        => 1025,
    'MAIL_USERNAME'    => '',
    'MAIL_PASSWORD'    => '',
    'MAIL_ENCRYPTION'  => '',              // Mailpit: để trống | Gmail: tls

    'MAIL_FROM_EMAIL'  => 'noreply@candycrunch.local',
    'MAIL_FROM_NAME'   => 'Candy Crunch',

    // false = chỉ hiện nút "View Email" trên trang success (không tự chuyển hướng)
    'MAIL_REDIRECT_TO_INBOX' => false,
    'MAIL_REDIRECT_DELAY_SEC' => 2,
];

$localPath = __DIR__ . '/mail_config.local.php';
if (is_file($localPath)) {
    $local = include $localPath;
    if (is_array($local)) {
        $mailConfig = array_merge($mailConfig, $local);
    }
}

foreach ($mailConfig as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

// URL website trong email — tự nhận host + thư mục project (không hard-code Laragon)
$mailProtocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$mailHost     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot      = isset($_SERVER['DOCUMENT_ROOT'])
    ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))
    : '';
$projectRoot  = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$basePath     = '';

if ($docRoot && $projectRoot && str_starts_with($projectRoot, $docRoot)) {
    $basePath = substr($projectRoot, strlen($docRoot));
}
if ($basePath === '' || $basePath === '/') {
    $basePath = '/Candy-Crunch-Website';
}

if (!defined('MAIL_SITE_URL')) {
    define('MAIL_SITE_URL', rtrim($mailProtocol . '://' . $mailHost . $basePath, '/'));
}
