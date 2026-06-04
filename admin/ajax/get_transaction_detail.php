<?php
// admin/ajax/get_transaction_detail.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isAdminLoggedIn()) {
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit;
}

$txId = trim($_GET['transaction_id'] ?? '');

if ($txId === '') {
    echo '<div class="alert alert-danger">Thiếu mã giao dịch</div>';
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        t.*,
        o.OrderDate,
        o.OrderStatus,
        CONCAT(c.FirstName, ' ', c.LastName) AS CustomerName,
        a.Email AS CustomerEmail
    FROM transaction t
    LEFT JOIN ORDERS o ON t.OrderID = o.OrderID
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID
    WHERE t.TransactionID = ?
");
$stmt->execute([$txId]);
$tx = $stmt->fetch();

if (!$tx) {
    echo '<div class="alert alert-danger">Không tìm thấy giao dịch</div>';
    exit;
}

$paymentMethods = getPaymentMethods();
$methodLabel = $paymentMethods[$tx['PaymentMethod']] ?? $tx['PaymentMethod'];
?>

<div class="row">
    <div class="col-md-6">
        <table class="table table-sm table-borderless">
            <tr><td class="text-muted">Mã giao dịch</td><td><code><?php echo htmlspecialchars($tx['TransactionID']); ?></code></td></tr>
            <tr><td class="text-muted">Loại</td><td><?php echo getTransactionTypeText($tx['TransactionType']); ?></td></tr>
            <tr><td class="text-muted">Phương thức</td><td><?php echo htmlspecialchars($methodLabel ?: '—'); ?></td></tr>
            <tr><td class="text-muted">Số tiền</td><td><strong class="text-success"><?php echo formatCurrency($tx['Amount'] ?? 0); ?></strong></td></tr>
            <tr>
                <td class="text-muted">Trạng thái</td>
                <td>
                    <span class="badge bg-<?php echo getPaymentStatusColor($tx['PaymentStatus']); ?>">
                        <?php echo getPaymentStatusText($tx['PaymentStatus']); ?>
                    </span>
                </td>
            </tr>
            <tr><td class="text-muted">Thời gian</td><td><?php echo formatDate($tx['CreatedAt']); ?></td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-sm table-borderless">
            <?php if ($tx['OrderID']): ?>
            <tr>
                <td class="text-muted">Đơn hàng</td>
                <td>
                    <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo urlencode($tx['OrderID']); ?>">
                        #<?php echo htmlspecialchars($tx['OrderID']); ?>
                    </a>
                    <span class="badge bg-<?php echo getStatusColor($tx['OrderStatus']); ?> ms-1"><?php echo getStatusText($tx['OrderStatus']); ?></span>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($tx['CustomerName']): ?>
            <tr><td class="text-muted">Khách hàng</td><td><?php echo htmlspecialchars(trim($tx['CustomerName'])); ?></td></tr>
            <?php endif; ?>
            <?php if ($tx['CustomerEmail']): ?>
            <tr><td class="text-muted">Email</td><td><?php echo htmlspecialchars($tx['CustomerEmail']); ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($tx['ProviderTransactionID'])): ?>
            <tr><td class="text-muted">Mã NCC</td><td><small><code><?php echo htmlspecialchars($tx['ProviderTransactionID']); ?></code></small></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if (!empty($tx['Note'])): ?>
<div class="alert alert-light border mt-2 mb-0">
    <strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($tx['Note'])); ?>
</div>
<?php endif; ?>

<div class="mt-3 text-end">
    <a href="<?php echo BASE_URL; ?>index.php?action=view_transaction&id=<?php echo urlencode($tx['TransactionID']); ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Xem đầy đủ
    </a>
</div>
