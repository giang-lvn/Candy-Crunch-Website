<?php
// admin/views/products.php
$products = $pdo->query("
    SELECT p.*, c.CategoryName 
    FROM PRODUCT p 
    LEFT JOIN CATEGORY c ON p.CategoryID = c.CategoryID 
    ORDER BY p.ProductID DESC 
    LIMIT 20
")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Quản lý sản phẩm</h4>
    <button class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm sản phẩm
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Hương vị</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['ProductID']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['ProductName']); ?></strong><br>
                            <small class="text-muted"><?php echo substr($product['Description'] ?? '', 0, 50); ?>...</small>
                        </td>
                        <td><?php echo htmlspecialchars($product['CategoryName'] ?? 'Chưa phân loại'); ?></td>
                        <td><?php echo htmlspecialchars($product['Unit'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($product['Flavour'] ?? '-'); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Xem SKU">
                                    <i class="bi bi-list-check"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#productsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        }
    });
});
</script>