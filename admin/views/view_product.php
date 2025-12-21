<?php
// admin/views/view_product.php
// Xem chi tiết sản phẩm (chỉ đọc) - Ảnh lấy từ PRODUCT

// 1. Lấy Product ID từ URL
$productId = $_GET['id'] ?? '';
if (empty($productId)) {
    echo '<div class="alert alert-danger">Không tìm thấy mã sản phẩm</div>';
    exit;
}

// 2. Load thông tin sản phẩm
$stmt = $pdo->prepare("
    SELECT p.*, c.CategoryName 
    FROM PRODUCT p 
    LEFT JOIN CATEGORY c ON p.CategoryID = c.CategoryID 
    WHERE p.ProductID = ?
");
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

// Tab labels
$tabLabels = [
    'New products' => 'Sản phẩm mới',
    'Best-seller' => 'Bán chạy',
    'On sales' => 'Đang giảm giá'
];

// Helper function to parse product images
function parseProductImages($imageData) {
    if (empty($imageData)) return [];
    
    $decoded = json_decode($imageData, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    
    // Old format: single image path
    return [['path' => $imageData, 'is_thumbnail' => true]];
}

// Helper function to get thumbnail
function getProductThumbnailView($imageData) {
    $images = parseProductImages($imageData);
    if (empty($images)) return '';
    
    // Find thumbnail
    foreach ($images as $img) {
        if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
            return $img['path'];
        }
    }
    
    // Return first image if no thumbnail set
    return isset($images[0]['path']) ? $images[0]['path'] : (is_string($images[0]) ? $images[0] : '');
}

// Parse product images
$productImages = parseProductImages($product['Image'] ?? '');
$thumbnail = getProductThumbnailView($product['Image'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Chi tiết sản phẩm: <?php echo htmlspecialchars($product['ProductID']); ?></h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=products">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?action=edit_product&id=<?php echo htmlspecialchars($product['ProductID']); ?>" 
           class="btn btn-warning">
            <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Thông tin chung + Ảnh sản phẩm -->
    <div class="col-lg-5">
        <!-- Ảnh sản phẩm -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-images me-2"></i>Ảnh sản phẩm (<?php echo count($productImages); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($thumbnail)): ?>
                <div class="text-center mb-3">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                         alt="<?php echo htmlspecialchars($product['ProductName']); ?>"
                         class="img-fluid rounded border"
                         style="max-height: 200px; object-fit: cover;">
                    <small class="d-block text-muted mt-1">
                        <i class="bi bi-star-fill text-warning"></i> Thumbnail
                    </small>
                </div>
                
                <?php if (count($productImages) > 1): ?>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <?php foreach ($productImages as $img): 
                        $imgPath = is_array($img) ? $img['path'] : $img;
                        $isThumbnail = is_array($img) && isset($img['is_thumbnail']) && $img['is_thumbnail'];
                        if ($isThumbnail) continue;
                    ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                         alt="Product Image"
                         class="rounded border"
                         style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                         onclick="window.open('<?php echo htmlspecialchars($imgPath); ?>', '_blank')">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-image display-4 d-block mb-2"></i>
                    <p class="mb-0">Chưa có ảnh sản phẩm</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Thông tin sản phẩm -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin sản phẩm</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Mã sản phẩm</label>
                    <div class="form-control bg-light"><?php echo htmlspecialchars($product['ProductID']); ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Tên sản phẩm</label>
                    <div class="form-control bg-light"><?php echo htmlspecialchars($product['ProductName']); ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Danh mục</label>
                    <div class="form-control bg-light">
                        <?php if (!empty($product['CategoryName'])): ?>
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($product['CategoryName']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Chưa phân loại</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Mô tả</label>
                    <div class="form-control bg-light">
                        <?php echo !empty($product['Description']) ? htmlspecialchars($product['Description']) : '<span class="text-muted">Không có mô tả</span>'; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Đơn vị</label>
                    <div class="form-control bg-light">
                        <?php echo !empty($product['Unit']) ? htmlspecialchars($product['Unit']) : '<span class="text-muted">-</span>'; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Hương vị</label>
                    <div class="form-control bg-light">
                        <?php echo !empty($product['Flavour']) ? htmlspecialchars($product['Flavour']) : '<span class="text-muted">-</span>'; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Thành phần</label>
                    <div class="form-control bg-light">
                        <?php echo !empty($product['Ingredient']) ? htmlspecialchars($product['Ingredient']) : '<span class="text-muted">-</span>'; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Nhãn sản phẩm</label>
                    <div class="form-control bg-light">
                        <?php if (!empty($product['Filter'])): ?>
                            <?php 
                            $tagClass = match($product['Filter']) {
                                'New products' => 'bg-success',
                                'Best-seller' => 'bg-warning text-dark',
                                'On sales' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $tagClass; ?>">
                                <?php echo htmlspecialchars($tabLabels[$product['Filter']] ?? $product['Filter']); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Không có nhãn</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cột phải: SKU -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Danh sách SKU (<?php echo count($skus); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($skus)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <p>Chưa có SKU nào cho sản phẩm này</p>
                </div>
                <?php else: ?>
                
                <?php foreach ($skus as $index => $sku): 
                    // Stock status
                    $stock = (int)$sku['Stock'];
                    if ($stock >= 20) {
                        $stockClass = 'text-success';
                        $stockBadge = 'bg-success';
                        $stockText = 'Còn hàng';
                    } elseif ($stock > 0) {
                        $stockClass = 'text-warning';
                        $stockBadge = 'bg-warning text-dark';
                        $stockText = 'Còn ít';
                    } else {
                        $stockClass = 'text-danger';
                        $stockBadge = 'bg-danger';
                        $stockText = 'Hết hàng';
                    }
                ?>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0">
                                <i class="bi bi-tag me-2"></i>SKU #<?php echo $index + 1; ?>
                                <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($sku['SKUID']); ?></span>
                            </h6>
                            <span class="badge <?php echo $stockBadge; ?>"><?php echo $stockText; ?></span>
                        </div>
                        
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label small fw-semibold text-muted mb-0">Thuộc tính</label>
                                <div class="fw-semibold"><?php echo htmlspecialchars($sku['Attribute']); ?></div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small fw-semibold text-muted mb-0">Tồn kho</label>
                                <div class="fw-bold <?php echo $stockClass; ?>"><?php echo number_format($stock); ?></div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small fw-semibold text-muted mb-0">Giá gốc</label>
                                <div class="fw-semibold"><?php echo number_format($sku['OriginalPrice'], 0, ',', '.'); ?>đ</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small fw-semibold text-muted mb-0">Giá khuyến mãi</label>
                                <div class="fw-semibold text-danger">
                                    <?php echo !empty($sku['PromotionPrice']) 
                                        ? number_format($sku['PromotionPrice'], 0, ',', '.') . 'đ'
                                        : '<span class="text-muted">-</span>'; ?>
                                </div>
                            </div>
                            <?php if (!empty($sku['PromotionPrice']) && $sku['PromotionPrice'] < $sku['OriginalPrice']): 
                                $discount = round((1 - $sku['PromotionPrice'] / $sku['OriginalPrice']) * 100);
                            ?>
                            <div class="col-12">
                                <span class="badge bg-danger">Giảm <?php echo $discount; ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tổng quan nhanh -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tổng quan</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 mb-0 text-primary"><?php echo count($skus); ?></div>
                        <small class="text-muted">SKU</small>
                    </div>
                    <div class="col-4">
                        <?php 
                        $totalStock = array_sum(array_column($skus, 'Stock')); 
                        $stockColorClass = $totalStock >= 20 ? 'text-success' : ($totalStock > 0 ? 'text-warning' : 'text-danger');
                        ?>
                        <div class="h4 mb-0 <?php echo $stockColorClass; ?>"><?php echo number_format($totalStock); ?></div>
                        <small class="text-muted">Tổng tồn kho</small>
                    </div>
                    <div class="col-4">
                        <?php 
                        $minPrice = !empty($skus) ? min(array_map(function($s) { 
                            return $s['PromotionPrice'] ?: $s['OriginalPrice']; 
                        }, $skus)) : 0;
                        ?>
                        <div class="h5 mb-0 text-success"><?php echo number_format($minPrice, 0, ',', '.'); ?>đ</div>
                        <small class="text-muted">Giá từ</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control.bg-light {
    border: 1px solid #dee2e6;
}
</style>
