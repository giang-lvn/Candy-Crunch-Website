<?php
// admin/views/transactions.php — Quản lý giao dịch thanh toán

$statusFilter = $_GET['status'] ?? '';
$methodFilter = $_GET['method'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $txId = $_POST['transaction_id'] ?? '';
    $newStatus = $_POST['payment_status'] ?? '';
    $note = trim($_POST['note'] ?? '');

    $allowed = array_keys(getPaymentStatuses());
    if (!in_array($newStatus, $allowed, true)) {
        $message = 'Trạng thái thanh toán không hợp lệ.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE transaction
                SET PaymentStatus = ?, Note = ?
                WHERE TransactionID = ?
            ");
            $stmt->execute([$newStatus, $note !== '' ? $note : null, $txId]);
            echo "<script>showToast('Cập nhật trạng thái giao dịch thành công!', 'success');
                setTimeout(function(){ window.location.href = '" . BASE_URL . "index.php?action=transactions&updated=1'; }, 1200);</script>";
        } catch (Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_transaction'])) {
    $txId = $_POST['transaction_id'] ?? '';
    try {
        $del = $pdo->prepare("DELETE FROM transaction WHERE TransactionID = ?");
        $del->execute([$txId]);
        echo "<script>showToast('Đã xóa giao dịch!', 'success');
            setTimeout(function(){ window.location.href = '" . BASE_URL . "index.php?action=transactions&deleted=1'; }, 1200);</script>";
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

if (isset($_GET['updated'])) {
    $message = 'Cập nhật giao dịch thành công!';
    $messageType = 'success';
}
if (isset($_GET['deleted'])) {
    $message = 'Đã xóa giao dịch!';
    $messageType = 'success';
}

$sql = "
    SELECT
        t.TransactionID,
        t.OrderID,
        t.TransactionType,
        t.PaymentMethod,
        t.PaymentStatus,
        t.Amount,
        t.ProviderTransactionID,
        t.Note,
        t.CreatedAt,
        o.OrderStatus,
        o.OrderDate,
        CONCAT(c.FirstName, ' ', c.LastName) AS CustomerName,
        a.Email AS CustomerEmail
    FROM transaction t
    LEFT JOIN ORDERS o ON t.OrderID = o.OrderID
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID
    WHERE 1=1
";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND t.PaymentStatus = ?";
    $params[] = $statusFilter;
}
if ($methodFilter !== '') {
    $sql .= " AND t.PaymentMethod = ?";
    $params[] = $methodFilter;
}
if ($typeFilter !== '') {
    $sql .= " AND t.TransactionType = ?";
    $params[] = $typeFilter;
}
if ($startDate !== '') {
    $sql .= " AND DATE(t.CreatedAt) >= ?";
    $params[] = $startDate;
}
if ($endDate !== '') {
    $sql .= " AND DATE(t.CreatedAt) <= ?";
    $params[] = $endDate;
}
if ($search !== '') {
    $sql .= " AND (
        t.TransactionID LIKE ? OR t.OrderID LIKE ? OR t.ProviderTransactionID LIKE ?
        OR a.Email LIKE ? OR c.FirstName LIKE ? OR c.LastName LIKE ?
    )";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
}

$sql .= " ORDER BY t.CreatedAt DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$statsSql = "
    SELECT
        COUNT(*) AS total_count,
        SUM(CASE WHEN PaymentStatus = 'Completed' THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN PaymentStatus = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        COALESCE(SUM(CASE WHEN PaymentStatus = 'Completed' THEN Amount ELSE 0 END), 0) AS completed_amount
    FROM transaction
";
$txStats = $pdo->query($statsSql)->fetch();
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Quản lý giao dịch</h4>
    <button type="button" class="btn btn-outline-secondary" onclick="exportTransactions()">
        <i class="bi bi-download me-2"></i>Xuất CSV
    </button>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-list-check text-white"></i></div>
            <div class="stat-number"><?php echo (int) ($txStats['total_count'] ?? 0); ?></div>
            <div class="stat-label">Tổng giao dịch</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle text-white"></i></div>
            <div class="stat-number"><?php echo (int) ($txStats['completed_count'] ?? 0); ?></div>
            <div class="stat-label">Đã thanh toán</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-hourglass-split text-white"></i></div>
            <div class="stat-number"><?php echo (int) ($txStats['pending_count'] ?? 0); ?></div>
            <div class="stat-label">Chờ thanh toán</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-cash-stack text-white"></i></div>
            <div class="stat-number" style="font-size:22px;"><?php echo formatCurrency($txStats['completed_amount'] ?? 0); ?></div>
            <div class="stat-label">Doanh thu (đã TT)</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="transactions">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Trạng thái TT</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (getPaymentStatuses() as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $statusFilter === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Phương thức</label>
                <select name="method" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (getPaymentMethods() as $key => $label): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $methodFilter === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Loại GD</label>
                <select name="type" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (getTransactionTypes() as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $typeFilter === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Từ ngày</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Đến ngày</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Lọc</button>
            </div>
        </form>
        <form method="GET" class="mt-3">
            <input type="hidden" name="action" value="transactions">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Mã GD, mã đơn, PayPal ID, email khách..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                <?php if ($search || $statusFilter || $methodFilter || $typeFilter || $startDate || $endDate): ?>
                <a href="<?php echo BASE_URL; ?>index.php?action=transactions" class="btn btn-outline-secondary">Xóa lọc</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="transactionsTable">
                <thead>
                    <tr>
                        <th>Mã GD</th>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Loại</th>
                        <th>Phương thức</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            Chưa có giao dịch nào
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($transactions as $tx):
                        $paymentMethods = getPaymentMethods();
                        $methodLabel = $paymentMethods[$tx['PaymentMethod']] ?? $tx['PaymentMethod'];
                    ?>
                    <tr>
                        <td><code class="text-primary"><?php echo htmlspecialchars($tx['TransactionID']); ?></code></td>
                        <td>
                            <?php if ($tx['OrderID']): ?>
                            <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo urlencode($tx['OrderID']); ?>">
                                #<?php echo htmlspecialchars($tx['OrderID']); ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tx['CustomerName']): ?>
                            <div><?php echo htmlspecialchars(trim($tx['CustomerName'])); ?></div>
                            <?php if ($tx['CustomerEmail']): ?>
                            <small class="text-muted"><?php echo htmlspecialchars($tx['CustomerEmail']); ?></small>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo getTransactionTypeText($tx['TransactionType']); ?></span></td>
                        <td><?php echo htmlspecialchars($methodLabel ?: '—'); ?></td>
                        <td><strong><?php echo formatCurrency($tx['Amount'] ?? 0); ?></strong></td>
                        <td>
                            <span class="badge bg-<?php echo getPaymentStatusColor($tx['PaymentStatus']); ?>">
                                <?php echo getPaymentStatusText($tx['PaymentStatus']); ?>
                            </span>
                        </td>
                        <td><small><?php echo formatDate($tx['CreatedAt']); ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info" title="Xem nhanh"
                                    onclick="viewTransactionDetail('<?php echo htmlspecialchars($tx['TransactionID']); ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="<?php echo BASE_URL; ?>index.php?action=view_transaction&id=<?php echo urlencode($tx['TransactionID']); ?>"
                                    class="btn btn-outline-primary" title="Chi tiết">
                                    <i class="bi bi-file-text"></i>
                                </a>
                                <button type="button" class="btn btn-outline-warning btn-update-tx" title="Cập nhật TT"
                                    data-id="<?php echo htmlspecialchars($tx['TransactionID']); ?>"
                                    data-status="<?php echo htmlspecialchars($tx['PaymentStatus']); ?>"
                                    data-note="<?php echo htmlspecialchars($tx['Note'] ?? '', ENT_QUOTES); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" title="Xóa"
                                    onclick="showDeleteTxModal('<?php echo htmlspecialchars($tx['TransactionID']); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chi tiết -->
<div class="modal fade" id="txDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Chi tiết giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="txDetailContent">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cập nhật trạng thái -->
<div class="modal fade" id="updateTxModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_payment_status" value="1">
                <input type="hidden" name="transaction_id" id="updateTxId">
                <div class="mb-3">
                    <label class="form-label">Trạng thái thanh toán</label>
                    <select name="payment_status" id="updateTxStatus" class="form-select" required>
                        <?php foreach (getPaymentStatuses() as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" id="updateTxNote" class="form-control" rows="3" placeholder="Ghi chú nội bộ..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal xóa -->
<div class="modal fade" id="deleteTxModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xóa giao dịch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="delete_transaction" value="1">
                <input type="hidden" name="transaction_id" id="deleteTxId">
                <p>Bạn có chắc muốn xóa giao dịch <strong id="deleteTxIdText"></strong>?</p>
                <p class="text-danger small mb-0">Thao tác không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger">Xóa</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    if ($('#transactionsTable tbody tr').length > 0 && !$('#transactionsTable tbody tr td').first().attr('colspan')) {
        $('#transactionsTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
            order: [[7, 'desc']],
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: [8] }]
        });
    }
    $(document).on('click', '.btn-update-tx', function () {
        showUpdateTxModal({
            id: $(this).data('id'),
            status: $(this).data('status'),
            note: $(this).data('note') || ''
        });
    });
});

