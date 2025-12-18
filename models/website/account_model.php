<?php

class AccountModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* =====================================================
       ACCOUNT + CUSTOMER (PROFILE)
       ===================================================== */

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

    public function updateCustomerProfile(string $customerId, array $data): bool
    {
        $sql = "
            UPDATE CUSTOMER
            SET 
                FirstName = :firstName,
                LastName = :lastName,
                CustomerBirth = :birth,
                CustomerGender = :gender
            WHERE CustomerID = :customerId
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'firstName'  => $data['first_name'],
            'lastName'   => $data['last_name'],
            'birth'      => $data['birth'],
            'gender'     => $data['gender'],
            'customerId' => $customerId
        ]);
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

    /* =====================================================
       ADDRESS (SHIPPING)
       ===================================================== */

    public function getAddresses(string $customerId): array
    {
        $sql = "
            SELECT 
                AddressID,
                Fullname,
                Phone,
                Alias,
                Address,
                CityState,
                Country,
                PostalCode,
                AddressDefault
            FROM ADDRESS
            WHERE CustomerID = :customerId
            ORDER BY AddressDefault DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAddress(string $customerId, array $data): bool
    {
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

        return $stmt->execute([
            'id'         => $data['address_id'],
            'customerId' => $customerId,
            'fullname'   => $data['fullname'],
            'phone'      => $data['phone'],
            'alias'      => $data['alias'],
            'address'    => $data['address'],
            'city'       => $data['city'],
            'country'    => $data['country'],
            'postal'     => $data['postal'],
            'isDefault'  => $data['is_default']
        ]);
    }

    public function updateAddress(string $addressId, string $customerId, array $data): bool
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
            'alias'      => $data['alias'],
            'address'    => $data['address'],
            'city'       => $data['city'],
            'country'    => $data['country'],
            'postal'     => $data['postal'],
            'isDefault'  => $data['is_default'],
            'addressId'  => $addressId,
            'customerId' => $customerId
        ]);
    }

    public function deleteAddress(string $addressId, string $customerId): bool
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

    /* =====================================================
       BANKING
       ===================================================== */

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
                BankDefault
            FROM BANKING
            WHERE CustomerID = :customerId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBanking(string $customerId, array $data): bool
    {
        $sql = "
            INSERT INTO BANKING (
                BankingID, CustomerID, IDNumber,
                AccountNumber, AccountHolderName,
                BankName, BankBranchName, BankDefault
            )
            VALUES (
                :id, :customerId, :idNumber,
                :accountNumber, :holder,
                :bankName, :branch, :isDefault
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'            => $data['banking_id'],
            'customerId'    => $customerId,
            'idNumber'      => $data['id_number'],
            'accountNumber' => $data['account_number'],
            'holder'        => $data['holder_name'],
            'bankName'      => $data['bank_name'],
            'branch'        => $data['branch_name'],
            'isDefault'     => $data['is_default']
        ]);
    }
    public function test()
    {
    $sql = "
        SELECT 
            a.AccountID, a.Email,
            c.CustomerID, c.FirstName, c.LastName,
            ad.Address, ad.CityState,
            b.BankName
        FROM ACCOUNT a
        JOIN CUSTOMER c ON a.AccountID = c.AccounntID
        LEFT JOIN ADDRESS ad ON c.CustomerID = ad.CustomerID
        LEFT JOIN BANKING b ON c.CustomerID = b.CustomerID
        WHERE a.AccountID = 'ACC001'
    ";

    return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
