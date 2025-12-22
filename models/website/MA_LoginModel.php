<?php
// models/website/MA_LoginModel.php

class MA_LoginModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Xác thực đăng nhập - Kiểm tra trạng thái tài khoản
     * @param string $email
     * @param string $password
     * @return array Trả về array với status và data
     */
    public function authenticate($email, $password) {
        try {
            // 1. Lấy account từ email (không lọc theo status)
            $sql = "SELECT AccountID, Email, Password, AccountStatus 
                    FROM ACCOUNT 
                    WHERE Email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $account = $stmt->fetch();
            
            // 2. Nếu không tìm thấy account
            if (!$account) {
                return ['status' => 'not_found', 'account' => null];
            }
            
            // 3. Kiểm tra password trước
            if (!password_verify($password, $account['Password'])) {
                return ['status' => 'wrong_password', 'account' => null];
            }
            
            // 4. Kiểm tra trạng thái tài khoản
            $accountStatus = $account['AccountStatus'];
            
            if ($accountStatus === 'Inactive') {
                return ['status' => 'inactive', 'account' => null];
            }
            
            if ($accountStatus === 'Banned') {
                return ['status' => 'banned', 'account' => null];
            }
            
            if ($accountStatus === 'Active') {
                return ['status' => 'success', 'account' => $account];
            }
            
            // Trạng thái không xác định
            return ['status' => 'unknown', 'account' => null];
            
        } catch (PDOException $e) {
            // Ghi log lỗi
            error_log("Login authentication error: " . $e->getMessage());
            return ['status' => 'error', 'account' => null];
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