function viewTransactionDetail(txId) {
    $('#txDetailContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    new bootstrap.Modal(document.getElementById('txDetailModal')).show();
    $.get('ajax/get_transaction_detail.php', { transaction_id: txId }, function (html) {
        $('#txDetailContent').html(html);
    }).fail(function () {
        $('#txDetailContent').html('<div class="alert alert-danger">Không tải được chi tiết giao dịch.</div>');
    });
}

function showUpdateTxModal(data) {
    $('#updateTxId').val(data.id);
    $('#updateTxStatus').val(data.status);
    $('#updateTxNote').val(data.note || '');
    new bootstrap.Modal(document.getElementById('updateTxModal')).show();
}

function showDeleteTxModal(txId) {
    $('#deleteTxId').val(txId);
    $('#deleteTxIdText').text(txId);
    new bootstrap.Modal(document.getElementById('deleteTxModal')).show();
}

function exportTransactions() {
    const table = document.getElementById('transactionsTable');
    let csv = "data:text/csv;charset=utf-8,\uFEFF";
    csv += "Mã GD,Mã đơn,Khách,Loại,Phương thức,Số tiền,Trạng thái,Thời gian\n";
    table.querySelectorAll('tbody tr').forEach(function (row) {
        if (row.querySelector('td[colspan]')) return;
        const cells = row.querySelectorAll('td');
        if (cells.length < 8) return;
        const rowData = [];
        for (let i = 0; i < 8; i++) {
            let t = cells[i].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/,/g, ';').trim();
            rowData.push('"' + t + '"');
        }
        csv += rowData.join(',') + '\n';
    });
    const link = document.createElement('a');
    link.href = encodeURI(csv);
    link.download = 'giao_dich_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();
}
</script>

<style>
.stat-card { text-align: center; padding: 20px; border-radius: 10px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; }
.stat-number { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 5px; }
.stat-label { color: #6c757d; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
</style>
