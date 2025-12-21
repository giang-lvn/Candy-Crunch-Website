<?php
// admin/views/customers.php

// Query lấy danh sách khách hàng với thông tin đầy đủ
$sql = "
    SELECT 
        c.CustomerID,
        c.FirstName,
        c.LastName,
        c.Avatar,
        a.AccountID,
        a.Email,
        a.AccountStatus,
        
        -- Tổng số đơn hàng
        COALESCE((
            SELECT COUNT(*) 
            FROM ORDERS o 
            WHERE o.CustomerID = c.CustomerID
        ), 0) AS TotalOrders,
        
        -- Tổng chi tiêu (tổng giá trị các đơn hàng đã hoàn thành)
        COALESCE((
            SELECT SUM(
                (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
                 FROM ORDER_DETAIL od 
                 JOIN SKU s ON od.SKUID = s.SKUID 
                 WHERE od.OrderID = o.OrderID)
            )
            FROM ORDERS o 
            WHERE o.CustomerID = c.CustomerID 
            AND o.OrderStatus = 'Complete'
        ), 0) AS TotalSpent
        
    FROM CUSTOMER c 
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID 
    ORDER BY c.CustomerID DESC
";

$stmt = $pdo->query($sql);
$customers = $stmt->fetchAll();

// Xử lý cập nhật trạng thái tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $accountId = $_POST['account_id'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    
    $validStatuses = ['Active', 'Inactive', 'Banned'];
    
    if (!empty($accountId) && in_array($newStatus, $validStatuses)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE ACCOUNT SET AccountStatus = ? WHERE AccountID = ?");
            $updateStmt->execute([$newStatus, $accountId]);
            
            echo "<script>window.location.href = '" . BASE_URL . "index.php?action=customers&updated=1';</script>";
            exit;
        } catch (Exception $e) {
            $updateError = "Lỗi khi cập nhật trạng thái: " . $e->getMessage();
        }
    }
}

// Thông báo từ query params
$message = '';
$messageType = '';
if (isset($_GET['updated'])) {
    $message = 'Đã cập nhật trạng thái tài khoản thành công!';
    $messageType = 'success';
}

// Lấy các giá trị filter
$statusFilter = $_GET['status'] ?? '';

