<?php
// admin/views/edit_product.php
// Chỉnh sửa sản phẩm - Ảnh được lưu ở cấp PRODUCT (không phải SKU)

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
function parseProductImagesEdit($imageData) {
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
        
        // --- Xử lý ảnh sản phẩm ---
        $uploadDir = __DIR__ . '/../../views/website/img/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // 1. Lấy ảnh hiện có từ form
        $existingImagesJson = $_POST['existing_product_images'] ?? '[]';
        $currentImages = json_decode($existingImagesJson, true) ?: [];
        
        // 2. Xử lý ảnh bị xóa
        $deleteImagesJson = $_POST['delete_product_images'] ?? '[]';
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
        if (isset($_FILES['product_images']['name'])) {
            foreach ($_FILES['product_images']['name'] as $fileIndex => $fileName) {
                if (empty($fileName)) continue;
                if (count($currentImages) >= 5) break;
                
                $tmpName = $_FILES['product_images']['tmp_name'][$fileIndex];
                $error = $_FILES['product_images']['error'][$fileIndex];
                
                if ($error === UPLOAD_ERR_OK) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = $productId . '_' . time() . '_' . $fileIndex . '.' . $ext;
                    
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
        $selectedThumbnail = isset($_POST['product_thumbnail']) ? intval($_POST['product_thumbnail']) : 0;
        foreach ($currentImages as $imgIndex => &$img) {
            if (is_array($img)) {
                $img['is_thumbnail'] = ($imgIndex === $selectedThumbnail);
            } else {
                $currentImages[$imgIndex] = [
                    'path' => $img,
                    'is_thumbnail' => ($imgIndex === $selectedThumbnail)
                ];
            }
        }
        unset($img);
        
        // Đảm bảo có ít nhất 1 thumbnail
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
        
        $productImageJson = !empty($currentImages) ? json_encode($currentImages) : null;
        
        // Update PRODUCT
        $updateProduct = $pdo->prepare("
            UPDATE PRODUCT 
            SET ProductName = :name, 
                Description = :desc, 
                Unit = :unit, 
                Flavour = :flavour, 
                Ingredient = :ingredient, 
                CategoryID = :catId,
                Filter = :filter,
                Image = :image
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
            'image' => $productImageJson,
            'id' => $productId
        ]);
        
        // --- Xử lý SKU (không còn xử lý ảnh ở đây) ---
        $formSkuIds = $_POST['sku_id'] ?? [];
        $formAttributes = $_POST['sku_attribute'] ?? [];
        $formStocks = $_POST['sku_stock'] ?? [];
        $formOriginalPrices = $_POST['sku_original_price'] ?? [];
        $formPromotionPrices = $_POST['sku_promotion_price'] ?? [];
        
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
                
                // Update SKU (không còn cột Image)
                $stmtUpdateSku = $pdo->prepare("
                    UPDATE SKU 
                    SET Attribute = ?, OriginalPrice = ?, PromotionPrice = ?
                    WHERE SKUID = ?
                ");
                $stmtUpdateSku->execute([$attribute, $originalPrice, $promotionPrice, $skuId]);
                
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
                    
                // Insert SKU (không còn cột Image)
                $pdo->prepare("INSERT INTO SKU (SKUID, ProductID, InventoryID, Attribute, OriginalPrice, PromotionPrice) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$skuId, $productId, $newInvId, $attribute, $originalPrice, $promotionPrice]);
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

// Parse product images for display
$productImages = parseProductImagesEdit($product['Image'] ?? '');
$thumbnailIndex = 0;
foreach ($productImages as $idx => $img) {
    if (is_array($img) && isset($img['is_thumbnail']) && $img['is_thumbnail']) {
        $thumbnailIndex = $idx;
        break;
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
        <!-- Cột trái: Thông tin chung + Ảnh sản phẩm -->
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
                    
                    <!-- Ảnh sản phẩm -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-images me-1"></i>Ảnh sản phẩm 
                            <span class="text-muted fw-normal">(Tối đa 5 ảnh, click để đặt thumbnail)</span>
                        </label>
                        
                        <!-- Existing images grid -->
                        <div class="image-grid d-flex flex-wrap gap-2 mb-2" id="productImageGrid">
                            <?php foreach ($productImages as $imgIndex => $img): 
                                $imgPath = is_array($img) ? $img['path'] : $img;
                                $isThumbnail = is_array($img) && isset($img['is_thumbnail']) && $img['is_thumbnail'];
                            ?>
                            <div class="image-item position-relative <?php echo $isThumbnail ? 'is-thumbnail' : ''; ?>" 
                                 data-path="<?php echo htmlspecialchars($imgPath); ?>"
                                 data-index="<?php echo $imgIndex; ?>">
                                <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                                     alt="Product Image" 
                                     class="rounded border"
                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                     onclick="setProductThumbnail(this)">
                                <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                        style="top: -5px; right: -5px; padding: 0 5px; font-size: 10px;"
                                        onclick="removeProductImage(this, '<?php echo htmlspecialchars($imgPath); ?>')">
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
                        <input type="hidden" name="existing_product_images" id="existingProductImagesInput" 
                               value='<?php echo htmlspecialchars(json_encode($productImages)); ?>'>
                        <input type="hidden" name="product_thumbnail" id="productThumbnailInput" value="<?php echo $thumbnailIndex; ?>">
                        <input type="hidden" name="delete_product_images" id="deleteProductImagesInput" value="[]">
                        
                        <!-- Upload new images -->
                        <div class="upload-area mt-2">
                            <input type="file" name="product_images[]" class="form-control" accept="image/*" multiple id="productImagesInput">
                            <small class="text-muted" id="uploadHint">
                                Còn có thể thêm <?php echo max(0, 5 - count($productImages)); ?> ảnh nữa
                            </small>
                        </div>
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
                        Mỗi SKU đại diện cho một biến thể của sản phẩm. Bạn cần thêm ít nhất 1 SKU cho mỗi sản phẩm. 
                    </div>
                    
                    <div id="skuContainer">
                        <?php foreach ($skus as $index => $sku): ?>
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
            </div>
        </div>
    </div>
</template>

<script>
var skuCount = <?php echo count($skus); ?>;

function addSku() {
    skuCount++;
    var template = document.getElementById('skuTemplate');
    var clone = template.content.cloneNode(true);
    
    clone.querySelector('.sku-number').textContent = skuCount;
    document.getElementById('skuContainer').appendChild(clone);
}

function removeSku(btn) {
    if (confirm('Bạn muốn xóa SKU này? (Chỉ xóa được nếu chưa có đơn hàng)')) {
        var item = btn.closest('.sku-item');
        item.remove();
    }
}

// Product image management
function setProductThumbnail(img) {
    var imageItem = img.closest('.image-item');
    var grid = document.getElementById('productImageGrid');
    var newIndex = parseInt(imageItem.dataset.index);
    
    // Remove current thumbnail marker
    grid.querySelectorAll('.image-item').forEach(function(item, idx) {
        item.classList.remove('is-thumbnail');
        var badge = item.querySelector('.badge');
        if (badge) badge.remove();
    });
    
    // Set new thumbnail
    imageItem.classList.add('is-thumbnail');
    var badge = document.createElement('span');
    badge.className = 'badge bg-warning text-dark position-absolute';
    badge.style.cssText = 'bottom: 2px; left: 2px; font-size: 9px;';
    badge.innerHTML = '<i class="bi bi-star-fill"></i>';
    imageItem.appendChild(badge);
    
    // Update hidden input
    document.getElementById('productThumbnailInput').value = newIndex;
    
    // Update existing images JSON
    updateExistingImagesJson();
}

function removeProductImage(btn, path) {
    if (!confirm('Xóa ảnh này?')) return;
    
    var imageItem = btn.closest('.image-item');
    var deleteInput = document.getElementById('deleteProductImagesInput');
    
    // Add to delete list
    var deleteList = JSON.parse(deleteInput.value || '[]');
    deleteList.push(path);
    deleteInput.value = JSON.stringify(deleteList);
    
    // Check if this was thumbnail
    var wasThumbnail = imageItem.classList.contains('is-thumbnail');
    
    // Remove the image item
    imageItem.remove();
    
    // Update indices
    var grid = document.getElementById('productImageGrid');
    var remainingImages = grid.querySelectorAll('.image-item');
    remainingImages.forEach(function(item, idx) {
        item.dataset.index = idx;
    });
    
    // If removed thumbnail, set first image as thumbnail
    if (wasThumbnail && remainingImages.length > 0) {
        setProductThumbnail(remainingImages[0].querySelector('img'));
    }
    
    // Update existing images JSON
    updateExistingImagesJson();
    updateUploadHint();
}

function updateExistingImagesJson() {
    var grid = document.getElementById('productImageGrid');
    var existingInput = document.getElementById('existingProductImagesInput');
    var thumbnailInput = document.getElementById('productThumbnailInput');
    var thumbnailIndex = parseInt(thumbnailInput.value) || 0;
    
    var images = [];
    grid.querySelectorAll('.image-item').forEach(function(item, idx) {
        images.push({
            path: item.dataset.path,
            is_thumbnail: (idx === thumbnailIndex)
        });
    });
    
    existingInput.value = JSON.stringify(images);
}

function updateUploadHint() {
    var grid = document.getElementById('productImageGrid');
    var currentCount = grid.querySelectorAll('.image-item').length;
    var hint = document.getElementById('uploadHint');
    if (hint) {
        var remaining = Math.max(0, 5 - currentCount);
        hint.textContent = 'Còn có thể thêm ' + remaining + ' ảnh nữa';
    }
}

// Validate upload limit
document.getElementById('productImagesInput').addEventListener('change', function() {
    var grid = document.getElementById('productImageGrid');
    var currentCount = grid.querySelectorAll('.image-item').length;
    var maxImages = 5;
    
    if (this.files.length + currentCount > maxImages) {
        showToast('Chỉ có thể upload tối đa ' + maxImages + ' ảnh. Bạn đã có ' + currentCount + ' ảnh.', 'warning');
        this.value = '';
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
