<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/wishlistmodel.php';

class WishlistController
{
    private $model;

    public function __construct()
    {
        $this->model = new WishlistModel();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $customerId = $_SESSION['customer_id'] ?? null;
        if ($customerId) {
            $wishlistItems = $this->model->getWishlistByCustomer($customerId);
        } else {
            $wishlistItems = [];
        }

        $ROOT = '/Candy-Crunch-Website'; // Ensure ROOT is defined for the view
        require_once __DIR__ . '/../../views/website/php/wishlist.php';
    }

    public function getWishlistJson()
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $customerId = $_SESSION['customer_id'] ?? null;
        
        if (!$customerId) {
            echo json_encode(['success' => false, 'items' => []]);
            return;
        }

        $items = $this->model->getWishlistByCustomer($customerId);
        echo json_encode(['success' => true, 'items' => $items]);
    }

    public function remove()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $customerId = $_SESSION['customer_id'];
        $productId = isset($_POST['product_id']) ? $_POST['product_id'] : (isset($_POST['skuid']) ? $_POST['skuid'] : null);

        if ($productId) {
            $this->model->removeFromWishlist($customerId, $productId);
        }

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            echo json_encode(['success' => true]);
            exit;
        }

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function add()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? null;
        $customerId = $_SESSION['customer_id'];

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
            return;
        }

        $result = $this->model->addToWishlist($customerId, $productId);
        echo json_encode($result);
    }

    public function toggle()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['customer_id'])) {
            // Return 401 or handled error
            echo json_encode(['success' => false, 'message' => 'Please login to use wishlist', 'redirect' => '/Candy-Crunch-Website/views/website/php/login.php']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? null;
        $customerId = $_SESSION['customer_id'];

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            return;
        }

        // Kiểm tra đã có trong wishlist chưa
        if ($this->model->isInWishlist($customerId, $productId)) {
            // Đã có -> Xóa
            $this->model->removeFromWishlist($customerId, $productId);
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
        } else {
            // Chưa có -> Thêm
            $result = $this->model->addToWishlist($customerId, $productId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
            } else {
                echo json_encode($result);
            }
        }
    }
}

/* ====== ROUTER MINI ====== */
$controller = new WishlistController();

$action = $_GET['action'] ?? 'index';

match ($action) {
    'add' => $controller->add(),
    'remove' => $controller->remove(),
    'toggle' => $controller->toggle(),
    'get_json' => $controller->getWishlistJson(),
    default => $controller->index(),
};
