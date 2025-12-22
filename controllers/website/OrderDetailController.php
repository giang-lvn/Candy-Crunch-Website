<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../models/website/Order_DetailModel.php';
require_once __DIR__ . '/../../models/website/CartModel.php';

class OrderDetailController {
    private $model;

    public function __construct() {
        $this->model = new OrderDetailModel();
    }

    public function index() {
        // Check login
        if (!isset($_SESSION['user_data']['CustomerID'])) {
             header('Location: /Candy-Crunch-Website/views/website/php/login.php');
             exit;
        }

        $customerId = $_SESSION['user_data']['CustomerID'];
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            echo "Order ID missing.";
            exit;
        }

        // Check ownership
        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo "Order not found or access denied.";
            exit;
        }

        // Fetch data
        $order = $this->model->getOrderById($orderId);
        $products = $this->model->getOrderProducts($orderId);

        // Process images
        foreach ($products as &$product) {
            $product['Image'] = $this->parseProductImage($product['Image']);
        }
        unset($product); // break reference

        $shippingAddress = $this->model->getShippingAddress($customerId);
        
        // Calculate Summary
        $summary = $this->model->calculateOrderSummary(
            $products,
            $order['ShippingFee'] ?? 0,
            $order // Pass the whole order array which now has DiscountPercent, DiscountAmount, MinOrder
        );

        // Determine Buttons
        $buttons = $this->getButtons($order['OrderStatus']);

        // Prepare Data for View
        $data = [
            'order' => $order,
            'products' => $products,
            'shippingAddress' => $shippingAddress,
            'summary' => $summary,
            'buttons' => $buttons
        ];

        // Load View
        require_once __DIR__ . '/../../views/website/php/order_detail.php';
    }

    public function cancel() {
        header('Content-Type: application/json');
        
        $orderId = $_POST['order_id'] ?? null;
        $customerId = $_SESSION['user_data']['CustomerID'] ?? null;

        if (!$orderId || !$customerId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $result = $this->model->cancelOrder($orderId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel this order']);
        }
        exit;
    }

    public function confirmReceived() {
        header('Content-Type: application/json');
        
        $orderId = $_POST['order_id'] ?? null;
        $customerId = $_SESSION['user_data']['CustomerID'] ?? null;

        if (!$orderId || !$customerId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $result = $this->model->confirmReceived($orderId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Order confirmed as received']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot confirm this order']);
        }
        exit;
    }

    // Placeholders for other actions
    public function reOrder() {
        if (!isset($_SESSION['user_data']['CustomerID'])) {
            header('Location: /Candy-Crunch-Website/views/website/php/login.php');
            exit;
        }

        $orderId = $_GET['id'] ?? null;
        $customerId = $_SESSION['user_data']['CustomerID'];

        if (!$orderId) {
            echo "Order ID missing.";
            exit;
        }

        if (!$this->model->checkOrderOwnership($orderId, $customerId)) {
            echo "Unauthorized";
            exit;
        }

        $products = $this->model->getOrderProducts($orderId);
        if (empty($products)) {
            echo "Order has no products.";
            exit;
        }

        $cartModel = new CartModel();
        
        foreach ($products as $product) {
            $skuId = $product['SKUID'];
            $quantity = $product['OrderQuantity']; // Use the quantity from order
            // Note: addToCart signature: addToCart($customerId, string $skuId, int $quantity = 1)
            $cartModel->addToCart($customerId, $skuId, $quantity);
        }

        // Redirect to Checkout
        header('Location: /Candy-Crunch-Website/views/website/php/checkout.php');
        exit;
    }

    public function payNow() {
         echo "Pay Now logic not implemented yet";
    }

    public function changeMethod() {
         echo "Change Payment Method logic not implemented yet";
    }

    private function getButtons($status) {
        return [
            'pay_now'       => ($status === 'Waiting Payment'),
            'change_method' => ($status === 'Waiting Payment'),
            'buy_again'     => ($status === 'Completed' || $status === 'Complete' || $status === 'Canceled'),
            'return'        => ($status === 'Complete' || $status === 'Completed'),
            'write_review'  => ($status === 'Complete' || $status === 'Completed'),
            'cancel'        => ($status === 'Waiting Payment' || $status === 'Pending' || $status === 'Pending Confirmation'), 
            'contact'       => true
        ];
    }

    private function parseProductImage($imageField) {
        if (empty($imageField)) return null;

        // Try to decode JSON
        $images = json_decode($imageField, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($images)) {
            // Find thumbnail
            foreach ($images as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'];
                }
            }
            // Fallback to first image
            return $images[0]['path'] ?? null;
        }

        // Handle legacy plain filenames (assume they are in ../img/)
        // Check if it's already a path
        if (strpos($imageField, '/') !== false) {
            return $imageField;
        }

        // If it is a simple filename, prepend standard image path
        return '../img/' . $imageField;
    }
}

