<?php

session_start();

require_once __DIR__ . '/../../models/website/ReturnModel.php';

class ReturnController
{
    private $returnModel;

    public function __construct()
    {
        $this->returnModel = new ReturnModel();
    }

    // Hiển thị trang Return
    public function index()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        $customerId = $_SESSION['customer_id'];

        // Lấy OrderID từ URL (hoặc session)
        // ← THÊM: Lấy OrderID từ GET parameter
        $orderId = $_GET['order_id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Please select an order to return.';
            header('Location: /index.php?controller=order&action=index'); // Redirect về trang order
            exit;
        }

        // Kiểm tra đơn hàng có thuộc customer không
        if (!$this->returnModel->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=order&action=index');
            exit;
        }

        // Kiểm tra đơn hàng đã completed chưa
        $order = $this->returnModel->getOrderById($orderId);
        if (!$order || $order['OrderStatus'] !== 'Completed') {
            $_SESSION['error'] = 'This order cannot be returned.';
            header('Location: /index.php?controller=order&action=index');
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
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        $customerId = $_SESSION['customer_id'];

        // Lấy dữ liệu form
        $orderId            = trim($_POST['order_id'] ?? '');
        $refundReason       = trim($_POST['refund_reason'] ?? '');
        $refundDescription  = trim($_POST['refund_description'] ?? '');
        $refundMethod       = $_POST['refund_method'] ?? null;
        $refundImage        = $_FILES['refund_image'] ?? null;

        // Validate cơ bản
        if (empty($orderId) || empty($refundReason)) {
            $_SESSION['error'] = 'Order ID and refund reason are required.';
            header('Location: /index.php?controller=return&action=index&order_id=' . $orderId);
            exit;
        }

        // Kiểm tra đơn hàng có thuộc customer không
        if (!$this->returnModel->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=order&action=index');
            exit;
        }

        // Kiểm tra đơn hàng đã refund chưa
        if ($this->returnModel->checkRefundExistByOrder($orderId)) {
            $_SESSION['error'] = 'This order has already been refunded.';
            header('Location: /index.php?controller=return&action=index&order_id=' . $orderId);
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
            'refund_image'      => $imagePath
        ]);

        if (!$refundId) {
            $_SESSION['error'] = 'Failed to submit refund request.';
            header('Location: /index.php?controller=return&action=index&order_id=' . $orderId);
            exit;
        }

        //Thành công
        $_SESSION['success'] = 'Refund request submitted successfully. Refund ID: ' . $refundId;
        header('Location: /index.php?controller=order&action=index'); // Redirect về order history
        exit;
    }
}