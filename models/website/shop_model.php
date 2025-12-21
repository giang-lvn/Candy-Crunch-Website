<?php

class ShopModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* =====================================================
       1. LOAD PRODUCT + TỒN KHO
    ===================================================== */
    public function getProducts(array $params): array
    {
        $sql = "
            SELECT 
                s.SKUID,
                p.ProductID,
                p.ProductName,
                c.CategoryName,
                p.Ingredient,
                p.Flavour,

                s.OriginalPrice,
                s.PromotionPrice,
                p.Image,

                i.Stock,
                i.InventoryStatus,

                IFNULL(AVG(fb.Rating), 0) AS Rating

            FROM PRODUCT p
            JOIN CATEGORY c ON p.CategoryID = c.CategoryID
            JOIN SKU s ON p.ProductID = s.ProductID
            JOIN INVENTORY i ON s.InventoryID = i.InventoryID
            LEFT JOIN FEEDBACK fb ON fb.SKUID = s.SKUID
            WHERE 1=1
        ";

        $bind = [];

        if (!empty($params['search'])) {
            $sql .= " AND p.ProductName LIKE :search ";
            $bind['search'] = '%' . $params['search'] . '%';
        }

        if (!empty($params['category'])) {
            $sql .= " AND c.CategoryName = :category ";
            $bind['category'] = $params['category'];
        }

        if (!empty($params['ingredient'])) {
            $sql .= " AND p.Ingredient LIKE :ingredient ";
            $bind['ingredient'] = '%' . $params['ingredient'] . '%';
        }

        if (!empty($params['flavour'])) {
            $sql .= " AND p.Flavour LIKE :flavour ";
            $bind['flavour'] = '%' . $params['flavour'] . '%';
        }

        $sql .= " GROUP BY s.SKUID ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => [
            'id'        => $row['SKUID'],
            'name'      => $row['ProductName'],
            'category'  => $row['CategoryName'],
            'rating'    => round($row['Rating'], 1),

            'price'     => $row['PromotionPrice'] ?: $row['OriginalPrice'],
            'oldPrice'  => $row['PromotionPrice'] ? $row['OriginalPrice'] : null,
            'image'     => $row['Image'],

            'ingredient'=> $row['Ingredient'],
            'flavour'   => $row['Flavour'],

            'stock'     => (int)$row['Stock'],
            'inventory_status' => $row['InventoryStatus'],
            'can_add_to_cart' =>
                $row['InventoryStatus'] !== 'Out of stock' && (int)$row['Stock'] > 0
        ], $rows);
    }

    /* =====================================================
       2. CART CORE
    ===================================================== */

    private function getInventoryBySku(string $skuId): array
    {
        $sql = "
            SELECT i.Stock, i.InventoryStatus
            FROM INVENTORY i
            JOIN SKU s ON i.InventoryID = s.InventoryID
            WHERE s.SKUID = :skuId
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);

        return $stmt->fetch(PDO::FETCH_ASSOC)
            ?: ['Stock' => 0, 'InventoryStatus' => 'Out of stock'];
    }

    private function getOrCreateCart(string $customerId): string
    {
        $sql = "SELECT CartID FROM CART WHERE CustomerID = :customerId LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cart) return $cart['CartID'];

        $cartId = 'CART' . substr(uniqid(), -6);
        $sql = "
            INSERT INTO CART (CartID, CustomerID, TimeUpdate)
            VALUES (:cartId, :customerId, NOW())
        ";
        $this->db->prepare($sql)->execute([
            'cartId' => $cartId,
            'customerId' => $customerId
        ]);

        return $cartId;
    }

    public function addToCart(string $customerId, string $skuId, int $qty = 1): void
    {
        $this->db->beginTransaction();

        try {
            $inventory = $this->getInventoryBySku($skuId);

            if (
                $inventory['InventoryStatus'] === 'Out of stock' ||
                (int)$inventory['Stock'] < $qty
            ) {
                throw new Exception('Sản phẩm đã hết hàng');
            }

            $cartId = $this->getOrCreateCart($customerId);

            $sql = "
                SELECT CartQuantity 
                FROM CART_DETAIL 
                WHERE CartID = :cartId AND SKUID = :skuId
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cartId' => $cartId,
                'skuId'  => $skuId
            ]);

            $currentQty = (int)($stmt->fetchColumn() ?? 0);

            if ($currentQty > 0) {
                $sql = "
                    UPDATE CART_DETAIL
                    SET CartQuantity = CartQuantity + :qty
                    WHERE CartID = :cartId AND SKUID = :skuId
                ";
            } else {
                $sql = "
                    INSERT INTO CART_DETAIL (CartID, SKUID, CartQuantity)
                    VALUES (:cartId, :skuId, :qty)
                ";
            }

            $this->db->prepare($sql)->execute([
                'cartId' => $cartId,
                'skuId'  => $skuId,
                'qty'    => $qty
            ]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
