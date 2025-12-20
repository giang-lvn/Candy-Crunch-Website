<?php
require_once __DIR__ . '/../db.php';

class ProductDetailModel
{
    private $conn;

    public function __construct()
    {
        global $db;
        $this->conn = $db;
    }

    /**
     * Lấy thông tin cơ bản của sản phẩm
     * 
     */
    public function getProductById($productId): array
    {
        $sql = "
            SELECT 
                p.ProductID,
                p.ProductName,
                p.Description,
                p.Flavour,
                p.Ingredient
            FROM PRODUCT p
            WHERE p.ProductID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Lấy danh sách SKU của sản phẩm
     *
     */
    public function getSkuByProductId($productId): array
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
            WHERE s.ProductID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy mô tả sản phẩm
     * @param string $productId
     * @return string|null
     */
    public function getProductDescription($productId): string|null
    {
        $sql = "
        SELECT Description
        FROM PRODUCT
        WHERE ProductID = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['Description'] : null;
    }


    /**
     * Lấy filter của sản phẩm (sugar-free, gluten-free...)
     * @param string $productId
     * @return array
     */
    public function getProductFilter($productId): array
    {
        $sql = "
        SELECT Filter
        FROM PRODUCT
        WHERE ProductID = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || empty($row['Filter'])) {
            return [];
        }

        // Tách filter thành mảng
        return array_map('trim', explode(',', $row['Filter']));
    }

    /**
     * Lấy danh sách Unit của sản phẩm
     * @param string $productId
     * @return array
     */
    public function getProductUnits($productId): array
    {
        $sql = "
        SELECT 
            SKUID,
            Attribute AS Unit
        FROM SKU
        WHERE ProductID = ?
        ORDER BY Attribute ASC
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy giá sản phẩm theo SKU
     * @param string $skuId
     * @return array|null
     */
    public function getProductPriceBySku($skuId): array
    {
        $sql = "
        SELECT 
            OriginalPrice,
            PromotionPrice
        FROM SKU
        WHERE SKUID = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $skuId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    /**
     * Lấy tồn kho sản phẩm theo SKU
     * @param string $skuId
     * @return array|null
     */
    public function getProductStockBySku($skuId): array
    {
        $sql = "
        SELECT 
            i.Stock,
            i.InventoryStatus
        FROM INVENTORY i
        JOIN SKU s ON i.InventoryID = s.InventoryID
        WHERE s.SKUID = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $skuId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }



}
