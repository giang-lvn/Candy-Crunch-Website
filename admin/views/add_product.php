<?php
// admin/views/add_product.php
// Xử lý form submit
$message = '';
$messageType = '';

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT CategoryID, CategoryName FROM CATEGORY ORDER BY CategoryName")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    try {
        $pdo->beginTransaction();
        
        // 1. Lấy ProductID từ form (nhập thủ công)
        $newProductId = trim($_POST['product_id'] ?? '');
        
        // Validate ProductID
        if (empty($newProductId)) {
            throw new Exception('Mã sản phẩm (ProductID) không được để trống');
        }
        
        // Kiểm tra ProductID đã tồn tại chưa
        $checkProduct = $pdo->prepare("SELECT ProductID FROM PRODUCT WHERE ProductID = ?");
        $checkProduct->execute([$newProductId]);
        if ($checkProduct->fetch()) {
            throw new Exception('Mã sản phẩm "' . $newProductId . '" đã tồn tại. Vui lòng chọn mã khác.');
        }
        
        // 2. Lấy dữ liệu PRODUCT từ form
        $productName = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $flavour = trim($_POST['flavour'] ?? '');
        $ingredient = trim($_POST['ingredient'] ?? '');
        $categoryId = $_POST['category_id'] ?? null;
        $filter = trim($_POST['filter'] ?? '');
        
        // Validate
        if (empty($productName)) {
            throw new Exception('Tên sản phẩm không được để trống');
        }
        
        // 3. INSERT vào bảng PRODUCT
        $stmt = $pdo->prepare("
            INSERT INTO PRODUCT (ProductID, ProductName, Description, Unit, Flavour, Ingredient, CategoryID, Filter)
            VALUES (:productId, :productName, :description, :unit, :flavour, :ingredient, :categoryId, :filter)
        ");
        $stmt->execute([
            'productId' => $newProductId,
            'productName' => $productName,
            'description' => $description,
            'unit' => $unit,
            'flavour' => $flavour,
            'ingredient' => $ingredient,
            'categoryId' => $categoryId ?: null,
            'filter' => $filter
        ]);
        
        // 4. Xử lý các SKU
        $skuIds = $_POST['sku_id'] ?? [];
        $skuAttributes = $_POST['sku_attribute'] ?? [];
        $skuOriginalPrices = $_POST['sku_original_price'] ?? [];
        $skuPromotionPrices = $_POST['sku_promotion_price'] ?? [];
        $skuStocks = $_POST['sku_stock'] ?? [];
        $skuStatuses = $_POST['sku_status'] ?? [];
        
        // Upload directory cho ảnh SKU
        $uploadDir = __DIR__ . '/../../views/website/img/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($skuIds as $index => $skuId) {
            $skuId = trim($skuId);
            $attribute = trim($skuAttributes[$index] ?? '');
            
            if (empty($skuId) || empty($attribute)) continue;
            
            // Kiểm tra SKUID đã tồn tại chưa
            $checkSku = $pdo->prepare("SELECT SKUID FROM SKU WHERE SKUID = ?");
            $checkSku->execute([$skuId]);
            if ($checkSku->fetch()) {
                throw new Exception('Mã SKU "' . $skuId . '" đã tồn tại. Vui lòng chọn mã khác.');
            }
            
            // Tự động tạo InventoryID mới
            $lastInventory = $pdo->query("SELECT InventoryID FROM INVENTORY ORDER BY InventoryID DESC LIMIT 1")->fetch();
            if ($lastInventory) {
                $lastInvNum = intval(substr($lastInventory['InventoryID'], 3));
                $newInventoryId = 'INV' . str_pad($lastInvNum + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newInventoryId = 'INV001';
            }
            
            // INSERT vào INVENTORY
            $stock = intval($skuStocks[$index] ?? 0);
            $status = $skuStatuses[$index] ?? 'Available';
            
            $stmtInv = $pdo->prepare("
                INSERT INTO INVENTORY (InventoryID, Stock, InventoryStatus)
                VALUES (:inventoryId, :stock, :status)
            ");
            $stmtInv->execute([
                'inventoryId' => $newInventoryId,
                'stock' => $stock,
                'status' => $status
            ]);
            
            // Xử lý upload ảnh
            $imagePath = '';
            if (isset($_FILES['sku_image']['name'][$index]) && !empty($_FILES['sku_image']['name'][$index])) {
                $fileName = $_FILES['sku_image']['name'][$index];
                $tmpName = $_FILES['sku_image']['tmp_name'][$index];
                $fileError = $_FILES['sku_image']['error'][$index];
                
                if ($fileError === UPLOAD_ERR_OK) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = $skuId . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imagePath = '/Candy-Crunch-Website/views/website/img/products/' . $newFileName;
                    }
                }
            }
            
            // INSERT vào SKU
            $originalPrice = floatval($skuOriginalPrices[$index] ?? 0);
            $promotionPrice = !empty($skuPromotionPrices[$index]) ? floatval($skuPromotionPrices[$index]) : null;
            
            $stmtSku = $pdo->prepare("
                INSERT INTO SKU (SKUID, ProductID, InventoryID, Attribute, OriginalPrice, PromotionPrice, Image)
                VALUES (:skuId, :productId, :inventoryId, :attribute, :originalPrice, :promotionPrice, :image)
            ");
            $stmtSku->execute([
                'skuId' => $skuId,
                'productId' => $newProductId,
                'inventoryId' => $newInventoryId,
                'attribute' => $attribute,
                'originalPrice' => $originalPrice,
                'promotionPrice' => $promotionPrice,
                'image' => $imagePath
            ]);
        }
        
        $pdo->commit();
        $message = 'Thêm sản phẩm thành công! Mã sản phẩm: ' . $newProductId;
        $messageType = 'success';
        
        // Redirect về trang products sau 2 giây
        header("Refresh: 2; url=" . BASE_URL . "index.php?action=products");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Thêm sản phẩm mới</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=products">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<!-- Thông báo -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Form -->
<form method="POST" enctype="multipart/form-data" id="addProductForm">
    <div class="row">
        <!-- Cột trái: Thông tin sản phẩm -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-box me-2"></i>Thông tin sản phẩm</h6>
                </div>
                <div class="card-body">
                    <!-- Mã sản phẩm (ProductID) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã sản phẩm (ProductID) <span class="text-danger">*</span></label>
                        <input type="text" name="product_id" class="form-control" required 
                               placeholder="CHG-001" value="<?php echo htmlspecialchars($_POST['product_id'] ?? ''); ?>">
                        <small class="text-muted">Nhập mã sản phẩm duy nhất. Mã này không thể trùng với sản phẩm đã có.</small>
                    </div>
                    
                    <!-- Tên sản phẩm -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control" required 
                               placeholder="Nhập tên sản phẩm..." value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>">
                    </div>
                    
                    <!-- Danh mục -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['CategoryID']; ?>"
                                <?php echo (($_POST['category_id'] ?? '') == $cat['CategoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['CategoryName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Mô tả -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Mô tả chi tiết sản phẩm..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Unit -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đơn vị</label>
                        <select name="unit" class="form-select">
                            <option value="">-- Chọn đơn vị --</option>
                            <option value="Packet" <?php echo ($_POST['unit'] ?? '') === 'Packet' ? 'selected' : ''; ?>>Packet</option>
                            <option value="Stick" <?php echo ($_POST['unit'] ?? '') === 'Stick' ? 'selected' : ''; ?>>Stick</option>
                        </select>
                    </div>
                    
                    <!-- Hương vị -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hương vị</label>
                        <input type="text" name="flavour" class="form-control" 
                               placeholder="VD: Dâu, Socola, Vani..." value="<?php echo htmlspecialchars($_POST['flavour'] ?? ''); ?>">
                    </div>
                    
                    <!-- Thành phần -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thành phần</label>
                        <textarea name="ingredient" class="form-control" rows="2" 
                                  placeholder="Liệt kê thành phần..."><?php echo htmlspecialchars($_POST['ingredient'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter/Tags</label>
                        <select name="filter" class="form-select">
                            <option value="">-- Chọn nhãn sản phẩm --</option>
                            <option value="New products" <?php echo ($_POST['filter'] ?? '') === 'New products' ? 'selected' : ''; ?>>New products</option>
                            <option value="Best-seller" <?php echo ($_POST['filter'] ?? '') === 'Best-seller' ? 'selected' : ''; ?>>Best-seller</option>
                            <option value="On sales" <?php echo ($_POST['filter'] ?? '') === 'On sales' ? 'selected' : ''; ?>>On sales</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cột phải: SKU -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Quản lý SKU</h6>
                    <button type="button" class="btn btn-light btn-sm" id="addSkuBtn">
                        <i class="bi bi-plus-circle me-1"></i>Thêm SKU
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Mỗi SKU đại diện cho một biến thể của sản phẩm.
                        Bạn cần thêm ít nhất 1 SKU cho mỗi sản phẩm.
                    </div>
                    
                    <!-- Container cho các SKU -->
                    <div id="skuContainer">
                        <!-- SKU mặc định -->
                        <div class="sku-item card bg-light mb-3" data-sku-index="0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="bi bi-tag me-2"></i>SKU #1
                                    </h6>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-sku-btn" disabled title="Không thể xóa SKU đầu tiên">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                
                                <div class="row">
                                    <!-- Mã SKU -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Mã SKU (SKUID) <span class="text-danger">*</span></label>
                                        <input type="text" name="sku_id[]" class="form-control" required
                                               placeholder="CHG-001-100">
                                        <small class="text-muted">Mã SKU phải duy nhất</small>
                                    </div>
                                    
                                    <!-- Thuộc tính -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Thuộc tính <span class="text-danger">*</span></label>
                                        <input type="text" name="sku_attribute[]" class="form-control" required
                                               placeholder="100g">
                                    </div>
                                    
                                    <!-- Tồn kho -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Số lượng tồn kho</label>
                                        <input type="number" name="sku_stock[]" class="form-control" 
                                               value="0" min="0" placeholder="0">
                                    </div>
                                    
                                    <!-- Giá gốc -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Giá gốc (VNĐ) <span class="text-danger">*</span></label>
                                        <input type="number" name="sku_original_price[]" class="form-control" required
                                               min="0" step="1000" placeholder="0">
                                    </div>
                                    
                                    <!-- Giá khuyến mãi -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Giá khuyến mãi (VNĐ)</label>
                                        <input type="number" name="sku_promotion_price[]" class="form-control" 
                                               min="0" step="1000" placeholder="Để trống nếu không giảm giá">
                                    </div>
                                    
                                    <!-- Trạng thái -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Trạng thái kho</label>
                                        <select name="sku_status[]" class="form-select">
                                            <option value="Available">Còn hàng (Available)</option>
                                            <option value="Out of stock">Hết hàng (Out of stock)</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Ảnh -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Ảnh sản phẩm</label>
                                        <input type="file" name="sku_image[]" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Nút submit -->
            <div class="mt-4 text-end">
                <button type="reset" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-x-circle me-2"></i>Xóa form
                </button>
                <button type="submit" name="save_product" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Lưu sản phẩm
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Template cho SKU mới -->
<template id="skuTemplate">
    <div class="sku-item card bg-light mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary mb-0">
                    <i class="bi bi-tag me-2"></i>SKU #<span class="sku-number">X</span>
                </h6>
                <button type="button" class="btn btn-outline-danger btn-sm remove-sku-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Mã SKU (SKUID) <span class="text-danger">*</span></label>
                    <input type="text" name="sku_id[]" class="form-control sku-id-input" required
                           placeholder="VD: SKU001, CANDY001-100G...">
                    <small class="text-muted">Mã SKU phải duy nhất</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Thuộc tính <span class="text-danger">*</span></label>
                    <input type="text" name="sku_attribute[]" class="form-control" required
                           placeholder="VD: 100g, Size M, Màu đỏ...">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Số lượng tồn kho</label>
                    <input type="number" name="sku_stock[]" class="form-control" 
                           value="0" min="0" placeholder="0">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Giá gốc (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="sku_original_price[]" class="form-control" required
                           min="0" step="1000" placeholder="0">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Giá khuyến mãi (VNĐ)</label>
                    <input type="number" name="sku_promotion_price[]" class="form-control" 
                           min="0" step="1000" placeholder="Để trống nếu không giảm giá">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Trạng thái kho</label>
                    <select name="sku_status[]" class="form-select">
                        <option value="Available">Còn hàng (Available)</option>
                        <option value="Out of stock">Hết hàng (Out of stock)</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Ảnh sản phẩm</label>
                    <input type="file" name="sku_image[]" class="form-control" accept="image/*">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
$(document).ready(function() {
    let skuCount = 1;
    
    // Thêm SKU mới
    $('#addSkuBtn').click(function() {
        skuCount++;
        
        const template = document.getElementById('skuTemplate');
        const clone = template.content.cloneNode(true);
        
        // Cập nhật số thứ tự
        clone.querySelector('.sku-number').textContent = skuCount;
        clone.querySelector('.sku-item').dataset.skuIndex = skuCount - 1;
        
        // Thêm vào container
        document.getElementById('skuContainer').appendChild(clone);
        
        // Scroll đến SKU mới
        const newSku = document.querySelector('.sku-item:last-child');
        newSku.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    
    // Xóa SKU
    $(document).on('click', '.remove-sku-btn:not([disabled])', function() {
        const skuItem = $(this).closest('.sku-item');
        
        if (confirm('Bạn có chắc muốn xóa SKU này?')) {
            skuItem.fadeOut(300, function() {
                $(this).remove();
                
                // Cập nhật lại số thứ tự
                $('.sku-item').each(function(index) {
                    $(this).find('.sku-number').text(index + 1);
                    $(this).data('sku-index', index);
                });
                
                skuCount = $('.sku-item').length;
            });
        }
    });
    
    // Validate form trước khi submit
    $('#addProductForm').on('submit', function(e) {
        const skuItems = $('.sku-item');
        
        if (skuItems.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất 1 SKU cho sản phẩm!');
            return false;
        }
        
        // Kiểm tra các trường required trong SKU
        let valid = true;
        let skuIdList = [];
        
        skuItems.each(function() {
            const skuId = $(this).find('input[name="sku_id[]"]').val();
            const attr = $(this).find('input[name="sku_attribute[]"]').val();
            const price = $(this).find('input[name="sku_original_price[]"]').val();
            
            if (!skuId || !attr || !price) {
                valid = false;
                $(this).addClass('border-danger');
            } else {
                $(this).removeClass('border-danger');
            }
            
            // Kiểm tra SKUID trùng lặp trong form
            if (skuId) {
                if (skuIdList.includes(skuId)) {
                    valid = false;
                    $(this).addClass('border-danger');
                    alert('Mã SKU "' + skuId + '" bị trùng lặp trong form!');
                }
                skuIdList.push(skuId);
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin cho tất cả các SKU (Mã SKU, Thuộc tính, Giá gốc)!');
            return false;
        }
    });
});
</script>

<style>
.sku-item {
    transition: all 0.3s ease;
}
.sku-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.sku-item.border-danger {
    border: 2px solid #dc3545 !important;
}
</style>
