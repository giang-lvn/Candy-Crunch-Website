<?php
require_once __DIR__ . '/../db.php';

class WishlistModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getWishlistByCustomer($customerId) {
        $sql = "
            SELECT 
                p.ProductID,
                p.ProductName,
                s.Attribute,
                s.OriginalPrice,
                s.PromotionPrice,
                s.Image
            FROM WISHLIST w
            JOIN PRODUCT p ON w.ProductID = p.ProductID
            JOIN SKU s ON p.ProductID = s.ProductID
            WHERE w.CustomerID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function removeFromWishlist($customerId, $productId) {
        $sql = "DELETE FROM WISHLIST WHERE CustomerID = ? AND ProductID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $customerId, $productId);
        return $stmt->execute();
    }
}
