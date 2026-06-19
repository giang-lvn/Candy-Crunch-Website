<?php

require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/../config/mail_config.php';

class OrderMailService
{
    /**
     * Gửi email xác nhận đơn hàng sau thanh toán / đặt hàng thành công.
     * Lỗi gửi mail không làm gián đoạn luồng thanh toán.
     */
    public static function sendOrderConfirmation(
        PDO $db,
        string $orderId,
        ?string $customerId = null,
        ?string $addressId = null
    ): bool {
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
                       o.VoucherID, v.DiscountPercent, v.DiscountAmount,
                       t.PaymentMethod, t.PaymentStatus, t.Amount
                FROM orders o
                LEFT JOIN voucher v ON o.VoucherID = v.VoucherID
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
                SELECT p.ProductName, p.ProductID, p.Image,
                       s.Attribute, s.SKUID, s.OriginalPrice, s.PromotionPrice,
                       od.OrderQuantity,
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

            $address = self::fetchShippingAddress($db, $customerId, self::resolveAddressId($addressId));
            $totals  = self::calculateTotals($items, $order);
            $subject = '[Candy Crunch] Xác nhận đơn hàng ' . $orderId;
            $html    = self::buildHtml($customerName, $order, $items, $address, $totals);

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

    private static function resolveAddressId(?string $addressId): ?string
    {
        if (!empty($addressId)) {
            return $addressId;
        }

        return $_SESSION['momo_pending']['address_id']
            ?? $_SESSION['paypal_pending']['address_id']
            ?? $_SESSION['selected_shipping_address']
            ?? null;
    }

    private static function fetchShippingAddress(PDO $db, string $customerId, ?string $addressId): array
    {
        if ($addressId) {
            $stmt = $db->prepare("
                SELECT Fullname, Phone, Address, CityState, Country, PostalCode
                FROM address
                WHERE AddressID = ? AND CustomerID = ?
                LIMIT 1
            ");
            $stmt->execute([$addressId, $customerId]);
            $address = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($address) {
                return $address;
            }
        }

        $stmt = $db->prepare("
            SELECT Fullname, Phone, Address, CityState, Country, PostalCode
            FROM address
            WHERE CustomerID = ?
            ORDER BY CASE WHEN AddressDefault = 'Yes' THEN 0 ELSE 1 END, AddressID
            LIMIT 1
        ");
        $stmt->execute([$customerId]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        return $address ?: [
            'Fullname'   => '',
            'Phone'      => '',
            'Address'    => '',
            'CityState'  => '',
            'Country'    => '',
            'PostalCode' => '',
        ];
    }

    private static function calculateTotals(array $items, array $order): array
    {
        $subtotal = 0.0;
        $discount = 0.0;

        foreach ($items as $item) {
            $qty           = (int) ($item['OrderQuantity'] ?? 0);
            $originalPrice = (float) ($item['OriginalPrice'] ?? 0);
            $promoPrice    = !empty($item['PromotionPrice'])
                ? (float) $item['PromotionPrice']
                : $originalPrice;

            $subtotal += $originalPrice * $qty;
            if ($promoPrice < $originalPrice) {
                $discount += ($originalPrice - $promoPrice) * $qty;
            }
        }

        $promo = 0.0;
        if (!empty($order['VoucherID'])) {
            $afterDiscount = $subtotal - $discount;
            if (!empty($order['DiscountPercent']) && $order['DiscountPercent'] > 0) {
                $promo = round($afterDiscount * ((float) $order['DiscountPercent'] / 100));
            } elseif (!empty($order['DiscountAmount']) && $order['DiscountAmount'] > 0) {
                $promo = min((float) $order['DiscountAmount'], $afterDiscount);
            }
        }

        $shipping = (float) ($order['ShippingFee'] ?? 0);
        $total    = (float) ($order['Amount'] ?? ($subtotal - $discount - $promo + $shipping));

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo'    => $promo,
            'shipping' => $shipping,
            'total'    => $total,
        ];
    }

    private static function extractThumbnail(?string $imageData): string
    {
        if (empty($imageData)) {
            return '';
        }

        $decoded = json_decode($imageData, true);
        if (!is_array($decoded)) {
            return $imageData;
        }

        foreach ($decoded as $img) {
            if (!empty($img['is_thumbnail']) && !empty($img['path'])) {
                return $img['path'];
            }
        }

        if (!empty($decoded[0])) {
            return is_array($decoded[0]) ? ($decoded[0]['path'] ?? '') : (string) $decoded[0];
        }

        return '';
    }

    private static function absoluteUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $siteUrl = rtrim(MAIL_SITE_URL, '/');
        if (str_starts_with($path, '/')) {
            $parts  = parse_url($siteUrl);
            $origin = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? 'localhost');
            if (!empty($parts['port'])) {
                $origin .= ':' . $parts['port'];
            }
            return $origin . $path;
        }

        return $siteUrl . '/' . ltrim($path, '/');
    }

    private static function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }

