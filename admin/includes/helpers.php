<?php
// admin/includes/helpers.php
// Helper functions cho Admin Panel

/**
 * Format tiền tệ VNĐ
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

/**
 * Format ngày tháng
 */
function formatDate($date) {
    if (empty($date)) return '-';
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Lấy danh sách trạng thái đơn hàng
 */
function getOrderStatuses() {
    return [
        'Waiting Payment' => 'Chờ thanh toán',
        'Pending Confirmation' => 'Chờ xác nhận',
        'Pending' => 'Đang xử lý',
        'On Shipping' => 'Đang giao hàng',
        'Complete' => 'Hoàn thành',
        'Cancelled' => 'Đã hủy',
        'Returned' => 'Đã trả hàng'
    ];
}

/**
 * Lấy màu badge theo trạng thái đơn hàng
 */
function getStatusColor($status) {
    return match($status) {
        'Complete' => 'success',
        'On Shipping' => 'info',
        'Pending Confirmation' => 'warning',
        'Pending' => 'primary',
        'Waiting Payment' => 'secondary',
        'Cancelled', 'Canceled' => 'danger',
        'Returned' => 'dark',
        default => 'secondary'
    };
}

/**
 * Lấy text hiển thị theo trạng thái đơn hàng
 */
function getStatusText($status) {
    $statuses = getOrderStatuses();
    return $statuses[$status] ?? $status;
}

/**
 * Lấy danh sách phương thức thanh toán
 */
function getPaymentMethods() {
    return [
        'COD' => 'Thanh toán khi nhận hàng',
        'Banking' => 'Chuyển khoản ngân hàng',
        'Momo' => 'Ví MoMo',
        'ZaloPay' => 'Ví ZaloPay',
        'VNPay' => 'VNPay',
        'Card' => 'Thẻ tín dụng/ghi nợ'
    ];
}

/**
 * Lấy danh sách phương thức vận chuyển
 */
function getShippingMethods() {
    return [
        'Standard' => 'Giao hàng tiêu chuẩn',
        'Express' => 'Giao hàng nhanh',
        'Same Day' => 'Giao trong ngày'
    ];
}

/**
 * Tạo OrderID mới theo format ORD + số thứ tự
 */
function generateOrderId($pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(OrderID, 4) AS UNSIGNED)) as maxNum FROM ORDERS WHERE OrderID LIKE 'ORD%'");
    $result = $stmt->fetch();
    $nextNum = ($result['maxNum'] ?? 0) + 1;
    return 'ORD' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
}
?>
