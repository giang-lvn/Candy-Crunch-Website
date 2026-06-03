<?php

require_once __DIR__ . '/../config/mail_config.php';

class MailService
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        if (!MAIL_ENABLED) {
            if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                error_log('MailService: MAIL_ENABLED is false');
            }
            return false;
        }
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('MailService: invalid recipient: ' . $to);
            return false;
        }

        if (MAIL_USE_SMTP) {
            return self::sendViaSmtp($to, $subject, $htmlBody);
        }

        return self::sendViaPhpMail($to, $subject, $htmlBody);
    }

    private static function sendViaPhpMail(string $to, string $subject, string $htmlBody): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::formatFrom(),
        ];

        return @mail($to, self::encodeSubject($subject), $htmlBody, implode("\r\n", $headers));
    }

    private static function sendViaSmtp(string $to, string $subject, string $htmlBody): bool
    {
        $encryption = strtolower(MAIL_ENCRYPTION);
        $host       = MAIL_HOST;
        $port       = (int) MAIL_PORT;

        if ($encryption === 'ssl') {
            $remote = 'ssl://' . $host . ':' . $port;
        } else {
            $remote = $host . ':' . $port;
        }

        $socket = @stream_socket_client($remote, $errno, $errstr, 30);
        if (!$socket) {
            error_log("Mail SMTP connect failed: $errstr ($errno)");
            return false;
        }

        stream_set_timeout($socket, 30);

        if (!self::expect($socket, [220])) {
            fclose($socket);
            return false;
        }

        self::cmd($socket, 'EHLO localhost');
        if (!self::expect($socket, [250])) {
            fclose($socket);
            return false;
        }

        if ($encryption === 'tls') {
            self::cmd($socket, 'STARTTLS');
            if (!self::expect($socket, [220])) {
                fclose($socket);
                return false;
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log('Mail SMTP STARTTLS failed');
                fclose($socket);
                return false;
            }
            self::cmd($socket, 'EHLO localhost');
            if (!self::expect($socket, [250])) {
                fclose($socket);
                return false;
            }
        }

        if (MAIL_USERNAME !== '') {
            self::cmd($socket, 'AUTH LOGIN');
            if (!self::expect($socket, [334])) {
                fclose($socket);
                return false;
            }
            self::cmd($socket, base64_encode(MAIL_USERNAME));
            if (!self::expect($socket, [334])) {
                fclose($socket);
                return false;
            }
            self::cmd($socket, base64_encode(MAIL_PASSWORD));
            if (!self::expect($socket, [235])) {
                fclose($socket);
                return false;
            }
        }

        self::cmd($socket, 'MAIL FROM:<' . MAIL_FROM_EMAIL . '>');
        if (!self::expect($socket, [250])) {
            fclose($socket);
            return false;
        }

        self::cmd($socket, 'RCPT TO:<' . $to . '>');
        if (!self::expect($socket, [250, 251])) {
            fclose($socket);
            return false;
        }

        self::cmd($socket, 'DATA');
        if (!self::expect($socket, [354])) {
            fclose($socket);
            return false;
        }

        $message = "From: " . self::formatFrom() . "\r\n";
        $message .= "To: <{$to}>\r\n";
        $message .= "Subject: " . self::encodeSubject($subject) . "\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "\r\n";
        $message .= $htmlBody . "\r\n";
        $message .= ".";

        fwrite($socket, $message . "\r\n");
        if (!self::expect($socket, [250])) {
            fclose($socket);
            return false;
        }

        self::cmd($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    private static function formatFrom(): string
    {
        return sprintf('%s <%s>', MAIL_FROM_NAME, MAIL_FROM_EMAIL);
    }

    private static function encodeSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private static function cmd($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    private static function expect($socket, array $codes): bool
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            error_log('Mail SMTP unexpected response: ' . trim($response));
            return false;
        }

        return true;
    }
}
