<?php
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('Localhost only');
}

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../services/MailService.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== Mail diagnose ===\n\n";
echo 'MAIL_ENABLED: ' . (MAIL_ENABLED ? 'true' : 'false') . "\n";
echo 'MAIL_HOST: ' . MAIL_HOST . ':' . MAIL_PORT . "\n";
echo 'MAIL_ENCRYPTION: ' . (MAIL_ENCRYPTION ?: '(none)') . "\n";
echo 'MAIL_USERNAME: ' . (MAIL_USERNAME !== '' ? MAIL_USERNAME : '(empty)') . "\n";
echo 'MAIL_FROM_EMAIL: ' . (MAIL_FROM_EMAIL !== '' ? MAIL_FROM_EMAIL : '(empty)') . "\n\n";

$errno = 0;
$errstr = '';
$enc = strtolower(MAIL_ENCRYPTION);
$remote = ($enc === 'ssl') ? 'ssl://' . MAIL_HOST . ':' . MAIL_PORT : MAIL_HOST . ':' . MAIL_PORT;
$sock = @stream_socket_client($remote, $errno, $errstr, 5);
echo 'TCP connect: ' . ($sock ? "OK ($remote)\n" : "FAIL — $errstr ($errno)\n");

if ($sock) {
    $greeting = fgets($sock, 512);
    echo 'Greeting: ' . trim($greeting) . "\n";
    fclose($sock);
}

$to = $_GET['to'] ?? 'test@example.com';
echo "\nSend test to: $to\n";
$ok = MailService::send($to, '[Candy Crunch] Diagnose', '<p>Test OK</p>');
echo 'MailService::send: ' . ($ok ? 'SUCCESS' : 'FAILED') . "\n";
echo "\nCheck PHP error log for SMTP details.\n";
