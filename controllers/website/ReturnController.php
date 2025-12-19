<?php

require_once __DIR__ . '/../../models/website/ReturnModel.php';

class ReturnController
{
    private $returnModel;

    public function __construct()
    {
        $this->returnModel = new ReturnModel();
    }

    //Hiển thị trang Return
    public function index()
    {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            header('Location: /login.php');
            exit;
        }

        $customerId = $_SESSION['customer_id'];

        // 2. Lấy các đơn hàng đã hoàn thành
        $orders = $this->returnModel->getCompletedOrdersByCustomer($customerId);

        // 3. Với mỗi đơn hàng, lấy sản phẩm
        $ordersWithProducts = [];

        foreach ($orders as $order) {
            $products = $this->returnModel->getOrderProducts($order['OrderID']);

            if (!empty($products)) {
                $ordersWithProducts[] = [
                    'OrderID'  => $order['OrderID'],
                    'OrderDate'=> $order['OrderDate'],
                    'products' => $products
                ];
            }
        }

        // 4. Truyền dữ liệu sang view
        $data = [
            'ordersWithProducts' => $ordersWithProducts
        ];

        require __DIR__ . '/../../views/website/php/return.php';
    }

    //XỬ LÝ SUBMIT YÊU CẦU TRẢ HÀNG
    public function submit()
    {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            header('Location: /login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        $customerId = $_SESSION['customer_id'];

        // 2. Lấy dữ liệu form
        $products           = $_POST['products'] ?? [];
        $refundReason       = trim($_POST['refund_reason'] ?? '');
        $refundDescription  = trim($_POST['refund_description'] ?? '');
        $refundMethod       = $_POST['refund_method'] ?? null;
        $refundImage        = $_FILES['refund_image'] ?? null;

        // 3. Validate cơ bản
        if (empty($products) || empty($refundReason)) {
            $_SESSION['error'] = 'Please select product(s) and refund reason.';
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        // 4. Lấy OrderID từ sản phẩm đầu tiên
        $orderId = $this->returnModel->getOrderIdBySku(array_key_first($products));

        if (!$orderId) {
            $_SESSION['error'] = 'Invalid order.';
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        // 5. Kiểm tra đơn hàng có thuộc customer không
        if (!$this->returnModel->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        // 6. Kiểm tra đơn hàng đã refund chưa
        if ($this->returnModel->checkRefundExistByOrder($orderId)) {
            $_SESSION['error'] = 'This order has already been refunded.';
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        // 7. Upload ảnh (nếu có)
        $imagePath = null;
        if ($refundImage && $refundImage['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->returnModel->uploadRefundImage($refundImage);
        }

        // 8. Lưu REFUND
        $refundId = $this->returnModel->createRefundRequest([
            'order_id'          => $orderId,
            'refund_reason'     => $refundReason,
            'refund_description'=> $refundDescription,
            'refund_image'      => $imagePath
        ]);

        if (!$refundId) {
            $_SESSION['error'] = 'Failed to submit refund request.';
            header('Location: /index.php?controller=return&action=index');
            exit;
        }

        // 9. Thành công
        $_SESSION['success'] = 'Refund request submitted successfully.';
        header('Location: /index.php?controller=return&action=index');
        exit;
    }
}


