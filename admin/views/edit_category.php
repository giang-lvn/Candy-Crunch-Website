<?php
// admin/views/edit_category.php
// Xử lý sửa danh mục

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $oldCategoryId = trim($_POST['old_category_id'] ?? '');
    $newCategoryId = trim($_POST['category_id'] ?? '');
    $categoryName = trim($_POST['category_name'] ?? '');
    
    try {
        // Validate
        if (empty($oldCategoryId)) {
            throw new Exception('Mã danh mục cũ không hợp lệ');
        }
        if (empty($newCategoryId)) {
            throw new Exception('Mã danh mục mới không được để trống');
        }
        if (empty($categoryName)) {
            throw new Exception('Tên danh mục không được để trống');
        }
        
        // Kiểm tra số lượng sản phẩm trong danh mục
        $checkProducts = $pdo->prepare("SELECT COUNT(*) FROM PRODUCT WHERE CategoryID = ?");
        $checkProducts->execute([$oldCategoryId]);
        $productCount = $checkProducts->fetchColumn();
        
        // Nếu có sản phẩm và muốn đổi mã danh mục -> không cho phép
        if ($productCount > 0 && $newCategoryId !== $oldCategoryId) {
            throw new Exception('Không thể thay đổi mã danh mục vì có ' . $productCount . ' sản phẩm trong danh mục này. Bạn chỉ có thể đổi tên danh mục.');
        }
        
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Kiểm tra nếu mã danh mục mới đã tồn tại (và khác với mã cũ)
        if ($newCategoryId !== $oldCategoryId) {
            $checkExist = $pdo->prepare("SELECT CategoryID FROM CATEGORY WHERE CategoryID = ?");
            $checkExist->execute([$newCategoryId]);
            if ($checkExist->fetch()) {
                throw new Exception('Mã danh mục "' . $newCategoryId . '" đã tồn tại. Vui lòng chọn mã khác.');
            }
            
            // Cập nhật bảng CATEGORY với mã mới (vì không có sản phẩm nên không cần update PRODUCT)
            $stmt = $pdo->prepare("
                UPDATE CATEGORY 
                SET CategoryID = :newId, CategoryName = :name
                WHERE CategoryID = :oldId
            ");
            $stmt->execute([
                'oldId' => $oldCategoryId,
                'newId' => $newCategoryId,
                'name' => $categoryName
            ]);
        } else {
            // Chỉ cập nhật tên nếu mã không đổi
            $stmt = $pdo->prepare("
                UPDATE CATEGORY 
                SET CategoryName = :name
                WHERE CategoryID = :id
            ");
            $stmt->execute([
                'id' => $oldCategoryId,
                'name' => $categoryName
            ]);
        }
        
        $pdo->commit();
        $success = true;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Nếu thành công, redirect bằng JavaScript
if ($success): ?>
<script>
    window.location.href = '<?php echo BASE_URL; ?>index.php?action=categories&updated=1';
</script>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    Đã cập nhật danh mục thành công! Đang chuyển hướng...
</div>
<?php elseif ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <br>
    <a href="<?php echo BASE_URL; ?>index.php?action=categories" class="btn btn-sm btn-outline-danger mt-2">
        Quay lại
    </a>
</div>
<?php else: ?>
<script>
    window.location.href = '<?php echo BASE_URL; ?>index.php?action=categories';
</script>
<?php endif; ?>
