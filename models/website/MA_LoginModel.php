<?php
// models/website/MA_LoginModel.php

class MA_LoginModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Xác thực đăng nhập - Đơn giản, không phức tạp
     * @param string $email
     * @param string $password
     * @return array|false Trả về account nếu thành công, false nếu thất bại
     */
    public function authenticate($email, $password) {
        try {
            // 1. Lấy account từ email
            $sql = "SELECT AccountID, Email, Password, AccountStatus 
                    FROM ACCOUNT 
                    WHERE Email = :email AND AccountStatus = 'Active'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $account = $stmt->fetch();
            
            // 2. Nếu không tìm thấy account
            if (!$account) {
                return false;
            }
            
            // 3. Kiểm tra password (đã được hash trong sign up)
            // password_verify() sẽ so sánh password người dùng nhập với hash trong database
            if (password_verify($password, $account['Password'])) {
                return $account;
            }
            
            return false;
            
        } catch (PDOException $e) {
            // Ghi log lỗi
            error_log("Login authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin customer theo AccountID
     * @param string $accountID
     * @return array|null
     */
    public function getCustomerByAccountID($accountID) {
        try {
            $sql = "SELECT CustomerID, FirstName, LastName 
                    FROM CUSTOMER 
                    WHERE AccountID = :accountID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':accountID', $accountID);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error getting customer: " . $e->getMessage());
            return null;
        }
    }
}
?>