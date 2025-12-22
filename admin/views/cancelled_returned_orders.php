<?php
// admin/views/cancelled_returned_orders.php
// Trang quản lý đơn hàng hủy và trả

// Lấy các tham số filter
$type = $_GET['type'] ?? ''; // 'cancel', 'return', hoặc ''
$search = $_GET['search'] ?? '';

// Xử lý duyệt/từ chối yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_cancel'])) {
        $orderId = $_POST['order_id'] ?? '';
        try {
            // Cập nhật trạng thái đơn hàng thành Cancelled
            $stmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = 'Cancelled' WHERE OrderID = ?");
            $stmt->execute([$orderId]);
            
            echo "<script>showToast('Đã duyệt yêu cầu hủy đơn!', 'success'); setTimeout(function(){ location.reload(); }, 1500);</script>";
        } catch (Exception $e) {
            echo "<script>showToast('Lỗi: " . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    }
    
    if (isset($_POST['reject_cancel'])) {
        $orderId = $_POST['order_id'] ?? '';
        $previousStatus = $_POST['previous_status'] ?? 'Pending';
        try {
            // Khôi phục trạng thái đơn hàng về trước đó
            $stmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = ? WHERE OrderID = ?");
            $stmt->execute([$previousStatus, $orderId]);
            
            echo "<script>showToast('Đã từ chối yêu cầu hủy đơn!', 'warning'); setTimeout(function(){ location.reload(); }, 1500);</script>";
        } catch (Exception $e) {
            echo "<script>showToast('Lỗi: " . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    }
    
    if (isset($_POST['approve_return'])) {
        $orderId = $_POST['order_id'] ?? '';
        try {
            // Cập nhật trạng thái đơn hàng thành Returned
            $stmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = 'Returned' WHERE OrderID = ?");
            $stmt->execute([$orderId]);
            
            echo "<script>showToast('Đã duyệt yêu cầu trả hàng!', 'success'); setTimeout(function(){ location.reload(); }, 1500);</script>";
        } catch (Exception $e) {
            echo "<script>showToast('Lỗi: " . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    }
    
    if (isset($_POST['reject_return'])) {
        $orderId = $_POST['order_id'] ?? '';
        try {
            // Khôi phục trạng thái đơn hàng về Complete
            $stmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = 'Complete' WHERE OrderID = ?");
            $stmt->execute([$orderId]);
            
            echo "<script>showToast('Đã từ chối yêu cầu trả hàng!', 'warning'); setTimeout(function(){ location.reload(); }, 1500);</script>";
        } catch (Exception $e) {
            echo "<script>showToast('Lỗi: " . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    }
}

// Query lấy danh sách đơn hàng đã hủy và đang chờ hủy từ bảng ORDERS
$cancellations = [];
$returns = [];

try {
    // Lấy đơn hàng có trạng thái Cancelled hoặc Pending Cancel
    $cancelSql = "SELECT 
        o.OrderID,
        o.OrderID as CancellationID,
        o.OrderDate,
        o.OrderDate as CancellationDate,
        'Khách hàng yêu cầu hủy đơn' as CancellationReason,
        CASE 
            WHEN o.OrderStatus = 'Pending Cancel' THEN 'Pending'
            WHEN o.OrderStatus = 'Cancelled' THEN 'Approved'
            ELSE 'Unknown'
        END as CancellationStatus,
        'Pending' as PreviousStatus,
        o.OrderStatus,
        CONCAT(cu.FirstName, ' ', cu.LastName) as CustomerName,
        a.Email as CustomerEmail
    FROM ORDERS o
    LEFT JOIN CUSTOMER cu ON o.CustomerID = cu.CustomerID
    LEFT JOIN ACCOUNT a ON cu.AccountID = a.AccountID
    WHERE o.OrderStatus IN ('Pending Cancel', 'Cancelled')";

    if (!empty($search)) {
        $cancelSql .= " AND (o.OrderID LIKE :search OR cu.FirstName LIKE :search OR cu.LastName LIKE :search)";
    }

    $cancelSql .= " ORDER BY o.OrderDate DESC";

    $cancelStmt = $pdo->prepare($cancelSql);
    if (!empty($search)) {
        $cancelStmt->bindValue(':search', "%$search%");
    }
    $cancelStmt->execute();
    $cancellations = $cancelStmt->fetchAll();
} catch (Exception $e) {
    $cancelError = $e->getMessage();
}

try {
    // Lấy đơn hàng có trạng thái Returned hoặc Pending Return
    $returnSql = "SELECT 
        o.OrderID,
        o.OrderID as RefundID,
        o.OrderDate,
        o.OrderDate as RefundDate,
        'Khách hàng yêu cầu trả hàng' as RefundReason,
        '' as RefundDescription,
        '' as RefundImage,
        CASE 
            WHEN o.OrderStatus = 'Pending Return' THEN 'Pending'
            WHEN o.OrderStatus = 'Returned' THEN 'Approved'
            ELSE 'Unknown'
        END as RefundStatus,
        o.OrderStatus,
        CONCAT(cu.FirstName, ' ', cu.LastName) as CustomerName,
        a.Email as CustomerEmail
    FROM ORDERS o
    LEFT JOIN CUSTOMER cu ON o.CustomerID = cu.CustomerID
    LEFT JOIN ACCOUNT a ON cu.AccountID = a.AccountID
    WHERE o.OrderStatus IN ('Pending Return', 'Returned')";

    if (!empty($search)) {
        $returnSql .= " AND (o.OrderID LIKE :search OR cu.FirstName LIKE :search OR cu.LastName LIKE :search)";
    }

    $returnSql .= " ORDER BY o.OrderDate DESC";

    $returnStmt = $pdo->prepare($returnSql);
    if (!empty($search)) {
        $returnStmt->bindValue(':search', "%$search%");
    }
    $returnStmt->execute();
    $returns = $returnStmt->fetchAll();
} catch (Exception $e) {
    $returnError = $e->getMessage();
}

// Thống kê
$pendingCancels = count(array_filter($cancellations, fn($c) => $c['CancellationStatus'] === 'Pending'));
$pendingReturns = count(array_filter($returns, fn($r) => $r['RefundStatus'] === 'Pending'));
$totalCancels = count($cancellations);
$totalReturns = count($returns);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Quản lý đơn hủy & trả hàng</h4>
</div>

<!-- Thống kê nhanh -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card border-danger">
            <div class="stat-icon bg-danger">
                <i class="bi bi-x-circle text-white"></i>
            </div>
            <div class="stat-number"><?php echo $pendingCancels; ?></div>
            <div class="stat-label">Chờ duyệt hủy</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card border-warning">
            <div class="stat-icon bg-warning">
                <i class="bi bi-arrow-return-left text-white"></i>
            </div>
            <div class="stat-number"><?php echo $pendingReturns; ?></div>
            <div class="stat-label">Chờ duyệt trả hàng</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-secondary">
                <i class="bi bi-x-lg text-white"></i>
            </div>
            <div class="stat-number"><?php echo $totalCancels; ?></div>
            <div class="stat-label">Tổng yêu cầu hủy</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="stat-icon bg-dark">
                <i class="bi bi-box-arrow-left text-white"></i>
            </div>
            <div class="stat-number"><?php echo $totalReturns; ?></div>
            <div class="stat-label">Tổng yêu cầu trả</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="cancel-tab" data-bs-toggle="tab" data-bs-target="#cancel-orders" type="button" role="tab">
            <i class="bi bi-x-circle me-1"></i> Yêu cầu hủy đơn
            <?php if ($pendingCancels > 0): ?>
            <span class="badge bg-danger ms-1"><?php echo $pendingCancels; ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="return-tab" data-bs-toggle="tab" data-bs-target="#return-orders" type="button" role="tab">
            <i class="bi bi-arrow-return-left me-1"></i> Yêu cầu trả hàng
            <?php if ($pendingReturns > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?php echo $pendingReturns; ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="orderTabsContent">
    <!-- Tab Hủy đơn -->
    <div class="tab-pane fade show active" id="cancel-orders" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="cancelTable">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày yêu cầu</th>
                                <th>Lý do hủy</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cancellations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox display-6 text-muted d-block mb-2"></i>
                                    Không có yêu cầu hủy đơn nào
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($cancellations as $cancel): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?php echo htmlspecialchars($cancel['OrderID']); ?></strong>
                                    <br><small class="text-muted">Đặt: <?php echo formatDate($cancel['OrderDate']); ?></small>
                                </td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($cancel['CustomerName'] ?? 'N/A'); ?></strong></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($cancel['CustomerEmail'] ?? ''); ?></small>
                                </td>
                                <td><?php echo formatDate($cancel['CancellationDate']); ?></td>
                                <td>
                                    <div class="text-wrap" style="max-width: 300px;">
                                        <?php echo htmlspecialchars($cancel['CancellationReason']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($cancel['CancellationStatus']) {
                                        'Pending' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    $statusText = match($cancel['CancellationStatus']) {
                                        'Pending' => 'Chờ duyệt',
                                        'Approved' => 'Đã duyệt',
                                        'Rejected' => 'Đã từ chối',
                                        default => $cancel['CancellationStatus']
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td>
                                    <?php if ($cancel['CancellationStatus'] === 'Pending'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $cancel['OrderID']; ?>">
                                            <input type="hidden" name="approve_cancel" value="1">
                                            <button type="submit" class="btn btn-success btn-sm" title="Duyệt" onclick="return confirm('Xác nhận duyệt yêu cầu hủy đơn này?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $cancel['OrderID']; ?>">
                                            <input type="hidden" name="previous_status" value="<?php echo $cancel['PreviousStatus'] ?? 'Pending'; ?>">
                                            <input type="hidden" name="reject_cancel" value="1">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Từ chối" onclick="return confirm('Xác nhận từ chối yêu cầu hủy đơn này?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $cancel['OrderID']; ?>" 
                                           class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $cancel['OrderID']; ?>" 
                                       class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Trả hàng -->
    <div class="tab-pane fade" id="return-orders" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="returnTable">
                        <thead>
                            <tr>
                                <th>Mã yêu cầu</th>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày yêu cầu</th>
                                <th>Lý do trả</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($returns)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox display-6 text-muted d-block mb-2"></i>
                                    Không có yêu cầu trả hàng nào
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($returns as $return): ?>
                            <tr>
                                <td><strong class="text-info"><?php echo htmlspecialchars($return['RefundID']); ?></strong></td>
                                <td>
                                    <strong class="text-primary">#<?php echo htmlspecialchars($return['OrderID']); ?></strong>
                                    <br><small class="text-muted">Đặt: <?php echo formatDate($return['OrderDate']); ?></small>
                                </td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($return['CustomerName'] ?? 'N/A'); ?></strong></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($return['CustomerEmail'] ?? ''); ?></small>
                                </td>
                                <td><?php echo formatDate($return['RefundDate']); ?></td>
                                <td>
                                    <div class="text-wrap" style="max-width: 250px;">
                                        <strong><?php echo htmlspecialchars($return['RefundReason']); ?></strong>
                                        <?php if (!empty($return['RefundDescription'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($return['RefundDescription']); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($return['RefundImage'])): ?>
                                        <br><a href="#" onclick="showImage('<?php echo htmlspecialchars($return['RefundImage']); ?>')" class="small">
                                            <i class="bi bi-image"></i> Xem ảnh
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($return['RefundStatus']) {
                                        'Pending' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    $statusText = match($return['RefundStatus']) {
                                        'Pending' => 'Chờ duyệt',
                                        'Approved' => 'Đã duyệt',
                                        'Rejected' => 'Đã từ chối',
                                        default => $return['RefundStatus']
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td>
                                    <?php if ($return['RefundStatus'] === 'Pending'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $return['OrderID']; ?>">
                                            <input type="hidden" name="approve_return" value="1">
                                            <button type="submit" class="btn btn-success btn-sm" title="Duyệt" onclick="return confirm('Xác nhận duyệt yêu cầu trả hàng này?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $return['OrderID']; ?>">
                                            <input type="hidden" name="reject_return" value="1">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Từ chối" onclick="return confirm('Xác nhận từ chối yêu cầu trả hàng này?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $return['OrderID']; ?>" 
                                           class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $return['OrderID']; ?>" 
                                       class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem ảnh -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ảnh minh chứng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Refund Image">
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Khởi tạo DataTable
    if ($('#cancelTable tbody tr').length > 1 || ($('#cancelTable tbody tr').length === 1 && !$('#cancelTable tbody tr td').hasClass('text-center'))) {
        $('#cancelTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            order: [[2, 'desc']],
            pageLength: 25
        });
    }
    
    if ($('#returnTable tbody tr').length > 1 || ($('#returnTable tbody tr').length === 1 && !$('#returnTable tbody tr td').hasClass('text-center'))) {
        $('#returnTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            order: [[3, 'desc']],
            pageLength: 25
        });
    }
});

function showImage(imagePath) {
    // Convert relative path to absolute
    const basePath = '/Candy-Crunch-Website/views/website/';
    document.getElementById('modalImage').src = basePath + imagePath;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
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

.nav-tabs .nav-link {
    color: #495057;
    border: 1px solid transparent;
}

.nav-tabs .nav-link.active {
    font-weight: 600;
    color: #667eea;
}
</style>
