<?php
// Cart Partial Component
// This file should be included in header.php, not used as a standalone page
?>

<!-- Cart CSS - loaded via header.php -->
<link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/cart.css">

<!-- OVERLAY -->
<div class="cart-overlay hidden" id="cart-overlay">
    <!-- GIỎ HÀNG -->
    <aside class="cart-panel">
        <!-- TIÊU ĐỀ + SẢN PHẨM TRONG GIỎ-->
        <div class="cart-info">
            <!-- CART TITLE -->
            <div class="cart-title">
                <h3>Your cart <span class="cart-count">(<?= !empty($cartItems) ? count($cartItems) : 0 ?>)</span></h3>

                <button class="cart-close" aria-label="Close cart">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M15.8333 5.34199L14.6583 4.16699L9.99996 8.82533L5.34163 4.16699L4.16663 5.34199L8.82496 10.0003L4.16663 14.6587L5.34163 15.8337L9.99996 11.1753L14.6583 15.8337L15.8333 14.6587L11.175 10.0003L15.8333 5.34199Z"
                            fill="black" />
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

                        <!-- FREE SHIPPING INFO -->
                        <div class="free-shipping">
                            <?php if ($remainingForFreeShip > 0): ?>
                                <p>Spend <strong><?= number_format($remainingForFreeShip, 0, ',', '.') ?> VND</strong> more for
                                    FREE SHIPPING</p>
                            <?php else: ?>
                                <p><strong>You've got FREE SHIPPING!</strong></p>
                            <?php endif; ?>

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
                                        <img class="product-image" src="<?= htmlspecialchars($item['Image']) ?>"
                                            alt="<?= htmlspecialchars($item['ProductName']) ?>" />

                                        <div class="product-info">
                                            <h4 class="product-name">
                                                <?= htmlspecialchars($item['ProductName']) ?>
                                            </h4>

                                            <div class="product-meta">
                                                <!-- ATTRIBUTE -->
                                                <button class="product-attribute" disabled>
                                                    <?= htmlspecialchars($item['Attribute']) ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="8"
                                                        viewBox="0 0 13 8" fill="none">
                                                        <path
                                                            d="M12.8 0.8C12.8 1.01667 12.7208 1.20417 12.5625 1.3625L6.9625 6.9625C6.80417 7.12083 6.61667 7.2 6.4 7.2C6.18333 7.2 5.99583 7.12083 5.8375 6.9625L0.2375 1.3625C0.0791667 1.20417 0 1.01667 0 0.8C0 0.583333 0.0791667 0.395833 0.2375 0.2375C0.395833 0.0791667 0.583333 0 0.8 0H12C12.2167 0 12.4042 0.0791667 12.5625 0.2375C12.7208 0.395833 12.8 0.583333 12.8 0.8Z"
                                                            fill="#9E9E9E" />
                                                    </svg>
                                                </button>

                                                <!-- QUANTITY -->
                                                <div class="quantity-control">
                                                    <button data-skuid="<?= $item['SKUID'] ?>" class="qty-minus">-</button>
                                                    <span><?= (int) $item['CartQuantity'] ?></span>
                                                    <button data-skuid="<?= $item['SKUID'] ?>" class="qty-plus">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RIGHT -->
                                    <div class="product-right">
                                        <button class="remove-product" data-skuid="<?= $item['SKUID'] ?>"
                                            aria-label="Remove product">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none">
                                                <path
                                                    d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z"
                                                    fill="black" />
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

        <!-- PAYMENT -->
        <div class="payment-section">

            <!-- INFO -->
            <div class="payment-info">

                <!-- SUBTOTAL -->
                <div class="payment-row subtotal">
                    <span class="label">Subtotal</span>

                    <span class="value-payment">
                        <?= number_format($subtotal ?? 0, 0, ',', '.') ?> VND
                    </span>
                </div>

                <!-- DISCOUNT -->
                <div class="payment-row discount">
                    <span class="label">Discount</span>

                    <span class="value-payment">
                        <?= ($discount ?? 0) > 0 ? '-' : '' ?>
                        <?= number_format($discount ?? 0, 0, ',', '.') ?> VND
                    </span>
                </div>

                <!-- PROMO -->
                <div class="payment-row promo">
                    <span class="label">Promo</span>

                    <span class="value-payment">
                        <?= ($promo ?? 0) > 0 ? '-' : '' ?>
                        <?= number_format($promo ?? 0, 0, ',', '.') ?> VND
                    </span>
                </div>

                <!-- SHIPPING -->
                <div class="payment-row shippingfee">
                    <span class="label">Shipping fee</span>

                    <span class="value-payment">
                        <?= ($shipping ?? 0) > 0 ? '-' : '' ?>
                        <?= number_format($shipping ?? 0, 0, ',', '.') ?> VND
                    </span>
                </div>

                <!-- PROMO INPUT -->
                <div class="promo-input">
                    <div class="promo-input-wrapper">
                        <input type="text" name="voucher_code" class="promo-input-field" placeholder="Add promo code">
                        <button class="promo-apply">Apply</button>
                    </div>
                </div>

            </div>

            <!-- TOTAL -->
            <div class="payment-total">
                <span class="label">Total</span>

                <span class="value-payment">
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
<!-- End Cart Partial -->