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
    /**
     * Resolve ID to a valid ProductID
     * Prioritizes checking PRODUCT table, then SKU table
     */
    private function resolveProductId($id)
    {
        // 1. Check if it's a direct ProductID
        $sql = "SELECT ProductID FROM PRODUCT WHERE ProductID = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        if ($stmt->fetch()) {
            return $id;
        }

        // 2. Check if it's a SKUID
        return $this->getProductIdFromSku($id);
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
     * Helper function to extract thumbnail path from JSON image data
     */
    private function getProductThumbnailPath($imageData)
    {
        if (empty($imageData))
            return '';

        $decoded = json_decode($imageData, true);
        if (is_array($decoded)) {
            foreach ($decoded as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'] ?? '';
                }
            }
            if (!empty($decoded[0])) {
                return is_array($decoded[0]) ? ($decoded[0]['path'] ?? '') : $decoded[0];
            }
            return '';
        }

        return $imageData;
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
                p.Image,
                s.SKUID,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice
            FROM WISHLIST w
            JOIN PRODUCT p ON w.ProductID = p.ProductID
            LEFT JOIN SKU s ON p.ProductID = s.ProductID
            WHERE w.CustomerID = :customerId
            GROUP BY w.CustomerID, w.ProductID
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process images
        foreach ($results as &$row) {
            $row['Image'] = $this->getProductThumbnailPath($row['Image']);
        }

        return $results;
    }

    /**
     * Xóa sản phẩm khỏi wishlist
     */
    public function removeFromWishlist($customerId, $productId)
    {
        $resolvedId = $this->resolveProductId($productId);

        if (!$resolvedId) {
            return false;
        }

        $sql = "DELETE FROM WISHLIST WHERE CustomerID = :customerId AND ProductID = :productId";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customerId' => $customerId,
            'productId' => $resolvedId
        ]);
    }

    /**
     * Thêm sản phẩm vào wishlist
     */
    public function addToWishlist($customerId, $productId)
    {
        $resolvedId = $this->resolveProductId($productId);

        if (!$resolvedId) {
            return ['success' => false, 'message' => 'Không tìm thấy sản phẩm'];
        }

        // Kiểm tra đã tồn tại chưa
        if ($this->isInWishlist($customerId, $resolvedId)) {
            return ['success' => false, 'message' => 'Sản phẩm đã có trong wishlist'];
        }

        // Thêm mới
        $sql = "INSERT INTO WISHLIST (CustomerID, ProductID) VALUES (:customerId, :productId)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute(['customerId' => $customerId, 'productId' => $resolvedId])) {
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
        // Don't auto-resolve here if we want to check strictly, 
        // but for safety in the context of previous methods, we resolve it too
        // However, if we are passing an ALREADY resolved ID (like inside addToWishlist), it's redundant but harmless if cached?
        // Actually, to be safe against external calls:
        $resolvedId = $this->resolveProductId($productId);

        if (!$resolvedId)
            return false;

        $sql = "SELECT * FROM WISHLIST WHERE CustomerID = :customerId AND ProductID = :productId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId, 'productId' => $resolvedId]);
        return $stmt->rowCount() > 0;
    }
}
