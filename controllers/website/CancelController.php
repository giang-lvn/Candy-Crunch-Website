<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/CancelModel.php';

class CancelController
{
    private $model;

    public function __construct()
    {
        $this->model = new CancelModel();
    }

    // Xử lý yêu cầu hủy đơn AJAX
    public function submitCancellationRequest()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');

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
            echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel an order.']);
            return;
        }

        // Kiểm tra đơn hàng tồn tại
        if (!$this->model->isOrderValid($orderID, $customerID)) {
            echo json_encode(['success' => false, 'message' => 'Order not found or access denied.']);
            return;
        }

        // Kiểm tra đơn chưa có yêu cầu hủy
        if (!$this->model->isOrderNotCancelled($orderID)) {
            echo json_encode(['success' => false, 'message' => 'Order has already been cancelled.']);
            return;
        }

        // Kiểm tra đơn hàng có thể hủy không
        if (!$this->model->canCancelOrder($orderID)) {
            echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled. It may already be shipping or completed.']);
            return;
        }

        // Đặt trạng thái thành Pending Cancel (chờ admin duyệt)
        if ($this->model->markOrderPendingCancel($orderID)) {
            echo json_encode([
                'success' => true,
                'message' => 'Cancel request submitted successfully. Waiting for admin approval.',
                'redirect' => '/Candy-Crunch-Website/views/website/php/my_orders.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit cancel request.']);
        }
    }
}
