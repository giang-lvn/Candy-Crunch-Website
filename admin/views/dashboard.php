<?php
// admin/views/dashboard.php - Dashboard hoàn chỉnh kết hợp báo cáo doanh thu

// Lấy filter thời gian
$period = $_GET['period'] ?? '30days';
$customStart = $_GET['start_date'] ?? '';
$customEnd = $_GET['end_date'] ?? '';

// Xác định khoảng thời gian
$today = date('Y-m-d');
switch ($period) {
    case '7days':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = $today;
        $prevStartDate = date('Y-m-d', strtotime('-14 days'));
        $prevEndDate = date('Y-m-d', strtotime('-8 days'));
        break;
    case '30days':
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = $today;
        $prevStartDate = date('Y-m-d', strtotime('-60 days'));
        $prevEndDate = date('Y-m-d', strtotime('-31 days'));
        break;
    case '12months':
        $startDate = date('Y-m-d', strtotime('-12 months'));
        $endDate = $today;
        $prevStartDate = date('Y-m-d', strtotime('-24 months'));
        $prevEndDate = date('Y-m-d', strtotime('-13 months'));
        break;
    case 'custom':
        $startDate = $customStart ?: date('Y-m-01');
        $endDate = $customEnd ?: $today;
        $dateDiff = (strtotime($endDate) - strtotime($startDate));
        $prevStartDate = date('Y-m-d', strtotime($startDate) - $dateDiff - 86400);
        $prevEndDate = date('Y-m-d', strtotime($startDate) - 86400);
        break;
    default:
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = $today;
        $prevStartDate = date('Y-m-d', strtotime('-60 days'));
        $prevEndDate = date('Y-m-d', strtotime('-31 days'));
}

