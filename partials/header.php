<?php
// Chỉ set $ROOT nếu chưa được định nghĩa
if (!isset($ROOT)) {
    $ROOT = '/Candy-Crunch-Website';
}
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

// Lấy tên file hiện tại để xác định trang active
$current_page_uri = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$current_page_file = basename($current_page_uri);

// Nếu không có file name hoặc là header.php thì mặc định là landing.php
if (empty($current_page_file) || $current_page_file === 'header.php') {
    $current_page_file = 'landing.php';
}

// Function kiểm tra active state
function is_active($page_name, $current_page_file)
{
    // Homepage special case
    if ($page_name === 'landing.php' || $page_name === '#') {
        return ($current_page_file === 'landing.php' || $current_page_file === '') ? 'Yes' : 'No';
    }

    // So sánh trực tiếp
    return ($page_name === $current_page_file) ? 'Yes' : 'No';
}
?>

<!-- Load CSS tùy theo trạng thái đăng nhập -->
<?php if ($is_logged_in): ?>
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/header_guest.css">
<?php else: ?>
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/header_login.css">
<?php endif; ?>
<link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">

<?php
// Load cart data nếu đã đăng nhập
$cartItems = [];
$upsellProducts = [];
$subtotal = 0;
$discount = 0;
$promo = 0;
$shipping = 0;
$total = 0;
$remainingForFreeShip = 200000;
$wishlistItems = [];

if ($is_logged_in && isset($_SESSION['customer_id'])) {
    require_once __DIR__ . '/../models/db.php';
    require_once __DIR__ . '/../models/website/CartModel.php';
    $cartModel = new CartModel();

    // Lấy cart ID
    $cart = $cartModel->findActiveCartByCustomer($_SESSION['customer_id']);
    $cartId = $cart ? $cart['CartID'] : null;

    if ($cartId) {
        $_SESSION['cart_id'] = $cartId;
        $cartItems = $cartModel->getCartItems($cartId);

        if (!empty($cartItems)) {
            // Tính tiền
            $amount = $cartModel->calculateCartAmount($cartItems);
            $subtotal = $amount['subtotal'];
            $discount = $amount['discount'];

            // Logic shipping
            $baseAmount = $subtotal - $discount;
            $freeShippingThreshold = 200000;

            if ($baseAmount >= $freeShippingThreshold) {
                $shipping = 0;
                $remainingForFreeShip = 0;
            } else {
                $shipping = 30000;
                $remainingForFreeShip = $freeShippingThreshold - $baseAmount;
            }

            $total = $baseAmount - $promo + $shipping;

            // Lấy upsell products
            $categoryIds = $cartModel->getCategoryIdsFromCart($_SESSION['customer_id']);
            $excludeSkuIds = $cartModel->getCartSkuIds($_SESSION['customer_id']);
            $upsellProducts = $cartModel->getUpsellProducts($categoryIds, $excludeSkuIds, 8);
        } else {
            // Giỏ rỗng - lấy sản phẩm gợi ý
            $upsellProducts = $cartModel->getFirstProducts(8);
        }
    }
    
    // Load wishlist data
    require_once __DIR__ . '/../models/website/wishlistmodel.php';
    $wishlistModel = new WishlistModel();
    $wishlistItems = $wishlistModel->getWishlistByCustomer($_SESSION['customer_id']);
}
include __DIR__ . '/../views/website/php/cart.php';
?>
<?php include __DIR__ . '/../views/website/php/wishlist.php'; ?>

