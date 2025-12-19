<?php

class SignUpModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Kiểm tra xem email đã tồn tại chưa
    public function isEmailTaken(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM ACCOUNT WHERE Email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    // Đăng ký tài khoản mới (Transaction)
    public function registerUser(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Tạo AccountID tự động (theo logic của bạn: ACC + số tăng dần)
            $accountId = $this->generateId('ACCOUNT', 'AccountID', 'ACC');

            // 2. Insert vào bảng ACCOUNT
            // Lưu ý: Tôi giả định bảng ACCOUNT có cột Password và Role. 
            // Nếu tên cột khác, bạn hãy sửa lại ở đây.
            $sqlAccount = "
                INSERT INTO ACCOUNT (AccountID, Email, Password, AccountStatus, Role)
                VALUES (:accountId, :email, :password, 'Active', 'Customer')
            ";
            
            $stmtAccount = $this->db->prepare($sqlAccount);
            $stmtAccount->execute([
                'accountId' => $accountId,
                'email'     => $data['email'],
                'password'  => $data['password'] // Controller sẽ hash password trước khi truyền vào
            ]);

            // 3. Tạo CustomerID tự động (CUS + số tăng dần)
            $customerId = $this->generateId('CUSTOMER', 'CustomerID', 'CUS');

            // 4. Insert vào bảng CUSTOMER
            $sqlCustomer = "
                INSERT INTO CUSTOMER (
                    CustomerID, AccountID, FirstName, LastName, 
                    CustomerBirth, CustomerGender, Avatar
                ) VALUES (
                    :customerId, :accountId, :firstName, :lastName, 
                    :birth, :gender, :avatar
                )
            ";

            $stmtCustomer = $this->db->prepare($sqlCustomer);
            $stmtCustomer->execute([
                'customerId' => $customerId,
                'accountId'  => $accountId,
                'firstName'  => $data['first_name'],
                'lastName'   => $data['last_name'],
                'birth'      => $data['birth'],
                'gender'     => $data['gender'],
                'avatar'     => 'default_avatar.png' // Avatar mặc định
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Register Error: " . $e->getMessage());
            return false;
        }
    }

    // Helper: Logic sinh ID giống hệt file account_model của bạn
    private function generateId(string $table, string $column, string $prefix): string
    {
        // Lấy số lớn nhất hiện tại từ chuỗi con (VD: ACC005 -> lấy 5)
        $sql = "SELECT MAX(CAST(SUBSTRING($column, 4) AS UNSIGNED)) FROM $table";
        $stmt = $this->db->query($sql);
        $next = ((int)$stmt->fetchColumn()) + 1;
        
        // Pad thêm số 0 vào trước (VD: ACC006)
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}