<?php

require_once __DIR__ . '/../db.php';

class ReturnModel
{
    private $conn;

    public function __construct()
    {
        global $db;
        $this->conn = $db;
    }

    // ← THÊM: Lấy thông tin đơn hàng theo OrderID
    public function getOrderById($orderId)
    {
        $sql = "
            SELECT OrderID, CustomerID, OrderDate, OrderStatus
            FROM ORDERS
            WHERE OrderID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy các đơn hàng đã hoàn thành của customer 
    public function getCompletedOrdersByCustomer($customerId)
    {
        $sql = "
            SELECT OrderID, OrderDate
            FROM ORDERS
            WHERE CustomerID = ?
              AND OrderStatus = 'Completed'
            ORDER BY OrderDate DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách sản phẩm trong đơn hàng
    public function getOrderProducts($orderId)
    {
        $sql = "
            SELECT 
                od.SKUID,
                od.OrderQuantity,
                p.ProductName,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                s.Image
            FROM ORDER_DETAIL od
            INNER JOIN SKU s ON od.SKUID = s.SKUID
            INNER JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE od.OrderID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    public function getOrderIdBySku($skuId)
    {
        $sql = "
            SELECT OrderID
            FROM ORDER_DETAIL
            WHERE SKUID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$skuId]);

        return $stmt->fetchColumn();
    }
    */

    // Kiểm tra đơn hàng có thuộc customer không 
    public function checkOrderOwnership($orderId, $customerId)
    {
        $sql = "
            SELECT COUNT(*)
            FROM ORDERS
            WHERE OrderID = ?
              AND CustomerID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId, $customerId]);

        return $stmt->fetchColumn() > 0;
    }

    // Kiểm tra đơn hàng đã có refund chưa
    public function checkRefundExistByOrder($orderId)
    {
        try {
            $sql = "
                SELECT COUNT(*)
                FROM REFUND
                WHERE OrderID = ?
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // Bảng REFUND có thể chưa tồn tại
            return false;
        }
    }

    // Đảm bảo bảng REFUND tồn tại
    private function ensureRefundTableExists()
    {
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS REFUND (
                    RefundID VARCHAR(10) PRIMARY KEY,
                    OrderID VARCHAR(20) NOT NULL,
                    RefundDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                    RefundReason TEXT,
                    RefundDescription TEXT,
                    RefundImage VARCHAR(255),
                    RefundStatus VARCHAR(20) DEFAULT 'Pending',
                    FOREIGN KEY (OrderID) REFERENCES ORDERS(OrderID) ON DELETE CASCADE
                )
            ");
        } catch (PDOException $e) {
            // Table might already exist, ignore
        }
    }

    // Tạo RefundID tự động 
    private function generateRefundId()
    {
        $sql = "SELECT RefundID FROM REFUND ORDER BY RefundID DESC LIMIT 1";
        $stmt = $this->conn->query($sql);
        $lastId = $stmt->fetchColumn();

        if ($lastId) {
            $num = intval(substr($lastId, 2)) + 1;
            return 'RF' . str_pad($num, 3, '0', STR_PAD_LEFT);
        }

        return 'RF001';
    }

    // Tạo yêu cầu refund 
    public function createRefundRequest($data)
    {
        try {
            // Đảm bảo bảng REFUND tồn tại
            $this->ensureRefundTableExists();
            
            $refundId = $this->generateRefundId();

            $sql = "
                INSERT INTO REFUND (
                    RefundID,
                    OrderID,
                    RefundDate,
                    RefundReason,
                    RefundDescription,
                    RefundImage,
                    RefundStatus
                ) VALUES (
                    :refund_id,
                    :order_id,
                    NOW(),
                    :refund_reason,
                    :refund_description,
                    :refund_image,
                    'Pending'
                )
            ";

            $stmt = $this->conn->prepare($sql);

            $success = $stmt->execute([
                ':refund_id'          => $refundId,
                ':order_id'           => $data['order_id'],
                ':refund_reason'      => $data['refund_reason'],
                ':refund_description' => $data['refund_description'],
                ':refund_image'       => $data['refund_image']
            ]);

            return $success ? $refundId : false;
        } catch (PDOException $e) {
            error_log("ReturnModel::createRefundRequest error: " . $e->getMessage());
            return false;
        }
    }

    // Upload ảnh refund
    public function uploadRefundImage($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../views/website/img/refund/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return '../img/refund/' . $fileName;
        }

        return null;
    }
}