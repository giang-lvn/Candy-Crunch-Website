<?php
// models/website/MA_CustomerModel.php

class MA_CustomerModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Tạo CustomerID tự động theo format CUS001, CUS002, CUS003...
     */
    public function generateCustomerID() {
        try {
            $sql = "SELECT CustomerID FROM CUSTOMER ORDER BY CustomerID DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $lastCustomer = $stmt->fetch();

            if ($lastCustomer) {
                // Lấy số từ CustomerID cuối cùng (VD: CUS001 -> 001)
                $lastNumber = intval(substr($lastCustomer['CustomerID'], 3));
                $newNumber = $lastNumber + 1;
                // Format lại thành CUS002, CUS003...
                return 'CUS' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            } else {
                // Nếu chưa có customer nào, bắt đầu từ CUS001
                return 'CUS001';
            }
        } catch (PDOException $e) {
            error_log("Error generating CustomerID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo thông tin customer mới
     * CustomerBirth, CustomerGender, Avatar sẽ để NULL
     */
    public function createCustomer($customerID, $accountID, $firstName, $lastName) {
        try {
            $sql = "INSERT INTO CUSTOMER (CustomerID, AccountID, FirstName, LastName, CustomerBirth, CustomerGender, Avatar) 
                    VALUES (:customerID, :accountID, :firstName, :lastName, NULL, NULL, NULL)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customerID', $customerID, PDO::PARAM_STR);
            $stmt->bindParam(':accountID', $accountID, PDO::PARAM_STR);
            $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
            $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating customer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin customer theo AccountID
     */
    public function getCustomerByAccountID($accountID) {
        try {
            $sql = "SELECT * FROM CUSTOMER WHERE AccountID = :accountID";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':accountID', $accountID, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting customer: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật thông tin customer
     */
    public function updateCustomer($customerID, $data) {
        try {
            $sql = "UPDATE CUSTOMER SET ";
            $fields = [];
            
            if (isset($data['FirstName'])) $fields[] = "FirstName = :firstName";
            if (isset($data['LastName'])) $fields[] = "LastName = :lastName";
            if (isset($data['CustomerBirth'])) $fields[] = "CustomerBirth = :customerBirth";
            if (isset($data['CustomerGender'])) $fields[] = "CustomerGender = :customerGender";
            if (isset($data['Avatar'])) $fields[] = "Avatar = :avatar";
            
            if (empty($fields)) return false;
            
            $sql .= implode(", ", $fields) . " WHERE CustomerID = :customerID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customerID', $customerID, PDO::PARAM_STR);
            
            if (isset($data['FirstName'])) $stmt->bindParam(':firstName', $data['FirstName'], PDO::PARAM_STR);
            if (isset($data['LastName'])) $stmt->bindParam(':lastName', $data['LastName'], PDO::PARAM_STR);
            if (isset($data['CustomerBirth'])) $stmt->bindParam(':customerBirth', $data['CustomerBirth'], PDO::PARAM_STR);
            if (isset($data['CustomerGender'])) $stmt->bindParam(':customerGender', $data['CustomerGender'], PDO::PARAM_STR);
            if (isset($data['Avatar'])) $stmt->bindParam(':avatar', $data['Avatar'], PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating customer: " . $e->getMessage());
            return false;
        }
    }
}
?>