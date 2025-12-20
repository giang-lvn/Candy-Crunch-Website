<?php
// models/website/MA_AccountModel.php

class MA_AccountModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Tạo AccountID tự động theo format ACC001, ACC002, ACC003...
     */
    public function generateAccountID() {
        try {
            $sql = "SELECT AccountID FROM ACCOUNT ORDER BY AccountID DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $lastAccount = $stmt->fetch();

            if ($lastAccount) {
                // Lấy số từ AccountID cuối cùng (VD: ACC001 -> 001)
                $lastNumber = intval(substr($lastAccount['AccountID'], 3));
                $newNumber = $lastNumber + 1;
                // Format lại thành ACC002, ACC003...
                return 'ACC' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            } else {
                // Nếu chưa có account nào, bắt đầu từ ACC001
                return 'ACC001';
            }
        } catch (PDOException $e) {
            error_log("Error generating AccountID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra email đã tồn tại trong hệ thống chưa
     */
    public function checkEmailExists($email) {
        try {
            $sql = "SELECT AccountID FROM ACCOUNT WHERE Email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo tài khoản mới
     */
    public function createAccount($accountID, $email, $password) {
        try {
            // Hash password trước khi lưu vào database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO ACCOUNT (AccountID, Email, Password, AccountStatus) 
                    VALUES (:accountID, :email, :password, 'Active')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':accountID', $accountID, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating account: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin account theo email (dùng cho đăng nhập)
     */
    public function getAccountByEmail($email) {
        try {
            $sql = "SELECT * FROM ACCOUNT WHERE Email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting account: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Xác thực đăng nhập
     */
    public function verifyLogin($email, $password) {
        $account = $this->getAccountByEmail($email);
        
        if ($account && password_verify($password, $account['Password'])) {
            return $account;
        }
        
        return false;
    }
}
?>