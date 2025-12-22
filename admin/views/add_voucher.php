<?php
// admin/views/add_voucher.php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_voucher'])) {
    try {
        $voucherId = trim($_POST['voucher_id'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $discountPercent = floatval($_POST['discount_percent'] ?? 0);
        $discountAmount = floatval($_POST['discount_amount'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $minOrder = floatval($_POST['min_order'] ?? 0);
// Tự động xác định trạng thái dựa trên ngày
        $currentDate = date('Y-m-d');
        if (strtotime($startDate) > strtotime($currentDate)) {
            $voucherStatus = 'Upcoming';
        } elseif (strtotime($currentDate) > strtotime($endDate)) {
            $voucherStatus = 'Expired';
        } else {
            $daysToExpiry = (strtotime($endDate) - strtotime($currentDate)) / (60 * 60 * 24);
            if ($daysToExpiry <= 7) {
                $voucherStatus = 'Expiring Soon';
            } else {
                $voucherStatus = 'Active';
            }
        }
        
        if (empty($voucherId)) throw new Exception('Mã voucher không được để trống');
        if (empty($code)) throw new Exception('Code voucher không được để trống');
        if ($discountPercent <= 0 && $discountAmount <= 0) throw new Exception('Phải có ít nhất một loại giảm giá');
        if ($discountPercent > 100) throw new Exception('Phần trăm giảm giá không được vượt quá 100%');
        if (empty($startDate) || empty($endDate)) throw new Exception('Ngày bắt đầu và kết thúc không được để trống');
        if (strtotime($endDate) < strtotime($startDate)) throw new Exception('Ngày kết thúc phải sau ngày bắt đầu');
        
        $checkId = $pdo->prepare("SELECT VoucherID FROM VOUCHER WHERE VoucherID = ?");
        $checkId->execute([$voucherId]);
        if ($checkId->fetch()) throw new Exception('Mã voucher đã tồn tại');
        
        $checkCode = $pdo->prepare("SELECT VoucherID FROM VOUCHER WHERE Code = ?");
        $checkCode->execute([$code]);
        if ($checkCode->fetch()) throw new Exception('Code đã tồn tại');
        
        $stmt = $pdo->prepare("INSERT INTO VOUCHER (VoucherID, Code, VoucherDescription, DiscountPercent, DiscountAmount, StartDate, EndDate, MinOrder, VoucherStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$voucherId, $code, $description, $discountPercent, $discountAmount, $startDate, $endDate, $minOrder, $voucherStatus]);
        
        // Redirect bằng JavaScript vì header đã được gửi
        echo "<script>setTimeout(function(){ window.location.href = '" . BASE_URL . "index.php?action=vouchers&added=1'; }, 2000);</script>";
        $message = 'Thêm voucher thành công! Đang chuyển hướng...';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$lastVoucher = $pdo->query("SELECT VoucherID FROM VOUCHER WHERE VoucherID LIKE 'V%' ORDER BY CAST(SUBSTRING(VoucherID, 2) AS UNSIGNED) DESC LIMIT 1")->fetch();
$suggestedId = $lastVoucher ? 'V' . str_pad(intval(substr($lastVoucher['VoucherID'], 1)) + 1, 4, '0', STR_PAD_LEFT) : 'V0001';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Thêm voucher mới</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=vouchers">Voucher</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
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

<form method="POST" id="addVoucherForm">
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Thông tin voucher</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã voucher (VoucherID) <span class="text-danger">*</span></label>
                        <input type="text" name="voucher_id" class="form-control" required value="<?php echo htmlspecialchars($_POST['voucher_id'] ?? $suggestedId); ?>">
                        <small class="text-muted">Gợi ý: <?php echo $suggestedId; ?></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code voucher <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" class="form-control text-uppercase" required placeholder="VD: SUMMER2024" value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>" onkeyup="this.value = this.value.toUpperCase()">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomCode()"><i class="bi bi-shuffle"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả voucher</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả chi tiết..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Chi tiết giảm giá</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Giảm giá (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_percent" class="form-control" min="0" max="100" step="0.01" value="<?php echo $_POST['discount_percent'] ?? '0'; ?>">
                                <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Giảm giá (VNĐ)</label>
                            <div class="input-group">
                                <input type="number" name="discount_amount" class="form-control" min="0" step="1000" value="<?php echo $_POST['discount_amount'] ?? '0'; ?>">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đơn hàng tối thiểu (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" name="min_order" class="form-control" min="0" step="1000" value="<?php echo $_POST['min_order'] ?? '0'; ?>">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="<?php echo $_POST['start_date'] ?? date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required value="<?php echo $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days')); ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>index.php?action=vouchers" class="btn btn-outline-secondary me-2"><i class="bi bi-x-circle me-2"></i>Hủy</a>
                <button type="submit" name="save_voucher" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-2"></i>Tạo voucher</button>
            </div>
        </div>
    </div>
</form>

<script>
function generateRandomCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const prefixes = ['CANDY', 'SWEET', 'SAVE', 'DEAL', 'SALE'];
    let code = prefixes[Math.floor(Math.random() * prefixes.length)];
    for (let i = 0; i < 5; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
    document.querySelector('input[name="code"]').value = code;
}
</script>
