<?php
require_once __DIR__ . '/../../models/website/CancelModel.php';

class CancelController {
    private $model;

    public function __construct() {
        $this->model = new CancelModel();
    }

    // Xử lý yêu cầu hủy đơn AJAX
    public function submitCancellationRequest() {
        session_start();
        header('Content-Type: application/json');

        // Lấy dữ liệu từ POST
        $orderID = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $customerID = isset($_SESSION['CustomerID']) ? intval($_SESSION['CustomerID']) : 0;

        // Validate dữ liệu
        if (!$orderID || !$reason || !$customerID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        // Kiểm tra đơn hàng tồn tại
        if (!$this->model->isOrderValid($orderID, $customerID)) {
            echo json_encode(['success' => false, 'message' => 'Order not found or access denied.']);
            return;
        }

        // Kiểm tra đơn chưa hủy
        if (!$this->model->isOrderNotCancelled($orderID)) {
            echo json_encode(['success' => false, 'message' => 'Order has already been cancelled.']);
            return;
        }

        // Lưu yêu cầu hủy đơn
        if ($this->model->createCancellation($orderID, $reason)) {
            // (Optional) Cập nhật trạng thái đơn
            $this->model->markOrderCancelled($orderID);

            echo json_encode(['success' => true, 'message' => 'Cancel request submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit cancel request.']);
        }
    }
}

// Execute if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CancelController();
    $controller->submitCancellationRequest();
}
?>
