<?php
// admin/views/edit_category.php
// Xử lý sửa danh mục

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoryId = trim($_POST['category_id'] ?? '');
    $categoryName = trim($_POST['category_name'] ?? '');
    
    try {
        // Validate
        if (empty($categoryId)) {
            throw new Exception('Mã danh mục không hợp lệ');
        }
        if (empty($categoryName)) {
            throw new Exception('Tên danh mục không được để trống');
        }
        
        // Update
        $stmt = $pdo->prepare("
            UPDATE CATEGORY 
            SET CategoryName = :name
            WHERE CategoryID = :id
        ");
        $stmt->execute([
            'id' => $categoryId,
            'name' => $categoryName
        ]);
        
        $success = true;
        
    } catch (Exception $e) {
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
