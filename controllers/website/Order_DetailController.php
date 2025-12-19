<?php

session_start();

require_once __DIR__ . '/../../models/website/OrderDetailModel.php';

class OrderDetailController
{
    private $model;

    public function __construct()
    {
        $this->model = new OrderDetailModel();
    }

    /**
     * Hiển thị trang chi tiết đơn hàng
     */
    public function show()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please login to view order details.';
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        $customerId = $_SESSION['customer_id'];

        // Lấy OrderID từ URL
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Order ID is required.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Kiểm tra quyền sở hữu đơn hàng
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'You do not have permission to view this order.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Lấy thông tin đơn hàng
        $order = $this->model->getOrderById($orderId);

        if (!$order) {
            $_SESSION['error'] = 'Order not found.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Lấy địa chỉ giao hàng
        $shippingAddress = $this->model->getShippingAddress($customerId);

        // Lấy danh sách sản phẩm
        $products = $this->model->getOrderProducts($orderId);

        // Tính toán summary
        $summary = $this->model->calculateOrderSummary(
            $products,
            $order['ShippingFee'],
            $order['VoucherDiscount']
        );

        // Xác định buttons hiển thị theo status
        $buttons = $this->getButtonsByStatus($order['OrderStatus']);

        // Truyền dữ liệu sang view
        $data = [
            'order' => $order,
            'shippingAddress' => $shippingAddress,
            'products' => $products,
            'summary' => $summary,
            'buttons' => $buttons
        ];

        // Load view
        require __DIR__ . '/../../views/website/php/OrderDetail.php';
    }

    /**
     * Xác định buttons hiển thị theo trạng thái đơn hàng
     * @param string $status
     * @return array
     */
    private function getButtonsByStatus($status)
    {
        $buttons = [
            'pay_now' => false,
            'change_method' => false,
            'cancel' => false,
            'contact' => false,
            'buy_again' => false,
            'return' => false,
            'write_review' => false
        ];

        switch ($status) {
            case 'Waiting Payment':
                $buttons['pay_now'] = true;
                $buttons['change_method'] = true;
                break;

            case 'Pending Confirmation':
                $buttons['cancel'] = true;
                $buttons['contact'] = true;
                $buttons['change_method'] = true;
                break;

            case 'Pending':
            case 'On Shipping':
                $buttons['contact'] = true;
                break;

            case 'Complete':
                $buttons['buy_again'] = true;
                $buttons['return'] = true;
                $buttons['write_review'] = true;
                break;

            case 'Returned':
                $buttons['contact'] = true;
                break;

            case 'Canceled':
                $buttons['contact'] = true;
                $buttons['buy_again'] = true;
                break;
        }

        return $buttons;
    }

    /**
     * Hủy đơn hàng (AJAX)
     */
    public function cancel()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $customerId = $_SESSION['customer_id'];
        $orderId = $_POST['order_id'] ?? null;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            exit;
        }

        // Kiểm tra quyền sở hữu
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
            exit;
        }

        // Hủy đơn hàng
        $result = $this->model->cancelOrder($orderId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot cancel this order. Please check order status.'
            ]);
        }
        exit;
    }

    /**
     * Xác nhận đã nhận hàng (AJAX)
     */
    public function confirmReceived()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $customerId = $_SESSION['customer_id'];
        $orderId = $_POST['order_id'] ?? null;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            exit;
        }

        // Kiểm tra quyền sở hữu
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
            exit;
        }

        // Xác nhận nhận hàng
        $result = $this->model->confirmReceived($orderId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Order confirmed successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot confirm this order. Please check order status.'
            ]);
        }
        exit;
    }

    /**
     * Mua lại đơn hàng (thêm tất cả sản phẩm vào giỏ hàng)
     */
    public function reOrder()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please login to continue.';
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        $customerId = $_SESSION['customer_id'];
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Order ID is required.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Kiểm tra quyền sở hữu
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Lấy danh sách sản phẩm trong đơn hàng
        $products = $this->model->getOrderProducts($orderId);

        if (empty($products)) {
            $_SESSION['error'] = 'No products found in this order.';
            header('Location: /index.php?controller=OrderDetail&action=show&id=' . $orderId);
            exit;
        }

        // Thêm tất cả sản phẩm vào giỏ hàng (giả sử có CartModel)
        // Bạn cần implement CartModel->addToCart() nếu chưa có
        require_once __DIR__ . '/../../models/website/CartModel.php';
        $cartModel = new CartModel();

        $addedCount = 0;
        foreach ($products as $product) {
            $result = $cartModel->addToCart(
                $customerId,
                $product['SKUID'],
                $product['OrderQuantity']
            );

            if ($result) {
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            $_SESSION['success'] = "$addedCount product(s) added to cart successfully!";
        } else {
            $_SESSION['error'] = 'Failed to add products to cart.';
        }

        // Chuyển hướng về giỏ hàng
        header('Location: /index.php?controller=cart&action=index');
        exit;
    }

    /**
     * Chuyển hướng đến trang thanh toán (Pay Now)
     */
    public function payNow()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please login to continue.';
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        $customerId = $_SESSION['customer_id'];
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Order ID is required.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Kiểm tra quyền sở hữu
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Chuyển hướng đến trang thanh toán
        header('Location: /index.php?controller=payment&action=process&order_id=' . $orderId);
        exit;
    }

    /**
     * Chuyển hướng đến trang đổi phương thức thanh toán
     */
    public function changeMethod()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please login to continue.';
            header('Location: /index.php?controller=auth&action=login');
            exit;
        }

        $customerId = $_SESSION['customer_id'];
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            $_SESSION['error'] = 'Order ID is required.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Kiểm tra quyền sở hữu
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /index.php?controller=order&action=history');
            exit;
        }

        // Chuyển hướng đến trang đổi phương thức
        header('Location: /index.php?controller=payment&action=changeMethod&order_id=' . $orderId);
        exit;
    }
}
