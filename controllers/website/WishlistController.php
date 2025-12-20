<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/wishlistmodel.php';

class WishlistController {
    private $model;

    public function __construct() {
        $this->model = new WishlistModel();
    }

    public function index() {
        session_start();

        $customerId = $_SESSION['customer_id'] ?? 1;

        $wishlistItems = $this->model->getWishlistByCustomer($customerId);

        require_once __DIR__ . '/../../views/website/php/wishlist.php';
    }

    public function remove() {
        session_start();

        $customerId = $_SESSION['customer_id'];
        $productId = $_POST['product_id'];

        $this->model->removeFromWishlist($customerId, $productId);

        header("Location: wishlist.php");
        exit;
    }

    public function add() {
        header('Content-Type: application/json');
        
        session_start();
        
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

    public function toggle() {
        header('Content-Type: application/json');
        
        session_start();
        
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
        
        // Kiểm tra đã có trong wishlist chưa
        if ($this->model->isInWishlist($customerId, $productId)) {
            // Đã có -> Xóa
            $this->model->removeFromWishlist($customerId, $productId);
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Đã xóa khỏi wishlist']);
        } else {
            // Chưa có -> Thêm
            $result = $this->model->addToWishlist($customerId, $productId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'action' => 'added', 'message' => $result['message']]);
            } else {
                echo json_encode($result);
            }
        }
    }
}
