<?php
require_once __DIR__ . '/../../models/db.php';

class ReturnController {

    // Xử lý yêu cầu trả hàng AJAX
    public function submitReturnRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');

        global $db;

        // Lấy dữ liệu từ POST
        $orderID = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        
        // Check multiple session variable names for compatibility
        $customerID = null;
        if (isset($_SESSION['user_data']['CustomerID'])) {
            $customerID = $_SESSION['user_data']['CustomerID'];
        } elseif (isset($_SESSION['customer_id'])) {
            $customerID = $_SESSION['customer_id'];
        } elseif (isset($_SESSION['CustomerID'])) {
            $customerID = $_SESSION['CustomerID'];
        }

        // Validate dữ liệu
        if (empty($orderID)) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
            return;
        }
        
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please select a reason.']);
            return;
        }
        
        if (empty($customerID)) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to return an order.']);
            return;
        }

        // Kiểm tra đơn hàng thuộc về customer
        $stmt = $db->prepare("SELECT * FROM ORDERS WHERE OrderID = ? AND CustomerID = ?");
        $stmt->execute([$orderID, $customerID]);
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found or access denied.']);
            return;
        }

        // Kiểm tra đơn hàng đã hoàn thành chưa
        $stmt = $db->prepare("SELECT OrderStatus FROM ORDERS WHERE OrderID = ?");
        $stmt->execute([$orderID]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order || !in_array($order['OrderStatus'], ['Complete', 'Completed'])) {
            echo json_encode(['success' => false, 'message' => 'Only completed orders can be returned.']);
            return;
        }

        // Kiểm tra đơn hàng chưa có yêu cầu trả hàng
        if (in_array($order['OrderStatus'], ['Pending Return', 'Returned'])) {
            echo json_encode(['success' => false, 'message' => 'A return request already exists for this order.']);
            return;
        }

        // Cập nhật trạng thái đơn hàng thành Pending Return (chờ admin duyệt)
        $stmt = $db->prepare("UPDATE ORDERS SET OrderStatus = 'Pending Return' WHERE OrderID = ?");
        $success = $stmt->execute([$orderID]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Return request submitted successfully. Waiting for admin approval.',
                'redirect' => '/Candy-Crunch-Website/views/website/php/my_orders.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit return request.']);
        }
    }
}

// Execute if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ReturnController();
    $controller->submitReturnRequest();
}
?>