    private static function formatDateVi(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return htmlspecialchars($date);
        }

        return date('d/m/Y', $timestamp);
    }

    private static function labelPaymentMethod(?string $method): string
    {
        return match (strtolower(trim((string) $method))) {
            'cod'            => 'Thanh toán khi nhận hàng (COD)',
            'paypal'         => 'PayPal',
            'bank transfer'  => 'Chuyển khoản ngân hàng',
            'momo'           => 'Ví MoMo',
            default          => $method !== '' && $method !== null ? $method : 'Chưa xác định',
        };
    }

    private static function labelShippingMethod(?string $method): string
    {
        return match ($method) {
            'Express'  => 'Giao hàng nhanh',
            'Same Day' => 'Giao trong ngày',
            'Standard' => 'Giao hàng tiêu chuẩn',
            default    => $method !== '' && $method !== null ? $method : 'Giao hàng tiêu chuẩn',
        };
    }

    private static function buildAddressLines(array $address): string
    {
        $lines = array_filter([
            $address['Fullname'] ?? '',
            $address['Address'] ?? '',
            $address['CityState'] ?? '',
            $address['PostalCode'] ?? '',
            $address['Country'] ?? '',
            !empty($address['Phone']) ? 'SĐT: ' . $address['Phone'] : '',
        ], fn($line) => trim((string) $line) !== '');

        if ($lines === []) {
            return '<p style="margin:0;color:#757575;font-size:13px;">Chưa có thông tin địa chỉ.</p>';
        }

        $html = '';
        foreach ($lines as $line) {
            $html .= '<p style="margin:0 0 4px;color:#424242;font-size:13px;line-height:1.5;">'
                . htmlspecialchars((string) $line) . '</p>';
        }

        return $html;
    }

    private static function buildProductCards(array $items): string
    {
        if ($items === []) {
            return '<p style="margin:0;color:#757575;font-size:14px;">Không có sản phẩm trong đơn hàng.</p>';
        }

        $cards = '';
        foreach ($items as $item) {
            $name          = htmlspecialchars($item['ProductName'] ?? 'Sản phẩm');
            $productId     = htmlspecialchars($item['ProductID'] ?? '');
            $skuId         = htmlspecialchars($item['SKUID'] ?? '');
            $variant       = htmlspecialchars(($item['Attribute'] ?? '') . 'g');
            $qty           = (int) ($item['OrderQuantity'] ?? 0);
            $originalPrice = (float) ($item['OriginalPrice'] ?? 0);
            $promoPrice    = !empty($item['PromotionPrice'])
                ? (float) $item['PromotionPrice']
                : $originalPrice;
            $lineDiscount  = max(0, ($originalPrice - $promoPrice) * $qty);
            $lineTotal     = $promoPrice * $qty;
            $unitPrice     = self::formatMoney($originalPrice);
            $lineTotalFmt  = self::formatMoney($lineTotal);
            $imagePath     = self::extractThumbnail($item['Image'] ?? '');
            $imageUrl      = self::absoluteUrl($imagePath);
            $imageCell     = $imageUrl !== ''
                ? '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . $name . '" width="120" height="120" style="display:block;width:120px;height:120px;object-fit:cover;border-radius:12px;border:1px solid #EEEEEE;">'
                : '<div style="width:120px;height:120px;border-radius:12px;background:#F5F5F5;border:1px solid #EEEEEE;"></div>';

            $discountRow = $lineDiscount > 0
                ? '<tr><td style="padding:4px 0;color:#757575;font-size:13px;">Giảm giá</td>'
                    . '<td style="padding:4px 0;color:#757575;font-size:13px;text-align:right;">-'
                    . self::formatMoney($lineDiscount) . '</td></tr>'
                : '';

            $cards .= <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;border-collapse:collapse;">
  <tr>
    <td width="132" valign="top" style="padding-right:12px;">{$imageCell}</td>
    <td valign="top" style="border:1px solid #EEEEEE;border-radius:12px;padding:16px;background:#FAFAFA;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td style="padding:0 0 8px;font-size:15px;font-weight:600;color:#212121;">{$name}</td>
          <td style="padding:0 0 8px;font-size:15px;font-weight:600;color:#212121;text-align:right;">{$variant}</td>
        </tr>
        <tr><td style="padding:4px 0;color:#757575;font-size:13px;">Giá gốc</td><td style="padding:4px 0;color:#757575;font-size:13px;text-align:right;">{$qty} x {$unitPrice}</td></tr>
        {$discountRow}
        <tr><td style="padding:8px 0 4px;color:#212121;font-size:13px;font-weight:600;">Thành tiền</td><td style="padding:8px 0 4px;color:#017E6A;font-size:14px;font-weight:700;text-align:right;">{$lineTotalFmt}</td></tr>
        <tr><td colspan="2" style="border-top:1px solid #EEEEEE;padding-top:10px;"></td></tr>
        <tr><td style="padding:3px 0;color:#9E9E9E;font-size:12px;">Mã sản phẩm</td><td style="padding:3px 0;color:#616161;font-size:12px;text-align:right;">{$productId}</td></tr>
        <tr><td style="padding:3px 0;color:#9E9E9E;font-size:12px;">Mã SKU</td><td style="padding:3px 0;color:#616161;font-size:12px;text-align:right;">{$skuId}</td></tr>
        <tr><td style="padding:3px 0;color:#9E9E9E;font-size:12px;">Khối lượng</td><td style="padding:3px 0;color:#616161;font-size:12px;text-align:right;">{$variant}</td></tr>
        <tr><td style="padding:3px 0;color:#9E9E9E;font-size:12px;">Số lượng</td><td style="padding:3px 0;color:#616161;font-size:12px;text-align:right;">{$qty}</td></tr>
      </table>
    </td>
  </tr>
</table>
HTML;
        }

        return $cards;
    }

    private static function buildHtml(
        string $customerName,
        array $order,
        array $items,
        array $address,
        array $totals
    ): string {
        $orderUrl = MAIL_SITE_URL . '/index.php?controller=OrderDetail&action=index&id='
            . urlencode($order['OrderID']);
        $logoUrl  = self::absoluteUrl('/Candy-Crunch-Website/views/website/img/logo.svg');

        $orderId        = htmlspecialchars($order['OrderID']);
        $orderDate      = self::formatDateVi($order['OrderDate'] ?? '');
        $payment        = htmlspecialchars(self::labelPaymentMethod($order['PaymentMethod'] ?? ''));
        $shippingMethod = htmlspecialchars(self::labelShippingMethod($order['ShippingMethod'] ?? ''));
        $customerSafe   = htmlspecialchars($customerName);
        $productCards   = self::buildProductCards($items);
        $billingLines   = self::buildAddressLines($address);
        $shippingLines  = self::buildAddressLines($address);

        $subtotal = self::formatMoney((float) $totals['subtotal']);
        $discount = self::formatMoney((float) $totals['discount']);
        $promo    = self::formatMoney((float) $totals['promo']);
        $shipping = self::formatMoney((float) $totals['shipping']);
        $total    = self::formatMoney((float) $totals['total']);

        $discountRow = (float) $totals['discount'] > 0
            ? '<tr><td style="padding:6px 0;color:#757575;font-size:14px;">Giảm giá sản phẩm</td><td style="padding:6px 0;color:#757575;font-size:14px;text-align:right;">-{$discount}</td></tr>'
            : '';
        $promoRow = (float) $totals['promo'] > 0
            ? '<tr><td style="padding:6px 0;color:#757575;font-size:14px;">Mã giảm giá</td><td style="padding:6px 0;color:#757575;font-size:14px;text-align:right;">-{$promo}</td></tr>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác nhận đơn hàng {$orderId}</title>
</head>
<body style="margin:0;padding:0;background:#FAFAFA;font-family:Poppins,Arial,sans-serif;color:#212121;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAFAFA;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;border-collapse:collapse;">

          <tr>
            <td style="background:#F8F5EE;padding:36px 32px 28px;text-align:center;border-radius:16px 16px 0 0;">
              <img src="{$logoUrl}" alt="Candy Crunch" width="56" height="56" style="display:block;margin:0 auto 16px;">
              <p style="margin:0 0 10px;font-size:12px;letter-spacing:2px;color:#616161;font-weight:600;">XÁC NHẬN ĐƠN HÀNG</p>
              <p style="margin:0 0 8px;font-size:18px;font-weight:600;color:#212121;">{$customerSafe}, cảm ơn bạn đã đặt hàng!</p>
              <p style="margin:0;font-size:14px;line-height:1.6;color:#757575;max-width:460px;display:inline-block;">
                Chúng tôi đã nhận đơn hàng của bạn và sẽ liên hệ khi đơn được giao cho đơn vị vận chuyển.
                Bạn có thể xem thông tin chi tiết bên dưới.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background:#FFFFFF;padding:32px;border-left:1px solid #EEEEEE;border-right:1px solid #EEEEEE;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                  <td style="font-size:18px;font-weight:700;color:#212121;">Tóm tắt đơn hàng</td>
                  <td align="right" style="font-size:14px;color:#757575;">{$orderDate}</td>
                </tr>
                <tr><td colspan="2" style="padding-top:10px;border-bottom:1px solid #E0E0E0;"></td></tr>
              </table>

              {$productCards}

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0 24px;">
                <tr>
                  <td style="font-size:18px;font-weight:700;color:#212121;">Tổng cộng đơn hàng</td>
                </tr>
                <tr><td style="padding-top:10px;border-bottom:1px solid #E0E0E0;"></td></tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
                <tr><td style="padding:6px 0;color:#757575;font-size:14px;">Tạm tính</td><td style="padding:6px 0;color:#212121;font-size:14px;text-align:right;">{$subtotal}</td></tr>
                {$discountRow}
                {$promoRow}
                <tr><td style="padding:6px 0;color:#757575;font-size:14px;">Phí vận chuyển</td><td style="padding:6px 0;color:#212121;font-size:14px;text-align:right;">{$shipping}</td></tr>
                <tr><td colspan="2" style="padding-top:8px;border-bottom:1px solid #E0E0E0;"></td></tr>
                <tr><td style="padding:14px 0 0;font-size:15px;font-weight:700;color:#212121;">Tổng thanh toán</td><td style="padding:14px 0 0;font-size:16px;font-weight:700;color:#017E6A;text-align:right;">{$total}</td></tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0 24px;">
                <tr>
                  <td style="font-size:18px;font-weight:700;color:#212121;">Thanh toán &amp; Giao hàng</td>
                </tr>
                <tr><td style="padding-top:10px;border-bottom:1px solid #E0E0E0;"></td></tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="50%" valign="top" style="padding-right:12px;">
                    <p style="margin:0 0 10px;font-size:14px;font-weight:700;color:#212121;">Thông tin nhận hàng</p>
                    {$shippingLines}
                  </td>
                  <td width="50%" valign="top" style="padding-left:12px;">
                    <p style="margin:0 0 10px;font-size:14px;font-weight:700;color:#212121;">Thông tin thanh toán</p>
                    {$billingLines}
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                <tr>
                  <td width="50%" style="padding:10px 12px 0 0;border-top:1px solid #EEEEEE;">
                    <p style="margin:0 0 4px;font-size:12px;color:#9E9E9E;">Phương thức thanh toán</p>
                    <p style="margin:0;font-size:14px;color:#212121;">{$payment}</p>
                  </td>
                  <td width="50%" style="padding:10px 0 0 12px;border-top:1px solid #EEEEEE;">
                    <p style="margin:0 0 4px;font-size:12px;color:#9E9E9E;">Phương thức vận chuyển</p>
                    <p style="margin:0;font-size:14px;color:#212121;">{$shippingMethod}</p>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;">
                <tr>
                  <td align="center">
                    <a href="{$orderUrl}" style="display:inline-block;background:#017E6A;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:600;padding:14px 36px;border-radius:999px;">
                      Xem đơn hàng
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="background:#FFFFFF;padding:0 32px 28px;border:1px solid #EEEEEE;border-top:none;border-radius:0 0 16px 16px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#9E9E9E;line-height:1.6;">
                Email tự động từ Candy Crunch — vui lòng không trả lời.<br>
                Mã đơn hàng: <strong>{$orderId}</strong>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
