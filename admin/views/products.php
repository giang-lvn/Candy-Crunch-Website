<?php
// admin/views/products.php

// Helper function to get thumbnail from JSON image format
function getProductThumbnail($imageData) {
    if (empty($imageData)) return '';
    
    // Check if it's JSON format
    $decoded = json_decode($imageData, true);
    if (is_array($decoded)) {
        // Find thumbnail in array
        foreach ($decoded as $img) {
            if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                return $img['path'];
            }
        }
        // Return first image if no thumbnail
        if (!empty($decoded[0])) {
            return is_array($decoded[0]) ? $decoded[0]['path'] : $decoded[0];
        }
        return '';
    }
    
    // Old format: return as-is
    return $imageData;
}

// Lấy danh sách category cho dropdown filter
$categories = $pdo->query("SELECT CategoryID, CategoryName FROM CATEGORY ORDER BY CategoryName")->fetchAll();

// Lấy các giá trị filter
$categoryFilter = $_GET['category'] ?? '';
$tabFilter = $_GET['tab'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Kiểm tra có filter nào đang active không
$hasActiveFilter = !empty($categoryFilter) || !empty($tabFilter) || !empty($statusFilter);

// Xử lý xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Kiểm tra xem có đơn hàng nào chứa sản phẩm này không
        $checkOrders = $pdo->prepare("
            SELECT COUNT(*) FROM ORDER_DETAIL od 
            JOIN SKU s ON od.SKUID = s.SKUID 
            WHERE s.ProductID = ?
        ");
        $checkOrders->execute([$productId]);
        $orderCount = $checkOrders->fetchColumn();
        
        if ($orderCount > 0) {
            throw new Exception("Không thể xóa sản phẩm này vì đang có $orderCount đơn hàng liên quan.");
        }
        
        // Xóa ảnh của các SKU
        $skuImages = $pdo->prepare("SELECT Image FROM SKU WHERE ProductID = ?");
        $skuImages->execute([$productId]);
        while ($img = $skuImages->fetchColumn()) {
            // Logic xóa file ảnh nếu cần
            $filePath = __DIR__ . '/../../' . str_replace('/Candy-Crunch-Website/', '', $img);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        // Xóa SKU và Inventory liên quan
        // Lấy danh sách InventoryID
        $invIds = $pdo->prepare("SELECT InventoryID FROM SKU WHERE ProductID = ?");
        $invIds->execute([$productId]);
        $inventoryIds = $invIds->fetchAll(PDO::FETCH_COLUMN);
        
        // Xóa SKU
        $deleteSku = $pdo->prepare("DELETE FROM SKU WHERE ProductID = ?");
        $deleteSku->execute([$productId]);
        
        // Xóa Inventory
        if (!empty($inventoryIds)) {
            $inList = str_repeat('?,', count($inventoryIds) - 1) . '?';
            $deleteInv = $pdo->prepare("DELETE FROM INVENTORY WHERE InventoryID IN ($inList)");
            $deleteInv->execute($inventoryIds);
        }
        
        // Xóa Product
        $deleteProduct = $pdo->prepare("DELETE FROM PRODUCT WHERE ProductID = ?");
        $deleteProduct->execute([$productId]);
        
        $pdo->commit();
        
        // Refresh bằng JS
        echo "<script>alert('Đã xóa sản phẩm thành công!'); window.location.href = '" . BASE_URL . "index.php?action=products';</script>";
        exit;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $deleteError = "Lỗi khi xóa sản phẩm: " . $e->getMessage();
    }
}

// Query lấy sản phẩm với thông tin đầy đủ
$sql = "
    SELECT 
        p.ProductID,
        p.ProductName,
        p.Description,
        p.Filter AS ProductTab,
        c.CategoryID,
        c.CategoryName,
        
        -- Lấy thumbnail từ SKU đầu tiên
        (SELECT s1.Image FROM SKU s1 WHERE s1.ProductID = p.ProductID LIMIT 1) AS Thumbnail,
        
        -- Tổng tồn kho từ bảng INVENTORY (qua SKU)
        COALESCE((
            SELECT SUM(i.Stock) 
            FROM INVENTORY i 
            JOIN SKU s2 ON i.InventoryID = s2.InventoryID 
            WHERE s2.ProductID = p.ProductID
        ), 0) AS TotalStock,
        
        -- Giá min từ SKU
        (SELECT MIN(COALESCE(s3.PromotionPrice, s3.OriginalPrice)) 
         FROM SKU s3 WHERE s3.ProductID = p.ProductID) AS MinPrice,
        
        -- Giá max từ SKU
        (SELECT MAX(COALESCE(s4.PromotionPrice, s4.OriginalPrice)) 
         FROM SKU s4 WHERE s4.ProductID = p.ProductID) AS MaxPrice,
        
        -- Trạng thái tồn kho
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM INVENTORY i2 
                JOIN SKU s5 ON i2.InventoryID = s5.InventoryID 
                WHERE s5.ProductID = p.ProductID 
                AND i2.InventoryStatus = 'Available' 
                AND i2.Stock > 0
            ) THEN 'Còn hàng'
            ELSE 'Hết hàng'
        END AS InventoryStatusLabel
        
    FROM PRODUCT p 
    LEFT JOIN CATEGORY c ON p.CategoryID = c.CategoryID 
    WHERE 1=1
