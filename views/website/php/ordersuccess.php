<?php
$ROOT = '/Candy-Crunch-Website';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['AccountID'])) {
    header('Location: ' . $ROOT . '/views/website/php/login.php');
    exit;
}

// Get order ID from URL parameter
$orderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : null;

$emailSent = !empty($_SESSION['last_order_email_sent']);
$emailTo = $_SESSION['last_order_email_to'] ?? ($_SESSION['email'] ?? '');
$webmailUrl = $_SESSION['last_order_webmail_url'] ?? null;

if ($emailSent && !empty($emailTo) && empty($webmailUrl)) {
    require_once __DIR__ . '/../../../services/OrderMailService.php';
    $webmailUrl = OrderMailService::getInboxRedirectUrl($emailTo);
}

$canViewEmail = $emailSent && !empty($emailTo) && !empty($webmailUrl);

unset($_SESSION['last_order_email_sent'], $_SESSION['last_order_email_to'], $_SESSION['last_order_webmail_url']);

// Initialize all variables with defaults
$orderDate = date('Y-m-d');
$paymentMethod = 'COD';
$orderStatus = 'Pending Confirmation';
$expectedDelivery = date('d/m/Y', strtotime('+3 days'));
$orderItems = [];
$subtotal = 0;
$discount = 0;
$shippingFee = 0;
$promo = 0;
$total = 0;
$shippingAddress = [
    'Fullname' => 'Customer',
    'Phone' => '',
    'Address' => '',
    'City' => '',
    'Country' => ''
];
require_once __DIR__ . '/../../../models/db.php';

