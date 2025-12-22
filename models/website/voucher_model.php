<?php
require_once __DIR__ . '/../db.php';

class VoucherModel {
    private PDO $conn;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    public function getActiveVouchers() {
        // Equivalent to 'all' filter: Active status and not expired
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
                DATEDIFF(EndDate, CURDATE()) AS DaysUntilExpire,
                CASE
                    WHEN StartDate > CURDATE() THEN 'Upcoming'
                    WHEN DATEDIFF(EndDate, CURDATE()) <= 7 THEN 'Expiring Soon'
                    ELSE 'Active'
                END AS DynamicStatus
            FROM VOUCHER
            WHERE VoucherStatus IN ('Active', 'Upcoming', 'Expiring Soon')
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
            // Expiring: Must be Active or Expiring Soon status AND valid date AND expiring soon
            $whereClause = "VoucherStatus IN ('Active', 'Expiring Soon') AND StartDate <= CURDATE() AND EndDate >= CURDATE() AND DATEDIFF(EndDate, CURDATE()) <= 7";
            $order = "EndDate ASC";
        } elseif ($filter === 'active') {
             // Active: Active or Expiring Soon status AND valid date
            $whereClause = "VoucherStatus IN ('Active', 'Expiring Soon') AND StartDate <= CURDATE() AND EndDate >= CURDATE()";
            $order = "EndDate ASC";
        } elseif ($filter === 'upcoming') {
            // Upcoming: Active OR Upcoming status AND Future Start Date
            $whereClause = "VoucherStatus IN ('Active', 'Upcoming') AND StartDate > CURDATE()";
            $order = "StartDate ASC";
        } else {
             // 'all' or default: Active, Upcoming or Expiring Soon status AND Not Expired (EndDate >= Today)
             $whereClause = "VoucherStatus IN ('Active', 'Upcoming', 'Expiring Soon') AND EndDate >= CURDATE()"; 
             $order = "EndDate ASC"; 
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
                DATEDIFF(EndDate, CURDATE()) AS DaysUntilExpire,
                CASE
                    WHEN StartDate > CURDATE() THEN 'Upcoming'
                    WHEN DATEDIFF(EndDate, CURDATE()) <= 7 THEN 'Expiring Soon'
                    ELSE 'Active'
                END AS DynamicStatus
            FROM VOUCHER
            WHERE $whereClause
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
