<?php
// admin/views/feedback.php
// Quản lý đánh giá/feedback của khách hàng

// Xử lý cập nhật trạng thái feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_feedback'])) {
        $feedbackId = $_POST['feedback_id'] ?? '';
        try {
            $stmt = $pdo->prepare("UPDATE FEEDBACK SET Status = 'approved' WHERE FeedbackID = ?");
            $stmt->execute([$feedbackId]);
            echo "<script>window.location.href = '" . BASE_URL . "index.php?action=feedback&approved=1';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Lỗi khi duyệt feedback: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['hide_feedback'])) {
        $feedbackId = $_POST['feedback_id'] ?? '';
        try {
            $stmt = $pdo->prepare("UPDATE FEEDBACK SET Status = 'hidden' WHERE FeedbackID = ?");
            $stmt->execute([$feedbackId]);
            echo "<script>window.location.href = '" . BASE_URL . "index.php?action=feedback&hidden=1';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Lỗi khi ẩn feedback: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['pending_feedback'])) {
        $feedbackId = $_POST['feedback_id'] ?? '';
        try {
            $stmt = $pdo->prepare("UPDATE FEEDBACK SET Status = 'pending' WHERE FeedbackID = ?");
            $stmt->execute([$feedbackId]);
            echo "<script>window.location.href = '" . BASE_URL . "index.php?action=feedback&pending=1';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Lỗi khi đặt lại trạng thái feedback: " . $e->getMessage();
        }
    }
}

// Lấy filter trạng thái
$statusFilter = $_GET['status'] ?? '';

// Query lấy feedback với thông tin đầy đủ
$sql = "
    SELECT 
        f.FeedbackID,
        f.Rating,
        f.Comment,
        f.CreateDate,
        f.Status,
        c.CustomerID,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        a.Email as CustomerEmail,
        s.SKUID,
        s.Attribute as SKUAttribute,
        p.ProductID,
        p.ProductName
    FROM FEEDBACK f
    JOIN CUSTOMER c ON f.CustomerID = c.CustomerID
    JOIN ACCOUNT a ON c.AccountID = a.AccountID
    JOIN SKU s ON f.SKUID = s.SKUID
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    WHERE 1=1
";

$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND f.Status = :status";
    $params['status'] = $statusFilter;
}

$sql .= " ORDER BY f.CreateDate DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

// Thống kê
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'hidden' => 0
];

$statsQuery = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN Status = 'pending' OR Status IS NULL THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN Status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN Status = 'hidden' THEN 1 ELSE 0 END) as hidden
    FROM FEEDBACK
");
$statsResult = $statsQuery->fetch();
if ($statsResult) {
    $stats['total'] = (int)($statsResult['total'] ?? 0);
    $stats['pending'] = (int)($statsResult['pending'] ?? 0);
    $stats['approved'] = (int)($statsResult['approved'] ?? 0);
    $stats['hidden'] = (int)($statsResult['hidden'] ?? 0);
}

// Thông báo
$message = '';
$messageType = '';
if (isset($_GET['approved'])) {
    $message = 'Đã duyệt đánh giá thành công!';
    $messageType = 'success';
}
if (isset($_GET['hidden'])) {
    $message = 'Đã ẩn đánh giá thành công!';
    $messageType = 'success';
}
if (isset($_GET['pending'])) {
    $message = 'Đã chuyển đánh giá về trạng thái chờ duyệt!';
    $messageType = 'info';
}

// Status labels và classes
$statusLabels = [
    'pending' => 'Chờ duyệt',
    'approved' => 'Đã duyệt',
    'hidden' => 'Đã ẩn'
];

