<?php
// admin/includes/auth.php

// Đảm bảo chỉ load một lần
if (!defined('AUTH_LOADED')) {
    define('AUTH_LOADED', true);
    
    // Load db.php để có kết nối database
    require_once __DIR__ . '/db.php';
    
    // Khởi tạo session nếu chưa có
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    /**
     * Kiểm tra admin đã đăng nhập chưa
     */
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
    }
    
    /**
     * Kiểm tra và chuyển hướng nếu chưa đăng nhập
     */
    function checkAdminAuth() {
        if (!isAdminLoggedIn()) {
            header('Location: ' . BASE_URL . 'login.php');
            exit();
        }
        
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE AccountID = ? AND Email = ?");
            $stmt->execute([$_SESSION['admin_id'], $_SESSION['admin_email']]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                session_destroy();
                header('Location: ' . BASE_URL . 'login.php');
                exit();
            }
        } catch (Exception $e) {
            // Nếu có lỗi database, vẫn cho phép tiếp tục để tránh loop
        }
    }
    
    /**
     * Lấy thông tin admin
     */
    function getAdminInfo() {
        if (!isAdminLoggedIn()) return null;
        
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE AccountID = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Helper function để chuyển hướng
     */
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}
?>