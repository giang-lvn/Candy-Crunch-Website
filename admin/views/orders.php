<?php
// admin/views/orders.php

// Lấy các tham số filter
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Xây dựng query
$sql = "SELECT 
            o.OrderID,
            o.OrderDate,
            o.OrderStatus,
            o.PaymentMethod,
            o.ShippingMethod,
            o.ShippingFee,
            CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
            c.Phone as CustomerPhone,
            (SELECT SUM(s.OriginalPrice * od.OrderQuantity) 
             FROM ORDER_DETAIL od 
             JOIN SKU s ON od.SKUID = s.SKUID 
             WHERE od.OrderID = o.OrderID) as SubTotal,
            v.Code as VoucherCode
        FROM ORDERS o
        LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
        LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
        WHERE 1=1";

$params = [];

// Filter theo status
if (!empty($status)) {
    $sql .= " AND o.OrderStatus = ?";
    $params[] = $status;
}

// Filter theo search
if (!empty($search)) {
    $sql .= " AND (o.OrderID LIKE ? OR c.FirstName LIKE ? OR c.LastName LIKE ? OR c.Phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Filter theo ngày
if (!empty($start_date)) {
    $sql .= " AND DATE(o.OrderDate) >= ?";
    $params[] = $start_date;
}

if (!empty($end_date)) {
    $sql .= " AND DATE(o.OrderDate) <= ?";
    $params[] = $end_date;
}

$sql .= " ORDER BY o.OrderDate DESC";

// Thực thi query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Thống kê
$stats_sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN OrderStatus = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN OrderStatus = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN OrderStatus = 'completed' THEN 1 ELSE 0 END) as completed_orders
              FROM ORDERS";
