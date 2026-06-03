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
    <p><strong>Laragon + Mailpit:</strong> Laragon → Start Mailpit → SMTP <code>127.0.0.1:1025</code> → xem mail tại <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a> (không vào hộp thư Gmail thật).</p>
    <p><strong>Gmail thật:</strong> Sửa <code>config/mail_config.php</code>: host <code>smtp.gmail.com</code>, port <code>587</code>, encryption <code>tls</code>, App Password.</p>
<?php else:
    $html = '<p>Nếu bạn nhận được email này, cấu hình SMTP đã hoạt động.</p>';
    $ok = MailService::send($to, '[Candy Crunch] Test email', $html);
    if ($ok): ?>
        <p class="ok">Đã gửi thử tới <strong><?= htmlspecialchars($to) ?></strong>.</p>
        <?php if (MAIL_HOST === '127.0.0.1' && (int) MAIL_PORT === 1025): ?>
            <p>Đang dùng Mailpit — mở <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a> để xem (không phải inbox Gmail).</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="err">Gửi thất bại. Xem <strong>Laragon → PHP → error log</strong> để biết chi tiết.</p>
        <ul>
            <li>Mailpit chưa chạy? Bật Mailpit trong Laragon.</li>
            <li>Dùng Gmail? Cần App Password và <code>MAIL_ENCRYPTION = 'tls'</code>.</li>
        </ul>
    <?php endif;
endif; ?>
</body>
</html>
