<?php
require_once __DIR__ . '/../db.php';

class WishlistModel
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Lấy ProductID từ SKUID
     */
    private function getProductIdFromSku($skuId)
    {
        $sql = "SELECT ProductID FROM SKU WHERE SKUID = :skuId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['ProductID'] : null;
    }

    /**
     * Lấy danh sách wishlist của customer
     */
    public function getWishlistByCustomer($customerId)
    {
        $sql = "
            SELECT 
                w.CustomerID,
                w.ProductID,
                p.ProductName,
                s.SKUID,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                s.Image
            FROM WISHLIST w
            JOIN PRODUCT p ON w.ProductID = p.ProductID
            JOIN SKU s ON p.ProductID = s.ProductID
            WHERE w.CustomerID = :customerId
            GROUP BY w.CustomerID, w.ProductID
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa sản phẩm khỏi wishlist
     */
    public function removeFromWishlist($customerId, $productId)
    {
        // Nếu productId là SKUID, chuyển đổi
        if (strpos($productId, '-') !== false) {
            $productId = $this->getProductIdFromSku($productId);
        }

        $sql = "DELETE FROM WISHLIST WHERE CustomerID = :customerId AND ProductID = :productId";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customerId' => $customerId,
            'productId' => $productId
        ]);
    }

    /**
     * Thêm sản phẩm vào wishlist
     */
    public function addToWishlist($customerId, $productId)
    {
        // Nếu productId là SKUID (có dấu -), lấy ProductID
        if (strpos($productId, '-') !== false) {
            $productId = $this->getProductIdFromSku($productId);
            if (!$productId) {
                return ['success' => false, 'message' => 'Không tìm thấy sản phẩm'];
            }
        }

        // Kiểm tra đã tồn tại chưa
        if ($this->isInWishlist($customerId, $productId)) {
            return ['success' => false, 'message' => 'Sản phẩm đã có trong wishlist'];
        }

        // Thêm mới
        $sql = "INSERT INTO WISHLIST (CustomerID, ProductID) VALUES (:customerId, :productId)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute(['customerId' => $customerId, 'productId' => $productId])) {
            return ['success' => true, 'message' => 'Đã thêm vào wishlist'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra'];
        }
    }

    /**
     * Kiểm tra sản phẩm có trong wishlist không
     */
    public function isInWishlist($customerId, $productId)
    {
        // Nếu productId là SKUID, chuyển đổi
        if (strpos($productId, '-') !== false) {
            $productId = $this->getProductIdFromSku($productId);
        }

        $sql = "SELECT * FROM WISHLIST WHERE CustomerID = :customerId AND ProductID = :productId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId, 'productId' => $productId]);
        return $stmt->rowCount() > 0;
    }
}