";

$params = [];

// Thêm điều kiện lọc theo category
if (!empty($categoryFilter)) {
    $sql .= " AND p.CategoryID = :categoryId";
    $params['categoryId'] = $categoryFilter;
}

// Thêm điều kiện lọc theo tab (Filter column)
if (!empty($tabFilter)) {
    $sql .= " AND p.Filter = :tab";
    $params['tab'] = $tabFilter;
}

$sql .= " ORDER BY p.ProductID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allProducts = $stmt->fetchAll();

// Lọc theo trạng thái tồn kho 
if (!empty($statusFilter)) {
    $products = array_filter($allProducts, function($p) use ($statusFilter) {
        $stock = (int)$p['TotalStock'];
        if ($statusFilter === 'in_stock') {
            return $stock >= 20; // Còn hàng >= 20
        } elseif ($statusFilter === 'low_stock') {
            return $stock > 0 && $stock < 20; // Còn ít hàng: 1-19
        } elseif ($statusFilter === 'out_of_stock') {
            return $stock == 0; // Hết hàng = 0
        }
        return true;
    });
    $products = array_values($products);
} else {
    $products = $allProducts;
}

// Lấy tên category đang filter (nếu có)
$filterCategoryName = '';
if (!empty($categoryFilter)) {
    foreach ($categories as $cat) {
        if ($cat['CategoryID'] === $categoryFilter) {
            $filterCategoryName = $cat['CategoryName'];
            break;
        }
    }
}

// Tab labels
$tabLabels = [
    'New products' => 'Sản phẩm mới',
    'Best-seller' => 'Bán chạy',
    'On sales' => 'Đang giảm giá'
];