<!-- HEADER NAV -->
<div class="header-nav">
    <img class="logo" src="<?php echo $ROOT; ?>/views/website/img/logo.svg" alt="Candy Crunch Logo" />

    <!-- Navigation Pills -->
    <div class="nav-pills">
        <!-- Homepage -->
        <!-- Homepage -->
        <a href="<?php echo $ROOT; ?>/views/website/php/landing.php" class="nav-item" data-active="<?php echo is_active('landing.php', $current_page_file); ?>"
            data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Homepage</div>
            </div>
        </a>

        <!-- About Us -->
        <!-- About Us -->
        <a href="<?php echo $ROOT; ?>/views/website/php/about.php" class="nav-item" data-active="<?php echo is_active('about.php', $current_page_file); ?>"
            data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">About us</div>
            </div>
        </a>

        <!-- SHOP DROPDOWN BUTTON -->
        <!-- SHOP DROPDOWN BUTTON -->
        <a href="<?php echo $ROOT; ?>/views/website/php/shop.php" class="nav-item" data-active="<?php echo is_active('shop.php', $current_page_file); ?>"
            data-dropdown="true" id="shopDropdownBtn">
            <div class="inline-flex-center">
                <div class="nav-text">Shop</div>
            </div>
            <div class="dropdown-icon">
                <div class="dropdown-icon-inner"></div>
            </div>
        </a>

        <!-- Checkout -->
        <!-- Checkout -->
        <a href="<?php echo $ROOT; ?>/views/website/php/checkout.php" class="nav-item"
            data-active="<?php echo is_active('checkout.php', $current_page_file); ?>" data-dropdown="false">
            <div class="inline-flex-center">
                <div class="nav-text">Checkout</div>
            </div>
        </a>



        <!-- Policy -->
        <!-- Policy -->
        <a href="<?php echo $ROOT; ?>/views/website/php/policy.php" class="nav-item" data-active="<?php echo is_active('policy.php', $current_page_file); ?>"
            data-dropdown="false">
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
            <button class="action-item cart-item" id="openCartBtn">
                <img src="<?php echo $ROOT; ?>/views/website/img/cart.svg" alt="Cart" class="action-icon" />
                <span class="action-text"> Cart </span>
            </button>

            <!-- Wishlist -->
            <button class="action-item" id="openWishlistBtn">
                <img src="<?php echo $ROOT; ?>/views/website/img/wishlist.svg" alt="Wishlist" class="action-icon" />
            </button>

            <!-- Account -->
            <a href="<?php echo $ROOT; ?>/views/website/php/my_account.php" class="action-item">
                <img src="<?php echo $ROOT; ?>/views/website/img/person.svg" alt="User" class="action-icon" />
            </a>
        </div>

    <?php else: ?>
        <!-- ============================================ -->
        <!-- HEADER CHO KHÁCH (CHƯA ĐĂNG NHẬP) -->
        <!-- ============================================ -->
        <div class="auth-buttons">
            <a href="<?php echo $ROOT; ?>/views/website/php/login.php" class="btn btn-login">
                <span class="btn-text">Log in</span>
            </a>

            <a href="<?php echo $ROOT; ?>/views/website/php/sign_up.php" class="btn btn-signup">
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
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=HAC-001"
                                data-image="/Candy-Crunch-Website/views/website/img/products/HAC-001_1766332406_0.webp" data-title="Milk-Filled Coffee Candy"
                                data-desc="Rich and creamy coffee-flavored hard candy with a smooth finish">
                                Milk Coffee Candy
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=HAC-002"
                                data-image="/Candy-Crunch-Website/views/website/img/products/HAC-002_1766332363_0.webp" data-title="Fruit Candy"
                                data-desc="Bright and juicy hard candies bursting with assorted fruit sweetness in every piece">
                                Fruit Candy
                            </a>
                        </div>
                    </div>

                    <!-- Filled Hard Candy -->
                    <div class="menu-column">
                        <div class="menu-title">Filled-Hard Candy</div>
                        <div class="menu-items">
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=FHC-001"
                                data-image="/Candy-Crunch-Website/views/website/img/products/FHC-001_1766334726_0.png" data-title="Caramel-Filled Coffee Candy"
                                data-desc="Coffee candy with gooey caramel center for double indulgence">
                                Caramel-Filled Coffee Candy
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=FHC-002"
                                data-image="/Candy-Crunch-Website/views/website/img/products/FHC-002_1766333086_0.webp" data-title="Milk-filled Coffee Candy"
                                data-desc="Smooth milk filling wrapped in coffee-flavored shell">
                                Milk-filled Coffee Candy
                            </a>
                        </div>
                    </div>

                    <!-- Gummy -->
                    <div class="menu-column">
                        <div class="menu-title">Gummy</div>
                        <div class="menu-items">
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=GUM-001"
                                data-image="/Candy-Crunch-Website/views/website/img/products/GUM-001_1766332806_0.webp" data-title="Wiggly Worm Gummies"
                                data-desc="Fun worm-shaped gummies in fruity flavors kids love">
                                Wiggly Worm Gummies
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=GUM-002"
                                data-image="/Candy-Crunch-Website/views/website/img/products/GUM-002_1766334760_0.png" data-title="Tiny Bear Gummies"
                                data-desc="Adorable bear-shaped gummies packed with fruit flavors">
                                Tiny Bear Gummies
                            </a>
                        </div>
                    </div>

                    <!-- Chewing Gum -->
                    <div class="menu-column">
                        <div class="menu-title">Chewing Gum</div>
                        <div class="menu-items">
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=CHG-001"
                                data-image="/Candy-Crunch-Website/views/website/img/products/CHG-001_1766334469_0.png" data-title="Blueberry Crisp Chewy"
                                data-desc="Crispy shell with chewy center, sweet blueberry taste">
                                Blueberry Crisp Chewy
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=CHG-002"
                                data-image="/Candy-Crunch-Website/views/website/img/products/CHG-002_1766334513_0.png"
                                data-title="Mint Crisp Chewy"
                                data-desc="Refreshing mint flavor for lasting fresh breath">
                                Mint Crisp Chewy
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=CHG-003"
                                data-image="/Candy-Crunch-Website/views/website/img/products/CHG-003_1766334546_0.png"
                                data-title="Cola Crisp Chewy"
                                data-desc="Classic cola taste in a fun chewing gum format">
                                Cola Crisp Chewy
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=CHG-004"
                                data-image="/Candy-Crunch-Website/views/website/img/products/CHG-004_1766334585_0.png"
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
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=MAR-001"
                                data-image="/Candy-Crunch-Website/views/website/img/products/MAR-001_1766334947_0.png" data-title="Vanilla Cotton Whirl"
                                data-desc="Cloud-like vanilla marshmallows that melt in your mouth">
                                Vanilla Cotton Whirl
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=MAR-002"
                                data-image="/Candy-Crunch-Website/views/website/img/products/MAR-002_1766334365_0.png" data-title="Chocolate Cotton Whirl"
                                data-desc="Rich chocolate marshmallows with fluffy texture">
                                Chocolate Cotton Whirl
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=MAR-003"
                                data-image="/Candy-Crunch-Website/views/website/img/products/MAR-003_1766334927_0.png"
                                data-title="Strawberry Cotton Whirl"
                                data-desc="Pink and fluffy strawberry marshmallow delights">
                                Strawberry Cotton Whirl
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=MAR-004"
                                data-image="/Candy-Crunch-Website/views/website/img/products/MAR-004_1766334899_0.png"
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
                            <a class="menu-item" href="<?php echo $ROOT; ?>/views/website/php/shop.php?category=Tet Collection"
                                data-image="/Candy-Crunch-Website/views/website/img/products/NOU-001_1766334996_0.png" data-title="Tet Collection"
                                data-desc="Special edition candy boxes for Lunar New Year celebrations">
                                Tet Collection
                            </a>
                            <a class="menu-item" href="<?php echo $ROOT; ?>/views/website/php/shop.php?category=Christmas Collection"
                                data-image="/Candy-Crunch-Website/views/website/img/products/WS-001_1766334638_0.png" data-title="Christmas Collection"
                                data-desc="Festive candy assortments for holiday season">
                                Christmas Collection
                            </a>
                        </div>
                    </div>

                </div>

                <!-- See All -->
                <div class="inline-flex-center">
                    <a class="see-all-link" href="<?php echo $ROOT; ?>/views/website/php/shop.php">See all products →</a>
                </div>
            </div>

            <!-- RIGHT: Featured Card -->
            <div class="featured-card" id="featuredCard">
                <img class="featured-image" id="featuredImage"
                    src="https://images.unsplash.com/photo-1575224300306-1b8da36134ec?w=400" alt="Featured candy" />
                <div class="card-content">
                    <div class="card-title" id="featuredTitle">Milk Coffee Candy</div>
                    <div class="card-subtitle" id="featuredDesc">Rich and creamy coffee-flavored hard candy with a
                        smooth finish</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header JavaScript -->
<script src="<?php echo $ROOT; ?>/views/website/js/header.js"></script>
<script src="<?php echo $ROOT; ?>/views/website/js/cart.js"></script>

<script src="<?php echo $ROOT; ?>/views/website/js/wishlist.js"></script>