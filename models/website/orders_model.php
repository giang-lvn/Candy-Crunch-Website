<?php
class OrderModel {
    private PDO $conn;

    public function __construct() {
        global $db; // PDO từ db.php
        $this->conn = $db;
    }

    public function getOrdersByCustomer(string $customerId): array {
        // Query lấy thông tin sản phẩm riêng biệt
        $sql = "
        SELECT
            o.OrderID,
            o.OrderStatus,
            o.OrderDate,

            SUM(od.OrderQuantity) AS Quantity,
            
            -- Giá của từng sản phẩm trong order detail
            SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) AS SubTotal,

            -- Thông tin voucher
            v.Code AS VoucherCode,
            v.DiscountPercent,
            v.DiscountAmount,
            v.MinOrder,

            p.ProductName,
            p.Image,
            s.Attribute,
            s.SKUID

        FROM ORDERS o
        LEFT JOIN ORDER_DETAIL od ON o.OrderID = od.OrderID
        LEFT JOIN SKU s ON od.SKUID = s.SKUID
        LEFT JOIN PRODUCT p ON s.ProductID = p.ProductID
        LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
            AND v.VoucherStatus = 'Active'
            AND CURDATE() BETWEEN v.StartDate AND v.EndDate

        WHERE o.CustomerID = ?

        GROUP BY o.OrderID, o.OrderStatus, o.OrderDate, v.VoucherID, v.Code, v.DiscountPercent, v.DiscountAmount, v.MinOrder, p.ProductName, p.Image, s.Attribute, s.SKUID
        ORDER BY o.OrderDate DESC, o.OrderID
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$customerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Lấy tổng tiền của một đơn hàng (bao gồm tính voucher)
     */
    public function getOrderTotal(string $orderId): float {
        $sql = "
        SELECT 
            SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) AS SubTotal,
            v.DiscountPercent,
            v.DiscountAmount,
            v.MinOrder
        FROM ORDERS o
        JOIN ORDER_DETAIL od ON o.OrderID = od.OrderID
        JOIN SKU s ON od.SKUID = s.SKUID
        LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
        WHERE o.OrderID = ?
        GROUP BY o.OrderID, v.VoucherID
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return 0;

        $subTotal = $result['SubTotal'];
        $discount = 0;

        if ($result['DiscountPercent'] && $subTotal >= ($result['MinOrder'] ?? 0)) {
            $discount = $subTotal * $result['DiscountPercent'] / 100;
        } elseif ($result['DiscountAmount'] && $subTotal >= ($result['MinOrder'] ?? 0)) {
            $discount = $result['DiscountAmount'];
        }

        return $subTotal - $discount;
    }
}
