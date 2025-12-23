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

// Load data from database if we have valid orderId
if (!empty($orderId)) {
    require_once __DIR__ . '/../../../models/db.php';
    
    // 1. Get order info from ORDERS table using PDO ($db)
    $stmtOrder = $db->prepare("
        SELECT 
            o.OrderDate, 
            o.PaymentMethod, 
            o.ShippingFee, 
            o.OrderStatus,
            o.VoucherID, 
            v.DiscountPercent, 
            v.DiscountAmount
        FROM ORDERS o
        LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
        WHERE o.OrderID = ?
    ");
    $stmtOrder->execute([$orderId]);
    $orderInfo = $stmtOrder->fetch(PDO::FETCH_ASSOC);
    
    if ($orderInfo) {
        $orderDate = $orderInfo['OrderDate'];
        $paymentMethod = $orderInfo['PaymentMethod'];
        $shippingFee = (int)$orderInfo['ShippingFee'];
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
            $qty = (int)$item['OrderQuantity'];
            $originalPrice = (float)$item['OriginalPrice'];
            $promoPrice = !empty($item['PromotionPrice']) ? (float)$item['PromotionPrice'] : $originalPrice;
            
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
                $promo = min((int)$orderInfo['DiscountAmount'], $afterDiscount);
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
            <h1 class="success-title">Order Placed Successfully!</h1>

            <!-- Order ID and Status -->
            <div class="order-header">
                <div class="order-info">
                    <span class="order-label">Order ID:</span>
                    <span class="order-id"><?= htmlspecialchars($orderId) ?></span>
                </div>
                <span class="status-tag pending"><?= htmlspecialchars($orderStatus) ?></span>
            </div>

            <!-- Payment Method -->
            <div class="info-section">
                <h3 class="section-label">Payment Method</h3>
                <p class="section-value"><?= $paymentMethod === 'COD' ? 'Cash On Delivery (COD)' : 'Bank Transfer' ?>
                </p>
            </div>

            <!-- Expected Delivery -->
            <div class="info-section">
                <h3 class="section-label">Expected Delivery Date</h3>
                <p class="section-value"><?= $expectedDelivery ?></p>
            </div>

            <!-- Shipping Address -->
            <div class="info-section shipping-section">
                <h3 class="section-label">Shipping Address</h3>
                <div class="shipping-card">
                    <div class="shipping-header">
                        <span
                            class="customer-name"><?= htmlspecialchars($shippingAddress['Fullname'] ?? 'Customer') ?></span>
                        <?php if (!empty($shippingAddress['Phone'])): ?>
                            <span class="separator">•</span>
                            <span class="customer-phone"><?= htmlspecialchars($shippingAddress['Phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="shipping-address-text">
                        <?php
                        $addrParts = array_filter([
                            $shippingAddress['Address'] ?? '',
                            $shippingAddress['City'] ?? '',
                            $shippingAddress['Country'] ?? ''
                        ]);
                        echo htmlspecialchars(implode(', ', $addrParts) ?: 'No address');
                        ?>
                    </p>
                </div>
            </div>

            <!-- Ordered Products -->
            <div class="info-section products-section">
                <h3 class="section-label">Ordered Products</h3>
                <div class="products-list">
                    <?php if (!empty($orderItems)): ?>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="product-item">
                                <div class="product-left">
                                    <div class="product-image-wrapper">
                                        <img src="<?= htmlspecialchars($item['Image'] ?? $ROOT . '/views/website/img/product-img/main-thumb-example.png') ?>"
                                            alt="<?= htmlspecialchars($item['ProductName'] ?? 'Product') ?>">
                                        <span class="product-quantity"><?= (int) ($item['CartQuantity'] ?? 1) ?></span>
                                    </div>
                                    <div class="product-info">
                                        <span
                                            class="product-name"><?= htmlspecialchars($item['ProductName'] ?? 'Product Name') ?></span>
                                        <span class="product-attribute"><?= htmlspecialchars($item['Attribute'] ?? '') ?></span>
                                    </div>
                                </div>
                                <div class="product-right">
                                    <?php
                                    $itemPrice = !empty($item['PromotionPrice']) ? $item['PromotionPrice'] : $item['OriginalPrice'];
                                    $lineTotal = $itemPrice * ($item['CartQuantity'] ?? 1);
                                    ?>
                                    <?php if (!empty($item['PromotionPrice']) && $item['PromotionPrice'] < $item['OriginalPrice']): ?>
                                        <span
                                            class="price-old"><?= number_format($item['OriginalPrice'] * ($item['CartQuantity'] ?? 1), 0, ',', '.') ?>đ</span>
                                    <?php endif; ?>
                                    <span class="price-new"><?= number_format($lineTotal, 0, ',', '.') ?>đ</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">No products in this order.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row discount">
                    <span class="summary-label">Product Discount</span>
                    <span
                        class="summary-value"><?= $discount > 0 ? '-' : '' ?><?= number_format($discount, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Shipping Fee</span>
                    <span class="summary-value"><?= number_format($shippingFee, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row promo">
                    <span class="summary-label">Voucher Discount</span>
                    <span
                        class="summary-value"><?= $promo > 0 ? '-' : '' ?><?= number_format($promo, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">Total</span>
                    <span class="summary-value"><?= number_format($total, 0, ',', '.') ?>đ</span>
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