<?php
// admin/views/edit_voucher.php
$voucherId = $_GET['id'] ?? '';
if (empty($voucherId)) {
    echo '<div class="alert alert-danger">Không tìm thấy mã voucher</div>';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM VOUCHER WHERE VoucherID = ?");
$stmt->execute([$voucherId]);
$voucher = $stmt->fetch();

if (!$voucher) {
    echo '<div class="alert alert-danger">Voucher không tồn tại</div>';
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_voucher'])) {
    try {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $discountPercent = floatval($_POST['discount_percent'] ?? 0);
        $discountAmount = floatval($_POST['discount_amount'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $minOrder = floatval($_POST['min_order'] ?? 0);
        $voucherStatus = $_POST['voucher_status'] ?? 'Active';
        
        if (empty($code)) throw new Exception('Code voucher không được để trống');
        if ($discountPercent <= 0 && $discountAmount <= 0) throw new Exception('Phải có ít nhất một loại giảm giá');
        if ($discountPercent > 100) throw new Exception('Phần trăm giảm giá không được vượt quá 100%');
        if (empty($startDate) || empty($endDate)) throw new Exception('Ngày bắt đầu và kết thúc không được để trống');
        if (strtotime($endDate) < strtotime($startDate)) throw new Exception('Ngày kết thúc phải sau ngày bắt đầu');
        
        // Kiểm tra Code trùng (trừ voucher hiện tại)
        $checkCode = $pdo->prepare("SELECT VoucherID FROM VOUCHER WHERE Code = ? AND VoucherID != ?");
        $checkCode->execute([$code, $voucherId]);
        if ($checkCode->fetch()) throw new Exception('Code đã tồn tại');
        
        $stmt = $pdo->prepare("UPDATE VOUCHER SET Code = ?, VoucherDescription = ?, DiscountPercent = ?, DiscountAmount = ?, StartDate = ?, EndDate = ?, MinOrder = ?, VoucherStatus = ? WHERE VoucherID = ?");
        $stmt->execute([$code, $description, $discountPercent, $discountAmount, $startDate, $endDate, $minOrder, $voucherStatus, $voucherId]);
        
        $message = 'Cập nhật voucher thành công!';
        $messageType = 'success';
        
        // Reload voucher data
        $stmt = $pdo->prepare("SELECT * FROM VOUCHER WHERE VoucherID = ?");
        $stmt->execute([$voucherId]);
        $voucher = $stmt->fetch();
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Lấy thống kê sử dụng voucher
$usageCount = $pdo->prepare("SELECT COUNT(*) FROM ORDERS WHERE VoucherID = ?");
$usageCount->execute([$voucherId]);
$usageCount = $usageCount->fetchColumn();

$statusBadges = [
    'Active' => ['class' => 'bg-success', 'text' => 'Hoạt động'],
    'Inactive' => ['class' => 'bg-secondary', 'text' => 'Tạm dừng'],
    'Expired' => ['class' => 'bg-danger', 'text' => 'Hết hạn']
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Chỉnh sửa voucher: <?php echo htmlspecialchars($voucher['VoucherID']); ?></h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=vouchers">Voucher</a></li>
            <li class="breadcrumb-item active">Chỉnh sửa</li>
        </ol></nav>
    </div>
    <a href="<?php echo BASE_URL; ?>index.php?action=vouchers" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" id="editVoucherForm">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-dark"><i class="bi bi-pencil-square me-2"></i>Thông tin voucher</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Mã voucher (VoucherID)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($voucher['VoucherID']); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Code voucher <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" required value="<?php echo htmlspecialchars($voucher['Code']); ?>" onkeyup="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả voucher</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($voucher['VoucherDescription']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Giảm giá (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_percent" class="form-control" min="0" max="100" step="0.01" value="<?php echo $voucher['DiscountPercent']; ?>">
                                <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Giảm giá (VNĐ)</label>
                            <div class="input-group">
                                <input type="number" name="discount_amount" class="form-control" min="0" step="1000" value="<?php echo $voucher['DiscountAmount']; ?>">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đơn hàng tối thiểu (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" name="min_order" class="form-control" min="0" step="1000" value="<?php echo $voucher['MinOrder']; ?>">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="<?php echo $voucher['StartDate']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required value="<?php echo $voucher['EndDate']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="voucher_status" class="form-select">
                                <option value="Active" <?php echo $voucher['VoucherStatus'] === 'Active' ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="Inactive" <?php echo $voucher['VoucherStatus'] === 'Inactive' ? 'selected' : ''; ?>>Tạm dừng</option>
                                <option value="Expired" <?php echo $voucher['VoucherStatus'] === 'Expired' ? 'selected' : ''; ?>>Hết hạn</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>index.php?action=vouchers" class="btn btn-outline-secondary me-2"><i class="bi bi-x-circle me-2"></i>Hủy</a>
                <button type="submit" name="save_voucher" class="btn btn-primary btn-lg"><i class="bi bi-save me-2"></i>Lưu thay đổi</button>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Thống kê -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Thống kê sử dụng</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Số đơn hàng đã dùng:</span>
                        <span class="badge bg-primary fs-6"><?php echo $usageCount; ?></span>
                    </div>
                </div>
            </div>
            <!-- Preview -->
            <div class="card border-warning">
                <div class="card-header bg-warning"><h6 class="mb-0 text-dark"><i class="bi bi-eye me-2"></i>Xem trước</h6></div>
                <div class="card-body p-0">
                    <div class="p-3 rounded-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center text-white">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($voucher['Code']); ?></h5>
                                <p class="mb-0 small"><?php echo htmlspecialchars(mb_substr($voucher['VoucherDescription'], 0, 40)); ?></p>
                            </div>
                            <div class="text-end">
                                <?php if ($voucher['DiscountPercent'] > 0): ?>
                                <h4 class="mb-0"><?php echo $voucher['DiscountPercent']; ?>%</h4>
                                <?php elseif ($voucher['DiscountAmount'] > 0): ?>
                                <h4 class="mb-0"><?php echo number_format($voucher['DiscountAmount'], 0, ',', '.'); ?>đ</h4>
                                <?php endif; ?>
                                <small>Đơn từ <?php echo number_format($voucher['MinOrder'], 0, ',', '.'); ?>đ</small>
                            </div>
                        </div>
                        <hr class="my-2 border-white opacity-50">
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span><?php echo date('d/m/Y', strtotime($voucher['StartDate'])); ?> - <?php echo date('d/m/Y', strtotime($voucher['EndDate'])); ?></span>
                            <span class="badge <?php echo $statusBadges[$voucher['VoucherStatus']]['class']; ?>"><?php echo $statusBadges[$voucher['VoucherStatus']]['text']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
</style>
