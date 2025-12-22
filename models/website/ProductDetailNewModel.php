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
     * - ProductName, Description từ bảng PRODUCT
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
                s.Image,
                i.Stock
            FROM SKU s
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
                s.Image,
                i.Stock
            FROM SKU s
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
