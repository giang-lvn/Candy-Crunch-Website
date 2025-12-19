<?php

require_once __DIR__ . '/../db.php';

class ReturnModel
{
    private $conn;

    public function __construct()
    {
        global $db;
    }

    //Lấy các đơn hàng đã hoàn thành của customer
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

    //Lấy danh sách sản phẩm trong đơn hàng
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

    //Lấy OrderID từ SKUID
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

    //Kiểm tra đơn hàng có thuộc customer không
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

    //Kiểm tra đơn hàng đã có refund chưa
    public function checkRefundExistByOrder($orderId)
    {
        $sql = "
            SELECT COUNT(*)
            FROM REFUND
            WHERE OrderID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetchColumn() > 0;
    }

    //Tạo yêu cầu refund
    public function createRefundRequest($data)
    {
        $sql = "
            INSERT INTO REFUND (
                OrderID,
                RefundDate,
                RefundReason,
                RefundDescription,
                RefundImage,
                RefundStatus
            ) VALUES (
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
            ':order_id'           => $data['order_id'],
            ':refund_reason'      => $data['refund_reason'],
            ':refund_description' => $data['refund_description'],
            ':refund_image'       => $data['refund_image']
        ]);

        return $success ? $this->conn->lastInsertId() : false;
    }

    //Upload ảnh refund
    public function uploadRefundImage($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/refund/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return '/public/uploads/refund/' . $fileName;
        }

        return null;
    }
}
