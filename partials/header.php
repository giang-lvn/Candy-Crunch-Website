<?php
$ROOT = '/Candy-Crunch-Website';
if (session_status() === PHP_SESSION_NONE) {
    session_start();    
}

// Check trạng thái đăng nhập
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Tính số lượng sản phẩm trong giỏ hàng (chỉ hiển thị khi đã login)
$cart_item_count = 0;
if ($is_logged_in && isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
         $cart_item_count += $quantity; 
    }
}
?>

<!-- Load CSS tùy theo trạng thái đăng nhập -->
<?php if ($is_logged_in): ?>
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/header_guest.css">
<?php else: ?>
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/header_login.css">
<?php endif; ?>
<link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">

<!-- HEADER NAV -->
<div class="header-nav">
    <img class="logo" src="<?php echo $ROOT; ?>/views/website/img/logo.svg" alt="Candy Crunch Logo" />

    <!-- Navigation Pills -->
    <div class="nav-pills">
        <a href="index.php" class="nav-item" data-active="Yes" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Homepage</div>
            </div>
        </a>

        <a href="about.php" class="nav-item" data-active="No" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">About us</div>
            </div>
        </a>

        <!-- SHOP DROPDOWN BUTTON -->
        <a href="#" class="nav-item" data-active="No" data-dropdown="true" id="shopDropdownBtn">
            <div class="inline-flex-center">
                <div class="nav-text">Shop</div>
            </div>
            <div class="dropdown-icon">
                <div class="dropdown-icon-inner"></div>
            </div>
        </a>

        <a href="checkout.php" class="nav-item" data-active="No" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Checkout</div>
            </div>
        </a>

        <a href="contact.php" class="nav-item" data-active="No" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Contact</div>
            </div>
        </a>

        <a href="policy.php" class="nav-item" data-active="No" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Policy</div>
            </div>
        </a>
    </div>

    <?php if ($is_logged_in): ?>
        <!-- ============================================ -->
        <!-- HEADER CHO NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP -->
        <!-- ============================================ -->
        <div class="user-actions">
            <!-- Cart -->
            <a href="cart.php" class="action-item cart-item">
                <img src="<?php echo $ROOT; ?>/views/website/img/cart.svg" alt="Cart" class="action-icon"/>
                <span class="action-text"> Cart (<span id="cartCount"><?php echo $cart_item_count; ?></span>) </span>
            </a>

            <!-- Wishlist -->
            <a href="wishlist.php" class="action-item">
                <img src="<?php echo @ROOT;?>/views/website/img/wishlist.svg" alt="Wishlist" class="action-icon"/>
            </a>

            <!-- Account -->
            <a href="account.php" class="action-item">
                <img src="<?php echo $ROOT; ?>/views/website/img/person.svg" alt="User" class="action-icon"/>
            </a>
        </div>

    <?php else: ?>
        <!-- ============================================ -->
        <!-- HEADER CHO KHÁCH (CHƯA ĐĂNG NHẬP) -->
        <!-- ============================================ -->
        <div class="auth-buttons">
            <a href="login.php" class="btn btn-login">
                <span class="btn-text">Log in</span>
            </a>

            <a href="signup.php" class="btn btn-signup">
                <span class="btn-text">Sign up</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- DROPDOWN CONTENT (Giống nhau cho cả 2 trạng thái) -->
    <div class="dropdown-content" id="shopDropdown">
        <div class="menu-panel">
            <!-- LEFT: Menu Columns -->
            <div class="menu-columns">
                <div class="menu-row">

                    <!-- Hard Candy -->
                    <div class="menu-column">
                        <div class="menu-title">Hard Candy</div>
                        <div class="menu-items">
                            <a class="menu-item" href="product.php?id=milk-coffee-candy" 
                               data-image="https://images.unsplash.com/photo-1575224300306-1b8da36134ec?w=400"
                               data-title="Milk Coffee Candy"
                               data-desc="Rich and creamy coffee-flavored hard candy with a smooth finish">
                                Milk Coffee Candy
                            </a>
                            <a class="menu-item" href="product.php?id=fruit-candy"
                               data-image="https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?w=400"
                               data-title="Fruit Candy"
                               data-desc="Assorted tropical fruit flavors bursting with natural sweetness">
                                Fruit Candy
                            </a>
                        </div>
                    </div>

                    <!-- Filled Hard Candy -->
                    <div class="menu-column">
                        <div class="menu-title">Filled-Hard Candy</div>
                        <div class="menu-items">
                            <a class="menu-item" href="product.php?id=caramel-coffee"
                               data-image="https://images.unsplash.com/photo-1568471173238-64ed8e7e9815?w=400"
                               data-title="Caramel-Filled Coffee Candy"
                               data-desc="Coffee candy with gooey caramel center for double indulgence">
                                Caramel-Filled Coffee Candy
                            </a>
                            <a class="menu-item" href="product.php?id=milk-filled-coffee"
                               data-image="https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=400"
                               data-title="Milk-filled Coffee Candy"
                               data-desc="Smooth milk filling wrapped in coffee-flavored shell">
                                Milk-filled Coffee Candy
                            </a>
                        </div>
                    </div>

                    <!-- Gummy -->
                    <div class="menu-column">
                        <div class="menu-title">Gummy</div>
                        <div class="menu-items">
                            <a class="menu-item" href="product.php?id=worm-gummies"
                               data-image="https://images.unsplash.com/photo-1582058091505-be6f8b6c1c88?w=400"
                               data-title="Wiggly Worm Gummies"
                               data-desc="Fun worm-shaped gummies in fruity flavors kids love">
                                Wiggly Worm Gummies
                            </a>
                            <a class="menu-item" href="product.php?id=bear-gummies"
                               data-image="https://images.unsplash.com/photo-1625869016774-3a92be2ae2cd?w=400"
                               data-title="Tiny Bear Gummies"
                               data-desc="Adorable bear-shaped gummies packed with fruit flavors">
                                Tiny Bear Gummies
                            </a>
                        </div>
                    </div>

                    <!-- Chewing Gum -->
                    <div class="menu-column">
                        <div class="menu-title">Chewing Gum</div>
                        <div class="menu-items">
                            <a class="menu-item" href="product.php?id=blueberry-chewy"
                               data-image="https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?w=400"
                               data-title="Blueberry Crisp Chewy"
                               data-desc="Crispy shell with chewy center, sweet blueberry taste">
                                Blueberry Crisp Chewy
                            </a>
                            <a class="menu-item" href="product.php?id=mint-chewy"
                               data-image="https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=400"
                               data-title="Mint Crisp Chewy"
                               data-desc="Refreshing mint flavor for lasting fresh breath">
                                Mint Crisp Chewy
                            </a>
                            <a class="menu-item" href="product.php?id=cola-chewy"
                               data-image="https://images.unsplash.com/photo-1629203851122-3726ecdf080e?w=400"
                               data-title="Cola Crisp Chewy"
                               data-desc="Classic cola taste in a fun chewing gum format">
                                Cola Crisp Chewy
                            </a>
                            <a class="menu-item" href="product.php?id=strawberry-chewy"
                               data-image="https://images.unsplash.com/photo-1588548961454-2b8e051686bf?w=400"
                               data-title="Strawberry Soft Chewy"
                               data-desc="Soft and sweet strawberry chewing gum">
                                Strawberry Soft Chewy
                            </a>
                        </div>
                    </div>

                    <!-- Marshmallow -->
                    <div class="menu-column">
                        <div class="menu-title">Marshmallow</div>
                        <div class="menu-items">
                            <a class="menu-item" href="product.php?id=vanilla-whirl"
                               data-image="https://images.unsplash.com/photo-1606312619070-d48b4a0a4f06?w=400"
                               data-title="Vanilla Cotton Whirl"
                               data-desc="Cloud-like vanilla marshmallows that melt in your mouth">
                                Vanilla Cotton Whirl
                            </a>
                            <a class="menu-item" href="product.php?id=chocolate-whirl"
                               data-image="https://images.unsplash.com/photo-1612203985729-70726954388c?w=400"
                               data-title="Chocolate Cotton Whirl"
                               data-desc="Rich chocolate marshmallows with fluffy texture">
                                Chocolate Cotton Whirl
                            </a>
                            <a class="menu-item" href="product.php?id=strawberry-whirl"
                               data-image="https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?w=400"
                               data-title="Strawberry Cotton Whirl"
                               data-desc="Pink and fluffy strawberry marshmallow delights">
                                Strawberry Cotton Whirl
                            </a>
                            <a class="menu-item" href="product.php?id=blueberry-cloud"
                               data-image="https://images.unsplash.com/photo-1559156596-d0fdb07da244?w=400"
                               data-title="Blueberry Fluffy Cloud"
                               data-desc="Light blueberry marshmallows with fruity burst">
                                Blueberry Fluffy Cloud
                            </a>
                        </div>
                    </div>

                    <!-- Collection -->
                    <div class="menu-column">
                        <div class="menu-title">Collection</div>
                        <div class="menu-items">
                            <a class="menu-item" href="collection.php?id=tet"
                               data-image="https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?w=400"
                               data-title="Tet Collection"
                               data-desc="Special edition candy boxes for Lunar New Year celebrations">
                                Tet Collection
                            </a>
                            <a class="menu-item" href="collection.php?id=christmas"
                               data-image="https://images.unsplash.com/photo-1512909006721-3d6018887383?w=400"
                               data-title="Christmas Collection"
                               data-desc="Festive candy assortments for holiday season">
                                Christmas Collection
                            </a>
                        </div>
                    </div>

                </div>

                <!-- See All -->
                <div class="inline-flex-center">
                    <a class="see-all-link" href="shop.php">See all products →</a>
                </div>
            </div>

            <!-- RIGHT: Featured Card -->
            <div class="featured-card" id="featuredCard">
                <img class="featured-image" id="featuredImage" 
                     src="https://images.unsplash.com/photo-1575224300306-1b8da36134ec?w=400" 
                     alt="Featured candy" />
                <div class="card-content">
                    <div class="card-title" id="featuredTitle">Milk Coffee Candy</div>
                    <div class="card-subtitle" id="featuredDesc">Rich and creamy coffee-flavored hard candy with a smooth finish</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header JavaScript -->
<script src="<?php echo $ROOT; ?>/views/website/js/header.js"></script>