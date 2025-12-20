
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <!-- Preload Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <!-- OVERLAY -->
    <div class="cart-overlay">
        <!-- GIỎ HÀNG -->
        <aside class="cart-panel">
            <!-- TIÊU ĐỀ + SẢN PHẨM TRONG GIỎ-->
            <div class="cart-info">
                <!-- CART TITLE -->
                <div class="cart-title">
                    <h3>Your cart <span class="cart-count">(<?= !empty($cartItems) ? count($cartItems) : 0 ?>)</span></h3>
            
                    <button class="cart-close" aria-label="Close cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15.8333 5.34199L14.6583 4.16699L9.99996 8.82533L5.34163 4.16699L4.16663 5.34199L8.82496 10.0003L4.16663 14.6587L5.34163 15.8337L9.99996 11.1753L14.6583 15.8337L15.8333 14.6587L11.175 10.0003L15.8333 5.34199Z" fill="black"/>
                        </svg>
                    </button>
                </div>
            
                <!-- CART PRODUCT -->
                <div class="cart-product">

                    <?php if (empty($cartItems)): ?>
                        <!-- EMPTY CART STATE -->
                        <p class="empty-cart">Your cart is empty.</p>

                    <?php else: ?>

                        <!-- HAS PRODUCT STATE -->
                        <div class="cart-has-product">

                            <!-- FREE SHIPPING INFO (optional static) -->
                            <div class="free-shipping">
                                <p>Spend <strong>50.000 VND</strong> more for FREE SHIPPING</p>

                                <div class="shipping-bar">
                                    <span class="bar-yellow"></span>
                                    <span class="bar-green"></span>
                                </div>
                            </div>

                            <!-- PRODUCT LIST -->
                            <div class="product-list">

                                <?php foreach ($cartItems as $item): ?>
                                <!-- SINGLE PRODUCT -->
                                <div class="product-item">

                                    <!-- LEFT -->
                                    <div class="product-left">
                                        <img
                                            class="product-image"
                                            src="<?= htmlspecialchars($item['Image']) ?>"
                                            alt="<?= htmlspecialchars($item['ProductName']) ?>"
                                        />

                                        <div class="product-info">
                                            <h4 class="product-name">
                                                <?= htmlspecialchars($item['ProductName']) ?>
                                            </h4>

                                            <div class="product-meta">
                                                <!-- ATTRIBUTE -->
                                                <button class="product-attribute" disabled>
                                                    <?= htmlspecialchars($item['Attribute']) ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="8" viewBox="0 0 13 8" fill="none">
                                                        <path d="M12.8 0.8C12.8 1.01667 12.7208 1.20417 12.5625 1.3625L6.9625 6.9625C6.80417 7.12083 6.61667 7.2 6.4 7.2C6.18333 7.2 5.99583 7.12083 5.8375 6.9625L0.2375 1.3625C0.0791667 1.20417 0 1.01667 0 0.8C0 0.583333 0.0791667 0.395833 0.2375 0.2375C0.395833 0.0791667 0.583333 0 0.8 0H12C12.2167 0 12.4042 0.0791667 12.5625 0.2375C12.7208 0.395833 12.8 0.583333 12.8 0.8Z" fill="#9E9E9E"/>
                                                    </svg>
                                                </button>

                                                <!-- QUANTITY -->
                                                <div class="quantity-control">
                                                    <button data-skuid="<?= $item['SKUID'] ?>" class="qty-minus">-</button>
                                                    <span><?= (int)$item['CartQuantity'] ?></span>
                                                    <button data-skuid="<?= $item['SKUID'] ?>" class="qty-plus">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RIGHT -->
                                    <div class="product-right">
                                        <button
                                            class="remove-product"
                                            data-skuid="<?= $item['SKUID'] ?>"
                                            aria-label="Remove product"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="black"/>
                                            </svg>
                                        </button>

                                        <div class="product-price">
                                            <?php if (!empty($item['PromotionPrice'])): ?>
                                                <span class="price-old">
                                                    <?= number_format($item['OriginalPrice'], 0, ',', '.') ?> VND
                                                </span>
                                                <span class="price-new">
                                                    <?= number_format($item['PromotionPrice'], 0, ',', '.') ?> VND
                                                </span>
                                            <?php else: ?>
                                                <span class="price-new">
                                                    <?= number_format($item['OriginalPrice'], 0, ',', '.') ?> VND
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                </div>
                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>
            </div>           
            
            

            <!-- UPSELL SECTION - Hiển thị cho cả giỏ rỗng và có sản phẩm -->
                <?php if (!empty($upsellProducts)): ?>
                <div class="upsell-section">

                    <h4 class="upsell-title">You might also like</h4>

                    <div class="upsell-slider">

                        <!-- LEFT ARROW -->
                        <button class="upsell-nav left" aria-label="Upsell-Prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <!-- TRACK -->
                        <div class="upsell-track">

                            <?php foreach ($upsellProducts as $upsell): ?>
                            <div class="upsell-item">

                                <!-- LEFT -->
                                <div class="upsell-left">
                                    <img
                                        class="upsell-image"
                                        src="<?= htmlspecialchars($upsell['Image']) ?>"
                                        alt="<?= htmlspecialchars($upsell['ProductName']) ?>"
                                    />

                                    <div class="upsell-info">
                                        <h5 class="upsell-name">
                                            <?= htmlspecialchars($upsell['ProductName']) ?>
                                        </h5>
                                        
                                        <button class="upsell-attribute" disabled>
                                            <?= htmlspecialchars($upsell['Attribute']) ?>g
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="8" viewBox="0 0 13 8" fill="none">
                                                <path d="M12.8 0.8C12.8 1.01667 12.7208 1.20417 12.5625 1.3625L6.9625 6.9625C6.80417 7.12083 6.61667 7.2 6.4 7.2C6.18333 7.2 5.99583 7.12083 5.8375 6.9625L0.2375 1.3625C0.0791667 1.20417 0 1.01667 0 0.8C0 0.583333 0.0791667 0.395833 0.2375 0.2375C0.395833 0.0791667 0.583333 0 0.8 0H12C12.2167 0 12.4042 0.0791667 12.5625 0.2375C12.7208 0.395833 12.8 0.583333 12.8 0.8Z" fill="#9E9E9E"/>
                                            </svg>
                                        </button>

                                        <div class="upsell-price">
                                            <?php if (!empty($upsell['PromotionPrice'])): ?>
                                                <span class="price-old">
                                                    <?= number_format($upsell['OriginalPrice'], 0, ',', '.') ?> VND
                                                </span>
                                                <span class="price-new">
                                                    <?= number_format($upsell['PromotionPrice'], 0, ',', '.') ?> VND
                                                </span>
                                            <?php else: ?>
                                                <span class="price-new">
                                                    <?= number_format($upsell['OriginalPrice'], 0, ',', '.') ?> VND
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT -->
                                <button
                                    class="upsell-add"
                                    data-skuid="<?= $upsell['SKUID'] ?>"
                                    aria-label="Add to cart"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="black"/>
                                    </svg>
                                </button>

                            </div>
                            <?php endforeach; ?>

                        </div>

                        <!-- RIGHT ARROW -->
                        <button class="upsell-nav right" aria-label="Upsell-Next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M9 6L15 12L9 18" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                    </div>

                </div>
                <?php endif; ?>


            <!-- PAYMENT -->
            <div class="payment-section">

                <!-- INFO -->
                <div class="payment-info">

                    <!-- SUBTOTAL -->
                    <div class="payment-row subtotal">
                        <span class="label">Subtotal</span>
                    
                        <span class="value">
                            <?= number_format($subtotal ?? 0, 0, ',', '.') ?> VND
                        </span>
                    </div>

                    <!-- DISCOUNT -->
                    <div class="payment-row discount">
                        <span class="label">Discount</span>
                        
                        <span class="value">
                            <?= ($discount ?? 0) > 0 ? '-' : '' ?>
                            <?= number_format($discount ?? 0, 0, ',', '.') ?> VND
                        </span>
                    </div>

                    <!-- PROMO -->
                    <div class="payment-row promo">
                        <span class="label">Promo</span>

                        <span class="value">
                            <?= ($promo ?? 0) > 0 ? '-' : '' ?>
                            <?= number_format($promo ?? 0, 0, ',', '.') ?> VND
                        </span>
                    </div>

                    <!-- SHIPPING -->
                    <div class="payment-row shipping">
                        <span class="label">Shipping fee</span>
                        
                        <span class="value">
                            <?= ($shipping ?? 0) > 0 ? '-' : '' ?>
                            <?= number_format($shipping ?? 0, 0, ',', '.') ?> VND
                        </span>
                    </div>

                    <!-- PROMO INPUT -->
                    <div class="promo-input">
                        <div class="promo-input-wrapper">
                            <input
                                type="text"
                                name="voucher_code"
                                class="promo-input-field"
                                placeholder="Add promo code"
                            >
                            <button class="promo-apply">Apply</button>
                        </div>
                    </div>

                </div>

                <!-- TOTAL -->
                <div class="payment-total">
                    <span class="label">Total</span>
                    
                    <span class="value">
                        <?= number_format($total ?? 0, 0, ',', '.') ?> VND
                    </span>
                </div>

                <!-- BUTTON -->
                <button class="checkout-btn">
                    Proceed to Checkout
                </button>
            </div>
            
        </aside>
    </div>

    <!-- Script -->
    <script src="../js/main.js"></script>
    <script src="../js/cart.js"></script>
</body>
</html>
