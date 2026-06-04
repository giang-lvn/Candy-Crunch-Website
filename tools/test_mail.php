<?php
/**
 * Kiểm tra gửi email — chỉ chạy trên localhost
 * Truy cập: http://localhost/Candy-Crunch-Website/tools/test_mail.php?to=email@example.com
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('Chỉ cho phép truy cập từ localhost.');
}

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../services/MailService.php';

header('Content-Type: text/html; charset=UTF-8');

$to = trim($_GET['to'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Email - Candy Crunch</title>
    <style>body{font-family:sans-serif;max-width:640px;margin:40px auto;padding:20px;} .ok{color:green;} .err{color:red;} code{background:#f4f4f4;padding:2px 6px;}</style>
</head>
<body>
<h1>Test gửi email</h1>
<p><strong>MAIL_ENABLED:</strong> <?= MAIL_ENABLED ? 'true' : 'false' ?></p>
<p><strong>SMTP:</strong> <?= MAIL_HOST ?>:<?= MAIL_PORT ?> (<?= MAIL_ENCRYPTION ?: 'no encryption' ?>)</p>

<?php if ($to === ''): ?>
    <p>Nhập email nhận thử:</p>
    <form method="get">
        <input type="email" name="to" placeholder="your@email.com" required style="width:260px;padding:8px;">
        <button type="submit">Gửi thử</button>
    </form>
    <hr>
    <p><strong>Mailpit (test local):</strong> Cài Mailpit, chạy SMTP <code>127.0.0.1:1025</code> — dùng được với Apache/XAMPP/Laragon. Xem mail tại <a href="http://127.0.0.1:8025" target="_blank">127.0.0.1:8025</a>.</p>
    <p><strong>Gmail (mọi localhost):</strong> Sửa <code>config/mail_config.php</code> hoặc tạo <code>config/mail_config.local.php</code> từ file <code>.example</code>.</p>
<?php else:
    $html = '<p>Nếu bạn nhận được email này, cấu hình SMTP đã hoạt động.</p>';
    $ok = MailService::send($to, '[Candy Crunch] Test email', $html);
    if ($ok): ?>
        <p class="ok">Đã gửi thử tới <strong><?= htmlspecialchars($to) ?></strong>.</p>
        <?php if (MAIL_HOST === '127.0.0.1' && (int) MAIL_PORT === 1025): ?>
            <p>Đang dùng Mailpit — mở <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a> để xem (không phải inbox Gmail).</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="err">Gửi thất bại.</p>
        <ul>
            <?php if (MAIL_HOST === '127.0.0.1' && (int) MAIL_PORT === 1025): ?>
            <li><strong>Mailpit chưa chạy?</strong> Tải/chạy <a href="https://github.com/axllent/mailpit/releases" target="_blank">Mailpit</a>, mở <code>mailpit.exe</code> hoặc <code>mailpit</code> trong terminal.</li>
            <li>Sau khi chạy, mở <a href="http://127.0.0.1:8025" target="_blank">127.0.0.1:8025</a> — mail test hiện ở đây, không phải Gmail thật.</li>
            <?php endif; ?>
            <?php if (stripos(MAIL_HOST, 'gmail') !== false && MAIL_USERNAME === ''): ?>
            <li><strong>Gmail:</strong> Điền <code>MAIL_USERNAME</code> và <code>MAIL_PASSWORD</code> (App Password).</li>
            <?php endif; ?>
            <li>Chi tiết: <a href="mail_diagnose.php">mail_diagnose.php</a> hoặc PHP error log.</li>
        </ul>
    <?php endif;
endif; ?>
</body>
</html>
