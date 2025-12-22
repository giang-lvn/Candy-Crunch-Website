<?php
// models/website/ProductDetailNewModel.php

require_once __DIR__ . '/../db.php';

class ProductDetailNewModel
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Lấy thông tin chi tiết sản phẩm bao gồm:
     * - ProductName, Description, Image từ bảng PRODUCT
     * - OriginalPrice, PromotionPrice từ bảng SKU
     * - Stock từ bảng INVENTORY
     */
    public function getProductDetail($productId)
    {
        $sql = "
            SELECT 
                p.ProductID,
                p.ProductName,
                p.Description,
                p.Image,
                p.CategoryID,
                s.SKUID,
                s.OriginalPrice,
                s.PromotionPrice,
                i.Stock
            FROM PRODUCT p
            LEFT JOIN SKU s ON p.ProductID = s.ProductID
            LEFT JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            WHERE p.ProductID = :productId
            ORDER BY s.SKUID ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get related products from the same category
     */
    public function getRelatedProducts($categoryId, $excludeProductId, $limit = 4)
    {
        $sql = "
            SELECT 
                p.ProductID, 
                p.ProductName, 
                p.Image,
                s.SKUID,
                s.OriginalPrice, 
                s.PromotionPrice
            FROM PRODUCT p
            JOIN SKU s ON p.ProductID = s.ProductID
            WHERE p.CategoryID = :categoryId 
            AND p.ProductID != :excludeProductId
            GROUP BY p.ProductID
            ORDER BY RAND()
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':excludeProductId', $excludeProductId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper function to parse product images from JSON format
     * @param string|null $imageData JSON encoded image data
     * @return array Array of image objects with 'path' and 'is_thumbnail'
     */
    public function parseProductImages($imageData)
    {
        if (empty($imageData)) return [];
        
        $decoded = json_decode($imageData, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        
        // Old format: single image path - convert to new format
        return [['path' => $imageData, 'is_thumbnail' => true]];
    }

    /**
     * Get thumbnail image path from JSON image data
     * @param string|null $imageData JSON encoded image data
     * @return string Thumbnail image path or empty string
     */
    public function getProductThumbnail($imageData)
    {
        if (empty($imageData)) return '';
        
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
        
        // Old format: return as-is
        return $imageData;
    }

    /**
     * Lấy tất cả SKU của sản phẩm với giá và tồn kho
     */
    public function getAllSkuWithStock($productId)
    {
        $sql = "
            SELECT 
                s.SKUID,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                p.Image,
                i.Stock
            FROM SKU s
            LEFT JOIN PRODUCT p ON s.ProductID = p.ProductID
            LEFT JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            WHERE s.ProductID = :productId
            ORDER BY s.Attribute ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin SKU theo SKUID (dùng cho AJAX)
     */
    public function getSkuById($skuId)
    {
        $sql = "
            SELECT 
                s.SKUID,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                p.ProductName,
                p.Image,
                i.Stock
            FROM SKU s
            LEFT JOIN PRODUCT p ON s.ProductID = p.ProductID
            LEFT JOIN INVENTORY i ON s.InventoryID = i.InventoryID

            WHERE s.SKUID = :skuId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách Ingredient của sản phẩm
     * @return array Mảng các ingredient
     */
    public function getProductIngredients($productId)
    {
        $sql = "
            SELECT Ingredient
            FROM PRODUCT
            WHERE ProductID = :productId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['Ingredient'])) {
            return [];
        }

        // Tách ingredient thành mảng (phân cách bằng dấu phẩy)
        return array_map('trim', explode(',', $row['Ingredient']));
    }
}
