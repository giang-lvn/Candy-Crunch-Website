<?php
class OrderModel {
    private PDO $conn;

    public function __construct() {
        global $db; // PDO từ db.php
        $this->conn = $db;
    }

    public function getOrdersByCustomer(string $customerId): array {
        $sql = "
        SELECT
            o.OrderID,
            o.OrderStatus,
            o.OrderDate,

            SUM(od.OrderQuantity) AS Quantity,

            SUM(
                od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)
            ) AS SubTotal,

            v.Code AS VoucherCode,
            v.DiscountPercent,
            v.DiscountAmount,
            v.MinOrder,

            CASE
                WHEN v.VoucherID IS NULL THEN 0

                WHEN v.DiscountPercent IS NOT NULL
                     AND SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) >= v.MinOrder
                THEN
                    SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice))
                    * v.DiscountPercent / 100

                WHEN v.DiscountAmount IS NOT NULL
                     AND SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) >= v.MinOrder
                THEN
                    v.DiscountAmount

                ELSE 0
            END AS VoucherDiscount,

            (
                SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice))
                -
                CASE
                    WHEN v.VoucherID IS NULL THEN 0

                    WHEN v.DiscountPercent IS NOT NULL
                         AND SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) >= v.MinOrder
                    THEN
                        SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice))
                        * v.DiscountPercent / 100

                    WHEN v.DiscountAmount IS NOT NULL
                         AND SUM(od.OrderQuantity * IFNULL(s.PromotionPrice, s.OriginalPrice)) >= v.MinOrder
                    THEN
                        v.DiscountAmount

                    ELSE 0
                END
            ) AS TotalPrice,

            p.ProductName,
            s.Attribute

        FROM ORDERS o
        JOIN ORDER_DETAIL od ON o.OrderID = od.OrderID
        JOIN SKU s ON od.SKUID = s.SKUID
        JOIN PRODUCT p ON s.ProductID = p.ProductID
        LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
            AND v.VoucherStatus = 'Active'
            AND CURDATE() BETWEEN v.StartDate AND v.EndDate

        WHERE o.CustomerID = ?

        GROUP BY o.OrderID, v.VoucherID, p.ProductName, s.Attribute
        ORDER BY o.OrderDate DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
