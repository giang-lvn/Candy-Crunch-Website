<?php
require_once __DIR__ . '/../db.php';

class RatingModel {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    // Tạo FeedbackID tự động (F + timestamp)
    private function generateFeedbackID() {
        return 'F' . time() . rand(100, 999);
    }

    // Kiểm tra khách hàng đã đánh giá sản phẩm này chưa
    public function hasCustomerRated($customerID, $skuID) {
        $stmt = $this->db->prepare("SELECT FeedbackID FROM FEEDBACK WHERE CustomerID = ? AND SKUID = ?");
        $stmt->execute([$customerID, $skuID]);
        return $stmt->rowCount() > 0;
    }

    // Kiểm tra sản phẩm tồn tại
    public function isProductValid($skuID) {
        $stmt = $this->db->prepare("SELECT SKUID FROM SKU WHERE SKUID = ?");
        $stmt->execute([$skuID]);
        return $stmt->rowCount() > 0;
    }

    // Lưu feedback vào database
    public function createFeedback($customerID, $skuID, $rating, $comment) {
        $feedbackID = $this->generateFeedbackID();
        
        $stmt = $this->db->prepare("
            INSERT INTO FEEDBACK (FeedbackID, CustomerID, SKUID, Rating, Comment, CreateDate) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$feedbackID, $customerID, $skuID, $rating, $comment]);
    }

    // Cập nhật feedback đã có (nếu cho phép sửa đánh giá)
    public function updateFeedback($customerID, $skuID, $rating, $comment) {
        $stmt = $this->db->prepare("
            UPDATE FEEDBACK 
            SET Rating = ?, Comment = ?, CreateDate = NOW() 
            WHERE CustomerID = ? AND SKUID = ?
        ");
        
        return $stmt->execute([$rating, $comment, $customerID, $skuID]);
    }

    // Lấy tất cả feedback của 1 sản phẩm
    public function getFeedbacksByProduct($skuID) {
        $stmt = $this->db->prepare("
            SELECT f.*, c.CustomerName 
            FROM FEEDBACK f
            JOIN CUSTOMER c ON f.CustomerID = c.CustomerID
            WHERE f.SKUID = ?
            ORDER BY f.CreateDate DESC
        ");
        $stmt->execute([$skuID]);
        return $stmt->fetchAll();
    }

    // Tính điểm trung bình và số lượng đánh giá
    public function getProductRatingStats($skuID) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(AVG(Rating), 0) as average_rating,
                COUNT(*) as total_reviews
            FROM FEEDBACK 
            WHERE SKUID = ?
        ");
        $stmt->execute([$skuID]);
        return $stmt->fetch();
    }
}
?>