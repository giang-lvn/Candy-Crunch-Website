<?php
// admin/views/vouchers.php
// Quản lý vouchers/khuyến mãi

// Xử lý lọc
$statusFilter = $_GET['status'] ?? '';

// Xử lý thông báo từ URL
$message = '';
$messageType = '';
if (isset($_GET['deleted'])) {
    $message = 'Đã xóa voucher thành công!';
    $messageType = 'success';
} elseif (isset($_GET['added'])) {
    $message = 'Đã thêm voucher mới thành công!';
    $messageType = 'success';
} elseif (isset($_GET['updated'])) {
    $message = 'Đã cập nhật voucher thành công!';
    $messageType = 'success';
}

// Xử lý xóa voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_voucher'])) {
    $voucherId = $_POST['voucher_id'] ?? '';
    
    try {
        // Lấy thông tin voucher
        $getVoucher = $pdo->prepare("SELECT VoucherStatus, EndDate FROM VOUCHER WHERE VoucherID = ?");
        $getVoucher->execute([$voucherId]);
        $voucherInfo = $getVoucher->fetch();
        
        if (!$voucherInfo) {
            throw new Exception("Voucher không tồn tại.");
        }
        
        // Kiểm tra xem voucher có đang được sử dụng trong đơn hàng không
        $checkOrders = $pdo->prepare("SELECT COUNT(*) FROM ORDERS WHERE VoucherID = ?");
        $checkOrders->execute([$voucherId]);
        $orderCount = $checkOrders->fetchColumn();
        
        // Kiểm tra nếu voucher đã hết hạn (theo trạng thái hoặc ngày kết thúc)
        $isExpired = ($voucherInfo['VoucherStatus'] === 'Expired') || (strtotime($voucherInfo['EndDate']) < strtotime(date('Y-m-d')));
        
        // Nếu có đơn hàng sử dụng và voucher chưa hết hạn -> không cho xóa
        if ($orderCount > 0 && !$isExpired) {
            throw new Exception("Không thể xóa voucher này vì đang có $orderCount đơn hàng sử dụng và voucher chưa hết hạn.");
        }
        
        // Xóa voucher
        $deleteVoucher = $pdo->prepare("DELETE FROM VOUCHER WHERE VoucherID = ?");
        $deleteVoucher->execute([$voucherId]);
        
        echo "<script>window.location.href = '" . BASE_URL . "index.php?action=vouchers&deleted=1';</script>";
        exit;
        
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Xử lý cập nhật trạng thái nhanh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $voucherId = $_POST['voucher_id'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    
    try {
        $updateStatus = $pdo->prepare("UPDATE VOUCHER SET VoucherStatus = ? WHERE VoucherID = ?");
        $updateStatus->execute([$newStatus, $voucherId]);
        
        echo "<script>window.location.href = '" . BASE_URL . "index.php?action=vouchers&updated=1';</script>";
        exit;
    } catch (Exception $e) {
        $message = 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Lấy danh sách voucher
$sql = "
    SELECT 
        v.*,
        (SELECT COUNT(*) FROM ORDERS o WHERE o.VoucherID = v.VoucherID) AS UsageCount
    FROM VOUCHER v
    WHERE 1=1
";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND v.VoucherStatus = :status";
    $params['status'] = $statusFilter;
}

$sql .= " ORDER BY v.VoucherID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vouchers = $stmt->fetchAll();

// Đếm theo trạng thái
$countActive = $pdo->query("SELECT COUNT(*) FROM VOUCHER WHERE VoucherStatus = 'Active'")->fetchColumn();
$countInactive = $pdo->query("SELECT COUNT(*) FROM VOUCHER WHERE VoucherStatus = 'Inactive'")->fetchColumn();
$countExpired = $pdo->query("SELECT COUNT(*) FROM VOUCHER WHERE VoucherStatus = 'Expired'")->fetchColumn();

// Status badges
$statusBadges = [
    'Active' => ['class' => 'bg-success', 'icon' => 'bi-check-circle', 'text' => 'Hoạt động'],
    'Inactive' => ['class' => 'bg-secondary', 'icon' => 'bi-pause-circle', 'text' => 'Tạm dừng'],
    'Expired' => ['class' => 'bg-danger', 'icon' => 'bi-x-circle', 'text' => 'Hết hạn']
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Quản lý Voucher / Khuyến mãi</h4>
    <a href="<?php echo BASE_URL; ?>index.php?action=add_voucher" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm voucher
    </a>
</div>

<!-- Thông báo -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Thống kê nhanh -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="bi bi-ticket-perforated text-primary fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Tổng voucher</h6>
                    <h3 class="mb-0"><?php echo count($vouchers); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Đang hoạt động</h6>
                    <h3 class="mb-0"><?php echo $countActive; ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                    <i class="bi bi-pause-circle text-secondary fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Tạm dừng</h6>
                    <h3 class="mb-0"><?php echo $countInactive; ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                    <i class="bi bi-x-circle text-danger fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Hết hạn</h6>
                    <h3 class="mb-0"><?php echo $countExpired; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Bộ lọc voucher</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php">
            <input type="hidden" name="action" value="vouchers">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>
                            Hoạt động (<?php echo $countActive; ?>)
                        </option>
                        <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>
                            Tạm dừng (<?php echo $countInactive; ?>)
                        </option>
                        <option value="Expired" <?php echo $statusFilter === 'Expired' ? 'selected' : ''; ?>>
                            Hết hạn (<?php echo $countExpired; ?>)
                        </option>
                    </select>
                </div>
                <?php if (!empty($statusFilter)): ?>
                <div class="col-md-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=vouchers" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bảng voucher -->
<div class="card">
    <div class="card-body">
        <?php if (empty($vouchers)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-ticket-perforated display-4 d-block mb-3"></i>
            <p class="mb-0">Không tìm thấy voucher nào.</p>
            <a href="<?php echo BASE_URL; ?>index.php?action=add_voucher" class="btn btn-outline-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Thêm voucher đầu tiên
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="vouchersTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Mã voucher</th>
                        <th>Code</th>
                        <th>Mô tả</th>
                        <th class="text-center">Giảm giá</th>
                        <th class="text-end">Đơn tối thiểu</th>
                        <th class="text-center">Thời hạn</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Sử dụng</th>
                        <th style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr>
                        <!-- Mã voucher -->
                        <td>
                            <code class="text-primary fw-bold"><?php echo htmlspecialchars($voucher['VoucherID']); ?></code>
                        </td>
                        
                        <!-- Code -->
                        <td>
                            <span class="badge bg-dark fs-6"><?php echo htmlspecialchars($voucher['Code']); ?></span>
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1" 
                                    onclick="copyCode('<?php echo htmlspecialchars($voucher['Code']); ?>')"
                                    title="Copy code">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </td>
                        
                        <!-- Mô tả -->
                        <td>
                            <span title="<?php echo htmlspecialchars($voucher['VoucherDescription']); ?>">
                                <?php echo htmlspecialchars(mb_substr($voucher['VoucherDescription'], 0, 40)); ?>
                                <?php echo mb_strlen($voucher['VoucherDescription']) > 40 ? '...' : ''; ?>
                            </span>
                        </td>
                        
                        <!-- Giảm giá -->
                        <td class="text-center">
                            <?php if ($voucher['DiscountPercent'] > 0): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-percent me-1"></i><?php echo number_format($voucher['DiscountPercent'], 0); ?>%
                                </span>
                            <?php endif; ?>
                            <?php if ($voucher['DiscountAmount'] > 0): ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-cash me-1"></i><?php echo number_format($voucher['DiscountAmount'], 0, ',', '.'); ?>đ
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Đơn tối thiểu -->
                        <td class="text-end">
                            <?php if ($voucher['MinOrder'] > 0): ?>
                                <span class="text-muted"><?php echo number_format($voucher['MinOrder'], 0, ',', '.'); ?>đ</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Thời hạn -->
                        <td class="text-center">
                            <small>
                                <?php 
                                $startDate = date('d/m/Y', strtotime($voucher['StartDate']));
                                $endDate = date('d/m/Y', strtotime($voucher['EndDate']));
                                $today = date('Y-m-d');
                                $isExpired = $voucher['EndDate'] < $today;
                                $isUpcoming = $voucher['StartDate'] > $today;
                                ?>
                                <span class="<?php echo $isExpired ? 'text-danger' : ($isUpcoming ? 'text-warning' : 'text-success'); ?>">
                                    <?php echo $startDate; ?><br>
                                    <i class="bi bi-arrow-down"></i><br>
                                    <?php echo $endDate; ?>
                                </span>
                            </small>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td class="text-center">
                            <?php 
                            $status = $voucher['VoucherStatus'];
                            $badge = $statusBadges[$status] ?? ['class' => 'bg-secondary', 'icon' => 'bi-question-circle', 'text' => $status];
                            ?>
                            <span class="badge <?php echo $badge['class']; ?>">
                                <i class="<?php echo $badge['icon']; ?> me-1"></i>
                                <?php echo $badge['text']; ?>
                            </span>
                        </td>
                        
                        <!-- Số lần sử dụng -->
                        <td class="text-center">
                            <span class="badge bg-light text-dark" title="Đã dùng trong <?php echo $voucher['UsageCount']; ?> đơn hàng">
                                <i class="bi bi-cart-check me-1"></i><?php echo $voucher['UsageCount']; ?> đơn
                            </span>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>index.php?action=edit_voucher&id=<?php echo htmlspecialchars($voucher['VoucherID']); ?>" 
                                   class="btn btn-outline-primary" title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                        data-bs-toggle="dropdown" title="Thay đổi trạng thái">
                                    <i class="bi bi-toggle-on"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Đổi trạng thái</h6></li>
                                    <?php foreach (['Active', 'Inactive', 'Expired'] as $s): ?>
                                    <li>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="voucher_id" value="<?php echo $voucher['VoucherID']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $s; ?>">
                                            <button type="submit" name="update_status" 
                                                    class="dropdown-item <?php echo $status === $s ? 'active' : ''; ?>">
                                                <i class="<?php echo $statusBadges[$s]['icon']; ?> me-2"></i>
                                                <?php echo $statusBadges[$s]['text']; ?>
                                            </button>
                                        </form>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn btn-outline-danger" title="Xóa"
                                        onclick="openDeleteVoucherModal('<?php echo htmlspecialchars($voucher['VoucherID']); ?>', '<?php echo htmlspecialchars(addslashes($voucher['Code'])); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($vouchers)): ?>
<!-- Modal Xác nhận xóa voucher -->
<div class="modal fade" id="deleteVoucherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="voucher_id" id="delete_voucher_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa voucher <strong id="delete_voucher_code"></strong>?</p>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Hành động này sẽ xóa voucher khỏi hệ thống. Không thể hoàn tác.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="delete_voucher" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Xóa voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Copy code to clipboard
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Đã copy mã voucher: ' + code, 'success');
    }).catch(function() {
        showToast('Không thể copy mã voucher', 'error');
    });
}

// Open delete modal
function openDeleteVoucherModal(id, code) {
    document.getElementById('delete_voucher_id').value = id;
    document.getElementById('delete_voucher_code').textContent = code;
    
    var modal = new bootstrap.Modal(document.getElementById('deleteVoucherModal'));
    modal.show();
}

$(document).ready(function() {
    $('#vouchersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });
});
</script>
<?php endif; ?>

<style>
.table td {
    vertical-align: middle;
}
.badge {
    font-weight: 500;
}
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
