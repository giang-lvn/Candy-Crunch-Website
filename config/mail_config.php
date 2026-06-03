<?php
// ============================================================
//  Cấu hình gửi email — Candy Crunch
// ============================================================

// Bật/tắt gửi mail — phải true thì mới gửi email
define('MAIL_ENABLED', true);

// Ghi log chi tiết vào PHP error log (xem Laragon → PHP error log)
define('MAIL_DEBUG', true);

// true = SMTP, false = dùng hàm mail() của PHP (cần cấu hình sendmail trên server)
define('MAIL_USE_SMTP', true);

// SMTP (Gmail: smtp.gmail.com, 587, tls — cần App Password)
// Laragon Mailpit (test local): host 127.0.0.1, port 1025, encryption '', user/pass rỗng
define('MAIL_HOST', '127.0.0.1');
define('MAIL_PORT', 1025);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', ''); // '', 'tls', hoặc 'ssl'

define('MAIL_FROM_EMAIL', 'noreply@candycrunch.com');
define('MAIL_FROM_NAME', 'Candy Crunch');

// URL website (dùng trong link xem đơn hàng trong email)
$mailProtocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$mailHost     = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('MAIL_SITE_URL', $mailProtocol . '://' . $mailHost . '/Candy-Crunch-Website');