// Status labels
$statusLabels = [
    'in_stock' => 'Còn hàng',
    'low_stock' => 'Còn ít hàng',
    'out_of_stock' => 'Hết hàng'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Quản lý sản phẩm</h4>
    <a href="<?php echo BASE_URL; ?>index.php?action=add_product" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm sản phẩm
    </a>
</div>

<!-- Bộ lọc sản phẩm -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Bộ lọc sản phẩm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" id="filterForm">
            <input type="hidden" name="action" value="products">
            
            <div class="row g-3">
                <!-- Lọc theo danh mục -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Danh mục</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['CategoryID']); ?>"
                            <?php echo $categoryFilter === $cat['CategoryID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['CategoryName']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Lọc theo Tab (Filter column) -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nhãn sản phẩm</label>
                    <select name="tab" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả nhãn --</option>
                        <option value="New products" <?php echo $tabFilter === 'New products' ? 'selected' : ''; ?>>
                            New products
                        </option>
                        <option value="Best-seller" <?php echo $tabFilter === 'Best-seller' ? 'selected' : ''; ?>>
                            Best-seller
                        </option>
                        <option value="On sales" <?php echo $tabFilter === 'On sales' ? 'selected' : ''; ?>>
                            On sales
                        </option>
                    </select>
                </div>
                
                <!-- Lọc theo trạng thái tồn kho -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái tồn kho</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="in_stock" <?php echo $statusFilter === 'in_stock' ? 'selected' : ''; ?>>
                            Còn hàng
                        </option>
                        <option value="low_stock" <?php echo $statusFilter === 'low_stock' ? 'selected' : ''; ?>>
                            Còn ít hàng
                        </option>
                        <option value="out_of_stock" <?php echo $statusFilter === 'out_of_stock' ? 'selected' : ''; ?>>
                            Hết hàng
                        </option>
                    </select>
                </div>
            </div>
            
            <?php if ($hasActiveFilter): ?>
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small">Đang lọc:</span>
                    
                    <?php if (!empty($categoryFilter)): ?>
                    <span class="badge bg-info">
                        <i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($filterCategoryName); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($tabFilter)): ?>
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-bookmark me-1"></i><?php echo htmlspecialchars($tabLabels[$tabFilter] ?? $tabFilter); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($statusFilter)): ?>
                    <?php 
                    $statusBadgeClass = match($statusFilter) {
                        'in_stock' => 'bg-success',
                        'low_stock' => 'bg-warning text-dark',
                        'out_of_stock' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?php echo $statusBadgeClass; ?>">
                        <i class="bi bi-box me-1"></i><?php echo htmlspecialchars($statusLabels[$statusFilter] ?? $statusFilter); ?>
                    </span>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                    
                    <span class="ms-auto text-muted small">
                        <strong><?php echo count($products); ?></strong> sản phẩm tìm thấy
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
        <!-- Hiển thị khi không có sản phẩm -->
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox display-4 d-block mb-3"></i>
            <p class="mb-0">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <?php if ($hasActiveFilter): ?>
            <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-primary mt-3">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Xem tất cả sản phẩm
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Bảng sản phẩm -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Mã SP</th>
                        <th style="width: 80px;">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-center">Tồn kho</th>
                        <th>Giá bán</th>
                        <th class="text-center">Trạng thái</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <!-- Mã sản phẩm -->
                        <td>
                            <code class="text-primary"><?php echo htmlspecialchars($product['ProductID']); ?></code>
                        </td>
                        
                        <!-- Thumbnail -->
                        <td>
                            <?php 
                            $thumbnail = getProductThumbnail($product['Thumbnail']);
                            if (!empty($thumbnail)): 
                            ?>
                                <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                                     alt="<?php echo htmlspecialchars($product['ProductName']); ?>"
                                     class="rounded" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-white"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Tên sản phẩm + Mô tả -->
                        <td>
                            <strong><?php echo htmlspecialchars($product['ProductName']); ?></strong>
                            <?php if (!empty($product['Description'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($product['Description'], 0, 50)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Danh mục -->
                        <td>
                            <span class="badge bg-info text-dark">
                                <?php echo htmlspecialchars($product['CategoryName'] ?? 'Chưa phân loại'); ?>
                            </span>
                        </td>
                        
                        <!-- Tổng tồn kho -->
                        <td class="text-center">
                            <?php 
                            $stock = (int)$product['TotalStock'];
                            if ($stock >= 20) {
                                $stockClass = 'text-success';
                            } elseif ($stock > 0) {
                                $stockClass = 'text-warning';
                            } else {
                                $stockClass = 'text-danger';
                            }
                            ?>
                            <span class="fw-bold <?php echo $stockClass; ?>">
                                <?php echo number_format($stock); ?>
                            </span>
                        </td>
                        
                        <!-- Giá bán (min - max) -->
                        <td>
                            <?php 
                            $minPrice = $product['MinPrice'];
                            $maxPrice = $product['MaxPrice'];
                            if ($minPrice && $maxPrice): 
                                if ($minPrice == $maxPrice): ?>
                                    <span class="text-success fw-semibold">
                                        <?php echo number_format($minPrice, 0, ',', '.'); ?>đ
                                    </span>
                                <?php else: ?>
                                    <span class="text-success fw-semibold">
                                        <?php echo number_format($minPrice, 0, ',', '.'); ?>đ
                                    </span>
                                    <span class="text-muted"> - </span>
                                    <span class="text-success fw-semibold">
                                        <?php echo number_format($maxPrice, 0, ',', '.'); ?>đ
                                    </span>
                                <?php endif;
                            else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td class="text-center">
                            <?php 
                            $stock = (int)$product['TotalStock'];
                            if ($stock >= 20) {
                                $statusClass = 'bg-success';
                                $statusText = 'Còn hàng';
                            } elseif ($stock > 0) {
                                $statusClass = 'bg-warning text-dark';
                                $statusText = 'Còn ít';
                            } else {
                                $statusClass = 'bg-danger';
                                $statusText = 'Hết hàng';
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        
                        <!-- Thao tác -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" 
                                        title="Sửa sản phẩm"
                                        onclick="window.location.href='<?php echo BASE_URL; ?>index.php?action=edit_product&id=<?php echo htmlspecialchars($product['ProductID']); ?>'">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" 
                                        title="Xem SKU"
                                        onclick="viewProductDetails('<?php echo htmlspecialchars($product['ProductID']); ?>')">
                                    <i class="bi bi-list-check"></i>
                                </button>
                                <button class="btn btn-outline-danger" 
                                        title="Xóa sản phẩm"
                                        onclick="openDeleteProductModal('<?php echo htmlspecialchars($product['ProductID']); ?>', '<?php echo htmlspecialchars(addslashes($product['ProductName'])); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<?php if (!empty($products)): ?>
<!-- Modal Xác nhận xóa sản phẩm -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="product_id" id="delete_product_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa sản phẩm <strong id="delete_product_name"></strong>?</p>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Hành động này sẽ xóa tất cả SKU và Inventory liên quan đến sản phẩm.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="delete_product" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Xóa sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function mở modal xóa
function openDeleteProductModal(id, name) {
    document.getElementById('delete_product_id').value = id;
    document.getElementById('delete_product_name').textContent = name;
    
    var modal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
    modal.show();
}

// Function xem chi tiết sản phẩm
function viewProductDetails(id) {
    window.location.href = '<?php echo BASE_URL; ?>index.php?action=view_product&id=' + id;
}

$(document).ready(function() {
    $('#productsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });
});
</script>
<?php endif; ?>