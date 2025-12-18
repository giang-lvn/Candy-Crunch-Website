<?php
// /models/website/changepass_model.php

class ChangePasswordModel {
    private $conn;

    public function __construct() {
        global $db; // Lấy PDO connection từ db.php
        $this->conn = $db;
    }

    public function getPasswordByAccountId($accountId) {
        try {
            $stmt = $this->conn->prepare("SELECT Password FROM ACCOUNT WHERE AccountID = ?");
            $stmt->execute([$accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPasswordByAccountId Error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($accountId, $hashedPassword) {
        try {
            $stmt = $this->conn->prepare("UPDATE ACCOUNT SET Password = ? WHERE AccountID = ?");
            return $stmt->execute([$hashedPassword, $accountId]);
        } catch (PDOException $e) {
            error_log("updatePassword Error: " . $e->getMessage());
            return false;
        }
    }
}