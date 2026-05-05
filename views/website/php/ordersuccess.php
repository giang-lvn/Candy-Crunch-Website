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
require_once __DIR__ . '/../../../models/db.php';

// Load data from database if we have valid orderId
if (!empty($orderId)) {

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
// --- ĐOẠN PHP FIX LỖI TRUY VẤN ---
$suggestedProducts = [];
try {
    // Phải JOIN với bảng SKU để lấy giá tiền (OriginalPrice)
    // SQL của bạn dùng tên bảng viết thường: product, sku
    $stmtSuggest = $db->prepare("
        SELECT p.ProductID, p.ProductName, p.Image, s.OriginalPrice 
        FROM product p
        JOIN sku s ON p.ProductID = s.ProductID
        GROUP BY p.ProductID 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmtSuggest->execute();
    $suggestedProducts = $stmtSuggest->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $suggestedProducts = [];
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
            <h1 class="success-title">ORDER PLACED SUCCESSFULLY!</h1>
            <p
                style="text-align: center; color: var(--text-subtitle); font-family: 'Poppins'; margin-top: -15px; margin-bottom: 20px; font-size: 14px; padding: 0 40px;">
                Thank you for choosing <strong>Candy Crunch</strong>! 🍭 <br>
                Your order is currently being prepared and will be on its way to you soon.
            </p>



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

    <!-- PHẦN HIỂN THỊ SẢN PHẨM GỢI Ý ĐÃ CHỈNH SỬA -->
    <section class="main-section" style="padding: 60px 120px; width: 100%; max-width: 1440px; margin: 0 auto;">
        <h2
            style="font-family: 'Modak', cursive; color: var(--pink-500); text-align: center; font-size: 34px; margin-bottom: 45px; letter-spacing: 1.5px; text-shadow: 2px 2px 0px rgba(255,255,255,0.8);">
            🍬 You may also like...
        </h2>

        <!-- Ép layout Grid 4 cột ngay tại đây -->
        <div class="product-listing"
            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; width: 100%;">
            <?php if (!empty($suggestedProducts)): ?>
                <?php foreach ($suggestedProducts as $product):
                    $imgData = json_decode($product['Image'], true);
                    $imgPath = is_array($imgData) ? ($imgData[0]['path'] ?? $imgData[0]) : $product['Image'];
                    ?>
                    <div class="product-card-suggest">
                        <!-- Bọc toàn bộ nội dung bằng thẻ <a> này -->
                        <a href="productdetail-new.php?id=<?= $product['ProductID'] ?>"
                            style="text-decoration: none; color: inherit; display: block; height: 100%;">

                            <div class="product-img-wrapper">
                                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product['ProductName']) ?>">
                            </div>

                            <div class="product-info-suggest">
                                <h3 class="product-name-suggest"><?= htmlspecialchars($product['ProductName']) ?></h3>
                                <p class="product-price-suggest"><?= number_format($product['OriginalPrice'], 0, ',', '.') ?>đ
                                </p>

                                <!-- Giữ nguyên nút này để khách hàng có điểm nhấn trực quan -->
                                <div class="btn-suggest">Mua ngay</div>
                            </div>

                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p
                    style="text-align: center; width: 100%; grid-column: span 4; font-family: 'Poppins'; color: var(--text-subtitle);">
                    Looking for the sweetest treats just for you...
                </p>
            <?php endif; ?>
        </div>
    </section>

    <style>
        /* ================================================
   PHẦN STYLE CHO SẢN PHẨM GỢI Ý (ĐÃ TINH CHỈNH)
   ================================================ */

        /* Tạo khoảng cách lớn giữa đơn hàng và phần gợi ý */
        .main-section {
            margin-top: 40px !important;
            padding: 40px 120px !important;
            width: 100%;
            max-width: 1440px;
            margin-left: auto;
            margin-right: auto;
        }

        .main-section h2 {
            position: relative;
            display: inline-block;
            width: 100%;
            /* Tạo hiệu ứng gạch chân điệu đà bằng màu hồng nhạt */
            background: linear-gradient(transparent 60%, rgba(242, 116, 148, 0.1) 40%);
            padding-bottom: 10px;
        }

        /* 1. Khung chứa sản phẩm (Grid) */
        .product-listing {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 30px !important;
            /* Tăng khoảng cách giữa các card */
            width: 100%;
        }

        /* 2. Thẻ sản phẩm */
        .product-card-suggest {
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--gray-300);
            overflow: hidden;
            position: relative;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* 3. Hiệu ứng Hover chuyên nghiệp hơn */
        .product-card-suggest:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(242, 116, 148, 0.12) !important;
            border-color: var(--pink-200);
        }

        /* 4. Hình ảnh và hiệu ứng Zoom */
        .product-img-wrapper {
            width: 100%;
            aspect-ratio: 1/1;
            overflow: hidden;
            background: #f9f9f9;
            border-bottom: 1px solid #f1f1f1;
        }

        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .product-card-suggest:hover .product-img-wrapper img {
            transform: scale(1.1);
            color: var(--pink-500);
            /* Đổi màu tên khi hover vào card */
            text-decoration: underline;
        }

        /* 5. Thông tin sản phẩm (Chỉnh lại padding cho thoáng) */
        .product-info-suggest {
            padding: 15px 20px 20px !important;
            /* Giảm padding trên để kéo chữ lên gần ảnh */
            display: flex;
            flex-direction: column;
            gap: 8px !important;
            flex-grow: 1;
            text-align: center;
        }

        .product-name-suggest {
            font-family: 'Poppins', sans-serif;
            font-size: 15px !important;
            /* Nhỏ lại một chút cho thanh thoát */
            font-weight: 600;
            color: var(--green-500);
            height: 42px !important;
            line-height: 1.4 !important;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-price-suggest {
            font-family: 'Poppins', sans-serif;
            font-size: 17px !important;
            font-weight: 700;
            color: var(--text-black);
            margin-bottom: 5px;
        }

        /* 6. Nút Mua ngay (Gọn gàng hơn) */
        .btn-suggest {
            display: block;
            width: 100%;
            padding: 8px !important;
            /* Gọn hơn mẫu cũ */
            background: var(--green-500);
            color: #fff !important;
            text-decoration: none;
            border-radius: 99px;
            font-weight: 500;
            font-size: 13px !important;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .btn-suggest:hover {
            background: var(--pink-500);
            transform: scale(1.02);
        }

        /* 7. Badge "Favorite" */
        .product-card-suggest::before {
            content: "♥ Favorite";
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(242, 116, 148, 0.9);
            backdrop-filter: blur(4px);
            /* Hiệu ứng mờ hiện đại */
            color: white;
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            z-index: 2;
            font-weight: 600;
        }

        /* 8. Responsive */
        @media (max-width: 1024px) {
            .main-section {
                padding: 40px 24px !important;
                margin-top: 40px !important;
            }

            .product-listing {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 20px !important;
            }
        }
    </style>

    <?php include(__DIR__ . '/../../../partials/footer_kovid.php'); ?>
</body>

</html>
