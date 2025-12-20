<?php
// models/website/product-detail.php

require_once __DIR__ . '/../db.php';

class ProductDetailModel
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Lấy thông tin cơ bản của sản phẩm
     */
    public function getProductById($productId)
    {
        $sql = "
            SELECT 
                p.ProductID,
                p.ProductName,
                p.Description,
                p.Flavour,
                p.Ingredient,
                p.Filter
            FROM PRODUCT p
            WHERE p.ProductID = :productId
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách SKU của sản phẩm
     */
    public function getSkuByProductId($productId)
    {
        $sql = "
            SELECT 
                s.SKUID,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                s.Image,
                i.Stock,
                i.InventoryStatus
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
     * Lấy mô tả sản phẩm
     */
    public function getProductDescription($productId)
    {
        $sql = "
            SELECT Description
            FROM PRODUCT
            WHERE ProductID = :productId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['Description'] : null;
    }

    /**
     * Lấy filter của sản phẩm (sugar-free, gluten-free...)
     */
    public function getProductFilter($productId)
    {
        $sql = "
            SELECT Filter
            FROM PRODUCT
            WHERE ProductID = :productId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['Filter'])) {
            return [];
        }

        // Tách filter thành mảng
        return array_map('trim', explode(',', $row['Filter']));
    }

    /**
     * Lấy danh sách Unit của sản phẩm
     */
    public function getProductUnits($productId)
    {
        $sql = "
            SELECT 
                SKUID,
                Attribute AS Unit
            FROM SKU
            WHERE ProductID = :productId
            ORDER BY Attribute ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['productId' => $productId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy giá sản phẩm theo SKU
     */
    public function getProductPriceBySku($skuId)
    {
        $sql = "
            SELECT 
                OriginalPrice,
                PromotionPrice
            FROM SKU
            WHERE SKUID = :skuId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tồn kho sản phẩm theo SKU
     */
    public function getProductStockBySku($skuId)
    {
        $sql = "
            SELECT 
                i.Stock,
                i.InventoryStatus
            FROM INVENTORY i
            JOIN SKU s ON i.InventoryID = s.InventoryID
            WHERE s.SKUID = :skuId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}