<?php
// admin/views/edit_product.php
// Chỉnh sửa sản phẩm với hỗ trợ nhiều ảnh cho SKU

// 1. Lấy Product ID từ URL
$productId = $_GET['id'] ?? '';
if (empty($productId)) {
    echo '<div class="alert alert-danger">Không tìm thấy mã sản phẩm</div>';
    exit;
}

// 2. Load thông tin sản phẩm
$stmt = $pdo->prepare("SELECT * FROM PRODUCT WHERE ProductID = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="alert alert-danger">Sản phẩm không tồn tại</div>';
    exit;
}

// 3. Load danh sách SKU của sản phẩm
$stmtSku = $pdo->prepare("
    SELECT s.*, i.Stock, i.InventoryStatus 
    FROM SKU s
    JOIN INVENTORY i ON s.InventoryID = i.InventoryID
    WHERE s.ProductID = ?
    ORDER BY s.SKUID ASC
");
$stmtSku->execute([$productId]);
$skus = $stmtSku->fetchAll();

// 4. Load danh mục (cho dropdown)
$categories = $pdo->query("SELECT CategoryID, CategoryName FROM CATEGORY ORDER BY CategoryName")->fetchAll();

$message = '';
$messageType = '';

// Helper function to parse images from JSON
function parseSkuImagesEdit($imageData) {
    if (empty($imageData)) return [];
    
    $decoded = json_decode($imageData, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    
    // Old format: single image path - convert to new format
    return [['path' => $imageData, 'is_thumbnail' => true]];
}

// 5. Xử lý lưu form (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    try {
        $pdo->beginTransaction();
        
        // --- Cập nhật bảng PRODUCT ---
        $productName = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $flavour = trim($_POST['flavour'] ?? '');
        $ingredient = trim($_POST['ingredient'] ?? '');
        $categoryId = $_POST['category_id'] ?? null;
        $filter = trim($_POST['filter'] ?? '');
        
        if (empty($productName)) throw new Exception('Tên sản phẩm không được để trống');
        
        $updateProduct = $pdo->prepare("
            UPDATE PRODUCT 
            SET ProductName = :name, 
                Description = :desc, 
                Unit = :unit, 
                Flavour = :flavour, 
                Ingredient = :ingredient, 
                CategoryID = :catId,
                Filter = :filter
            WHERE ProductID = :id
        ");
        $updateProduct->execute([
            'name' => $productName,
            'desc' => $description,
            'unit' => $unit,
            'flavour' => $flavour,
            'ingredient' => $ingredient,
            'catId' => $categoryId,
            'filter' => $filter,
            'id' => $productId
        ]);
        
        // --- Xử lý SKU ---
        $formSkuIds = $_POST['sku_id'] ?? [];
        $formAttributes = $_POST['sku_attribute'] ?? [];
        $formStocks = $_POST['sku_stock'] ?? [];
        $formOriginalPrices = $_POST['sku_original_price'] ?? [];
        $formPromotionPrices = $_POST['sku_promotion_price'] ?? [];
        $formExistingImages = $_POST['existing_images'] ?? []; // JSON array of existing images per SKU
        $formThumbnails = $_POST['sku_thumbnail'] ?? []; // Selected thumbnail index per SKU
        $formDeleteImages = $_POST['delete_images'] ?? []; // Images to delete per SKU
        
        // Upload dir
        $uploadDir = __DIR__ . '/../../views/website/img/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Danh sách SKUID hiện có trong DB
        $existingSkuIds = array_column($skus, 'SKUID');
        $processedSkuIds = [];
        
        foreach ($formSkuIds as $index => $skuId) {
            $skuId = trim($skuId);
            if (empty($skuId)) continue;
            
            $processedSkuIds[] = $skuId;
            
            $attribute = trim($formAttributes[$index] ?? '');
            $stock = intval($formStocks[$index] ?? 0);
            $originalPrice = floatval($formOriginalPrices[$index] ?? 0);
            $promotionPrice = !empty($formPromotionPrices[$index]) ? floatval($formPromotionPrices[$index]) : null;
            
            // Tính trạng thái tồn kho
            if ($stock >= 20) $status = 'Available';
            elseif ($stock > 0) $status = 'Low in stock';
            else $status = 'Out of stock';
            
            // Xử lý ảnh
            // 1. Lấy ảnh hiện có từ form (đã loại bỏ ảnh bị xóa)
            $existingImagesJson = $formExistingImages[$index] ?? '[]';
            $currentImages = json_decode($existingImagesJson, true) ?: [];
            
            // 2. Xử lý ảnh bị xóa
            $deleteImagesJson = $formDeleteImages[$index] ?? '[]';
            $imagesToDelete = json_decode($deleteImagesJson, true) ?: [];
            
            // Xóa file ảnh khỏi server
            foreach ($imagesToDelete as $imgPath) {
                $filePath = __DIR__ . '/../../' . str_replace('/Candy-Crunch-Website/', '', $imgPath);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            // Loại bỏ ảnh đã xóa khỏi danh sách hiện có
            $currentImages = array_filter($currentImages, function($img) use ($imagesToDelete) {
                $path = is_array($img) ? $img['path'] : $img;
                return !in_array($path, $imagesToDelete);
            });
            $currentImages = array_values($currentImages);
            
            // 3. Xử lý upload ảnh mới (tối đa 5 ảnh)
            if (isset($_FILES['sku_images']['name'][$index])) {
                $uploadedFiles = $_FILES['sku_images']['name'][$index];
                
                foreach ($uploadedFiles as $fileIndex => $fileName) {
                    if (empty($fileName)) continue;
                    if (count($currentImages) >= 5) break; // Giới hạn 5 ảnh
                    
                    $tmpName = $_FILES['sku_images']['tmp_name'][$index][$fileIndex];
                    $error = $_FILES['sku_images']['error'][$index][$fileIndex];
                    
                    if ($error === UPLOAD_ERR_OK) {
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newFileName = $skuId . '_' . time() . '_' . $fileIndex . '.' . $ext;
                        
                        if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                            $imagePath = '/Candy-Crunch-Website/views/website/img/products/' . $newFileName;
                            $currentImages[] = [
                                'path' => $imagePath,
                                'is_thumbnail' => false
                            ];
                        }
                    }
                }
            }
            
            // 4. Đặt thumbnail
            $selectedThumbnail = isset($formThumbnails[$index]) ? intval($formThumbnails[$index]) : 0;
            foreach ($currentImages as $imgIndex => &$img) {
                if (is_array($img)) {
                    $img['is_thumbnail'] = ($imgIndex === $selectedThumbnail);
                } else {
                    // Convert old format to new format
                    $currentImages[$imgIndex] = [
                        'path' => $img,
                        'is_thumbnail' => ($imgIndex === $selectedThumbnail)
                    ];
                }
            }
            unset($img);
            
            // Nếu không có thumbnail được chọn và có ảnh, đặt ảnh đầu tiên làm thumbnail
            if (!empty($currentImages)) {
                $hasThumbnail = false;
                foreach ($currentImages as $img) {
                    if ($img['is_thumbnail']) {
                        $hasThumbnail = true;
                        break;
                    }
                }
                if (!$hasThumbnail) {
                    $currentImages[0]['is_thumbnail'] = true;
                }
            }
            
            // Convert to JSON
            $imagesJson = !empty($currentImages) ? json_encode($currentImages) : '';
            
            // Kiểm tra xem SKU này đã tồn tại chưa
            $checkSku = $pdo->prepare("SELECT InventoryID FROM SKU WHERE SKUID = ?");
            $checkSku->execute([$skuId]);
            $currentSku = $checkSku->fetch();
            
            if ($currentSku) {
                // --- UPDATE SKU ĐÃ CÓ ---
                $inventoryId = $currentSku['InventoryID'];
                
                // Update INVENTORY
                $updateInv = $pdo->prepare("UPDATE INVENTORY SET Stock = ?, InventoryStatus = ? WHERE InventoryID = ?");
                $updateInv->execute([$stock, $status, $inventoryId]);
                
                // Update SKU
                $stmtUpdateSku = $pdo->prepare("
                    UPDATE SKU 
                    SET Attribute = ?, OriginalPrice = ?, PromotionPrice = ?, Image = ?
                    WHERE SKUID = ?
                ");
                $stmtUpdateSku->execute([$attribute, $originalPrice, $promotionPrice, $imagesJson, $skuId]);
                
            } else {
                // --- INSERT SKU MỚI ---
                $lastInv = $pdo->query("SELECT InventoryID FROM INVENTORY WHERE InventoryID LIKE 'IVEN%' ORDER BY CAST(SUBSTRING(InventoryID, 5) AS UNSIGNED) DESC LIMIT 1")->fetch();
                if ($lastInv) {
                    $num = intval(substr($lastInv['InventoryID'], 4)) + 1;
                    $newInvId = 'IVEN' . str_pad($num, 3, '0', STR_PAD_LEFT);
                } else {
                    $newInvId = 'IVEN001';
                }
                
                // Insert INVENTORY
                $pdo->prepare("INSERT INTO INVENTORY (InventoryID, Stock, InventoryStatus) VALUES (?, ?, ?)")
                    ->execute([$newInvId, $stock, $status]);
                    
                // Insert SKU
                $pdo->prepare("INSERT INTO SKU (SKUID, ProductID, InventoryID, Attribute, OriginalPrice, PromotionPrice, Image) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$skuId, $productId, $newInvId, $attribute, $originalPrice, $promotionPrice, $imagesJson]);
            }
        }
        
        // --- XÓA SKU BỊ LOẠI KHỎI FORM ---
        $diff = array_diff($existingSkuIds, $processedSkuIds);
        foreach ($diff as $removedSkuId) {
            $checkOrder = $pdo->prepare("SELECT COUNT(*) FROM ORDER_DETAIL WHERE SKUID = ?");
            $checkOrder->execute([$removedSkuId]);
            if ($checkOrder->fetchColumn() > 0) {
                throw new Exception("Không thể xóa SKU $removedSkuId vì đã có đơn hàng sử dụng.");
            }
             
            // Xóa
            $invIdToDelete = $pdo->query("SELECT InventoryID FROM SKU WHERE SKUID = '$removedSkuId'")->fetchColumn();
            $pdo->prepare("DELETE FROM SKU WHERE SKUID = ?")->execute([$removedSkuId]);
            if ($invIdToDelete) {
                $pdo->prepare("DELETE FROM INVENTORY WHERE InventoryID = ?")->execute([$invIdToDelete]);
            }
        }
        
        $pdo->commit();
        $message = 'Cập nhật sản phẩm thành công!';
        $messageType = 'success';
        
        // Reload lại dữ liệu mới để hiển thị
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        $stmtSku->execute([$productId]);
        $skus = $stmtSku->fetchAll();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Chỉnh sửa sản phẩm: <?php echo htmlspecialchars($product['ProductID']); ?></h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=products">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?action=view_product&id=<?php echo htmlspecialchars($product['ProductID']); ?>" 
           class="btn btn-outline-info me-2">
            <i class="bi bi-eye me-2"></i>Xem chi tiết
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="editProductForm">
    <div class="row">
        <!-- Cột trái: Thông tin chung -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-dark"><i class="bi bi-pencil-square me-2"></i>Thông tin chung</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã sản phẩm</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['ProductID']); ?>" disabled>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['ProductID']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control" required value="<?php echo htmlspecialchars($product['ProductName']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['CategoryID']; ?>"
                                <?php echo ($product['CategoryID'] == $cat['CategoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['CategoryName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                     <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['Description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đơn vị</label>
                        <select name="unit" class="form-select">
                            <option value="Packet" <?php echo ($product['Unit'] == 'Packet') ? 'selected' : ''; ?>>Packet</option>
                            <option value="Stick" <?php echo ($product['Unit'] == 'Stick') ? 'selected' : ''; ?>>Stick</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hương vị</label>
                        <input type="text" name="flavour" class="form-control" value="<?php echo htmlspecialchars($product['Flavour']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thành phần</label>
                        <textarea name="ingredient" class="form-control" rows="2"><?php echo htmlspecialchars($product['Ingredient']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter/Tags</label>
                        <select name="filter" class="form-select">
                            <option value="">-- Chọn nhãn --</option>
                            <option value="New products" <?php echo ($product['Filter'] == 'New products') ? 'selected' : ''; ?>>New products</option>
                            <option value="Best-seller" <?php echo ($product['Filter'] == 'Best-seller') ? 'selected' : ''; ?>>Best-seller</option>
                            <option value="On sales" <?php echo ($product['Filter'] == 'On sales') ? 'selected' : ''; ?>>On sales</option>
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
                    <button type="button" class="btn btn-light btn-sm" onclick="addSku()">
                        <i class="bi bi-plus-circle me-1"></i>Thêm SKU
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Mỗi SKU có thể có tối đa <strong>5 ảnh</strong>. Chọn một ảnh làm <strong>thumbnail</strong> bằng cách click vào ảnh.
                    </div>
                    
                    <div id="skuContainer">
                        <?php foreach ($skus as $index => $sku): 
                            $skuImages = parseSkuImagesEdit($sku['Image']);
                        ?>
                        <div class="sku-item card bg-light mb-3" data-sku-index="<?php echo $index; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0"><i class="bi bi-tag me-2"></i>SKU #<?php echo $index + 1; ?></h6>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSku(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Mã SKU</label>
                                        <input type="text" name="sku_id[]" class="form-control" value="<?php echo htmlspecialchars($sku['SKUID']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Thuộc tính</label>
                                        <input type="text" name="sku_attribute[]" class="form-control" value="<?php echo htmlspecialchars($sku['Attribute']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Tồn kho</label>
                                        <input type="number" name="sku_stock[]" class="form-control" value="<?php echo $sku['Stock']; ?>" min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Giá gốc</label>
                                        <input type="number" name="sku_original_price[]" class="form-control" value="<?php echo $sku['OriginalPrice']; ?>" required step="1000">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-semibold">Giá khuyến mãi</label>
                                        <input type="number" name="sku_promotion_price[]" class="form-control" value="<?php echo $sku['PromotionPrice']; ?>" step="1000">
                                    </div>
                                    
                                    <!-- Multi-image upload section -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label small fw-semibold">
                                            Ảnh sản phẩm 
                                            <span class="text-muted">(Tối đa 5 ảnh, click vào ảnh để đặt làm thumbnail)</span>
                                        </label>
                                        
                                        <!-- Existing images grid -->
                                        <div class="image-grid d-flex flex-wrap gap-2 mb-2" data-sku-index="<?php echo $index; ?>">
                                            <?php 
                                            $thumbnailIndex = 0;
                                            foreach ($skuImages as $imgIndex => $img): 
                                                $imgPath = is_array($img) ? $img['path'] : $img;
                                                $isThumbnail = is_array($img) && isset($img['is_thumbnail']) && $img['is_thumbnail'];
                                                if ($isThumbnail) $thumbnailIndex = $imgIndex;
                                            ?>
                                            <div class="image-item position-relative <?php echo $isThumbnail ? 'is-thumbnail' : ''; ?>" 
                                                 data-path="<?php echo htmlspecialchars($imgPath); ?>"
                                                 data-index="<?php echo $imgIndex; ?>">
                                                <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                                                     alt="SKU Image" 
                                                     class="rounded border"
                                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                                     onclick="setThumbnail(this, <?php echo $index; ?>)">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                                        style="top: -5px; right: -5px; padding: 0 5px; font-size: 10px;"
                                                        onclick="removeImage(this, '<?php echo htmlspecialchars($imgPath); ?>', <?php echo $index; ?>)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                                <?php if ($isThumbnail): ?>
                                                <span class="badge bg-warning text-dark position-absolute" style="bottom: 2px; left: 2px; font-size: 9px;">
                                                    <i class="bi bi-star-fill"></i>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Hidden inputs for tracking -->
                                        <input type="hidden" name="existing_images[]" class="existing-images-input" 
                                               value='<?php echo htmlspecialchars(json_encode($skuImages)); ?>'>
                                        <input type="hidden" name="sku_thumbnail[]" class="thumbnail-input" value="<?php echo $thumbnailIndex; ?>">
                                        <input type="hidden" name="delete_images[]" class="delete-images-input" value="[]">
                                        
                                        <!-- Upload new images -->
                                        <div class="upload-area mt-2">
                                            <input type="file" name="sku_images[<?php echo $index; ?>][]" 
                                                   class="form-control sku-image-input" 
                                                   accept="image/*" 
                                                   multiple
                                                   data-sku-index="<?php echo $index; ?>">
                                            <small class="text-muted">
                                                Còn có thể thêm <?php echo max(0, 5 - count($skuImages)); ?> ảnh nữa
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <button type="submit" name="save_product" class="btn btn-primary btn-lg">
                    <i class="bi bi-save me-2"></i>Lưu thay đổi
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
                <h6 class="text-primary mb-0"><i class="bi bi-tag me-2"></i>SKU #<span class="sku-number"></span> (Mới)</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSku(this)"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Mã SKU <span class="text-danger">*</span></label>
                    <input type="text" name="sku_id[]" class="form-control" required placeholder="Nhập mã SKU mới">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Thuộc tính <span class="text-danger">*</span></label>
                    <input type="text" name="sku_attribute[]" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Tồn kho</label>
                    <input type="number" name="sku_stock[]" class="form-control" value="0" min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Giá gốc <span class="text-danger">*</span></label>
                    <input type="number" name="sku_original_price[]" class="form-control" required step="1000">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Giá KM</label>
                    <input type="number" name="sku_promotion_price[]" class="form-control" step="1000">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label small fw-semibold">
                        Ảnh sản phẩm 
                        <span class="text-muted">(Tối đa 5 ảnh)</span>
                    </label>
                    <div class="image-grid d-flex flex-wrap gap-2 mb-2"></div>
                    <input type="hidden" name="existing_images[]" class="existing-images-input" value="[]">
                    <input type="hidden" name="sku_thumbnail[]" class="thumbnail-input" value="0">
                    <input type="hidden" name="delete_images[]" class="delete-images-input" value="[]">
                    <div class="upload-area mt-2">
                        <input type="file" name="sku_images[NEW_INDEX][]" class="form-control sku-image-input" accept="image/*" multiple>
                        <small class="text-muted">Có thể thêm tối đa 5 ảnh</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// Định nghĩa biến toàn cục
var skuCount = <?php echo count($skus); ?>;

function addSku() {
    skuCount++;
    var template = document.getElementById('skuTemplate');
    var clone = template.content.cloneNode(true);
    
    // Cập nhật số thứ tự
    clone.querySelector('.sku-number').textContent = skuCount;
    
    // Update file input name với index đúng
    var fileInput = clone.querySelector('.sku-image-input');
    fileInput.name = 'sku_images[' + (skuCount - 1) + '][]';
    
    // Thêm vào container
    document.getElementById('skuContainer').appendChild(clone);
}

function removeSku(btn) {
    if (confirm('Bạn muốn xóa SKU này? (Chỉ xóa được nếu chưa có đơn hàng)')) {
        var item = btn.closest('.sku-item');
        item.remove();
    }
}

function setThumbnail(img, skuIndex) {
    var container = img.closest('.sku-item');
    var imageGrid = container.querySelector('.image-grid');
    var thumbnailInput = container.querySelector('.thumbnail-input');
    var imageItem = img.closest('.image-item');
    var newIndex = parseInt(imageItem.dataset.index);
    
    // Remove current thumbnail marker
    var currentThumbnail = imageGrid.querySelector('.is-thumbnail');
    if (currentThumbnail) {
        currentThumbnail.classList.remove('is-thumbnail');
        var badge = currentThumbnail.querySelector('.badge');
        if (badge) badge.remove();
    }
    
    // Set new thumbnail
    imageItem.classList.add('is-thumbnail');
    if (!imageItem.querySelector('.badge')) {
        var badge = document.createElement('span');
        badge.className = 'badge bg-warning text-dark position-absolute';
        badge.style.cssText = 'bottom: 2px; left: 2px; font-size: 9px;';
        badge.innerHTML = '<i class="bi bi-star-fill"></i>';
        imageItem.appendChild(badge);
    }
    
    // Update hidden input
    thumbnailInput.value = newIndex;
    
    // Update existing images JSON
    updateExistingImagesJson(container);
}

function removeImage(btn, path, skuIndex) {
    if (!confirm('Xóa ảnh này?')) return;
    
    var container = btn.closest('.sku-item');
    var imageItem = btn.closest('.image-item');
    var deleteInput = container.querySelector('.delete-images-input');
    var existingInput = container.querySelector('.existing-images-input');
    
    // Add to delete list
    var deleteList = JSON.parse(deleteInput.value || '[]');
    deleteList.push(path);
    deleteInput.value = JSON.stringify(deleteList);
    
    // Check if this was thumbnail
    var wasThumbnail = imageItem.classList.contains('is-thumbnail');
    
    // Remove the image item
    imageItem.remove();
    
    // Update indices
    var imageGrid = container.querySelector('.image-grid');
    var remainingImages = imageGrid.querySelectorAll('.image-item');
    remainingImages.forEach(function(item, idx) {
        item.dataset.index = idx;
    });
    
    // If removed thumbnail, set first image as thumbnail
    if (wasThumbnail && remainingImages.length > 0) {
        var firstImg = remainingImages[0].querySelector('img');
        setThumbnail(firstImg, skuIndex);
    }
    
    // Update existing images JSON
    updateExistingImagesJson(container);
    
    // Update upload hint
    updateUploadHint(container);
}

function updateExistingImagesJson(container) {
    var imageGrid = container.querySelector('.image-grid');
    var existingInput = container.querySelector('.existing-images-input');
    var thumbnailInput = container.querySelector('.thumbnail-input');
    var thumbnailIndex = parseInt(thumbnailInput.value) || 0;
    
    var images = [];
    imageGrid.querySelectorAll('.image-item').forEach(function(item, idx) {
        images.push({
            path: item.dataset.path,
            is_thumbnail: (idx === thumbnailIndex)
        });
    });
    
    existingInput.value = JSON.stringify(images);
}

function updateUploadHint(container) {
    var imageGrid = container.querySelector('.image-grid');
    var currentCount = imageGrid.querySelectorAll('.image-item').length;
    var hint = container.querySelector('.upload-area small');
    if (hint) {
        var remaining = Math.max(0, 5 - currentCount);
        hint.textContent = 'Còn có thể thêm ' + remaining + ' ảnh nữa';
    }
}

// Preview new images before upload
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('sku-image-input')) {
        var input = e.target;
        var container = input.closest('.sku-item');
        var imageGrid = container.querySelector('.image-grid');
        var currentCount = imageGrid.querySelectorAll('.image-item').length;
        var maxImages = 5;
        
        if (input.files.length + currentCount > maxImages) {
            showToast('Chỉ có thể upload tối đa ' + maxImages + ' ảnh. Bạn đã có ' + currentCount + ' ảnh.', 'warning');
            input.value = '';
            return;
        }
    }
});
</script>

<style>
.sku-item {
    transition: all 0.3s ease;
}
.sku-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.image-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    overflow: visible;
}
.image-item:hover {
    transform: scale(1.05);
}
.image-item.is-thumbnail img {
    border: 3px solid #ffc107 !important;
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
}
.image-item .btn-danger {
    opacity: 0;
    transition: opacity 0.2s;
}
.image-item:hover .btn-danger {
    opacity: 1;
}
</style>
