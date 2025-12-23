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


    /**
     * Helper function to extract thumbnail path from JSON image data
     */
    private function getProductThumbnailPath($imageData)
    {
        if (empty($imageData))
            return '';


        $decoded = json_decode($imageData, true);
        if (is_array($decoded)) {
            // Find the thumbnail image
            foreach ($decoded as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'] ?? '';
                }
            }
            // Return first image if no thumbnail is set
            if (!empty($decoded[0])) {
                return is_array($decoded[0]) ? ($decoded[0]['path'] ?? '') : $decoded[0];
            }
            return '';
        }


        return $imageData;
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
                p.Image,


                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice


            FROM CART_DETAIL cd
            JOIN SKU s ON cd.SKUID = s.SKUID
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE cd.CartID = ?
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cartId); // CartID is VARCHAR(10)
        $stmt->execute();


        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);


        // Process thumbnail images
        foreach ($items as &$item) {
            $item['Image'] = $this->getProductThumbnailPath($item['Image']);
        }


        return $items;
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
        $stmt->bind_param("iss", $quantity, $cartId, $skuId);
        return $stmt->execute();
    }


    // Xóa sản phẩm khỏi giỏ
    public function removeItem($cartId, $skuId)
    {
        $sql = "
            DELETE FROM CART_DETAIL
            WHERE CartID = ? AND SKUID = ?
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $cartId, $skuId);
        return $stmt->execute();
    }


    // Lấy tất cả SKUs của một sản phẩm (cho attribute dropdown)
    public function getProductSKUs($productId)
    {
        $sql = "
            SELECT
                SKUID,
                Attribute,
                OriginalPrice,
                PromotionPrice
            FROM SKU
            WHERE ProductID = ?
            ORDER BY Attribute
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();


        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // Đổi attribute (đổi SKUID) trong giỏ hàng
    public function changeAttribute($cartId, $oldSkuId, $newSkuId)
    {
        // Lấy quantity hiện tại
        $currentQty = $this->getQuantity($cartId, $oldSkuId);
        if ($currentQty <= 0) {
            return false;
        }


        // Kiểm tra xem SKU mới đã có trong giỏ chưa
        $existingQty = $this->getQuantity($cartId, $newSkuId);


        if ($existingQty > 0) {
            // Nếu SKU mới đã có -> cộng dồn quantity và xóa SKU cũ
            $this->updateQuantity($cartId, $newSkuId, $existingQty + $currentQty);
            $this->removeItem($cartId, $oldSkuId);
        } else {
            // Nếu SKU mới chưa có -> update SKUID trực tiếp
            $sql = "
                UPDATE CART_DETAIL
                SET SKUID = ?
                WHERE CartID = ? AND SKUID = ?
            ";


            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sss", $newSkuId, $cartId, $oldSkuId);
            return $stmt->execute();
        }


        return true;
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
        $stmt->bind_param("ss", $cartId, $skuId);
        $stmt->execute();


        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['CartQuantity'] : 0;
    }


    //Lấy danh mục từ giỏ hàng
    public function getCategoryIdsFromCart($customerId): array
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
        $stmt->bind_param("s", $customerId);
        $stmt->execute();


        $result = $stmt->get_result();
        $categoryIds = [];
        while ($row = $result->fetch_assoc()) {
            $categoryIds[] = $row['CategoryID'];
        }
        return $categoryIds;
    }


    //Lấy sku đang có trng giỏ hàng
    public function getCartSkuIds($customerId): array
    {
        $sql = "
            SELECT cd.SKUID
            FROM CART c
            JOIN CART_DETAIL cd ON c.CartID = cd.CartID
            WHERE c.CustomerID = ?
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $customerId);
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
    ): array {
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
                p.Image,
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
                p.Image,
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
            $qty = (int) $item['CartQuantity'];


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
    // Validate Voucher - returns [success => bool, message => string]
    public function validateVoucher($voucher, $subtotal)
    {
        if (!$voucher)
            return ['success' => false, 'message' => 'Voucher không tồn tại'];


        $today = date('Y-m-d');
        $startDate = $voucher['StartDate'];
        $endDate = $voucher['EndDate'];
        $minOrder = (float) $voucher['MinOrder'];


        // 1. Check Date
        if (strtotime($startDate) > strtotime($today)) {
            return ['success' => false, 'message' => 'Voucher chưa đến ngày áp dụng (Bắt đầu: ' . date('d/m/Y', strtotime($startDate)) . ')'];
        }


        if (strtotime($today) > strtotime($endDate)) {
            return ['success' => false, 'message' => 'Voucher đã hết hạn sử dụng (Hết hạn: ' . date('d/m/Y', strtotime($endDate)) . ')'];
        }


        // 2. Check Min Order
        if ($minOrder > 0 && $subtotal < $minOrder) {
            return ['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($minOrder, 0, ',', '.') . 'đ'];
        }


        return ['success' => true, 'message' => 'Áp dụng voucher thành công'];
    }


    public function calculateVoucherDiscount(array $voucher, float $subtotal): float
    {
        // Use validateVoucher internal check or just simple calc
        $validation = $this->validateVoucher($voucher, $subtotal);
        if (!$validation['success']) {
            return 0;
        }


        if (isset($voucher['DiscountPercent']) && $voucher['DiscountPercent'] > 0) {
            return round($subtotal * ($voucher['DiscountPercent'] / 100));
        }


        if (isset($voucher['DiscountAmount']) && $voucher['DiscountAmount'] > 0) {
            return min($voucher['DiscountAmount'], $subtotal);
        }


        return 0;
    }


    //Tìm giỏ hàng đang hoạt động của khách hàng
    public function findActiveCartByCustomer($customerId): ?array
    {
        $sql = "
            SELECT CartID
            FROM CART
            WHERE CustomerID = ?
            LIMIT 1
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $customerId);
        $stmt->execute();


        $result = $stmt->get_result();
        $cart = $result->fetch_assoc();
        return $cart ?: null;
    }


    //Tạo giỏ hàng mới
    public function createCart($customerId): string
    {
        // Tạo CartID dạng VARCHAR(10) - VD: "CA001"
        $cartId = $this->generateCartId();


        $sql = "
            INSERT INTO CART (CartID, CustomerID, TimeUpdate)
            VALUES (?, ?, NOW())
        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $cartId, $customerId);
        $stmt->execute();


        return $cartId;
    }


    // Tạo CartID mới theo format VARCHAR(10) - VD: "CA001"
    private function generateCartId(): string
    {
        $sql = "SELECT CartID FROM CART ORDER BY CartID DESC LIMIT 1";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();


        if ($row) {
            // Lấy số từ CartID hiện tại và tăng lên 1
            $lastId = $row['CartID'];
            $number = (int) substr($lastId, 2); // Bỏ "CA" đầu tiên
            $newNumber = $number + 1;
            return 'CA' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } else {
            return 'CA001';
        }
    }


    //Thêm sản phẩm vào giỏ hàng
    public function addToCart($customerId, string $skuId, int $quantity = 1): bool
    {
        // Kiểm tra tồn kho trước khi thêm vào giỏ
        $stockInfo = $this->getStockBySku($skuId);
        if (!$stockInfo) {
            return false; // SKU không tồn tại
        }

        $availableStock = (int) $stockInfo['Stock'];

        // Lấy hoặc tạo cart cho customer
        $cart = $this->findActiveCartByCustomer($customerId);
        $cartId = $cart ? $cart['CartID'] : $this->createCart($customerId);


        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $existingQty = $this->getQuantity($cartId, $skuId);
        $totalQtyNeeded = $existingQty + $quantity;

        // Kiểm tra nếu tổng số lượng vượt quá tồn kho
        if ($totalQtyNeeded > $availableStock) {
            return false; // Không đủ hàng
        }


        if ($existingQty > 0) {
            // Nếu đã có, cập nhật số lượng (cộng thêm)
            return $this->updateQuantity($cartId, $skuId, $totalQtyNeeded);
        } else {
            // Nếu chưa có, thêm mới
            $sql = "
                INSERT INTO CART_DETAIL (CartID, SKUID, CartQuantity)
                VALUES (?, ?, ?)
            ";


            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $cartId, $skuId, $quantity);
            return $stmt->execute();
        }
    }

    /**
     * Lấy thông tin tồn kho của SKU
     */
    public function getStockBySku(string $skuId): ?array
    {
        $sql = "
            SELECT i.Stock, i.InventoryStatus, p.ProductName, s.Attribute
            FROM SKU s
            JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            JOIN PRODUCT p ON s.ProductID = p.ProductID
            WHERE s.SKUID = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $skuId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Thêm sản phẩm vào giỏ với thông tin chi tiết về lỗi
     * Trả về array với success và message
     */
    public function addToCartWithMessage($customerId, string $skuId, int $quantity = 1): array
    {
        // Kiểm tra tồn kho trước khi thêm vào giỏ
        $stockInfo = $this->getStockBySku($skuId);
        if (!$stockInfo) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $availableStock = (int) $stockInfo['Stock'];
        $productName = $stockInfo['ProductName'] . ' (' . $stockInfo['Attribute'] . 'g)';

        if ($availableStock <= 0) {
            return ['success' => false, 'message' => "Sorry, '{$productName}' is out of stock"];
        }

        // Lấy hoặc tạo cart cho customer
        $cart = $this->findActiveCartByCustomer($customerId);
        $cartId = $cart ? $cart['CartID'] : $this->createCart($customerId);


        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $existingQty = $this->getQuantity($cartId, $skuId);
        $totalQtyNeeded = $existingQty + $quantity;

        // Kiểm tra nếu tổng số lượng vượt quá tồn kho
        if ($totalQtyNeeded > $availableStock) {
            $canAdd = $availableStock - $existingQty;
            if ($canAdd <= 0) {
                return ['success' => false, 'message' => "You already have the maximum available quantity ({$availableStock}) in your cart"];
            }
            return ['success' => false, 'message' => "Only {$canAdd} more items available. You have {$existingQty} in cart, stock is {$availableStock}"];
        }


        if ($existingQty > 0) {
            // Nếu đã có, cập nhật số lượng (cộng thêm)
            $success = $this->updateQuantity($cartId, $skuId, $totalQtyNeeded);
        } else {
            // Nếu chưa có, thêm mới
            $sql = "
                INSERT INTO CART_DETAIL (CartID, SKUID, CartQuantity)
                VALUES (?, ?, ?)
            ";


            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $cartId, $skuId, $quantity);
            $success = $stmt->execute();
        }

        if ($success) {
            return ['success' => true, 'message' => 'Product added to cart successfully'];
        }
        return ['success' => false, 'message' => 'Failed to add product to cart'];
    }






}