// Load data from database if we have valid orderId
if (!empty($orderId)) {

    // 1. Get order info from ORDERS table using PDO ($db)
    $stmtOrder = $db->prepare("
        SELECT 
            o.OrderDate, 
            o.ShippingFee, 
            o.OrderStatus,
            o.VoucherID, 
            v.DiscountPercent, 
            v.DiscountAmount,
            t.PaymentMethod
        FROM orders o
        LEFT JOIN voucher v ON o.VoucherID = v.VoucherID
        LEFT JOIN transaction t ON t.OrderID = o.OrderID AND t.TransactionType = 'Payment'
        WHERE o.OrderID = ?
        ORDER BY t.CreatedAt DESC
        LIMIT 1
    ");
    $stmtOrder->execute([$orderId]);
    $orderInfo = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if ($orderInfo) {
        $orderDate = $orderInfo['OrderDate'];
        $paymentMethod = $orderInfo['PaymentMethod']
            ?? $_SESSION['last_payment_method']
            ?? (isset($_GET['method']) && $_GET['method'] === 'paypal' ? 'PayPal' : 'COD');
        $shippingFee = (int) $orderInfo['ShippingFee'];
        $orderStatus = $orderInfo['OrderStatus'];
        $expectedDelivery = date('d/m/Y', strtotime($orderDate . ' +3 days'));

        // 2. Get order items from ORDER_DETAIL + SKU + PRODUCT
        $stmtItems = $db->prepare("
            SELECT 
                od.SKUID,
                od.OrderQuantity,
                p.ProductName,
                p.ProductID,
                p.Image,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice
            FROM ORDER_DETAIL od
            JOIN SKU s ON od.SKUID = s.SKUID
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE od.OrderID = ?
        ");
        $stmtItems->execute([$orderId]);
        $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. Process images and calculate subtotal/discount
        foreach ($orderItems as &$item) {
            // Process image JSON
            if (!empty($item['Image'])) {
                $decoded = json_decode($item['Image'], true);
                if (is_array($decoded)) {
                    $thumbPath = '';
                    foreach ($decoded as $img) {
                        if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                            $thumbPath = $img['path'] ?? '';
                            break;
                        }
                    }
                    if (empty($thumbPath) && !empty($decoded[0])) {
                        $thumbPath = is_array($decoded[0]) ? ($decoded[0]['path'] ?? '') : $decoded[0];
                    }
                    $item['Image'] = $thumbPath;
                }
            }

            // Calculate amounts
            $qty = (int) $item['OrderQuantity'];
            $originalPrice = (float) $item['OriginalPrice'];
            $promoPrice = !empty($item['PromotionPrice']) ? (float) $item['PromotionPrice'] : $originalPrice;

            // Add to CartQuantity for display compatibility
            $item['CartQuantity'] = $qty;

            // Subtotal = sum of original prices
            $subtotal += $originalPrice * $qty;

            // Discount = difference when promo price is lower
            if ($promoPrice < $originalPrice) {
                $discount += ($originalPrice - $promoPrice) * $qty;
            }
        }
        unset($item); // Break reference

        // 4. Calculate voucher discount (promo)
        if (!empty($orderInfo['VoucherID'])) {
            $afterDiscount = $subtotal - $discount;
            if (!empty($orderInfo['DiscountPercent']) && $orderInfo['DiscountPercent'] > 0) {
                $promo = round($afterDiscount * ($orderInfo['DiscountPercent'] / 100));
            } elseif (!empty($orderInfo['DiscountAmount']) && $orderInfo['DiscountAmount'] > 0) {
                $promo = min((int) $orderInfo['DiscountAmount'], $afterDiscount);
            }
        }

        // 5. Calculate total
        $total = $subtotal - $discount - $promo + $shippingFee;
    }
}

// Get shipping address from session (fallback)
if (isset($_SESSION['last_order_address']) && is_array($_SESSION['last_order_address'])) {
    $shippingAddress = $_SESSION['last_order_address'];
}

include(__DIR__ . '/../../../partials/header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Candy Crunch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/ordersuccess.css">
    <style>
        .email-confirmation-notice {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            font-size: 15px;
            color: #1b5e20;
            background: linear-gradient(180deg, #f1faf4 0%, #e8f5e9 100%);
            padding: 22px 24px;
            border-radius: 18px;
            border: 1px solid rgba(46, 125, 50, 0.18);
            box-shadow: 0 10px 24px rgba(30, 80, 45, 0.06);
            margin: 0 0 20px;
            gap: 14px;
            width: 100%;
            max-width: 560px;
        }

        .email-confirmation-header {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            background: rgba(22, 101, 52, 0.08);
            border-radius: 999px;
            color: #165c32;
            font-weight: 700;
            text-transform: capitalize;
        }

        .email-confirmation-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #dcedc8;
            color: #2e7d32;
            font-size: 16px;
        }

        .email-confirmation-heading {
            font-size: 14px;
        }

        .email-confirmation-text {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #175d34;
        }

        .email-confirmation-text strong {
            color: #0f3f25;
        }

        .btn-view-email {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 26px;
            background: #1e7d32;
            color: #fff !important;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 999px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease, border-color 0.2s ease;
        }

        .btn-view-email:hover {
            background: #145a25;
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        @media (max-width: 520px) {
            .email-confirmation-notice {
                padding: 18px 18px;
            }

            .email-confirmation-header {
                width: 100%;
                justify-content: center;
            }

            .btn-view-email {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="order-success-container">
        <div class="order-success-card">
            <!-- Success Icon -->
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"
                        fill="var(--green-500)" />
                </svg>
            </div>

            <!-- Success Title -->
            <h1 class="success-title">ORDER PLACED SUCCESSFULLY!</h1>
            <p
                style="text-align: center; color: var(--text-subtitle); font-family: 'Poppins'; margin-top: -15px; margin-bottom: 20px; font-size: 14px; padding: 0 40px;">
                Thank you for choosing <strong>Candy Crunch</strong>! 🍭 <br>
                Your order is currently being prepared and will be on its way to you soon.
            </p>

            <?php if ($emailSent && !empty($emailTo)): ?>
            <div class="email-confirmation-notice">
                <!-- <div class="email-confirmation-header">
                    <span class="email-confirmation-icon">✉️</span>
                    <span class="email-confirmation-heading">Email confirmation sent</span>
                </div> -->
                <p class="email-confirmation-text">
                    We have sent an order confirmation to <strong><?= htmlspecialchars($emailTo) ?></strong>.
                </p>
                <?php if ($canViewEmail): ?>
                <a href="<?= htmlspecialchars($webmailUrl) ?>"
                   class="btn-view-email">
                    View Email
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>



            <!-- Order ID and Status -->
            <!-- GIỮ LẠI KHỐI NÀY VÀ CẬP NHẬT MỘT CHÚT CHO ĐẸP -->
            <div class="order-header"
                style="flex-direction: column; gap: 15px; padding: 25px; background: #fff; border: 1px solid var(--gray-300); border-radius: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <div class="order-info">
                        <span class="order-label">Order ID:</span>
                        <span class="order-id"
                            style="color: var(--green-500); font-weight: 700;"><?= htmlspecialchars($orderId) ?></span>
                    </div>
                    <span class="status-tag pending"><?= htmlspecialchars($orderStatus) ?></span>
                </div>

                <div
                    style="width: 100%; padding-top: 15px; border-top: 1px dashed #ddd; display: flex; align-items: center; gap: 10px; color: #555; font-size: 14px;">
                    <span style="font-size: 18px;">🚚</span>
                    <span>Estimated delivery time: <strong
                            style="color: var(--text-black);"><?= $expectedDelivery ?></strong></span>
                </div>
            </div>


            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?= $ROOT ?>/index.php?controller=OrderDetail&action=index&id=<?= htmlspecialchars($orderId) ?>"
                    class="btn-primary-outline-large">View Order Detail</a>
                <a href="<?= $ROOT ?>/views/website/php/shop.php" class="btn-primary-large">Continue Shopping</a>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../../../partials/footer_kovid.php'); ?>
</body>

</html>
