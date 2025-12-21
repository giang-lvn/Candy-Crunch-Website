<?php
require_once __DIR__ . '/../db.php';

class VoucherModel {
    private PDO $conn;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    public function getActiveVouchers() {
        $sql = "
            SELECT
                VoucherID,
                Code,
                VoucherDescription,
                DiscountPercent,
                DiscountAmount,
                CASE 
                    WHEN DiscountPercent > 0 THEN CONCAT(DiscountPercent, '% OFF')
                    WHEN DiscountAmount > 0 THEN CONCAT(FORMAT(DiscountAmount, 0), ' VND OFF')
                END AS DiscountText,
                MinOrder,
                StartDate,
                EndDate,
                VoucherStatus,
                DATEDIFF(EndDate, CURDATE()) AS DaysUntilExpire
            FROM VOUCHER
            WHERE VoucherStatus = 'Active'
              AND StartDate <= CURDATE()
              AND EndDate >= CURDATE()
            ORDER BY EndDate ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVoucherByFilter($filter) {
        $order = "VoucherID DESC";

        if ($filter === 'expiring') {
            $order = "EndDate ASC";
        } elseif ($filter === 'latest') {
            $order = "StartDate DESC";
        }

        $sql = "
            SELECT
                VoucherID,
                Code,
                VoucherDescription,
                DiscountPercent,
                DiscountAmount,
                CASE 
                    WHEN DiscountPercent > 0 THEN CONCAT(DiscountPercent, '% OFF')
                    WHEN DiscountAmount > 0 THEN CONCAT(FORMAT(DiscountAmount, 0), ' VND OFF')
                END AS DiscountText,
                MinOrder,
                StartDate,
                EndDate,
                VoucherStatus,
                DATEDIFF(EndDate, CURDATE()) AS DaysUntilExpire
            FROM VOUCHER
            WHERE VoucherStatus = 'Active'
            ORDER BY $order
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVoucherById($voucherId) {
        $sql = "
            SELECT
                VoucherID,
                Code,
                DiscountPercent,
                DiscountAmount,
                MinOrder,
                CASE 
                    WHEN DiscountPercent > 0 THEN CONCAT(DiscountPercent, '% OFF')
                    WHEN DiscountAmount > 0 THEN CONCAT(FORMAT(DiscountAmount, 0), ' VND OFF')
                END AS DiscountText
            FROM VOUCHER
            WHERE VoucherID = :id
              AND VoucherStatus = 'Active'
              AND StartDate <= CURDATE()
              AND EndDate >= CURDATE()
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $voucherId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
