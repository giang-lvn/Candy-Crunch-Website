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
            header("Location: " . BASE_URL . "index.php?action=categories&deleted=1");
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
                        <th class="text-center" style="width: 150px;">Số sản phẩm</th>
                        <th style="width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
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
                            <?php if ($count > 0): ?>
                                <a href="<?php echo BASE_URL; ?>index.php?action=products&category=<?php echo $cat['CategoryID']; ?>" 
                                   class="small d-block text-decoration-none">
                                    Xem sản phẩm
                                </a>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-category-btn" 
                                        title="Sửa danh mục"
                                        data-id="<?php echo htmlspecialchars($cat['CategoryID']); ?>"
                                        data-name="<?php echo htmlspecialchars($cat['CategoryName']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Xem chi tiết"
                                        onclick="viewCategoryDetails('<?php echo htmlspecialchars($cat['CategoryID']); ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger delete-category-btn" 
                                        title="Xóa danh mục"
                                        data-id="<?php echo htmlspecialchars($cat['CategoryID']); ?>"
                                        data-name="<?php echo htmlspecialchars($cat['CategoryName']); ?>"
                                        data-count="<?php echo $count; ?>">
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
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã danh mục</label>
                        <input type="text" class="form-control" id="edit_category_id_display" disabled>
                        <small class="text-muted">Mã danh mục không thể thay đổi</small>
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
$(document).ready(function() {
    // DataTable
    if ($('#categoriesTable tbody tr').length > 1) {
        $('#categoriesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [4] }
            ]
        });
    }
    
    // Mở modal sửa
    $('.edit-category-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#edit_category_id').val(id);
        $('#edit_category_id_display').val(id);
        $('#edit_category_name').val(name);
        
        $('#editCategoryModal').modal('show');
    });
    
    // Mở modal xóa
    $('.delete-category-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const count = parseInt($(this).data('count'));
        
        $('#delete_category_id').val(id);
        $('#delete_category_name').text(name);
        
        if (count > 0) {
            $('#delete_warning').removeClass('d-none');
            $('#delete_warning_text').text('Danh mục này có ' + count + ' sản phẩm. Bạn cần chuyển sản phẩm sang danh mục khác trước khi xóa.');
            $('#confirmDeleteBtn').prop('disabled', true);
        } else {
            $('#delete_warning').addClass('d-none');
            $('#confirmDeleteBtn').prop('disabled', false);
        }
        
        $('#deleteCategoryModal').modal('show');
    });
});

function viewCategoryDetails(categoryId) {
    window.location.href = '<?php echo BASE_URL; ?>index.php?action=products&category=' + categoryId;
}
</script>
