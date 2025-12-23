<?php

require_once __DIR__ . '/../db.php';

class OrderDetailModel
{
    private $conn;

    public function __construct()
    {
        global $db;
        $this->conn = $db;
    }

    /**
     * Lấy thông tin đơn hàng theo OrderID
     * @param string $orderId
     * @return array|false
     */
    public function getOrderById($orderId)
    {
        $sql = "
            SELECT 
                o.OrderID,
                o.CustomerID,
                o.VoucherID,
                o.OrderDate,
                o.PaymentMethod,
                o.ShippingMethod,
                o.ShippingFee,
                o.OrderStatus,
                v.DiscountPercent,
                v.DiscountAmount,
                v.MinOrder
            FROM ORDERS o
            LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
            WHERE o.OrderID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra đơn hàng có thuộc về customer không (bảo mật)
     * @param string $orderId
     * @param string $customerId
     * @return bool
     */
    public function checkOrderOwnership($orderId, $customerId)
    {
        $sql = "
            SELECT COUNT(*) 
            FROM ORDERS 
            WHERE OrderID = ? AND CustomerID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId, $customerId]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Lấy địa chỉ giao hàng của customer (lấy địa chỉ default)
     * @param string $customerId
     * @return array|false
     */
    public function getShippingAddress($customerId)
    {
        $sql = "
            SELECT 
                AddressID,
                Fullname,
                Phone,
                Address,
                CityState,
                Country,
                PostalCode
            FROM ADDRESS
            WHERE CustomerID = ? 
              AND AddressDefault = 'Yes'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$customerId]);

        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        // Nếu không có địa chỉ default, lấy địa chỉ đầu tiên
        if (!$address) {
            $sql = "
                SELECT 
                    AddressID,
                    Fullname,
                    Phone,
                    Address,
                    CityState,
                    Country,
                    PostalCode
                FROM ADDRESS
                WHERE CustomerID = ?
                ORDER BY AddressID
                LIMIT 1
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$customerId]);

            $address = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $address;
    }

    /**
     * Lấy danh sách sản phẩm trong đơn hàng
     * @param string $orderId
     * @return array
     */
    public function getOrderProducts($orderId)
    {
        $sql = "
            SELECT 
                od.SKUID,
                od.OrderQuantity,
                p.ProductID,
                p.ProductName,
                p.Image,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice
            FROM ORDER_DETAIL od
            INNER JOIN SKU s ON od.SKUID = s.SKUID
            INNER JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE od.OrderID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính toán tổng tiền đơn hàng (Summary)
     * @param array $products - Danh sách sản phẩm từ getOrderProducts()
     * @param float $shippingFee
     * @param array $voucherInfo - [DiscountPercent, DiscountAmount, MinOrder]
     * @return array [subtotal, discount, promo, shipping_fee, total]
     */
    public function calculateOrderSummary($products, $shippingFee, $voucherInfo = [])
    {
        $subtotal = 0;  // Tổng theo OriginalPrice
        $discount = 0;  // Tổng giảm giá sản phẩm (OriginalPrice - PromotionPrice)

        foreach ($products as $product) {
            $originalPrice = $product['OriginalPrice'];
            $promotionPrice = $product['PromotionPrice'] ?? $originalPrice;
            $quantity = $product['OrderQuantity'];

            // Tính subtotal (theo giá gốc)
            $subtotal += $originalPrice * $quantity;

            // Tính discount (chênh lệch giá)
            if ($promotionPrice < $originalPrice) {
                $discount += ($originalPrice - $promotionPrice) * $quantity;
            }
        }

        // Tính Voucher Discount
        $promo = 0;
        $orderTotalForVoucher = 0; 
        // Note: Voucher condition usually based on PromotionPrice total (Actual accumulated payment before voucher)
        // Let's calculate the "Subtotal after Product Discount"
        foreach ($products as $product) {
             $price = $product['PromotionPrice'] ?? $product['OriginalPrice'];
             $orderTotalForVoucher += $price * $product['OrderQuantity'];
        }

        if (!empty($voucherInfo)) {
            $minOrder = $voucherInfo['MinOrder'] ?? 0;
            if ($orderTotalForVoucher >= $minOrder) {
                if (!empty($voucherInfo['DiscountPercent'])) {
                    $promo = $orderTotalForVoucher * ($voucherInfo['DiscountPercent'] / 100);
                } elseif (!empty($voucherInfo['DiscountAmount'])) {
                    $promo = $voucherInfo['DiscountAmount'];
                }
            }
        }

        // Shipping fee
        $shipping = $shippingFee ?? 0;

        // Tổng tiền = Subtotal - Discount - Promo + Shipping
        // Correct formula based on previous logic:
        // Total = (Sum of PromotionPrice) - Voucher + Shipping
        // But logic here was: Total = Subtotal (Original) - ProductDiscount - Promo + Shipping
        // Subtotal (Original) - ProductDiscount = Sum of PromotionPrice.
        // So it matches.
        
        $total = ($subtotal - $discount) - $promo + $shipping;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo' => $promo,
            'shipping_fee' => $shipping,
            'total' => max(0, $total)
        ];
    }

    /**
     * Cập nhật trạng thái đơn hàng
     * @param string $orderId
     * @param string $newStatus
     * @return bool
     */
    public function updateOrderStatus($orderId, $newStatus)
    {
        $sql = "
            UPDATE ORDERS 
            SET OrderStatus = ?
            WHERE OrderID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$newStatus, $orderId]);
    }

    /**
     * Hủy đơn hàng (chỉ cho phép khi status = 'Waiting Payment' hoặc 'Pending Confirmation')
     * @param string $orderId
     * @return bool
     */
    public function cancelOrder($orderId)
    {
        // Kiểm tra trạng thái hiện tại
        $order = $this->getOrderById($orderId);

        if (!$order) {
            return false;
        }

        $allowedStatuses = ['Waiting Payment', 'Pending Confirmation'];

        if (!in_array($order['OrderStatus'], $allowedStatuses)) {
            return false; // Không cho phép hủy
        }

        return $this->updateOrderStatus($orderId, 'Cancelled');
    }

    /**
     * Xác nhận đã nhận hàng (từ 'On Shipping' → 'Complete')
     * @param string $orderId
     * @return bool
     */
    public function confirmReceived($orderId)
    {
        $order = $this->getOrderById($orderId);

        if (!$order || $order['OrderStatus'] !== 'On Shipping') {
            return false;
        }

        return $this->updateOrderStatus($orderId, 'Complete');
    }

    /**
     * Lấy tất cả đơn hàng của customer (dùng cho trang Order History)
     * @param string $customerId
     * @return array
     */
    public function getOrdersByCustomer($customerId)
    {
        $sql = "
            SELECT 
                OrderID,
                OrderDate,
                OrderStatus,
                ShippingFee
            FROM ORDERS
            WHERE CustomerID = ?
            ORDER BY OrderDate DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
