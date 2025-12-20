<?php
// File: /Candy-Crunch-Website/index.php (ROUTER CHÍNH)

// Bắt đầu session
session_start();

// Include database connection
require_once __DIR__ . '/models/db.php';

// Lấy controller và action từ URL
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// ==========================================
// ROUTING CHO TỪNG CONTROLLER
// ==========================================

switch ($controller) {
    
    // ========== HOME ==========
    case 'home':
        require_once 'controllers/website/HomeController.php';
        $homeController = new HomeController();
        
        if ($action === 'index') {
            $homeController->index();
        }
        break;

    // ========== SHOP ==========
    case 'shop':
        require_once 'controllers/website/ShopController.php';
        $shopController = new ShopController();
        
        if ($action === 'index') {
            $shopController->index();
        }
        break;

    // ========== CART ==========
    case 'cart':
        require_once 'controllers/website/CartController.php';
        $cartController = new CartController();
        
        switch ($action) {
            case 'index':
                $cartController->index();
                break;
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
            default:
                $cartController->index();
                break;
        }
        break;

    // ========== WISHLIST (THÊM MỚI) ==========
    case 'wishlist':
        require_once 'controllers/website/WishlistController.php';
        $wishlistController = new WishlistController();
        
        switch ($action) {
            case 'index':
                $wishlistController->index();
                break;
            case 'add':
                $wishlistController->add();
                break;
            case 'toggle':
                $wishlistController->toggle();
                break;
            case 'remove':
                $wishlistController->remove();
                break;
            default:
                $wishlistController->index();
                break;
        }
        break;

    // ========== DEFAULT (404) ==========
    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}
?>
