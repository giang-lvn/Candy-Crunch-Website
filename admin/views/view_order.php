<?php
// admin/views/view_order.php

$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
    echo '<div class="alert alert-danger">Không tìm thấy mã đơn hàng!</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=orders" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>';
    return;
}

// Lấy thông tin đơn hàng
$orderSql = "
    SELECT 
        o.*,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        c.CustomerID,
        (SELECT addr.Phone FROM ADDRESS addr WHERE addr.CustomerID = c.CustomerID ORDER BY addr.AddressDefault DESC LIMIT 1) as CustomerPhone,
        a.Email as CustomerEmail,
        v.Code as VoucherCode,
        v.VoucherDescription,
        v.DiscountPercent,
        v.DiscountAmount
    FROM ORDERS o
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID
    LEFT JOIN VOUCHER v ON o.VoucherID = v.VoucherID
    WHERE o.OrderID = ?
";
$stmt = $pdo->prepare($orderSql);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="alert alert-danger">Không tìm thấy đơn hàng!</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=orders" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>';
    return;
}

// Lấy chi tiết sản phẩm trong đơn hàng
$detailsSql = "
    SELECT 
        od.SKUID,
        od.OrderQuantity,
        s.Attribute,
        s.OriginalPrice,
        s.PromotionPrice,
        p.ProductID,
        p.ProductName,
        p.Image
    FROM ORDER_DETAIL od
    JOIN SKU s ON od.SKUID = s.SKUID
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    WHERE od.OrderID = ?
";
$detailsStmt = $pdo->prepare($detailsSql);
$detailsStmt->execute([$orderId]);
$orderDetails = $detailsStmt->fetchAll();

// Lấy địa chỉ giao hàng (lấy default address của customer)
$addressSql = "
    SELECT * FROM ADDRESS 
    WHERE CustomerID = ? 
    ORDER BY AddressDefault DESC 
    LIMIT 1
";
$addrStmt = $pdo->prepare($addressSql);
$addrStmt->execute([$order['CustomerID']]);
$address = $addrStmt->fetch();

// Tính toán tổng tiền
$subtotal = 0;
$discount = 0;
foreach ($orderDetails as $item) {
    $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
    $originalTotal = $item['OriginalPrice'] * $item['OrderQuantity'];
    $actualTotal = $price * $item['OrderQuantity'];
    $subtotal += $originalTotal;
    $discount += ($originalTotal - $actualTotal);
}

$voucherDiscount = 0;
if (!empty($order['DiscountPercent'])) {
    $voucherDiscount = ($subtotal - $discount) * $order['DiscountPercent'] / 100;
} elseif (!empty($order['DiscountAmount'])) {
    $voucherDiscount = $order['DiscountAmount'];
}

$shippingFee = $order['ShippingFee'] ?? 0;
$total = $subtotal - $discount - $voucherDiscount + $shippingFee;

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $newStatus = $_POST['new_status'];
        $oldStatus = $order['OrderStatus'];
        
        // Kiểm tra nếu đang chuyển sang Cancelled hoặc Returned
        // và trước đó KHÔNG phải là Cancelled/Returned (để tránh cộng trùng)
        $restoreStatuses = ['Cancelled', 'Returned'];
        $shouldRestoreStock = in_array($newStatus, $restoreStatuses) && !in_array($oldStatus, $restoreStatuses);
        
        if ($shouldRestoreStock) {
            // Hoàn trả tồn kho cho từng sản phẩm trong đơn hàng
            foreach ($orderDetails as $item) {
                $pdo->prepare("UPDATE INVENTORY i 
                              JOIN SKU s ON i.InventoryID = s.InventoryID 
                              SET i.Stock = i.Stock + ? 
                              WHERE s.SKUID = ?")->execute([$item['OrderQuantity'], $item['SKUID']]);
            }
        }
        
        // Cập nhật trạng thái đơn hàng
        $updateStmt = $pdo->prepare("UPDATE ORDERS SET OrderStatus = ? WHERE OrderID = ?");
        $updateStmt->execute([$newStatus, $orderId]);
        
        // Redirect bằng JavaScript để refresh trang với dữ liệu mới
        $stockParam = $shouldRestoreStock ? '&stock_restored=1' : '';
        $redirectUrl = BASE_URL . 'index.php?action=view_order&id=' . $orderId . '&status_updated=1' . $stockParam;
        echo '<script>window.location.href = "' . $redirectUrl . '";</script>';
        exit;
    } catch (Exception $e) {
        $updateError = $e->getMessage();
    }
}

