<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/website/ReturnModel.php';

class ReturnController
{
    private $returnModel;

    public function __construct()
    {
        $this->returnModel = new ReturnModel();
    }

    // Get customer ID from session (multiple session variable names)
    private function getCustomerId()
    {
        if (isset($_SESSION['user_data']['CustomerID'])) {
            return $_SESSION['user_data']['CustomerID'];
        } elseif (isset($_SESSION['customer_id'])) {
            return $_SESSION['customer_id'];
        } elseif (isset($_SESSION['CustomerID'])) {
            return $_SESSION['CustomerID'];
        }
        return null;
    }

    // Hiển thị trang Return
    public function index()
    {
        $customerId = $this->getCustomerId();
        
        if (!$customerId) {
            header('Location: /Candy-Crunch-Website/views/website/login.php');
            exit;
        }

        // Lấy OrderID từ URL
        $orderId = $_GET['order_id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Please select an order to return.';
            header('Location: /Candy-Crunch-Website/views/website/php/my_orders.php');
            exit;
        }

        // Kiểm tra đơn hàng có thuộc customer không
        if (!$this->returnModel->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /Candy-Crunch-Website/views/website/php/my_orders.php');
            exit;
        }

        // Kiểm tra đơn hàng đã completed chưa
        $order = $this->returnModel->getOrderById($orderId);
        if (!$order || !in_array($order['OrderStatus'], ['Completed', 'Complete'])) {
            $_SESSION['error'] = 'This order cannot be returned.';
            header('Location: /Candy-Crunch-Website/views/website/php/my_orders.php');
            exit;
        }

        // Lấy danh sách sản phẩm trong đơn hàng
        $products = $this->returnModel->getOrderProducts($orderId);

        // Truyền dữ liệu sang view
        $data = [
            'orderId'  => $orderId,
            'orderDate'=> $order['OrderDate'],
            'products' => $products
        ];

        require __DIR__ . '/../../views/website/php/return.php';
    }

    // XỬ LÝ SUBMIT YÊU CẦU TRẢ HÀNG
    public function submitReturn()
    {
        $customerId = $this->getCustomerId();
        
        // Debug log session
        error_log("ReturnController submitReturn: customerId=" . ($customerId ?? 'null'));
        error_log("ReturnController session keys: " . implode(', ', array_keys($_SESSION ?? [])));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Candy-Crunch-Website/views/website/php/my_orders.php');
            exit;
        }

        // Lấy dữ liệu form
        $orderId            = trim($_POST['order_id'] ?? '');
        $refundReason       = trim($_POST['refund_reason'] ?? '');
        $refundDescription  = trim($_POST['refund_description'] ?? '');
        $refundMethod       = trim($_POST['refund_method'] ?? '');
        $refundImage        = $_FILES['refund_image'] ?? null;

        // Validate cơ bản
        if (empty($orderId) || empty($refundReason)) {
            $_SESSION['error'] = 'Order ID and refund reason are required.';
            header('Location: /Candy-Crunch-Website/views/website/php/return.php?order_id=' . $orderId);
            exit;
        }

        // Log for debugging
        error_log("ReturnController: orderId=$orderId, customerId=$customerId, reason=$refundReason");

        // Kiểm tra đơn hàng đã refund chưa
        if ($this->returnModel->checkRefundExistByOrder($orderId)) {
            $_SESSION['error'] = 'This order has already been refunded.';
            header('Location: /Candy-Crunch-Website/views/website/php/return.php?order_id=' . $orderId);
            exit;
        }

        // Upload ảnh (nếu có)
        $imagePath = null;
        if ($refundImage && $refundImage['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->returnModel->uploadRefundImage($refundImage);
        }

        // Lưu REFUND
        $refundId = $this->returnModel->createRefundRequest([
            'order_id'          => $orderId,
            'refund_reason'     => $refundReason,
            'refund_description'=> $refundDescription,
            'refund_method'     => $refundMethod,
            'refund_image'      => $imagePath
        ]);

        if (!$refundId) {
            $_SESSION['error'] = 'Failed to submit refund request.';
            header('Location: /Candy-Crunch-Website/views/website/php/return.php?order_id=' . $orderId);
            exit;
        }

        // Cập nhật trạng thái đơn hàng thành 'Pending Return'
        $this->returnModel->updateOrderStatus($orderId, 'Pending Return');

        // Thành công
        $_SESSION['success'] = 'Refund request submitted successfully. Refund ID: ' . $refundId;
        header('Location: /Candy-Crunch-Website/views/website/php/my_orders.php');
        exit;
    }
}

// ROUTING
$controller = new ReturnController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'submitReturn':
        $controller->submitReturn();
        break;
    default:
        $controller->index();
        break;
}