$statusClasses = [
    'pending' => 'bg-warning text-dark',
    'approved' => 'bg-success',
    'hidden' => 'bg-secondary'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Quản lý đánh giá</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=products">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Đánh giá</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Thông báo -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Thống kê nhanh -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Tổng đánh giá</div>
                        <div class="h3 mb-0"><?php echo number_format($stats['total']); ?></div>
                    </div>
                    <i class="bi bi-chat-quote display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-dark opacity-75">Chờ duyệt</div>
                        <div class="h3 mb-0 text-dark"><?php echo number_format($stats['pending']); ?></div>
                    </div>
                    <i class="bi bi-clock-history display-6 text-dark opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Đã duyệt</div>
                        <div class="h3 mb-0"><?php echo number_format($stats['approved']); ?></div>
                    </div>
                    <i class="bi bi-check-circle display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Đã ẩn</div>
                        <div class="h3 mb-0"><?php echo number_format($stats['hidden']); ?></div>
                    </div>
                    <i class="bi bi-eye-slash display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Bộ lọc</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php">
            <input type="hidden" name="action" value="feedback">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="hidden" <?php echo $statusFilter === 'hidden' ? 'selected' : ''; ?>>Đã ẩn</option>
                    </select>
                </div>
                <?php if (!empty($statusFilter)): ?>
                <div class="col-md-4">
                    <a href="<?php echo BASE_URL; ?>index.php?action=feedback" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bảng feedback -->
<div class="card">
    <div class="card-body">
        <?php if (empty($feedbacks)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-chat-quote display-4 d-block mb-3"></i>
            <p class="mb-0">Chưa có đánh giá nào<?php echo !empty($statusFilter) ? ' với trạng thái này' : ''; ?>.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="feedbackTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Mã</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th class="text-center" style="width: 100px;">Đánh giá</th>
                        <th>Nội dung</th>
                        <th style="width: 120px;">Ngày tạo</th>
                        <th class="text-center" style="width: 100px;">Trạng thái</th>
                        <th style="width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $fb): 
                        $status = $fb['Status'] ?? 'pending';
                    ?>
                    <tr>
                        <!-- Mã feedback -->
                        <td>
                            <code class="text-primary small"><?php echo htmlspecialchars($fb['FeedbackID']); ?></code>
                        </td>
                        
                        <!-- Khách hàng -->
                        <td>
                            <strong><?php echo htmlspecialchars($fb['CustomerName']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($fb['CustomerEmail']); ?></small>
                        </td>
                        
                        <!-- Sản phẩm -->
                        <td>
                            <a href="<?php echo BASE_URL; ?>index.php?action=view_product&id=<?php echo htmlspecialchars($fb['ProductID']); ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($fb['ProductName']); ?>
                            </a>
                            <br><small class="text-muted">SKU: <?php echo htmlspecialchars($fb['SKUID']); ?></small>
                        </td>
                        
                        <!-- Rating -->
                        <td class="text-center">
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $fb['Rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?php echo $fb['Rating']; ?>/5</small>
                        </td>
                        
                        <!-- Nội dung -->
                        <td>
                            <?php if (!empty($fb['Comment'])): ?>
                                <div class="comment-preview" style="max-width: 250px;">
                                    <?php 
                                    $comment = htmlspecialchars($fb['Comment']);
                                    echo mb_strlen($comment) > 100 
                                        ? mb_substr($comment, 0, 100) . '...' 
                                        : $comment; 
                                    ?>
                                </div>
                                <?php if (mb_strlen($fb['Comment']) > 100): ?>
                                <button type="button" class="btn btn-link btn-sm p-0" 
                                        onclick="showFullComment('<?php echo htmlspecialchars(addslashes($fb['Comment'])); ?>')">
                                    Xem thêm
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Không có nội dung</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Ngày tạo -->
                        <td>
                            <small><?php echo date('d/m/Y', strtotime($fb['CreateDate'])); ?></small>
                            <br><small class="text-muted"><?php echo date('H:i', strtotime($fb['CreateDate'])); ?></small>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td class="text-center">
                            <span class="badge <?php echo $statusClasses[$status] ?? 'bg-warning text-dark'; ?>">
                                <?php echo $statusLabels[$status] ?? 'Chờ duyệt'; ?>
                            </span>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if ($status !== 'approved'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($fb['FeedbackID']); ?>">
                                    <button type="submit" name="approve_feedback" class="btn btn-outline-success" title="Duyệt">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($status !== 'hidden'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($fb['FeedbackID']); ?>">
                                    <button type="submit" name="hide_feedback" class="btn btn-outline-secondary" title="Ẩn">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($status !== 'pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($fb['FeedbackID']); ?>">
                                    <button type="submit" name="pending_feedback" class="btn btn-outline-warning" title="Đặt chờ duyệt">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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

<!-- Modal xem nội dung đầy đủ -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-chat-quote me-2"></i>Nội dung đánh giá</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="fullCommentText" style="white-space: pre-wrap;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function showFullComment(comment) {
    document.getElementById('fullCommentText').textContent = comment;
    var modal = new bootstrap.Modal(document.getElementById('commentModal'));
    modal.show();
}

$(document).ready(function() {
    if ($('#feedbackTable tbody tr').length > 1) {
        $('#feedbackTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            pageLength: 10,
            order: [[5, 'desc']], // Sắp xếp theo ngày tạo mới nhất
            columnDefs: [
                { orderable: false, targets: [3, 4, 7] }
            ]
        });
    }
});
</script>

<style>
.comment-preview {
    font-size: 0.9rem;
    line-height: 1.4;
}
</style>
