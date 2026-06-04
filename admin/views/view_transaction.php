<?php
// admin/views/view_transaction.php

$txId = trim($_GET['id'] ?? '');

if ($txId === '') {
    echo '<div class="alert alert-danger">Thiếu mã giao dịch.</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=transactions" class="btn btn-secondary">Quay lại</a>';
    return;
}

$stmt = $pdo->prepare("
    SELECT
        t.*,
        o.OrderDate,
        o.OrderStatus,
        o.ShippingMethod,
        o.ShippingFee,
        o.CustomerID,
        CONCAT(c.FirstName, ' ', c.LastName) AS CustomerName,
        a.Email AS CustomerEmail,
        (SELECT addr.Phone FROM ADDRESS addr WHERE addr.CustomerID = c.CustomerID ORDER BY addr.AddressDefault DESC LIMIT 1) AS CustomerPhone
    FROM transaction t
    LEFT JOIN ORDERS o ON t.OrderID = o.OrderID
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID
    WHERE t.TransactionID = ?
");
$stmt->execute([$txId]);
$tx = $stmt->fetch();

if (!$tx) {
    echo '<div class="alert alert-danger">Không tìm thấy giao dịch.</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=transactions" class="btn btn-secondary">Quay lại</a>';
    return;
}

$paymentMethods = getPaymentMethods();
$methodLabel = $paymentMethods[$tx['PaymentMethod']] ?? $tx['PaymentMethod'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $newStatus = $_POST['payment_status'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $allowed = array_keys(getPaymentStatuses());
    if (in_array($newStatus, $allowed, true)) {
        $upd = $pdo->prepare("UPDATE transaction SET PaymentStatus = ?, Note = ? WHERE TransactionID = ?");
        $upd->execute([$newStatus, $note !== '' ? $note : null, $txId]);
        echo "<script>showToast('Đã cập nhật!', 'success'); setTimeout(function(){ location.reload(); }, 800);</script>";
        $stmt->execute([$txId]);
        $tx = $stmt->fetch();
    }
}
?>

<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>index.php?action=transactions" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Danh sách giao dịch
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i><?php echo htmlspecialchars($tx['TransactionID']); ?></h5>
                <span class="badge bg-<?php echo getPaymentStatusColor($tx['PaymentStatus']); ?> fs-6">
                    <?php echo getPaymentStatusText($tx['PaymentStatus']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Loại giao dịch</label>
                        <div><?php echo getTransactionTypeText($tx['TransactionType']); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Phương thức thanh toán</label>
                        <div><?php echo htmlspecialchars($methodLabel ?: '—'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Số tiền</label>
                        <div class="fs-4 fw-bold text-success"><?php echo formatCurrency($tx['Amount'] ?? 0); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Thời gian tạo</label>
                        <div><?php echo formatDate($tx['CreatedAt']); ?></div>
                    </div>
                    <?php if (!empty($tx['ProviderTransactionID'])): ?>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">Mã giao dịch nhà cung cấp (PayPal, VNPay…)</label>
                        <div><code><?php echo htmlspecialchars($tx['ProviderTransactionID']); ?></code></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($tx['Note'])): ?>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">Ghi chú</label>
                        <div class="p-2 bg-light rounded"><?php echo nl2br(htmlspecialchars($tx['Note'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($tx['OrderID']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Đơn hàng liên kết</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td width="140" class="text-muted">Mã đơn</td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo urlencode($tx['OrderID']); ?>">
                                <strong>#<?php echo htmlspecialchars($tx['OrderID']); ?></strong>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ngày đặt</td>
                        <td><?php echo formatDate($tx['OrderDate']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Trạng thái đơn</td>
                        <td>
                            <span class="badge bg-<?php echo getStatusColor($tx['OrderStatus']); ?>">
                                <?php echo getStatusText($tx['OrderStatus']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Vận chuyển</td>
                        <td><?php echo htmlspecialchars($tx['ShippingMethod'] ?? '—'); ?> — <?php echo formatCurrency($tx['ShippingFee'] ?? 0); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <?php if ($tx['CustomerName']): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Khách hàng</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo htmlspecialchars(trim($tx['CustomerName'])); ?></strong></p>
                <?php if ($tx['CustomerEmail']): ?>
                <p class="mb-1 small"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($tx['CustomerEmail']); ?></p>
                <?php endif; ?>
                <?php if ($tx['CustomerPhone']): ?>
                <p class="mb-2 small"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($tx['CustomerPhone']); ?></p>
                <?php endif; ?>
                <?php if ($tx['CustomerID']): ?>
                <a href="<?php echo BASE_URL; ?>index.php?action=view_customer&id=<?php echo urlencode($tx['CustomerID']); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Xem hồ sơ
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Cập nhật trạng thái</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_payment_status" value="1">
                    <div class="mb-3">
                        <label class="form-label small">Trạng thái thanh toán</label>
                        <select name="payment_status" class="form-select" required>
                            <?php foreach (getPaymentStatuses() as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $tx['PaymentStatus'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3"><?php echo htmlspecialchars($tx['Note'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
