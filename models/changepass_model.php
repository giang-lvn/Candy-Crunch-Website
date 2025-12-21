<?php
class ChangePasswordModel {
    private $conn;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    // ✅ LẤY PASSWORD QUA CUSTOMERID
    public function getPasswordByCustomerId($customerId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT A.Password
                FROM ACCOUNT A
                JOIN CUSTOMER C ON A.AccountID = C.AccountID
                WHERE C.CustomerID = ?
            ");
            $stmt->execute([$customerId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPasswordByCustomerId Error: " . $e->getMessage());
            return false;
        }
    }

    // ✅ UPDATE PASSWORD QUA CUSTOMERID
    public function updatePasswordByCustomerId($customerId, $hashedPassword) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE ACCOUNT
                SET Password = ?
                WHERE AccountID = (
                    SELECT AccountID FROM CUSTOMER WHERE CustomerID = ?
                )
            ");
            return $stmt->execute([$hashedPassword, $customerId]);
        } catch (PDOException $e) {
            error_log("updatePasswordByCustomerId Error: " . $e->getMessage());
            return false;
        }
    }
}