// Hiển thị thông báo từ redirect
if (isset($_GET['status_updated'])) {
    $stockMessage = isset($_GET['stock_restored']) ? ' Đã hoàn trả tồn kho.' : '';
    echo '<script>document.addEventListener("DOMContentLoaded", function() { showToast("Cập nhật trạng thái thành công!' . $stockMessage . '", "success"); });</script>';
}
if (isset($updateError)) {
    echo '<script>document.addEventListener("DOMContentLoaded", function() { showToast("Lỗi: ' . addslashes($updateError) . '", "error"); });</script>';
}

// Helper function để lấy thumbnail
function getProductThumbnailOrder($imageData) {
    if (empty($imageData)) return '';
    $decoded = json_decode($imageData, true);
    if (is_array($decoded)) {
        foreach ($decoded as $img) {
            if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                return $img['path'];
            }
        }
        if (!empty($decoded[0])) {
            return is_array($decoded[0]) ? $decoded[0]['path'] : $decoded[0];
        }
        return '';
    }
    return $imageData;
}
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?action=orders" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
        <h4 class="mb-0">Chi tiết đơn hàng #<?php echo htmlspecialchars($orderId); ?></h4>
    </div>
    <div>
        <span class="badge bg-<?php echo getStatusColor($order['OrderStatus']); ?> fs-6 px-3 py-2">
            <?php echo getStatusText($order['OrderStatus']); ?>
        </span>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Thông tin đơn hàng -->
    <div class="col-lg-8">
        <!-- Thông tin đơn hàng -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Thông tin đơn hàng</h6>
                <small><?php echo formatDate($order['OrderDate']); ?></small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th class="text-muted" width="40%">Mã đơn hàng:</th>
                                <td><code class="text-primary fs-6"><?php echo htmlspecialchars($order['OrderID']); ?></code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ngày đặt:</th>
                                <td><?php echo formatDate($order['OrderDate']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Trạng thái:</th>
                                <td>
                                    <span class="badge bg-<?php echo getStatusColor($order['OrderStatus']); ?>">
                                        <?php echo getStatusText($order['OrderStatus']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th class="text-muted" width="40%">Thanh toán:</th>
                                <td>
                                    <?php 
                                    $paymentMethods = getPaymentMethods();
                                    echo $paymentMethods[$order['PaymentMethod']] ?? $order['PaymentMethod']; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Vận chuyển:</th>
                                <td>
                                    <?php 
                                    $shippingMethods = getShippingMethods();
                                    echo $shippingMethods[$order['ShippingMethod']] ?? $order['ShippingMethod']; 
                                    ?>
                                </td>
                            </tr>
                            <?php if ($order['VoucherCode']): ?>
                            <tr>
                                <th class="text-muted">Voucher:</th>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-ticket-perforated me-1"></i><?php echo htmlspecialchars($order['VoucherCode']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sản phẩm trong đơn hàng -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Sản phẩm (<?php echo count($orderDetails); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($orderDetails)): ?>
                <p class="text-muted text-center mb-0">Không có sản phẩm trong đơn hàng.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails as $item): 
                                $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
                                $itemTotal = $price * $item['OrderQuantity'];
                                $hasDiscount = !empty($item['PromotionPrice']) && $item['PromotionPrice'] < $item['OriginalPrice'];
                                $thumbnail = getProductThumbnailOrder($item['Image']);
                            ?>
                            <tr>
                                <td>
                                    <?php if ($thumbnail): ?>
                                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                                         alt="<?php echo htmlspecialchars($item['ProductName']); ?>"
                                         class="rounded"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['ProductName']); ?></strong>
                                    <br>
                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($item['SKUID']); ?></small>
                                    <?php if ($item['Attribute']): ?>
                                    <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($item['Attribute']); ?>g</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?php echo $item['OrderQuantity']; ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if ($hasDiscount): ?>
                                    <del class="text-muted small"><?php echo formatCurrency($item['OriginalPrice']); ?></del>
                                    <br>
                                    <?php endif; ?>
                                    <span class="text-success"><?php echo formatCurrency($price); ?></span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success"><?php echo formatCurrency($itemTotal); ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Cột phải: Khách hàng, địa chỉ, tổng tiền -->
    <div class="col-lg-4">
        <!-- Thông tin khách hàng -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Khách hàng</h6>
            </div>
            <div class="card-body">
                <?php if ($order['CustomerName']): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px;">
                        <i class="bi bi-person text-white fs-4"></i>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars(trim($order['CustomerName'])); ?></strong>
                        <br>
                        <small class="text-muted">
                            <code><?php echo htmlspecialchars($order['CustomerID']); ?></code>
                        </small>
                    </div>
                </div>
                <table class="table table-borderless table-sm mb-0">
                    <?php if ($order['CustomerPhone']): ?>
                    <tr>
                        <td><i class="bi bi-telephone text-muted me-2"></i></td>
                        <td><?php echo htmlspecialchars($order['CustomerPhone']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($order['CustomerEmail']): ?>
                    <tr>
                        <td><i class="bi bi-envelope text-muted me-2"></i></td>
                        <td><?php echo htmlspecialchars($order['CustomerEmail']); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <div class="mt-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=view_customer&id=<?php echo htmlspecialchars($order['CustomerID']); ?>" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Xem hồ sơ
                    </a>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0"><i class="bi bi-person-x me-2"></i>Khách vãng lai (Không có tài khoản)</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Địa chỉ giao hàng -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Địa chỉ giao hàng</h6>
            </div>
            <div class="card-body">
                <?php if ($address): ?>
                <p class="mb-1"><strong><?php echo htmlspecialchars($address['Fullname']); ?></strong></p>
                <p class="mb-1 text-muted">
                    <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($address['Phone']); ?>
                </p>
                <p class="mb-0 small">
                    <?php echo htmlspecialchars($address['Address']); ?><br>
                    <?php echo htmlspecialchars($address['CityState']); ?>, <?php echo htmlspecialchars($address['Country']); ?>
                    <?php if ($address['PostalCode']): ?>
                    - <?php echo htmlspecialchars($address['PostalCode']); ?>
                    <?php endif; ?>
                </p>
                <?php else: ?>
                <p class="text-muted mb-0">Chưa có địa chỉ giao hàng.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tổng tiền -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Tổng tiền</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td>Tạm tính:</td>
                        <td class="text-end"><?php echo formatCurrency($subtotal); ?></td>
                    </tr>
                    <?php if ($discount > 0): ?>
                    <tr>
                        <td>Giảm giá sản phẩm:</td>
                        <td class="text-end text-danger">-<?php echo formatCurrency($discount); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($voucherDiscount > 0): ?>
                    <tr>
                        <td>Voucher:</td>
                        <td class="text-end text-danger">-<?php echo formatCurrency($voucherDiscount); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Phí vận chuyển:</td>
                        <td class="text-end"><?php echo formatCurrency($shippingFee); ?></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Tổng cộng:</strong></td>
                        <td class="text-end"><strong class="text-success fs-5"><?php echo formatCurrency($total); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Cập nhật trạng thái</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <div class="mb-3">
                        <select name="new_status" class="form-select">
                            <?php foreach (getOrderStatuses() as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $order['OrderStatus'] === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