// Lọc theo trạng thái nếu có
if (!empty($statusFilter)) {
    $customers = array_filter($customers, function($c) use ($statusFilter) {
        return $c['AccountStatus'] === $statusFilter;
    });
    $customers = array_values($customers);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Quản lý khách hàng</h4>
    <span class="badge bg-primary fs-6">
        <i class="bi bi-people me-2"></i><?php echo count($customers); ?> khách hàng
    </span>
</div>

<!-- Thông báo -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($updateError)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($updateError); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Bộ lọc khách hàng -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Bộ lọc khách hàng</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" id="filterForm">
            <input type="hidden" name="action" value="customers">
            
            <div class="row g-3 align-items-end">
                <!-- Lọc theo trạng thái -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái tài khoản</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>
                            Hoạt động
                        </option>
                        <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>
                            Không hoạt động
                        </option>
                        <option value="Banned" <?php echo $statusFilter === 'Banned' ? 'selected' : ''; ?>>
                            Bị cấm
                        </option>
                    </select>
                </div>
                
                <?php if (!empty($statusFilter)): ?>
                <div class="col-md-4">
                    <a href="<?php echo BASE_URL; ?>index.php?action=customers" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
        <!-- Hiển thị khi không có khách hàng -->
        <div class="text-center text-muted py-5">
            <i class="bi bi-people display-4 d-block mb-3"></i>
            <p class="mb-0">Không tìm thấy khách hàng nào.</p>
            <?php if (!empty($statusFilter)): ?>
            <a href="<?php echo BASE_URL; ?>index.php?action=customers" class="btn btn-outline-primary mt-3">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Xem tất cả khách hàng
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Bảng khách hàng -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="customersTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Mã KH</th>
                        <th style="width: 60px;">Avatar</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th class="text-center">Tổng đơn hàng</th>
                        <th class="text-end">Tổng chi tiêu</th>
                        <th class="text-center">Trạng thái</th>
                        <th style="width: 100px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <!-- Mã khách hàng -->
                        <td>
                            <code class="text-primary"><?php echo htmlspecialchars($customer['CustomerID']); ?></code>
                        </td>
                        
                        <!-- Avatar -->
                        <td>
                            <?php if (!empty($customer['Avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($customer['Avatar']); ?>" 
                                     alt="Avatar"
                                     class="rounded-circle" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Họ tên -->
                        <td>
                            <strong><?php echo htmlspecialchars(trim($customer['FirstName'] . ' ' . $customer['LastName'])); ?></strong>
                        </td>
                        
                        <!-- Email -->
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($customer['Email']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($customer['Email']); ?>
                            </a>
                        </td>
                        
                        <!-- Tổng đơn hàng -->
                        <td class="text-center">
                            <?php 
                            $totalOrders = (int)$customer['TotalOrders'];
                            if ($totalOrders > 0): ?>
                                <span class="badge bg-info"><?php echo number_format($totalOrders); ?> đơn</span>
                            <?php else: ?>
                                <span class="text-muted">0 đơn</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Tổng chi tiêu -->
                        <td class="text-end">
                            <?php 
                            $totalSpent = (float)$customer['TotalSpent'];
                            if ($totalSpent > 0): ?>
                                <span class="text-success fw-semibold">
                                    <?php echo number_format($totalSpent, 0, ',', '.'); ?>đ
                                </span>
                            <?php else: ?>
                                <span class="text-muted">0đ</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td class="text-center">
                            <?php 
                            $status = $customer['AccountStatus'] ?? 'Unknown';
                            $statusClass = match($status) {
                                'Active' => 'bg-success',
                                'Inactive' => 'bg-warning text-dark',
                                'Banned' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $statusText = match($status) {
                                'Active' => 'Hoạt động',
                                'Inactive' => 'Không hoạt động',
                                'Banned' => 'Bị cấm',
                                default => 'Không xác định'
                            };
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>index.php?action=view_customer&id=<?php echo htmlspecialchars($customer['CustomerID']); ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-warning" 
                                        title="Đổi trạng thái"
                                        onclick="openChangeStatusModal('<?php echo htmlspecialchars($customer['AccountID']); ?>', '<?php echo htmlspecialchars(addslashes(trim($customer['FirstName'] . ' ' . $customer['LastName']))); ?>', '<?php echo htmlspecialchars($customer['AccountStatus']); ?>')">
                                    <i class="bi bi-toggle-on"></i>
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

<?php if (!empty($customers)): ?>
<!-- Modal Thay đổi trạng thái -->
<div class="modal fade" id="changeStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="account_id" id="status_account_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-toggle-on me-2"></i>Thay đổi trạng thái</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Thay đổi trạng thái tài khoản cho khách hàng: <strong id="status_customer_name"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái mới</label>
                        <select name="new_status" id="new_status_select" class="form-select" required>
                            <option value="Active">Hoạt động (Active)</option>
                            <option value="Inactive">Không hoạt động (Inactive)</option>
                            <option value="Banned">Bị cấm (Banned)</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info small">
                        <strong>Active:</strong> Khách hàng có thể đăng nhập và mua hàng bình thường.<br>
                        <strong>Inactive:</strong> Tài khoản tạm ngưng.<br>
                        <strong>Banned:</strong> Khách hàng bị cấm và không thể đăng nhập.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_status" class="btn btn-warning">
                        <i class="bi bi-check-lg me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function mở modal thay đổi trạng thái
function openChangeStatusModal(accountId, customerName, currentStatus) {
    document.getElementById('status_account_id').value = accountId;
    document.getElementById('status_customer_name').textContent = customerName;
    document.getElementById('new_status_select').value = currentStatus;
    
    var modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
    modal.show();
}

$(document).ready(function() {
    $('#customersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });
});
</script>
<?php endif; ?>
