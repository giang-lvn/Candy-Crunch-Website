<?php
// admin/ajax/get_order_detail.php
// API để lấy chi tiết đơn hàng cho modal

// Load các file cần thiết
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Kiểm tra đăng nhập
if (!isAdminLoggedIn()) {
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit;
}

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    echo '<div class="alert alert-danger">Thiếu mã đơn hàng</div>';
    exit;
}

// Lấy thông tin đơn hàng
$orderSql = "
    SELECT 
        o.*,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        (SELECT addr.Phone FROM ADDRESS addr WHERE addr.CustomerID = c.CustomerID ORDER BY addr.AddressDefault DESC LIMIT 1) as CustomerPhone,
        a.Email as CustomerEmail,
        v.Code as VoucherCode,
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
    echo '<div class="alert alert-danger">Không tìm thấy đơn hàng</div>';
    exit;
}

// Lấy chi tiết sản phẩm
$detailsSql = "
    SELECT 
        od.SKUID,
        od.OrderQuantity,
        s.Attribute,
        s.OriginalPrice,
        s.PromotionPrice,
        p.ProductName
    FROM ORDER_DETAIL od
    JOIN SKU s ON od.SKUID = s.SKUID
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    WHERE od.OrderID = ?
";
$detailsStmt = $pdo->prepare($detailsSql);
$detailsStmt->execute([$orderId]);
$details = $detailsStmt->fetchAll();

// Tính toán tổng tiền
$subtotal = 0;
$discount = 0;
foreach ($details as $item) {
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
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Thông tin đơn hàng</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <th width="40%">Mã đơn:</th>
                <td><code class="text-primary"><?php echo htmlspecialchars($order['OrderID']); ?></code></td>
            </tr>
            <tr>
                <th>Ngày đặt:</th>
                <td><?php echo formatDate($order['OrderDate']); ?></td>
            </tr>
            <tr>
                <th>Trạng thái:</th>
                <td>
                    <span class="badge bg-<?php echo getStatusColor($order['OrderStatus']); ?>">
                        <?php echo getStatusText($order['OrderStatus']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Thanh toán:</th>
                <td><?php 
                    $paymentMethods = getPaymentMethods();
                    echo $paymentMethods[$order['PaymentMethod']] ?? $order['PaymentMethod']; 
                ?></td>
            </tr>
            <tr>
                <th>Vận chuyển:</th>
                <td><?php 
                    $shippingMethods = getShippingMethods();
                    echo $shippingMethods[$order['ShippingMethod']] ?? $order['ShippingMethod']; 
                ?></td>
            </tr>
            <?php if ($order['VoucherCode']): ?>
            <tr>
                <th>Voucher:</th>
                <td><span class="badge bg-success"><?php echo htmlspecialchars($order['VoucherCode']); ?></span></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Khách hàng</h6>
        <?php if ($order['CustomerName']): ?>
        <p class="mb-1"><strong><?php echo htmlspecialchars(trim($order['CustomerName'])); ?></strong></p>
        <p class="mb-1 text-muted small">
            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($order['CustomerPhone'] ?? 'N/A'); ?>
        </p>
        <p class="mb-0 text-muted small">
            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($order['CustomerEmail'] ?? 'N/A'); ?>
        </p>
        <?php else: ?>
        <p class="text-muted">Khách vãng lai</p>
        <?php endif; ?>
    </div>
</div>

<hr>

<h6 class="text-muted mb-3">Sản phẩm (<?php echo count($details); ?>)</h6>
<div class="table-responsive">
    <table class="table table-sm">
        <thead class="table-light">
            <tr>
                <th>Sản phẩm</th>
                <th class="text-center">SL</th>
                <th class="text-end">Đơn giá</th>
                <th class="text-end">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $item): 
                $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
                $itemTotal = $price * $item['OrderQuantity'];
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($item['ProductName']); ?></strong>
                    <?php if ($item['Attribute']): ?>
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($item['Attribute']); ?>g</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?php echo $item['OrderQuantity']; ?></td>
                <td class="text-end"><?php echo formatCurrency($price); ?></td>
                <td class="text-end text-success"><?php echo formatCurrency($itemTotal); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<hr>

<div class="row">
    <div class="col-md-6 offset-md-6">
        <table class="table table-sm table-borderless">
            <tr>
                <td>Tạm tính:</td>
                <td class="text-end"><?php echo formatCurrency($subtotal); ?></td>
            </tr>
            <?php if ($discount > 0): ?>
            <tr>
                <td>Giảm giá SP:</td>
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
                <td>Phí ship:</td>
                <td class="text-end"><?php echo formatCurrency($shippingFee); ?></td>
            </tr>
            <tr class="border-top">
                <td><strong>Tổng cộng:</strong></td>
                <td class="text-end"><strong class="text-success fs-5"><?php echo formatCurrency($total); ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<div class="mt-3">
    <a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo htmlspecialchars($orderId); ?>" 
       class="btn btn-primary">
        <i class="bi bi-eye me-2"></i>Xem chi tiết đầy đủ
    </a>
</div>
