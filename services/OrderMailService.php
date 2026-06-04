<?php

require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/../config/mail_config.php';

class OrderMailService
{
    /**
     * Gửi email xác nhận đơn hàng sau thanh toán / đặt hàng thành công.
     * Lỗi gửi mail không làm gián đoạn luồng thanh toán.
     */
    public static function sendOrderConfirmation(PDO $db, string $orderId, ?string $customerId = null): bool
    {
        if (!MAIL_ENABLED) {
            if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                error_log('OrderMailService: MAIL_ENABLED is false — skip sending for ' . $orderId);
            }
            return false;
        }

        try {
            if (!$customerId) {
                $customerId = $_SESSION['user_data']['CustomerID']
                    ?? $_SESSION['customer_id']
                    ?? null;
            }

            if (!$customerId) {
                if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                    error_log('OrderMailService: no customer_id in session for order ' . $orderId);
                }
                return false;
            }

            $stmt = $db->prepare("
                SELECT a.Email, c.FirstName, c.LastName
                FROM customer c
                JOIN account a ON c.AccountID = a.AccountID
                WHERE c.CustomerID = ?
                LIMIT 1
            ");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            $toEmail = $customer['Email'] ?? ($_SESSION['email'] ?? null);
            if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                    error_log('OrderMailService: invalid or missing email for customer ' . $customerId);
                }
                return false;
            }

            $orderStmt = $db->prepare("
                SELECT o.OrderID, o.OrderDate, o.ShippingMethod, o.ShippingFee, o.OrderStatus,
                       t.PaymentMethod, t.PaymentStatus, t.Amount
                FROM orders o
                LEFT JOIN transaction t ON t.OrderID = o.OrderID AND t.TransactionType = 'Payment'
                WHERE o.OrderID = ?
                ORDER BY t.CreatedAt DESC
                LIMIT 1
            ");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                return false;
            }

            $itemsStmt = $db->prepare("
                SELECT p.ProductName, s.Attribute, od.OrderQuantity,
                       COALESCE(s.PromotionPrice, s.OriginalPrice) AS Price
                FROM order_detail od
                JOIN sku s ON od.SKUID = s.SKUID
                JOIN product p ON s.ProductID = p.ProductID
                WHERE od.OrderID = ?
            ");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $customerName = trim(($customer['FirstName'] ?? '') . ' ' . ($customer['LastName'] ?? ''));
            if ($customerName === '') {
                $customerName = 'Khách hàng';
            }

            $subject = '[Candy Crunch] Xác nhận đơn hàng ' . $orderId;
            $html      = self::buildHtml($customerName, $order, $items);

            $sent = MailService::send($toEmail, $subject, $html);
            if ($sent) {
                if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                    error_log('OrderMailService: sent order confirmation to ' . $toEmail . ' for ' . $orderId);
                }
                self::storeInboxRedirectSession($toEmail);
                return true;
            }

            error_log('OrderMailService: FAILED to send to ' . $toEmail . ' for order ' . $orderId
                . ' — check SMTP in config/mail_config.php');
            return false;
        } catch (Throwable $e) {
            error_log('OrderMailService error: ' . $e->getMessage());
            return false;
        }
    }

    /** URL mở hộp thư web (Gmail, Outlook, Mailpit…) */
    public static function getInboxRedirectUrl(string $email): ?string
    {
        if (defined('MAIL_HOST') && MAIL_HOST === '127.0.0.1' && (int) MAIL_PORT === 1025) {
            return 'http://127.0.0.1:8025';
        }

        $domain = strtolower(ltrim(strrchr($email, '@') ?: '', '@'));

        return match ($domain) {
            'gmail.com', 'googlemail.com' => 'https://mail.google.com/mail/u/0/#inbox',
            'outlook.com', 'hotmail.com', 'live.com' => 'https://outlook.live.com/mail/',
            'yahoo.com' => 'https://mail.yahoo.com/',
            default => null,
        };
    }

    private static function storeInboxRedirectSession(string $toEmail): void
    {
        $_SESSION['last_order_email_sent'] = true;
        $_SESSION['last_order_email_to']   = $toEmail;
        $_SESSION['last_order_webmail_url'] = self::getInboxRedirectUrl($toEmail);
    }

    private static function buildHtml(string $customerName, array $order, array $items): string
    {
        $orderUrl = MAIL_SITE_URL . '/index.php?controller=OrderDetail&action=index&id='
            . urlencode($order['OrderID']);

        $total     = number_format((float) ($order['Amount'] ?? 0), 0, ',', '.');
        $shipping  = number_format((float) ($order['ShippingFee'] ?? 0), 0, ',', '.');
        $payment   = htmlspecialchars($order['PaymentMethod'] ?? 'N/A');
        $status    = htmlspecialchars($order['PaymentStatus'] ?? '');
        $orderId   = htmlspecialchars($order['OrderID']);
        $orderDate = htmlspecialchars($order['OrderDate'] ?? '');

        $rows = '';
        foreach ($items as $item) {
            $name  = htmlspecialchars($item['ProductName'] . ' (' . $item['Attribute'] . 'g)');
            $qty   = (int) $item['OrderQuantity'];
            $price = number_format((float) $item['Price'] * $qty, 0, ',', '.');
            $rows .= "<tr><td style=\"padding:8px;border-bottom:1px solid #eee;\">{$name}</td>"
                . "<td style=\"padding:8px;border-bottom:1px solid #eee;text-align:center;\">{$qty}</td>"
                . "<td style=\"padding:8px;border-bottom:1px solid #eee;text-align:right;\">{$price} VND</td></tr>";
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="3" style="padding:8px;">Không có chi tiết sản phẩm.</td></tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f9f9f9;padding:24px;">
  <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;border:1px solid #eee;">
    <h2 style="color:#e91e63;margin:0 0 8px;">Candy Crunch</h2>
    <p style="color:#555;">Xin chào <strong>{$customerName}</strong>,</p>
    <p style="color:#555;">Cảm ơn bạn đã đặt hàng! Đơn hàng của bạn đã được ghi nhận thành công.</p>
    <table style="width:100%;margin:16px 0;font-size:14px;">
      <tr><td><strong>Mã đơn:</strong></td><td>{$orderId}</td></tr>
      <tr><td><strong>Ngày đặt:</strong></td><td>{$orderDate}</td></tr>
      <tr><td><strong>Thanh toán:</strong></td><td>{$payment} ({$status})</td></tr>
      <tr><td><strong>Phí ship:</strong></td><td>{$shipping} VND</td></tr>
      <tr><td><strong>Tổng tiền:</strong></td><td><strong>{$total} VND</strong></td></tr>
    </table>
    <h3 style="font-size:16px;margin:20px 0 8px;">Sản phẩm</h3>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead>
        <tr style="background:#fdf2f8;">
          <th style="padding:8px;text-align:left;">Sản phẩm</th>
          <th style="padding:8px;">SL</th>
          <th style="padding:8px;text-align:right;">Thành tiền</th>
        </tr>
      </thead>
      <tbody>{$rows}</tbody>
    </table>
    <p style="margin-top:24px;">
      <a href="{$orderUrl}" style="background:#e91e63;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block;">
        Xem chi tiết đơn hàng
      </a>
    </p>
    <p style="color:#888;font-size:12px;margin-top:24px;">Email tự động — vui lòng không trả lời.</p>
  </div>
</body>
</html>
HTML;
    }
}
