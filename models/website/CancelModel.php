<?php
require_once __DIR__ . '/../db.php';

class CancelModel {
    private $conn;

    public function __construct() {
        global $db;
    }

    // Kiểm tra đơn hàng tồn tại và thuộc về khách hàng
    public function isOrderValid($orderID, $customerID) {
        $stmt = $this->conn->prepare("SELECT * FROM ORDERS WHERE OrderID = ? AND CustomerID = ?");
        $stmt->bind_param("ii", $orderID, $customerID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra đơn hàng chưa bị hủy
    public function isOrderNotCancelled($orderID) {
        $stmt = $this->conn->prepare("SELECT * FROM CANCELLATION WHERE OrderID = ?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 0;
    }

    // Lưu yêu cầu hủy đơn
    public function createCancellation($orderID, $reason) {
        $stmt = $this->conn->prepare("INSERT INTO CANCELLATION (OrderID, CancellationDate, CancellationReason, CancellationStatus) VALUES (?, NOW(), ?, 'Pending')");
        $stmt->bind_param("is", $orderID, $reason);
        return $stmt->execute();
    }

    // (Optional) cập nhật trạng thái đơn hàng
    public function markOrderCancelled($orderID) {
        $stmt = $this->conn->prepare("UPDATE ORDERS SET OrderStatus = 'Cancelled' WHERE OrderID = ?");
        $stmt->bind_param("i", $orderID);
        return $stmt->execute();
    }
}
?>
