<?php 
$ROOT = '/Candy-Crunch-Website';
include __DIR__ . '/../../../partials/header.php'; 
?>

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
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/order_detail.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/notification.css">
</head>
<body>
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="breadcrumb">
            <a href="<?php echo $ROOT; ?>/index.php" class="breadcrumb-item home-icon">
                <img src="<?php echo $ROOT; ?>/views/website/img/home.svg">
                <i class="fas fa-home"></i>
            </a>
            <span class="separator"></span>
            <a href="<?php echo $ROOT; ?>/views/website/php/my_account.php" class="breadcrumb-item">
                My Account
            </a>
            <span class="separator"></span>
            <a href="<?php echo $ROOT; ?>/views/website/php/my_orders.php" class="breadcrumb-item">
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
                <?php else: ?>
                    <p>No shipping address found.</p>
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
                    <span class="value-payment"><?php echo number_format($data['summary']['subtotal'], 0, ',', '.'); ?> VND</span>
                </div>

                <div class="payment-row discount">
                    <span class="label">Discount</span>
                    <span class="value-payment">
                        <?php echo $data['summary']['discount'] > 0 ? '-' : ''; ?>
                        <?php echo number_format($data['summary']['discount'], 0, ',', '.'); ?> VND
                    </span>
                </div>

                <div class="payment-row promo">
                    <span class="label">Promo</span>
                    <span class="value-payment">
                        <?php echo $data['summary']['promo'] > 0 ? '-' : ''; ?>
                        <?php echo number_format($data['summary']['promo'], 0, ',', '.'); ?> VND
                    </span>
                </div>

                <div class="payment-row shippingfee">
                    <span class="label">Shipping fee</span>
                    <span class="value-payment"><?php echo number_format($data['summary']['shipping_fee'], 0, ',', '.'); ?> VND</span>
                </div>
            </div>

            <div class="payment-total">
                <span class="label">Total</span>
                <span class="value-payment"><?php echo number_format($data['summary']['total'], 0, ',', '.'); ?> VND</span>
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

    </div>

    <!-- Rating Popup -->
    <div id="rating-overlay" class="overlay hidden">
        <div class="rating-popup">
            <button class="close-btn" id="closeRatingPopup">&times;</button>
            <h2 class="rating-title">Rating</h2>
            <p class="rating-desc">Share your thoughts and help Candy Crunch get even sweeter!</p>
            
            <input type="hidden" id="rating-order-id" value="">
            
            <div class="input">
                <label class="input-label">Select Product</label>
                <div class="input-field">
                    <select id="rating-product-select">
                        <?php foreach ($data['products'] as $product): ?>
                            <option value="<?php echo htmlspecialchars($product['SKUID']); ?>">
                                <?php echo htmlspecialchars($product['ProductName']); ?> - <?php echo htmlspecialchars($product['Attribute']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="input">
                <label class="input-label">Your Rating</label>
                <div class="star-rating" data-rating="0">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
            </div>

            <div class="input">
                <label class="input-label">Product Review</label>
                <div class="input-field">
                    <textarea id="rating-review-text" placeholder="Provide a detailed review..." rows="3"></textarea>
                </div>
            </div>

            <div class="return-submit">
                <button class="btn-primary-medium" id="submitRating">Submit</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/rating.css">
    <script src="<?php echo $ROOT; ?>/views/website/js/main.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/order_detail.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/rating.js"></script>
</body>
</html>

<?php include __DIR__ . '/../../../partials/footer_kovid.php'; ?>