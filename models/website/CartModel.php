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
}