$stats = $pdo->query($stats_sql)->fetch();

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $update_stmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = ? WHERE OrderID = ?");
        $update_stmt->execute([$new_status, $order_id]);
        
        echo '<script>showToast("Cập nhật trạng thái thành công!", "success");</script>';
    } catch (Exception $e) {
        echo '<script>showToast("Có lỗi xảy ra: ' . $e->getMessage() . '", "error");</script>';
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Quản lý đơn hàng</h4>
    <div>
        <button class="btn btn-primary" onclick="exportOrders()">
            <i class="bi bi-download me-2"></i>Xuất Excel
        </button>
    </div>
</div>

<!-- Thống kê nhanh -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon bg-primary">
                <i class="bi bi-receipt text-white"></i>
            </div>
            <div class="stat-number"><?php echo $stats['total_orders'] ?? 0; ?></div>
            <div class="stat-label">Tổng đơn hàng</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon bg-warning">
                <i class="bi bi-clock text-white"></i>
            </div>
            <div class="stat-number"><?php echo $stats['pending_orders'] ?? 0; ?></div>
            <div class="stat-label">Chờ xử lý</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon bg-info">
                <i class="bi bi-gear text-white"></i>
            </div>
            <div class="stat-number"><?php echo $stats['processing_orders'] ?? 0; ?></div>
            <div class="stat-label">Đang xử lý</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon bg-success">
                <i class="bi bi-check-circle text-white"></i>
            </div>
            <div class="stat-number"><?php echo $stats['completed_orders'] ?? 0; ?></div>
            <div class="stat-label">Hoàn thành</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="orders">
            
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach (getOrderStatuses() as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $status == $key ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" 
                       placeholder="Từ ngày" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" 
                       placeholder="Đến ngày" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        
        <div class="mt-3">
            <input type="text" id="searchInput" class="form-control" 
                   placeholder="Tìm kiếm theo mã đơn, tên hoặc số điện thoại khách hàng..." 
                   onkeyup="filterOrders()">
        </div>
    </div>
</div>

<!-- Bảng đơn hàng -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="ordersTable">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có đơn hàng nào</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): 
                        $total = ($order['SubTotal'] ?? 0) + ($order['ShippingFee'] ?? 0);
                    ?>
                    <tr>
                        <td>
                            <strong class="text-primary">#<?php echo htmlspecialchars($order['OrderID']); ?></strong>
                            <?php if ($order['VoucherCode']): ?>
                            <br><small class="text-success"><?php echo htmlspecialchars($order['VoucherCode']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($order['CustomerName'] ?? 'N/A'); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($order['CustomerPhone'] ?? ''); ?></small>
                        </td>
                        <td><?php echo formatDate($order['OrderDate']); ?></td>
                        <td>
                            <strong><?php echo formatCurrency($total); ?></strong>
                            <?php if ($order['ShippingFee'] > 0): ?>
                            <br><small class="text-muted">Ship: <?php echo formatCurrency($order['ShippingFee']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo getStatusColor($order['OrderStatus']); ?>">
                                <?php echo getStatusText($order['OrderStatus']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php 
                                $paymentMethods = getPaymentMethods();
                                echo $paymentMethods[$order['PaymentMethod']] ?? $order['PaymentMethod'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" 
                                        onclick="viewOrderDetail('<?php echo $order['OrderID']; ?>')"
                                        title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info" 
                                        onclick="showUpdateStatusModal('<?php echo $order['OrderID']; ?>', '<?php echo $order['OrderStatus']; ?>')"
                                        title="Cập nhật trạng thái">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?action=order_edit&id=<?php echo $order['OrderID']; ?>" 
                                   class="btn btn-outline-warning" title="Sửa đơn hàng">
                                    <i class="bi bi-gear"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination nếu có nhiều đơn hàng -->
        <?php if (count($orders) > 10): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Trước</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Tiếp</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Nội dung sẽ được load bằng AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal cập nhật trạng thái -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="updateStatusForm">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới</label>
                        <select name="status" class="form-select" id="updateStatusSelect" required>
                            <?php foreach (getOrderStatuses() as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Khởi tạo DataTable
    $('#ordersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        order: [[2, 'desc']],
        pageLength: 25,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>tip'
    });
});

function filterOrders() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('ordersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.textContent.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

function viewOrderDetail(orderId) {
    // Hiển thị loading
    $('#orderDetailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Đang tải thông tin...</p>
        </div>
    `);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();
    
    // Load dữ liệu bằng AJAX
    $.ajax({
        url: 'ajax/get_order_detail.php',
        method: 'GET',
        data: { order_id: orderId },
        success: function(response) {
            $('#orderDetailContent').html(response);
        },
        error: function() {
            $('#orderDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Không thể tải thông tin đơn hàng. Vui lòng thử lại.
                </div>
            `);
        }
    });
}

function showUpdateStatusModal(orderId, currentStatus) {
    $('#updateOrderId').val(orderId);
    $('#updateStatusSelect').val(currentStatus);
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function exportOrders() {
    // Tạo dữ liệu cho export
    const table = document.getElementById('ordersTable');
    const rows = table.querySelectorAll('tbody tr');
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Header
    const headers = ['Mã đơn', 'Khách hàng', 'Ngày đặt', 'Tổng tiền', 'Trạng thái', 'Thanh toán'];
    csvContent += headers.join(",") + "\n";
    
    // Data
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const rowData = [];
            
            for (let i = 0; i < cells.length - 1; i++) { // Bỏ cột thao tác
                let cellText = cells[i].textContent.trim();
                // Xóa các ký tự đặc biệt
                cellText = cellText.replace(/(\r\n|\n|\r)/gm, "");
                cellText = cellText.replace(/,/g, ";");
                rowData.push('"' + cellText + '"');
            }
            
            csvContent += rowData.join(",") + "\n";
        }
    });
    
    // Tạo và tải file
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "don_hang_" + new Date().toISOString().slice(0,10) + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Xử lý form cập nhật trạng thái
$('#updateStatusForm').submit(function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: formData,
        success: function(response) {
            // Đóng modal
            bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
            
            // Hiển thị thông báo
            showToast("Đã cập nhật trạng thái thành công!", "success");
            
            // Reload trang sau 1.5 giây
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },
        error: function() {
            showToast("Có lỗi xảy ra khi cập nhật!", "error");
        }
    });
});
</script>

<style>
.stat-card {
    text-align: center;
    padding: 20px;
    border-radius: 10px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 24px;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>