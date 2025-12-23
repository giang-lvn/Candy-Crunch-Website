<?php
require_once __DIR__ . '/../db.php';

class CancelModel {
    private $conn;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    // Kiểm tra đơn hàng tồn tại và thuộc về khách hàng
    public function isOrderValid($orderID, $customerID) {
        $stmt = $this->conn->prepare("SELECT * FROM ORDERS WHERE OrderID = ? AND CustomerID = ?");
        $stmt->execute([$orderID, $customerID]);
        return $stmt->rowCount() > 0;
    }

    // Kiểm tra đơn hàng chưa bị hủy (không cần bảng CANCELLATION)
    public function isOrderNotCancelled($orderID) {
        $status = $this->getOrderStatus($orderID);
        // Đã hủy hoặc đang chờ hủy thì không cho hủy nữa
        return !in_array($status, ['Cancelled', 'Pending Cancel']);
    }

    // Lấy trạng thái hiện tại của đơn hàng
    public function getOrderStatus($orderID) {
        $stmt = $this->conn->prepare("SELECT OrderStatus FROM ORDERS WHERE OrderID = ?");
        $stmt->execute([$orderID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['OrderStatus'] : null;
    }

    // Kiểm tra đơn hàng có thể hủy không (chưa ship)
    public function canCancelOrder($orderID) {
        $status = $this->getOrderStatus($orderID);
        // Chỉ có thể hủy nếu chưa shipping hoặc hoàn thành
        $cancellableStatuses = ['Waiting Payment', 'Pending Confirmation', 'Pending'];
        return in_array($status, $cancellableStatuses);
    }

    // Kiểm tra đơn hàng có thể trả hàng không (đã giao)
    public function canReturnOrder($orderID) {
        $status = $this->getOrderStatus($orderID);
        return $status === 'Complete' || $status === 'Completed';
    }

    // Cập nhật trạng thái đơn hàng thành Cancelled trực tiếp (không qua pending)
    public function cancelOrderDirectly($orderID) {
        $stmt = $this->conn->prepare("UPDATE ORDERS SET OrderStatus = 'Cancelled' WHERE OrderID = ?");
        return $stmt->execute([$orderID]);
    }

    // Cập nhật trạng thái đơn hàng thành Pending Cancel
    public function markOrderPendingCancel($orderID) {
        $stmt = $this->conn->prepare("UPDATE ORDERS SET OrderStatus = 'Pending Cancel' WHERE OrderID = ?");
        return $stmt->execute([$orderID]);
    }

    // Cập nhật trạng thái đơn hàng thành Cancelled (khi admin duyệt)
    public function markOrderCancelled($orderID) {
        $stmt = $this->conn->prepare("UPDATE ORDERS SET OrderStatus = 'Cancelled' WHERE OrderID = ?");
        return $stmt->execute([$orderID]);
    }

    // Khôi phục trạng thái đơn hàng (khi admin từ chối)
    public function restoreOrderStatus($orderID, $previousStatus) {
        $stmt = $this->conn->prepare("UPDATE ORDERS SET OrderStatus = ? WHERE OrderID = ?");
        return $stmt->execute([$previousStatus, $orderID]);
    }
}
?>
