<?php
// File: /Candy-Crunch-Website/index.php (ROUTER CHÍNH)

// Bắt đầu session (chỉ nếu chưa start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/models/db.php';

// Lấy controller và action từ URL
// Mặc định là 'landing' để hiển thị trang chủ
$controller = $_GET['controller'] ?? 'landing';
$action = $_GET['action'] ?? 'index';

// ==========================================
// ROUTING CHO TỪNG CONTROLLER
// ==========================================

switch ($controller) {

    // ========== LANDING (TRANG CHỦ) ==========
    case 'landing':
    case 'home':
        require_once 'controllers/website/HomeController.php';
        $homeController = new HomeController();
        $homeController->index();
        break;

    // ========== SHOP ==========
    case 'shop':
        require_once 'controllers/website/shop_controller.php';
        $shopController = new ShopController();

        switch ($action) {
            case 'getProducts':
                $shopController->getProducts();
                break;
            case 'addToCart':
                // Forward to CartController
                require_once 'controllers/website/CartController.php';
                $cartController = new CartController();
                $cartController->handleAddToCart();
                break;
            case 'index':
            default:
                $shopController->index();
                break;
        }
        break;

    // ========== PRODUCT DETAIL ==========
    case 'productdetail':
        require_once 'controllers/website/ProductDetailNewController.php';
        $productController = new ProductDetailNewController();

        switch ($action) {
            case 'getSkuInfo':
                $productController->getSkuInfo();
                break;
            case 'buyNow':
                $productController->buyNow();
                break;
            case 'addToCart':
                // Forward to CartController
                require_once 'controllers/website/CartController.php';
                $cartController = new CartController();
                $cartController->handleAddToCart();
                break;
            case 'index':
            default:
                $productController->index();
                break;
        }
        break;

    // ========== CART ==========
    case 'cart':
        require_once 'controllers/website/CartController.php';
        $cartController = new CartController();

        switch ($action) {
            case 'updateQuantity':
                $cartController->updateQuantity();
                break;
            case 'handleAddToCart':
                $cartController->handleAddToCart();
                break;
            case 'removeItem':
                $cartController->removeItem();
                break;
            case 'applyVoucher':
                $cartController->applyVoucher();
                break;
            case 'getCartContent':
                $cartController->getCartContent();
                break;
            case 'changeAttribute':
                $cartController->changeAttribute();
                break;
            case 'index':
            default:
                $cartController->index();
                break;
        }
        break;

    // ========== WISHLIST ==========
    case 'wishlist':
        require_once 'controllers/website/WishlistController.php';
        $wishlistController = new WishlistController();

        switch ($action) {
            case 'add':
                $wishlistController->add();
                break;
            case 'toggle':
                $wishlistController->toggle();
                break;
            case 'remove':
                $wishlistController->remove();
                break;
            case 'index':
            default:
                $wishlistController->index();
                break;
        }
        break;

    // ========== ACCOUNT ==========
    case 'account':
        require_once 'controllers/website/account_controller.php';
        $accountController = new AccountController($db);

        switch ($action) {
            case 'updateProfile':
                $accountController->updateProfile();
                break;
            case 'addBanking':
                $accountController->addBanking();
                break;
            case 'editBanking':
            case 'updateBanking':
                $accountController->editBanking();
                break;
            case 'deleteBanking':
                $accountController->deleteBanking();
                break;
            case 'addAddress':
                $accountController->addAddress();
                break;
            case 'updateAddress':
                $accountController->updateAddress();
                break;
            case 'deleteAddress':
                $accountController->deleteAddress();
                break;
            case 'logout':
                $accountController->logout();
                break;
            case 'uploadAvatar':
                $accountController->uploadAvatar();
                break;
            case 'index':
            default:
                $accountController->index();
                break;
        }
        break;

    // ========== ORDERS (My Orders) ==========
    case 'orders':
        require_once 'controllers/website/orders_controller.php';
        $ordersController = new OrderController();

        switch ($action) {
            case 'getMyOrder':
                $ordersController->getMyOrder();
                break;
            case 'index':
            default:
                $ordersController->index();
                break;
        }
        break;

    // ========== ORDER DETAIL ==========
    case 'OrderDetail':
    case 'orderdetail':
        require_once 'controllers/website/OrderDetailController.php';
        $odController = new OrderDetailController();

        switch ($action) {
            case 'cancel':
                $odController->cancel();
                break;
            case 'confirmReceived':
                $odController->confirmReceived();
                break;
            case 'reOrder':
                $odController->reOrder();
                break;
            case 'payNow':
                $odController->payNow();
                break;
            case 'changeMethod':
                $odController->changeMethod();
                break;
            case 'index':
            default:
                $odController->index();
                break;
        }
        break;

    // ========== RETURN (Trả hàng) ==========
    case 'return':
        require_once 'controllers/website/ReturnController.php';
        $returnController = new ReturnController();

        switch ($action) {
            case 'submitReturn':
                $returnController->submitReturn();
                break;
            case 'index':
            default:
                $returnController->index();
                break;
        }
        break;

    // ========== CANCEL (Hủy đơn) ==========
    case 'cancel':
        require_once 'controllers/website/CancelController.php';
        $cancelController = new CancelController();

        if ($action === 'submitCancellationRequest') {
            $cancelController->submitCancellationRequest();
        }
        break;

    // ========== LOGIN ==========
    case 'login':
        require_once 'controllers/website/MA_LoginController.php';
        // MA_LoginController tự xử lý POST/GET
        break;

    // ========== SIGNUP ==========
    case 'signup':
        require_once 'controllers/website/sign_up_controller.php';
        // sign_up_controller tự xử lý
        break;

    // ========== ORDER SUCCESS ==========
    case 'ordersuccess':
        require_once 'controllers/website/OrderSuccessController.php';
        // OrderSuccessController tự xử lý action
        break;

    // ========== RATING ==========
    case 'rating':
        require_once 'controllers/website/RatingController.php';
        $ratingController = new RatingController();

        switch ($action) {
            case 'submit':
                $ratingController->submitRating();
                break;
            case 'reviews':
                $ratingController->getProductReviews();
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
        break;

    // ========== VOUCHERS ==========
    case 'vouchers':
        require_once 'controllers/website/voucher_controller.php';
        // voucher_controller tự xử lý
        break;

    // ========== CHANGE PASSWORD ==========
    case 'changepass':
        require_once 'controllers/website/changepass_controller.php';
        // changepass_controller tự xử lý
        break;

    // ========== STATIC PAGES ==========
    case 'about':
        require_once __DIR__ . '/views/website/php/about.php';
        break;

    case 'policy':
        require_once __DIR__ . '/views/website/php/policy.php';
        break;

    case 'checkout':
        require_once __DIR__ . '/views/website/php/checkout.php';
        break;

    // ========== FEATURED PRODUCTS (API) ==========
    case 'featured':
        require_once 'controllers/website/featured_products_controller.php';
        $featuredController = new FeaturedProductsController();
        $featuredController->getProducts();
        break;

    // ========== DEFAULT (404) ==========
    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}
?>