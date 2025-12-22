<?php
require_once __DIR__ . '/../../models/website/RatingModel.php';

class RatingController {
    private $model;

    public function __construct() {
        $this->model = new RatingModel();
    }

    // Xử lý submit rating từ AJAX
    public function submitRating() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');

        // Lấy dữ liệu từ POST
        $skuID = isset($_POST['sku_id']) ? trim($_POST['sku_id']) : '';
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        // Lấy CustomerID từ session - kiểm tra cả 2 cách lưu session
        $customerID = '';
        if (isset($_SESSION['user_data']['CustomerID'])) {
            $customerID = trim($_SESSION['user_data']['CustomerID']);
        } elseif (isset($_SESSION['CustomerID'])) {
            $customerID = trim($_SESSION['CustomerID']);
        }

        // Validate dữ liệu
        if (!$customerID) {
            echo json_encode(['success' => false, 'message' => 'Please login to rate this product.']);
            return;
        }

        if (!$skuID || !$rating) {
            echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
            return;
        }

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5 stars.']);
            return;
        }

        // Kiểm tra sản phẩm tồn tại
        if (!$this->model->isProductValid($skuID)) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            return;
        }

        // Kiểm tra đã đánh giá chưa
        $hasRated = $this->model->hasCustomerRated($customerID, $skuID);

        // Lưu hoặc cập nhật feedback
        if ($hasRated) {
            // Nếu muốn cho phép sửa đánh giá
            $result = $this->model->updateFeedback($customerID, $skuID, $rating, $comment);
            $message = 'Your rating has been updated successfully.';
        } else {
            // Tạo mới
            $result = $this->model->createFeedback($customerID, $skuID, $rating, $comment);
            $message = 'Thank you for your rating!';
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit rating. Please try again.']);
        }
    }

    // Lấy danh sách feedback của sản phẩm (để hiển thị)
    public function getProductReviews() {
        header('Content-Type: application/json');
        
        $skuID = isset($_GET['sku_id']) ? trim($_GET['sku_id']) : '';
        
        if (!$skuID) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
            return;
        }

        $reviews = $this->model->getFeedbacksByProduct($skuID);
        $stats = $this->model->getProductRatingStats($skuID);

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => round($stats['average_rating'], 1),
            'total_reviews' => $stats['total_reviews']
        ]);
    }
}

// Routing - xử lý các request
$action = $_GET['action'] ?? '';
$controller = new RatingController();

switch ($action) {
    case 'submit':
        $controller->submitRating();
        break;
    case 'reviews':
        $controller->getProductReviews();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>