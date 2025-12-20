<?php include '../../../partials/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail - <?php echo htmlspecialchars($data['order']['OrderID']); ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/order_detail.css">
    <link rel="stylesheet" href="../css/notification.css">
</head>
<body>
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="breadcrumb">
            <a href="index.php" class="breadcrumb-item home-icon">
                <img src="../img/home.svg">
                <i class="fas fa-home"></i>
            </a>
            <span class="separator"></span>
            <a href="my_account.php" class="breadcrumb-item">
                My Account
            </a>
            <span class="separator"></span>
            <a href="my_orders.php" class="breadcrumb-item">
                My Orders
            </a>
            <span class="separator"></span>
            <span class="breadcrumb-item active">
                Order Detail
            </span> 
        </div>
    </div>

    <!-- TITLE -->
    <div class="title">
        <h2>ORDER DETAIL</h2>
    </div>

    <!-- THÔNG TIN ĐƠN HÀNG -->
    <div class="order-detail">

        <!-- ORDER + SHIPPING -->
        <div class="order-info">
            <!-- ORDER INFORMATION -->
            <div class="detail">
                <div class="section-title">
                    <h3>Order Information</h3>
                </div>

                <div class="info-row">
                    <span class="label">Order ID:</span>
                    <span class="value order-id"><?php echo htmlspecialchars($data['order']['OrderID']); ?></span>
                </div>

                <div class="info-row">
                    <span class="label">Order Status:</span>
                    <span class="value"><?php echo htmlspecialchars($data['order']['OrderStatus']); ?></span>
                </div>

                <div class="info-row">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('d-m-Y H:i', strtotime($data['order']['OrderDate'])); ?></span>
                </div>

                <div class="info-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo htmlspecialchars($data['order']['PaymentMethod']); ?></span>
                </div>

                <div class="info-row">
                    <span class="label">Shipping Method:</span>
                    <span class="value"><?php echo htmlspecialchars($data['order']['ShippingMethod']); ?></span>
                </div>
            </div>

            <!-- SHIPPING INFORMATION -->
            <div class="detail">
                <div class="section-title">
                    <h3>Shipping Information</h3>
                </div>

                <?php if ($data['shippingAddress']): ?>
                    <div class="info-row">
                        <span class="label">Full Name:</span>
                        <span class="value"><?php echo htmlspecialchars($data['shippingAddress']['Fullname']); ?></span>
                    </div>

                    <div class="info-row">
                        <span class="label">Phone Number:</span>
                        <span class="value"><?php echo htmlspecialchars($data['shippingAddress']['Phone']); ?></span>
                    </div>

                    <div class="info-row">
                        <span class="label">Shipping Address:</span>
                        <span class="value">
                            <?php echo htmlspecialchars($data['shippingAddress']['Address']); ?>,
                            <?php echo htmlspecialchars($data['shippingAddress']['CityState']); ?>,
                            <?php echo htmlspecialchars($data['shippingAddress']['Country']); ?>
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="label">Postal Code:</span>
                        <span class="value"><?php echo htmlspecialchars($data['shippingAddress']['PostalCode']); ?></span>
                    </div>
                <?php else: ?>
                    <p>No shipping address found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- STATUS -->
        <div class="status-detail">
            <div class="status-title">
                <h3>Order Status</h3>
            </div>

            <!-- STATUS LIST -->
            <div class="status-list">
                <?php
                // Định nghĩa các trạng thái và logic hiển thị
                $statusFlow = [
                    'Waiting Payment' => ['time' => $data['order']['OrderDate'], 'active' => true],
                    'Pending Confirmation' => ['time' => null, 'active' => false],
                    'Pending' => ['time' => null, 'active' => false],
                    'On Shipping' => ['time' => null, 'active' => false],
                    'Complete' => ['time' => null, 'active' => false]
                ];

                $currentStatus = $data['order']['OrderStatus'];
                $statusReached = true;

                foreach ($statusFlow as $status => $info):
                    if ($status === $currentStatus) {
                        $statusReached = false;
                    }
                    
                    $isActive = ($status === $currentStatus || $statusReached) ? 'active' : '';
                ?>
                    <div class="status-item <?php echo $isActive; ?>">
                        <div class="status-icon"></div>
                        
                        <?php if ($info['time']): ?>
                            <div class="time"><?php echo date('H:i', strtotime($info['time'])); ?></div>
                            <div class="date"><?php echo date('d-m-Y', strtotime($info['time'])); ?></div>
                        <?php else: ?>
                            <div class="time">--:--</div>
                            <div class="date">--/--/----</div>
                        <?php endif; ?>
                        
                        <div class="status-text"><?php echo $status; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ACTIONS BUTTONS -->
            <div class="order-actions">
                <?php if ($data['buttons']['pay_now']): ?>
                    <button class="btn-primary-medium btn-pay-now">Pay Now</button>
                <?php endif; ?>

                <?php if ($data['buttons']['change_method']): ?>
                    <button class="btn-primary-outline-medium btn-change-method">Change Method</button>
                <?php endif; ?>

                <?php if ($data['buttons']['buy_again']): ?>
                    <button class="btn-primary-medium btn-buy-again">Buy Again</button>
                <?php endif; ?>

                <?php if ($data['buttons']['write_review']): ?>
                    <button class="btn-primary-outline-medium btn-write-review">Write Review</button>
                <?php endif; ?>

                <?php if ($data['buttons']['return']): ?>
                    <button class="btn-secondary-outline-medium btn-return">Return</button>
                <?php endif; ?>

                <?php if ($data['buttons']['cancel']): ?>
                    <button class="btn-secondary-medium btn-cancel-order">Cancel</button>
                <?php endif; ?>

                <?php if ($data['buttons']['contact']): ?>
                    <button class="btn-secondary-outline-medium btn-contact">Contact</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- PRODUCTS -->
        <div class="detail">
            <div class="section-title">
                <h3>Products</h3>
            </div>

            <div class="products-list">
                <?php foreach ($data['products'] as $item):
                    $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
                    $hasDiscount = isset($item['PromotionPrice']) && $item['PromotionPrice'] < $item['OriginalPrice'];
                ?>
                    <div class="single-product">
                        <div class="product-info">
                            <!-- IMAGE -->
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($item['Image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                            </div>

                            <!-- DETAILS -->
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($item['ProductName']); ?></div>
                                <div class="product-attribute"><?php echo htmlspecialchars($item['Attribute']); ?>g</div>

                                <div class="product-price-qty">
                                    <div class="product-quantity">Quantity: <strong><?php echo $item['OrderQuantity']; ?></strong></div>
                                    <div class="product-price">
                                        <?php if ($hasDiscount): ?>
                                            <span class="price-old"><?php echo number_format($item['OriginalPrice'], 0, ',', '.'); ?> VND</span>
                                        <?php endif; ?>
                                        <span class="price-new"><?php echo number_format($price, 0, ',', '.'); ?> VND</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="summary-detail">
            <div class="section-title">
                <h3>Order Summary</h3>
            </div>

            <div class="payment-info">
                <div class="payment-row subtotal">
                    <span class="label">Subtotal</span>
                    <span class="value"><?php echo number_format($data['summary']['subtotal'], 0, ',', '.'); ?> VND</span>
                </div>

                <div class="payment-row discount">
                    <span class="label">Discount</span>
                    <span class="value">
                        <?php echo $data['summary']['discount'] > 0 ? '-' : ''; ?>
                        <?php echo number_format($data['summary']['discount'], 0, ',', '.'); ?> VND
                    </span>
                </div>

                <div class="payment-row promo">
                    <span class="label">Promo</span>
                    <span class="value">
                        <?php echo $data['summary']['promo'] > 0 ? '-' : ''; ?>
                        <?php echo number_format($data['summary']['promo'], 0, ',', '.'); ?> VND
                    </span>
                </div>

                <div class="payment-row shipping">
                    <span class="label">Shipping fee</span>
                    <span class="value"><?php echo number_format($data['summary']['shipping_fee'], 0, ',', '.'); ?> VND</span>
                </div>
            </div>

            <div class="payment-total">
                <span class="label">Total</span>
                <span class="value"><?php echo number_format($data['summary']['total'], 0, ',', '.'); ?> VND</span>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="../js/main.js"></script>
    <script src="../js/order_detail.js"></script>
</body>
</html>

<?php include '../../../partials/footer_kovid.php'; ?>