// 1. KPIs - Doanh thu kỳ hiện tại
$revenueQuery = $pdo->prepare("
    SELECT COALESCE(SUM(
        (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
         FROM ORDER_DETAIL od 
         JOIN SKU s ON od.SKUID = s.SKUID 
         WHERE od.OrderID = o.OrderID)
    ), 0) as total
    FROM ORDERS o
    WHERE DATE(o.OrderDate) BETWEEN ? AND ?
    AND o.OrderStatus = 'Complete'
");
$revenueQuery->execute([$startDate, $endDate]);
$currentRevenue = $revenueQuery->fetchColumn() ?: 0;

// Doanh thu kỳ trước
$revenueQuery->execute([$prevStartDate, $prevEndDate]);
$prevRevenue = $revenueQuery->fetchColumn() ?: 0;
$revenueChange = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;

// Tổng đơn hàng kỳ hiện tại
$ordersQuery = $pdo->prepare("
    SELECT COUNT(*) FROM ORDERS 
    WHERE DATE(OrderDate) BETWEEN ? AND ?
");
$ordersQuery->execute([$startDate, $endDate]);
$currentOrders = $ordersQuery->fetchColumn() ?: 0;

$ordersQuery->execute([$prevStartDate, $prevEndDate]);
$prevOrders = $ordersQuery->fetchColumn() ?: 0;
$ordersChange = $prevOrders > 0 ? (($currentOrders - $prevOrders) / $prevOrders) * 100 : 0;

// Giá trị đơn hàng trung bình
$avgOrderValue = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

// Khách hàng mới (đặt đơn hàng đầu tiên trong kỳ)
$newCustomersQuery = $pdo->prepare("
    SELECT COUNT(DISTINCT CustomerID) 
    FROM ORDERS 
    WHERE CustomerID IS NOT NULL
    AND CustomerID NOT IN (
        SELECT DISTINCT CustomerID FROM ORDERS 
        WHERE CustomerID IS NOT NULL AND DATE(OrderDate) < ?
    )
    AND DATE(OrderDate) BETWEEN ? AND ?
");
$newCustomersQuery->execute([$startDate, $startDate, $endDate]);
$newCustomers = $newCustomersQuery->fetchColumn() ?: 0;

$newCustomersQuery->execute([$prevStartDate, $prevStartDate, $prevEndDate]);
$prevNewCustomers = $newCustomersQuery->fetchColumn() ?: 0;
$customersChange = $prevNewCustomers > 0 ? (($newCustomers - $prevNewCustomers) / $prevNewCustomers) * 100 : 0;

// Tỷ lệ chuyển đổi (đơn hoàn thành / tổng đơn)
$completedOrdersQuery = $pdo->prepare("
    SELECT COUNT(*) FROM ORDERS 
    WHERE DATE(OrderDate) BETWEEN ? AND ? AND OrderStatus = 'Complete'
");
$completedOrdersQuery->execute([$startDate, $endDate]);
$completedOrders = $completedOrdersQuery->fetchColumn() ?: 0;
$conversionRate = $currentOrders > 0 ? ($completedOrders / $currentOrders) * 100 : 0;

// 2. Dữ liệu biểu đồ doanh thu
$chartData = [];
if ($period == '12months') {
    $chartQuery = $pdo->prepare("
        SELECT DATE_FORMAT(o.OrderDate, '%Y-%m') as period,
               COALESCE(SUM(
                   (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
                    FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID)
               ), 0) as revenue
        FROM ORDERS o
        WHERE DATE(o.OrderDate) BETWEEN ? AND ? AND o.OrderStatus = 'Complete'
        GROUP BY DATE_FORMAT(o.OrderDate, '%Y-%m')
        ORDER BY period
    ");
} else {
    $chartQuery = $pdo->prepare("
        SELECT DATE(o.OrderDate) as period,
               COALESCE(SUM(
                   (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
                    FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID)
               ), 0) as revenue
        FROM ORDERS o
        WHERE DATE(o.OrderDate) BETWEEN ? AND ? AND o.OrderStatus = 'Complete'
        GROUP BY DATE(o.OrderDate)
        ORDER BY period
    ");
}
$chartQuery->execute([$startDate, $endDate]);
$chartData = $chartQuery->fetchAll();

// Dữ liệu kỳ trước cho so sánh
$chartQuery->execute([$prevStartDate, $prevEndDate]);
$prevChartData = $chartQuery->fetchAll();

// 3. Phân tích đơn hàng theo trạng thái
$orderStatusQuery = $pdo->query("
    SELECT OrderStatus, COUNT(*) as count
    FROM ORDERS
    GROUP BY OrderStatus
");
$orderStatusData = $orderStatusQuery->fetchAll(PDO::FETCH_KEY_PAIR);

// Đơn hàng cần xử lý gấp (pending > 2 ngày)
$urgentOrdersQuery = $pdo->query("
    SELECT o.OrderID, o.OrderDate, o.OrderStatus,
           CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
           (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
            FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID) as Total,
           DATEDIFF(NOW(), o.OrderDate) as DaysWaiting
    FROM ORDERS o
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    WHERE o.OrderStatus IN ('Pending', 'Pending Confirmation', 'Waiting Payment')
    AND DATEDIFF(NOW(), o.OrderDate) >= 2
    ORDER BY o.OrderDate ASC
    LIMIT 5
");
$urgentOrders = $urgentOrdersQuery->fetchAll();

// 4. Top sản phẩm bán chạy
$topProductsQuery = $pdo->query("
    SELECT p.ProductID, p.ProductName, p.Image,
           SUM(od.OrderQuantity) as TotalSold,
           SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) as TotalRevenue
    FROM ORDER_DETAIL od
    JOIN SKU s ON od.SKUID = s.SKUID
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    JOIN ORDERS o ON od.OrderID = o.OrderID
    WHERE o.OrderStatus = 'Complete'
    GROUP BY p.ProductID, p.ProductName, p.Image
    ORDER BY TotalSold DESC
    LIMIT 10
");
$topProducts = $topProductsQuery->fetchAll();

// Sản phẩm sắp hết hàng
$lowStockQuery = $pdo->query("
    SELECT p.ProductID, p.ProductName, p.Image,
           SUM(i.Stock) as TotalStock
    FROM PRODUCT p
    JOIN SKU s ON p.ProductID = s.ProductID
    JOIN INVENTORY i ON s.InventoryID = i.InventoryID
    WHERE i.InventoryStatus = 'Available'
    GROUP BY p.ProductID, p.ProductName, p.Image
    HAVING TotalStock < 10 AND TotalStock > 0
    ORDER BY TotalStock ASC
    LIMIT 5
");
$lowStockProducts = $lowStockQuery->fetchAll();

// 5. Phân tích khách hàng
$returningCustomersQuery = $pdo->prepare("
    SELECT COUNT(DISTINCT CustomerID) 
    FROM ORDERS 
    WHERE DATE(OrderDate) BETWEEN ? AND ?
    AND CustomerID IN (
        SELECT CustomerID FROM ORDERS 
        WHERE DATE(OrderDate) < ?
        GROUP BY CustomerID
    )
");
$returningCustomersQuery->execute([$startDate, $endDate, $startDate]);
$returningCustomers = $returningCustomersQuery->fetchColumn() ?: 0;

// Top khách hàng giá trị cao
$topCustomersQuery = $pdo->query("
    SELECT c.CustomerID, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
           c.Avatar, a.Email,
           COUNT(DISTINCT o.OrderID) as TotalOrders,
           SUM(
               (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
                FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID)
           ) as TotalSpent
    FROM CUSTOMER c
    JOIN ACCOUNT a ON c.AccountID = a.AccountID
    JOIN ORDERS o ON c.CustomerID = o.CustomerID
    WHERE o.OrderStatus = 'Complete'
    GROUP BY c.CustomerID, c.FirstName, c.LastName, c.Avatar, a.Email
    ORDER BY TotalSpent DESC
    LIMIT 5
");
$topCustomers = $topCustomersQuery->fetchAll();

// 6. Hiệu quả khuyến mãi
$voucherStatsQuery = $pdo->query("
    SELECT v.VoucherID, v.Code, v.DiscountPercent, v.DiscountAmount,
           COUNT(o.OrderID) as UsageCount,
           SUM(
               (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
                FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID)
           ) as TotalRevenue
    FROM VOUCHER v
    JOIN ORDERS o ON v.VoucherID = o.VoucherID
    WHERE o.OrderStatus = 'Complete'
    GROUP BY v.VoucherID, v.Code, v.DiscountPercent, v.DiscountAmount
    ORDER BY UsageCount DESC
    LIMIT 5
");
$topVouchers = $voucherStatsQuery->fetchAll();

// 7. Hoạt động gần đây - Đơn hàng mới nhất
$recentOrdersQuery = $pdo->query("
    SELECT o.OrderID, o.OrderDate, o.OrderStatus,
           CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
           (SELECT SUM(COALESCE(s.PromotionPrice, s.OriginalPrice) * od.OrderQuantity) 
            FROM ORDER_DETAIL od JOIN SKU s ON od.SKUID = s.SKUID WHERE od.OrderID = o.OrderID) as Total
    FROM ORDERS o
    LEFT JOIN CUSTOMER c ON o.CustomerID = c.CustomerID
    ORDER BY o.OrderDate DESC
    LIMIT 10
");
$recentOrders = $recentOrdersQuery->fetchAll();

// Đánh giá mới nhất
$recentFeedbackQuery = $pdo->query("
    SELECT f.FeedbackID, f.Rating, f.Comment, f.CreateDate,
           CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
           p.ProductName
    FROM FEEDBACK f
    JOIN CUSTOMER c ON f.CustomerID = c.CustomerID
    JOIN SKU s ON f.SKUID = s.SKUID
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    ORDER BY f.CreateDate DESC
    LIMIT 5
");
$recentFeedback = $recentFeedbackQuery->fetchAll();

// Helper function để lấy thumbnail
function getDashboardThumbnail($imageData)
{
    if (empty($imageData))
        return '';
    $decoded = json_decode($imageData, true);
    if (is_array($decoded)) {
        foreach ($decoded as $img) {
            if (isset($img['is_thumbnail']) && $img['is_thumbnail'])
                return $img['path'];
        }
        return !empty($decoded[0]) ? (is_array($decoded[0]) ? $decoded[0]['path'] : $decoded[0]) : '';
    }
    return $imageData;
}
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .kpi-card {
        background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
    }

    .kpi-card.revenue {
        --bg-start: #667eea;
        --bg-end: #764ba2;
    }

    .kpi-card.orders {
        --bg-start: #11998e;
        --bg-end: #38ef7d;
    }

    .kpi-card.customers {
        --bg-start: #ee0979;
        --bg-end: #ff6a00;
    }

    .kpi-card.conversion {
        --bg-start: #2193b0;
        --bg-end: #6dd5ed;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .kpi-label {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .kpi-change {
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 20px;
    }

    .kpi-change.positive {
        background: rgba(255, 255, 255, 0.25);
    }

    .kpi-change.negative {
        background: rgba(255, 0, 0, 0.25);
    }

    .section-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
    }

    .mini-table {
        font-size: 0.9rem;
    }

    .mini-table td,
    .mini-table th {
        padding: 10px 12px;
    }

    .product-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
    }

    .avatar-thumb {
        width: 36px;
        height: 36px;
        object-fit: cover;
        border-radius: 50%;
    }

    .urgent-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }
</style>

<!-- Filter Bar -->
<div class="card dashboard-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="dashboard">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Khoảng thời gian</label>
                <select name="period" class="form-select" onchange="toggleCustomDate(this.value)">
                    <option value="7days" <?php echo $period == '7days' ? 'selected' : ''; ?>>7 ngày qua</option>
                    <option value="30days" <?php echo $period == '30days' ? 'selected' : ''; ?>>30 ngày qua</option>
                    <option value="12months" <?php echo $period == '12months' ? 'selected' : ''; ?>>12 tháng qua</option>
                    <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Tùy chỉnh</option>
                </select>
            </div>
            <div class="col-md-2 custom-date" style="<?php echo $period != 'custom' ? 'display:none' : ''; ?>">
                <label class="form-label small fw-semibold">Từ ngày</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $customStart; ?>">
            </div>
            <div class="col-md-2 custom-date" style="<?php echo $period != 'custom' ? 'display:none' : ''; ?>">
                <label class="form-label small fw-semibold">Đến ngày</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $customEnd; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Áp dụng
                </button>
            </div>
            <div class="col-md-3 text-end">
                <small class="text-muted">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?php echo date('d/m/Y', strtotime($startDate)); ?> -
                    <?php echo date('d/m/Y', strtotime($endDate)); ?>
                </small>
            </div>
        </form>
    </div>
</div>

<!-- 1. KPIs -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="kpi-card revenue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Tổng doanh thu</div>
                    <div class="kpi-value"><?php echo number_format($currentRevenue, 0, ',', '.'); ?>đ</div>
                </div>
                <i class="bi bi-cash-coin fs-2 opacity-50"></i>
            </div>
            <div class="mt-2">
                <span class="kpi-change <?php echo $revenueChange >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="bi bi-arrow-<?php echo $revenueChange >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo number_format(abs($revenueChange), 1); ?>% so với kỳ trước
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card orders">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Tổng đơn hàng</div>
                    <div class="kpi-value"><?php echo number_format($currentOrders); ?></div>
                </div>
                <i class="bi bi-cart-check fs-2 opacity-50"></i>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="opacity-75">TB: <?php echo number_format($avgOrderValue, 0, ',', '.'); ?>đ/đơn</small>
                <span class="kpi-change <?php echo $ordersChange >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="bi bi-arrow-<?php echo $ordersChange >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo number_format(abs($ordersChange), 1); ?>%
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card customers">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Khách hàng mới</div>
                    <div class="kpi-value"><?php echo number_format($newCustomers); ?></div>
                </div>
                <i class="bi bi-person-plus fs-2 opacity-50"></i>
            </div>
            <div class="mt-2">
                <span class="kpi-change <?php echo $customersChange >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="bi bi-arrow-<?php echo $customersChange >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo number_format(abs($customersChange), 1); ?>%
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card conversion">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Tỷ lệ hoàn thành</div>
                    <div class="kpi-value"><?php echo number_format($conversionRate, 1); ?>%</div>
                    <small class="opacity-75"><?php echo $completedOrders; ?>/<?php echo $currentOrders; ?> đơn</small>
                </div>
                <i class="bi bi-graph-up-arrow fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Biểu đồ doanh thu + 3. Phân tích đơn hàng -->
<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-graph-up"></i>Biểu đồ doanh thu</h5>
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-pie-chart"></i>Trạng thái đơn hàng</h5>
                <canvas id="orderStatusChart" height="200"></canvas>
                <div class="mt-3">
                    <?php
                    $statusHexColors = [
                        'Complete' => '#198754',        // success - green
                        'Completed' => '#198754',       // success - green
                        'On Shipping' => '#0dcaf0',     // info - cyan
                        'Pending' => '#0d6efd',         // primary - blue
                        'Pending Confirmation' => '#ffc107', // warning - yellow
                        'Waiting Payment' => '#6c757d', // secondary - gray
                        'Cancelled' => '#dc3545',       // danger - red
                        'Pending Cancel' => '#fd7e14',  // orange
                        'Pending Return' => '#20c997',  // teal
                        'Returned' => '#212529'         // dark - black
                    ];
                    foreach ($orderStatusData as $status => $count):
                        $hexColor = $statusHexColors[$status] ?? '#6c757d';
                        ?>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>
                                <span class="d-inline-block me-1"
                                    style="width: 12px; height: 12px; border-radius: 2px; background-color: <?php echo $hexColor; ?>;"></span>
                                <?php echo $status; ?>
                            </span>
                            <strong><?php echo $count; ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Đơn hàng cần xử lý gấp -->
<?php if (!empty($urgentOrders)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2 urgent-badge"></i>
                    Đơn hàng cần xử lý gấp (chờ > 2 ngày)
                </div>
                <div class="card-body p-0">
                    <table class="table mini-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Chờ</th>
                                <th>Tổng tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urgentOrders as $order): ?>
                                <tr>
                                    <td><code class="text-danger">#<?php echo htmlspecialchars($order['OrderID']); ?></code>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['CustomerName'] ?? 'Khách vãng lai'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['OrderDate'])); ?></td>
                                    <td><span class="badge bg-danger"><?php echo $order['DaysWaiting']; ?> ngày</span></td>
                                    <td class="text-success fw-bold">
                                        <?php echo number_format($order['Total'] ?? 0, 0, ',', '.'); ?>đ</td>
                                    <td><a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $order['OrderID']; ?>"
                                            class="btn btn-sm btn-outline-danger">Xử lý</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Top sản phẩm + Sắp hết hàng -->
<div class="row mb-4">
    <div class="col-md-7 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-trophy"></i>Top 10 sản phẩm bán chạy</h5>
                <table class="table mini-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Sản phẩm</th>
                            <th class="text-center">Đã bán</th>
                            <th class="text-end">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $i => $product):
                            $thumb = getDashboardThumbnail($product['Image']);
                            ?>
                            <tr>
                                <td><span
                                        class="badge bg-<?php echo $i < 3 ? 'warning text-dark' : 'secondary'; ?>"><?php echo $i + 1; ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($thumb): ?>
                                            <img src="<?php echo htmlspecialchars($thumb); ?>" class="product-thumb me-2">
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=view_product&id=<?php echo $product['ProductID']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($product['ProductName']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><strong><?php echo number_format($product['TotalSold']); ?></strong>
                                </td>
                                <td class="text-end text-success">
                                    <?php echo number_format($product['TotalRevenue'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-exclamation-circle text-warning"></i>Sản phẩm sắp hết hàng
                </h5>
                <?php if (empty($lowStockProducts)): ?>
                    <div class="text-center text-muted py-4"><i
                            class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>Tất cả sản phẩm còn đủ hàng</div>
                <?php else: ?>
                    <table class="table mini-table">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=edit_product&id=<?php echo $product['ProductID']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($product['ProductName']); ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><span
                                            class="badge bg-danger"><?php echo $product['TotalStock']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 5. Khách hàng + 6. Voucher -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-people"></i>Phân tích khách hàng</h5>
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="fs-3 fw-bold text-primary"><?php echo $newCustomers; ?></div>
                            <small class="text-muted">Khách mới</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="fs-3 fw-bold text-success"><?php echo $returningCustomers; ?></div>
                            <small class="text-muted">Khách quay lại</small>
                        </div>
                    </div>
                </div>
                <h6 class="text-muted mb-2">Top 5 khách hàng VIP</h6>
                <table class="table mini-table">
                    <tbody>
                        <?php foreach ($topCustomers as $customer): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($customer['Avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($customer['Avatar']); ?>"
                                                class="avatar-thumb me-2">
                                        <?php else: ?>
                                            <div
                                                class="avatar-thumb bg-secondary d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-white"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($customer['CustomerName']); ?></strong>
                                            <br><small class="text-muted"><?php echo $customer['TotalOrders']; ?>
                                                đơn</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <?php echo number_format($customer['TotalSpent'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-ticket-perforated"></i>Hiệu quả khuyến mãi</h5>
                <?php if (empty($topVouchers)): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-ticket-perforated fs-1 d-block mb-2"></i>Chưa
                        có voucher nào được sử dụng</div>
                <?php else: ?>
                    <table class="table mini-table">
                        <thead class="table-light">
                            <tr>
                                <th>Mã voucher</th>
                                <th>Giảm giá</th>
                                <th class="text-center">Lượt dùng</th>
                                <th class="text-end">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topVouchers as $voucher): ?>
                                <tr>
                                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($voucher['Code']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($voucher['DiscountPercent'] > 0): ?>
                                            <span
                                                class="badge bg-warning text-dark"><?php echo $voucher['DiscountPercent']; ?>%</span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-info"><?php echo number_format($voucher['DiscountAmount'], 0, ',', '.'); ?>đ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><strong><?php echo $voucher['UsageCount']; ?></strong></td>
                                    <td class="text-end text-success">
                                        <?php echo number_format($voucher['TotalRevenue'], 0, ',', '.'); ?>đ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 7. Hoạt động gần đây -->
<div class="row">
    <div class="col-md-7 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-clock-history"></i>Đơn hàng mới nhất</h5>
                <table class="table mini-table">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="<?php echo BASE_URL; ?>index.php?action=view_order&id=<?php echo $order['OrderID']; ?>"
                                        class="text-decoration-none">#<?php echo htmlspecialchars($order['OrderID']); ?></a>
                                </td>
                                <td><?php echo htmlspecialchars($order['CustomerName'] ?? 'Khách vãng lai'); ?></td>
                                <td><small><?php echo date('d/m H:i', strtotime($order['OrderDate'])); ?></small></td>
                                <td><span
                                        class="badge bg-<?php echo getStatusColor($order['OrderStatus']); ?>"><?php echo getStatusText($order['OrderStatus']); ?></span>
                                </td>
                                <td class="text-end text-success">
                                    <?php echo number_format($order['Total'] ?? 0, 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-star"></i>Đánh giá mới nhất</h5>
                <?php if (empty($recentFeedback)): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-chat-quote fs-1 d-block mb-2"></i>Chưa có đánh
                        giá mới</div>
                <?php else: ?>
                    <?php foreach ($recentFeedback as $fb): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong class="small"><?php echo htmlspecialchars($fb['CustomerName']); ?></strong>
                                <div class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $fb['Rating'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <small class="text-muted"><?php echo htmlspecialchars($fb['ProductName']); ?></small>
                            <?php if (!empty($fb['Comment'])): ?>
                                <p class="small mb-0 mt-1">
                                    <?php echo htmlspecialchars(mb_substr($fb['Comment'], 0, 80)); ?>            <?php echo mb_strlen($fb['Comment']) > 80 ? '...' : ''; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomDate(value) {
        document.querySelectorAll('.custom-date').forEach(el => {
            el.style.display = value === 'custom' ? 'block' : 'none';
        });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($chartData, 'period')); ?>,
            datasets: [{
                label: 'Doanh thu kỳ này',
                data: <?php echo json_encode(array_column($chartData, 'revenue')); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Kỳ trước',
                data: <?php echo json_encode(array_column($prevChartData, 'revenue')); ?>,
                borderColor: '#ccc',
                borderDash: [5, 5],
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v.toLocaleString('vi-VN') + 'đ' }
                }
            }
        }
    });

    // Order Status Chart với màu đúng theo status
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');

    // Mapping màu theo status (khớp với Bootstrap colors)
    const statusColorMap = {
        'Complete': '#198754',        // success - green
        'Completed': '#198754',       // success - green
        'On Shipping': '#0dcaf0',     // info - cyan
        'Pending': '#0d6efd',         // primary - blue
        'Pending Confirmation': '#ffc107', // warning - yellow
        'Waiting Payment': '#6c757d', // secondary - gray
        'Cancelled': '#dc3545',       // danger - red
        'Pending Cancel': '#fd7e14',  // orange
        'Pending Return': '#20c997',  // teal
        'Returned': '#212529'         // dark - black
    };

    const statusLabels = <?php echo json_encode(array_keys($orderStatusData)); ?>;
    const statusValues = <?php echo json_encode(array_values($orderStatusData)); ?>;
    const statusColors = statusLabels.map(label => statusColorMap[label] || '#6c757d');

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: statusColors
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
</script>