<?php

class AccountModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(string $accountId): ?array
    {
        $sql = "
            SELECT AccountID, Email, AccountStatus
            FROM ACCOUNT
            WHERE AccountID = :accountId
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['accountId' => $accountId]);

        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        return $account ?: null;
    }

    public function getCustomerByAccountId(string $accountId): array
    {
        $sql = "
            SELECT 
                c.CustomerID,
                a.Email,
                c.FirstName,
                c.LastName,
                c.CustomerBirth,
                c.CustomerGender,
                c.Avatar,
                a.AccountStatus
            FROM CUSTOMER c
            JOIN ACCOUNT a ON c.AccountID = a.AccountID
            WHERE a.AccountID = :accountId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['accountId' => $accountId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // ✅ FIX: Update cả ACCOUNT (email) và CUSTOMER (profile)
    public function updateCustomerProfile(string $accountId, string $customerId, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // ✅ 1. Update Email trong bảng ACCOUNT
            if (!empty($data['email'])) {
                $sqlAccount = "
                    UPDATE ACCOUNT
                    SET Email = :email
                    WHERE AccountID = :accountId
                ";
                $stmtAccount = $this->db->prepare($sqlAccount);
                $stmtAccount->execute([
                    'email' => $data['email'],
                    'accountId' => $accountId
                ]);
            }

            // ✅ 2. Update thông tin trong bảng CUSTOMER
            $sqlCustomer = "
                UPDATE CUSTOMER
                SET 
                    FirstName = :firstName,
                    LastName = :lastName,
                    CustomerBirth = :birth,
                    CustomerGender = :gender
                WHERE CustomerID = :customerId
            ";

            $stmtCustomer = $this->db->prepare($sqlCustomer);
            $stmtCustomer->execute([
                'firstName'  => $data['first_name'],
                'lastName'   => $data['last_name'],
                'birth'      => $data['birth'],
                'gender'     => $data['gender'],
                'customerId' => $customerId
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }

    public function updateAvatar(string $customerId, string $avatarPath): bool
    {
        $sql = "
            UPDATE CUSTOMER
            SET Avatar = :avatar
            WHERE CustomerID = :customerId
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'avatar'     => $avatarPath,
            'customerId' => $customerId
        ]);
    }

    /* ===================================================== ADDRESS ===================================================== */

    public function getAddresses(string $customerId): array
    {
        $sql = "
            SELECT 
                AddressID,
                Fullname,
                Phone,
                Alias,
                Address,
                CityState AS City,
                Country,
                PostalCode AS Postal,
                AddressDefault AS IsDefault
            FROM ADDRESS
            WHERE CustomerID = :customerId
            ORDER BY AddressDefault DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tự động tạo AddressID
    public function addAddress(string $customerId, array $data): bool
    {
        // Tạo AddressID tự động (global sequential)
        $stmt = $this->db->query("
            SELECT MAX(CAST(SUBSTRING(AddressID, 4) AS UNSIGNED)) FROM ADDRESS
        ");
        $next = ((int)$stmt->fetchColumn()) + 1;
        $addressId = 'ADD' . str_pad($next, 3, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO ADDRESS (
                AddressID, CustomerID, Fullname, Phone, Alias,
                Address, CityState, Country, PostalCode, AddressDefault
            )
            VALUES (
                :id, :customerId, :fullname, :phone, :alias,
                :address, :city, :country, :postal, :isDefault
            )
        ";

        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([
                'id'         => $addressId,
                'customerId' => $customerId,
                'fullname'   => $data['fullname'],
                'phone'      => $data['phone'],
                'alias'      => $data['alias'] ?? '',
                'address'    => $data['address'],
                'city'       => $data['city'],
                'country'    => $data['country'],
                'postal'     => $data['postal'] ?? '',
                'isDefault'  => $data['is_default'] ?? 'No'
            ]);
        } catch (PDOException $e) {
            error_log("addAddress error: " . $e->getMessage());
            return false;
        }
    }

    public function updateAddress(string $customerId, array $data): bool
    {
        $sql = "
            UPDATE ADDRESS
            SET 
                Fullname = :fullname,
                Phone = :phone,
                Alias = :alias,
                Address = :address,
                CityState = :city,
                Country = :country,
                PostalCode = :postal,
                AddressDefault = :isDefault
            WHERE AddressID = :addressId
              AND CustomerID = :customerId
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'fullname'   => $data['fullname'],
            'phone'      => $data['phone'],
            'alias'      => $data['alias'] ?? '',
            'address'    => $data['address'],
            'city'       => $data['city'],
            'country'    => $data['country'],
            'postal'     => $data['postal'] ?? '',
            'isDefault'  => $data['is_default'] ?? 'No',
            'addressId'  => $data['address_id'],
            'customerId' => $customerId
        ]);
    }

    public function deleteAddress(string $customerId, string $addressId): bool
    {
        $sql = "
            DELETE FROM ADDRESS
            WHERE AddressID = :addressId
              AND CustomerID = :customerId
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'addressId'  => $addressId,
            'customerId' => $customerId
        ]);
    }

    /* ===================================================== BANKING ===================================================== */

    public function getBankingInfo(string $customerId): array
    {
        $sql = "
            SELECT 
                BankingID,
                IDNumber,
                AccountNumber,
                AccountHolderName,
                BankName,
                BankBranchName,
                BankDefault AS IsDefault
            FROM BANKING
            WHERE CustomerID = :customerId
            ORDER BY BankDefault DESC, BankingID ASC
        ";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addBanking(string $customerId, array $data): bool
    {
        $stmt = $this->db->query("
            SELECT MAX(CAST(SUBSTRING(BankingID, 4) AS UNSIGNED)) FROM BANKING
        ");
        $next = ((int)$stmt->fetchColumn()) + 1;
        $bankingId = 'BAN' . str_pad($next, 3, '0', STR_PAD_LEFT);
    
        $check = $this->db->prepare("
            SELECT COUNT(*) FROM BANKING WHERE CustomerID = :customerId
        ");
        $check->execute(['customerId' => $customerId]);
        $isDefault = $check->fetchColumn() == 0 ? 'Yes' : 'No';
    
        $sql = "
            INSERT INTO BANKING (
                BankingID, CustomerID, IDNumber,
                AccountNumber, AccountHolderName,
                BankName, BankBranchName, BankDefault
            ) VALUES (
                :id, :customerId, :idNumber,
                :accountNumber, :holder,
                :bankName, :branch, :isDefault
            )
        ";
    
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'            => $bankingId,
            'customerId'    => $customerId,
            'idNumber'      => $data['id_number'],
            'accountNumber' => $data['account_number'],
            'holder'        => $data['holder_name'],
            'bankName'      => $data['bank_name'],
            'branch'        => $data['branch_name'],
            'isDefault'     => $isDefault
        ]);
    }
    
    public function updateBanking(string $customerId, array $data): bool
    {
        if (($data['is_default'] ?? 'No') === 'Yes') {
            $this->db->prepare("
                UPDATE BANKING SET BankDefault='No'
                WHERE CustomerID = :customerId
            ")->execute(['customerId' => $customerId]);
        }
    
        $sql = "
            UPDATE BANKING SET
                IDNumber = :idNumber,
                AccountNumber = :accountNumber,
                AccountHolderName = :holder,
                BankName = :bankName,
                BankBranchName = :branch,
                BankDefault = :isDefault
            WHERE BankingID = :bankingId
              AND CustomerID = :customerId
        ";
    
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'idNumber'      => $data['id_number'],
            'accountNumber' => $data['account_number'],
            'holder'        => $data['holder_name'],
            'bankName'      => $data['bank_name'],
            'branch'        => $data['branch_name'],
            'isDefault'     => $data['is_default'] ?? 'No',
            'bankingId'     => $data['banking_id'],
            'customerId'    => $customerId
        ]);
    }
    
    public function deleteBanking(string $customerId, string $bankingId): bool
    {
        return $this->db->prepare("
            DELETE FROM BANKING
            WHERE BankingID = :id AND CustomerID = :customerId
        ")->execute([
            'id' => $bankingId,
            'customerId' => $customerId
        ]);
    }
}