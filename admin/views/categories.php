<?php
// admin/views/categories.php
// Quản lý danh mục sản phẩm

// Lấy danh sách danh mục với số lượng sản phẩm
$categories = $pdo->query("
    SELECT 
        c.CategoryID,
        c.CategoryName,
        COUNT(p.ProductID) AS TotalProducts
    FROM CATEGORY c
    LEFT JOIN PRODUCT p ON c.CategoryID = p.CategoryID
    GROUP BY c.CategoryID, c.CategoryName
    ORDER BY c.CategoryName ASC
")->fetchAll();

// Xử lý xóa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $categoryId = $_POST['category_id'] ?? '';
    
    try {
        // Kiểm tra có sản phẩm trong danh mục không
        $checkProducts = $pdo->prepare("SELECT COUNT(*) FROM PRODUCT WHERE CategoryID = ?");
        $checkProducts->execute([$categoryId]);
        $productCount = $checkProducts->fetchColumn();
        
        if ($productCount > 0) {
            $deleteError = "Không thể xóa danh mục này vì còn $productCount sản phẩm. Vui lòng chuyển sản phẩm sang danh mục khác trước.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM CATEGORY WHERE CategoryID = ?");
            $stmt->execute([$categoryId]);
            $deleteSuccess = "Đã xóa danh mục thành công!";
            
            // Refresh danh sách
            // Refresh danh sách bằng JavaScript vì headers đã được gửi
            echo "<script>window.location.href = '" . BASE_URL . "index.php?action=categories&deleted=1';</script>";
            exit;
        }
    } catch (Exception $e) {
        $deleteError = "Lỗi khi xóa danh mục: " . $e->getMessage();
    }
}

// Thông báo
$message = '';
$messageType = '';
if (isset($_GET['deleted'])) {
    $message = 'Đã xóa danh mục thành công!';
    $messageType = 'success';
}
if (isset($_GET['added'])) {
    $message = 'Đã thêm danh mục mới thành công!';
    $messageType = 'success';
}
if (isset($_GET['updated'])) {
    $message = 'Đã cập nhật danh mục thành công!';
    $messageType = 'success';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Quản lý danh mục</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=products">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Danh mục</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm danh mục
    </button>
</div>

<!-- Thông báo -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($deleteError)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($deleteError); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 150px;">Mã danh mục</th>
                        <th>Tên danh mục</th>
                        <th class="text-center" style="width: 100px;">Số sản phẩm</th>
                        <th class="text-center" style="width: 180px;">Sản phẩm</th>
                        <th style="width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            Chưa có danh mục nào. Bấm "Thêm danh mục" để tạo mới.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <!-- Mã danh mục -->
                        <td>
                            <code class="text-primary fw-bold"><?php echo htmlspecialchars($cat['CategoryID']); ?></code>
                        </td>
                        
                        <!-- Tên danh mục -->
                        <td>
                            <strong><?php echo htmlspecialchars($cat['CategoryName']); ?></strong>
                        </td>
                        
                        <!-- Số sản phẩm -->
                        <td class="text-center">
                            <?php 
                            $count = (int)$cat['TotalProducts'];
                            $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?> fs-6">
                                <?php echo number_format($count); ?>
                            </span>
                        </td>
                        
                        <!-- Nút xem/thêm sản phẩm -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>index.php?action=products&category=<?php echo $cat['CategoryID']; ?>" 
                                   class="btn btn-outline-info" title="Xem sản phẩm trong danh mục">
                                    <i class="bi bi-eye me-1"></i>Xem SP
                                </a>
                                <a href="<?php echo BASE_URL; ?>index.php?action=add_product&category=<?php echo $cat['CategoryID']; ?>" 
                                   class="btn btn-outline-success" title="Thêm sản phẩm vào danh mục">
                                    <i class="bi bi-plus-circle me-1"></i>Thêm SP
                                </a>
                            </div>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" 
                                        title="Sửa danh mục"
                                        onclick="openEditModal('<?php echo htmlspecialchars($cat['CategoryID']); ?>', '<?php echo htmlspecialchars(addslashes($cat['CategoryName'])); ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Xem chi tiết"
                                        onclick="viewCategoryDetails('<?php echo htmlspecialchars($cat['CategoryID']); ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" 
                                        title="Xóa danh mục"
                                        onclick="openDeleteModal('<?php echo htmlspecialchars($cat['CategoryID']); ?>', '<?php echo htmlspecialchars(addslashes($cat['CategoryName'])); ?>', <?php echo $count; ?>)">
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

<!-- Modal Thêm danh mục -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=add_category">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Thêm danh mục mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="category_id" class="form-control" required 
                               placeholder="VD: CAT001, CANDY...">
                        <small class="text-muted">Mã danh mục phải duy nhất</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required 
                               placeholder="Nhập tên danh mục...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Thêm danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa danh mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=edit_category">
                <input type="hidden" name="old_category_id" id="edit_old_category_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="category_id" id="edit_category_id" class="form-control" required>
                        <small class="text-muted">Thay đổi mã danh mục sẽ tự động cập nhật các sản phẩm liên quan</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="edit_category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_category" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xác nhận xóa -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="category_id" id="delete_category_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa danh mục <strong id="delete_category_name"></strong>?</p>
                    <div id="delete_warning" class="alert alert-warning d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="delete_warning_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="delete_category" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-2"></i>Xóa danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Các function xử lý modal - không phụ thuộc vào jQuery ready
function openEditModal(id, name) {
    document.getElementById('edit_old_category_id').value = id;
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    
    // Sử dụng Bootstrap modal API
    var modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}

function openDeleteModal(id, name, count) {
    document.getElementById('delete_category_id').value = id;
    document.getElementById('delete_category_name').textContent = name;
    
    var warningEl = document.getElementById('delete_warning');
    var warningTextEl = document.getElementById('delete_warning_text');
    var confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (count > 0) {
        warningEl.classList.remove('d-none');
        warningTextEl.textContent = 'Danh mục này có ' + count + ' sản phẩm. Bạn cần chuyển sản phẩm sang danh mục khác trước khi xóa.';
        confirmBtn.disabled = true;
    } else {
        warningEl.classList.add('d-none');
        confirmBtn.disabled = false;
    }
    
    var modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    modal.show();
}

function viewCategoryDetails(categoryId) {
    window.location.href = '<?php echo BASE_URL; ?>index.php?action=products&category=' + categoryId;
}

// Khởi tạo DataTable khi jQuery đã sẵn sàng
if (typeof jQuery !== 'undefined') {
    $(document).ready(function() {
        if ($('#categoriesTable tbody tr').length > 1 && $.fn.DataTable) {
            if (!$.fn.DataTable.isDataTable('#categoriesTable')) {
                $('#categoriesTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    },
                    pageLength: 10,
                    order: [[1, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: [3, 4] }
                    ]
                });
            }
        }
    });
} else {
    // Nếu jQuery chưa load, đợi và khởi tạo sau
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof jQuery !== 'undefined' && $.fn.DataTable) {
                if (!$.fn.DataTable.isDataTable('#categoriesTable')) {
                    $('#categoriesTable').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                        },
                        pageLength: 10,
                        order: [[1, 'asc']],
                        columnDefs: [
                            { orderable: false, targets: [3, 4] }
                        ]
                    });
                }
            }
        }, 100);
    });
}
</script>
