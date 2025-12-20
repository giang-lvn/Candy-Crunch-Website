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

    // Thêm vào class WishlistModel
    public function addToWishlist($customerId, $productId) {
        // Kiểm tra đã tồn tại chưa
        $checkSql = "SELECT * FROM WISHLIST WHERE CustomerID = ? AND ProductID = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $customerId, $productId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Sản phẩm đã có trong wishlist'];
        }

        // Thêm mới
        $sql = "INSERT INTO WISHLIST (CustomerID, ProductID) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $customerId, $productId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đã thêm vào wishlist'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra'];
        }
    }

    public function isInWishlist($customerId, $productId) {
        $sql = "SELECT * FROM WISHLIST WHERE CustomerID = ? AND ProductID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $customerId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
