<?php

require_once __DIR__ . '/../db.php';

class CartModel
{
    protected $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Lấy danh sách sản phẩm trong giỏ hàng
    public function getCartItems($cartId)
    {
        $sql = "
            SELECT
                cd.SKUID,
                cd.CartQuantity,

                p.ProductName,
                p.ProductID,
                p.CategoryID,

                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                s.Image

            FROM CART_DETAIL cd
            JOIN SKU s ON cd.SKUID = s.SKUID
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE cd.CartID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $cartId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //Cập nhật số lượng sản phẩm trong giỏ
    public function updateQuantity($cartId, $skuId, $quantity)
    {
        $sql = "
            UPDATE CART_DETAIL
            SET CartQuantity = ?
            WHERE CartID = ? AND SKUID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $cartId, $skuId);
        return $stmt->execute();
    }

    //Xóa sản phẩm khỏi giỏ
    public function removeItem($cartId, $skuId)
    {
        $sql = "
            DELETE FROM CART_DETAIL
            WHERE CartID = ? AND SKUID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $skuId);
        return $stmt->execute();
    }

    //Lấy số lượng sản phẩm trong giỏ
    public function getQuantity($cartId, $skuId)
    {
        $sql = "
            SELECT CartQuantity
            FROM CART_DETAIL
            WHERE CartID = ? AND SKUID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $skuId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int)$row['CartQuantity'] : 0;
    }

    //Lấy danh mục từ giỏ hàng
    public function getCategoryIdsFromCart(int $customerId): array
    {
        $sql = "
            SELECT DISTINCT p.CategoryID
            FROM CART c
            JOIN CART_DETAIL cd ON c.CartID = cd.CartID
            JOIN SKU s ON cd.SKUID = s.SKUID
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE c.CustomerID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $categoryIds = [];
        while ($row = $result->fetch_assoc()) {
            $categoryIds[] = $row['CategoryID'];
        }
        return $categoryIds;
    }

    //Lấy sku đang có trng giỏ hàng
    public function getCartSkuIds(int $customerId): array
    {
        $sql = "
            SELECT cd.SKUID
            FROM CART c
            JOIN CART_DETAIL cd ON c.CartID = cd.CartID
            WHERE c.CustomerID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $skuIds = [];
        while ($row = $result->fetch_assoc()) {
            $skuIds[] = $row['SKUID'];
        }
        return $skuIds;
    }

    //Gợi ý sản phẩm upsell
    public function getUpsellProducts(
    array $categoryIds,
    array $excludeSkuIds,
    int $limit = 8
    ): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $params = [];

        // Category filter
        $catPlaceholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $params = array_merge($params, $categoryIds);

        // Exclude SKU in cart
        $excludeSql = '';
        if (!empty($excludeSkuIds)) {
            $excludePlaceholders = implode(',', array_fill(0, count($excludeSkuIds), '?'));
            $excludeSql = "AND s.SKUID NOT IN ($excludePlaceholders)";
            $params = array_merge($params, $excludeSkuIds);
        }

        $params[] = $limit;

        $sql = "
            SELECT
                s.SKUID,
                p.ProductName,
                s.Image,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice
            FROM SKU s
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            WHERE p.CategoryID IN ($catPlaceholders)
            $excludeSql
            AND i.InventoryStatus = 1
            AND i.Stock > 0
            ORDER BY RAND()
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        
        // Build bind_param types string
        $types = str_repeat('i', count($categoryIds));
        if (!empty($excludeSkuIds)) {
            $types .= str_repeat('i', count($excludeSkuIds));
        }
        $types .= 'i'; // for limit
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy 8 sản phẩm đầu tiên từ bảng PRODUCT (khi giỏ hàng trống)
    public function getFirstProducts(int $limit = 8): array
    {
        $sql = "
            SELECT
                s.SKUID,
                p.ProductName,
                s.Image,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice
            FROM SKU s
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            WHERE i.InventoryStatus = 'Available'
            ORDER BY p.ProductID ASC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //Tính tiền
    public function calculateCartAmount(array $cartItems): array
    {
        $subtotal = 0;
        $discount = 0;

        foreach ($cartItems as $item) {
            $qty = (int)$item['CartQuantity'];

            $originalTotal = $item['OriginalPrice'] * $qty;
            $subtotal += $originalTotal;

            if (!empty($item['PromotionPrice'])) {
                $promoTotal = $item['PromotionPrice'] * $qty;
                $discount += ($originalTotal - $promoTotal);
            }
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount
        ];
    }


    //Kiểm tra voucher
    public function findVoucherByCode(string $code): ?array
    {
        $sql = "
            SELECT *
            FROM VOUCHER
            WHERE Code = ?
            AND VoucherStatus = 'active'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();

        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        return $voucher ?: null;
    }

    //Tính giá rị voucher
    public function calculateVoucherDiscount(array $voucher, float $subtotal): float
    {
        if ($subtotal < $voucher['MinOrder']) {
            return 0;
        }

        //if ($baseAmount < $voucher['MinOrder']) {
        //    return 0;
        //}
        
        $today = date('Y-m-d');

        if ($today < $voucher['StartDate'] || $today > $voucher['EndDate']) {
            return 0;
        }

        if (!empty($voucher['DiscountPercent'])) {
            return round($subtotal * ($voucher['DiscountPercent'] / 100));
        }

        if (!empty($voucher['DiscountAmount'])) {
            return min($voucher['DiscountAmount'], $subtotal);
        }

        return 0;
    }

    //Tìm giỏ hàng đang hoạt động của khách hàng
    public function findActiveCartByCustomer(int $customerId): ?array
    {
        $sql = "
            SELECT CartID
            FROM CART
            WHERE CustomerID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $cart = $result->fetch_assoc();
        return $cart ?: null;
    }

    //Tạo giỏ hàng mới
    public function createCart(int $customerId): int
    {
        $sql = "
            INSERT INTO CART (CustomerID)
            VALUES (?)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        return $this->conn->insert_id;
    }

    //Thêm sản phẩm vào giỏ hàng
    public function addToCart(int $customerId, int $skuId, int $quantity = 1): bool
    {
        // Lấy hoặc tạo cart cho customer
        $cart = $this->findActiveCartByCustomer($customerId);
        $cartId = $cart ? $cart['CartID'] : $this->createCart($customerId);

        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $existingQty = $this->getQuantity($cartId, $skuId);

        if ($existingQty > 0) {
            // Nếu đã có, cập nhật số lượng (cộng thêm)
            $newQty = $existingQty + $quantity;
            return $this->updateQuantity($cartId, $skuId, $newQty);
        } else {
            // Nếu chưa có, thêm mới
            $sql = "
                INSERT INTO CART_DETAIL (CartID, SKUID, CartQuantity)
                VALUES (?, ?, ?)
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $cartId, $skuId, $quantity);
            return $stmt->execute();
        }
    }

}
