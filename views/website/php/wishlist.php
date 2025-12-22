<!-- Wishlist CSS - loaded via header.php -->
<link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/wishlist.css">

<!-- WISHLIST OVERLAY -->
<div class="wishlist-overlay hidden" id="wishlist-overlay">
    <aside class="cart-panel">
        <div class="cart-info">
            <!-- CART TITLE -->
            <div class="cart-title">
                <h3>My Wishlist</h3>

                <button class="wishlist-close" aria-label="Close wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M15.8333 5.34199L14.6583 4.16699L9.99996 8.82533L5.34163 4.16699L4.16663 5.34199L8.82496 10.0003L4.16663 14.6587L5.34163 15.8337L9.99996 11.1753L14.6583 15.8337L15.8333 14.6587L11.175 10.0003L15.8333 5.34199Z"
                            fill="black" />
                    </svg>
                </button>
            </div>

            <!-- CART PRODUCT -->
            <div class="cart-product">

                <?php if (empty($wishlistItems)): ?>
                    <!-- EMPTY STATE -->
                    <p class="empty-cart">Your wishlist is empty.</p>

                <?php else: ?>

                    <!-- HAS PRODUCT STATE -->
                    <div class="cart-has-product">

                        <!-- PRODUCT LIST -->
                        <div class="product-list">

                            <?php foreach ($wishlistItems as $item): ?>
                                <!-- SINGLE PRODUCT -->
                                <div class="wishlist-product-item">

                                    <!-- LEFT -->
                                    <div class="wishlist-product-left">
                                        <img class="product-image" src="<?= htmlspecialchars($item['Image']) ?>"
                                            alt="<?= htmlspecialchars($item['ProductName']) ?>" />

                                        <div class="product-info">
                                            <h4 class="product-name">
                                                <?= htmlspecialchars($item['ProductName']) ?>
                                            </h4>

                                            <div class="product-meta">
                                                <button class="product-attribute" disabled>
                                                    <?= htmlspecialchars($item['Attribute']) ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="8"
                                                        viewBox="0 0 13 8" fill="none">
                                                        <path
                                                            d="M12.8 0.8C12.8 1.01667 12.7208 1.20417 12.5625 1.3625L6.9625 6.9625C6.80417 7.12083 6.61667 7.2 6.4 7.2C6.18333 7.2 5.99583 7.12083 5.8375 6.9625L0.2375 1.3625C0.0791667 1.20417 0 1.01667 0 0.8C0 0.583333 0.0791667 0.395833 0.2375 0.2375C0.395833 0.0791667 0.583333 0 0.8 0H12C12.2167 0 12.4042 0.0791667 12.5625 0.2375C12.7208 0.395833 12.8 0.583333 12.8 0.8Z"
                                                            fill="#9E9E9E" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="upsell-price">
                                                <?php if (!empty($item['PromotionPrice'])): ?>
                                                    <span class="price-old">
                                                        <?= number_format($item['OriginalPrice'], 0, ',', '.') ?> VND
                                                    </span>
                                                <?php endif; ?>

                                                <span class="price-new">
                                                    <?= number_format(
                                                        $item['PromotionPrice'] ?? $item['OriginalPrice'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) ?> VND
                                                </span>
                                            </div>
                                        </div>


                                    </div>

                                    <!-- RIGHT -->
                                    <div class="wishlist-product-right">
                                        <button class="remove-product" data-skuid="<?= $item['SKUID'] ?>"
                                            aria-label="Remove product">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none">
                                                <path
                                                    d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7"
                                                    stroke="black" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>

                                        <button class="wishlist-add" data-skuid="<?= $item['SKUID'] ?>"
                                            aria-label="Wishlist-Add to cart">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 1.75C12.862 1.75 13.6893 2.09266 14.2988 2.70215C14.9082 3.31162 15.25 4.13816 15.25 5V6.5L15.7354 6.51465C17.0204 6.55359 17.6495 6.69593 18.1074 7.07617V7.0752C18.4229 7.33727 18.6455 7.70404 18.8438 8.32617C19.0462 8.96152 19.205 9.80357 19.4268 10.9863L20.1768 14.9863C20.4881 16.6473 20.7102 17.8404 20.75 18.7549C20.7843 19.5431 20.6791 20.0519 20.4268 20.4385L20.3096 20.5967C19.9729 21.0021 19.4726 21.2418 18.5801 21.3691C17.6738 21.4984 16.4603 21.5 14.7705 21.5H9.23047C7.54006 21.5 6.32608 21.4984 5.41992 21.3691C4.52787 21.2418 4.02806 21.0021 3.69141 20.5967C3.35486 20.1913 3.2115 19.6557 3.25098 18.7549C3.29105 17.8403 3.51339 16.6474 3.82422 14.9863L4.57422 10.9863C4.79656 9.80388 4.95487 8.96178 5.15723 8.32617C5.35528 7.70411 5.57758 7.33712 5.89258 7.0752L5.89355 7.07617C6.35152 6.69593 6.98061 6.55359 8.26562 6.51465L8.75 6.5V5C8.75 4.13816 9.0928 3.31162 9.70215 2.70215C10.3115 2.09277 11.1382 1.75013 12 1.75ZM14.1719 11.1104C13.4859 10.87 12.6984 11.025 12 11.5391C11.3018 11.0253 10.5149 10.87 9.8291 11.1104C9.01314 11.3964 8.5 12.1866 8.5 13.1973C8.5001 13.8742 8.89184 14.4967 9.31445 14.9854C9.75016 15.4891 10.2943 15.9359 10.7471 16.2686L10.748 16.2695C11.1335 16.5522 11.4795 16.828 12 16.8281C12.5219 16.8281 12.8676 16.5521 13.2529 16.2695L13.2539 16.2686C13.7067 15.9359 14.2508 15.4893 14.6865 14.9854C15.1092 14.4964 15.4999 13.8737 15.5 13.1963C15.5 12.1864 14.9876 11.3963 14.1719 11.1104ZM12 2.25C11.2708 2.25013 10.5713 2.54005 10.0557 3.05566C9.54009 3.57137 9.25 4.27077 9.25 5V6.5H14.75V5C14.75 4.27077 14.4609 3.57137 13.9453 3.05566C13.4296 2.53994 12.7293 2.25 12 2.25Z" fill="#017E6A" stroke="#017E6A"/>
                                            </svg>
                                            
                                        </button>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</div>