<?php
// admin/views/view_customer.php

$customerId = $_GET['id'] ?? '';

if (empty($customerId)) {
    echo '<div class="alert alert-danger">Không tìm thấy mã khách hàng!</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=customers" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>';
    return;
}

// Lấy thông tin khách hàng
$customerSql = "
    SELECT 
        c.CustomerID,
        c.FirstName,
        c.LastName,
        c.CustomerBirth,
        c.CustomerGender,
        c.Avatar,
        a.AccountID,
        a.Email,
        a.AccountStatus
    FROM CUSTOMER c 
    LEFT JOIN ACCOUNT a ON c.AccountID = a.AccountID 
    WHERE c.CustomerID = ?
";
$stmt = $pdo->prepare($customerSql);
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    echo '<div class="alert alert-danger">Không tìm thấy khách hàng!</div>';
    echo '<a href="' . BASE_URL . 'index.php?action=customers" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>';
    return;
}

// Lấy danh sách địa chỉ
$addressSql = "
    SELECT 
        AddressID,
        Fullname,
        Phone,
        Alias,
        Address,
        CityState,
        Country,
        PostalCode,
        AddressDefault
    FROM ADDRESS
    WHERE CustomerID = ?
    ORDER BY AddressDefault DESC
";
$addrStmt = $pdo->prepare($addressSql);
$addrStmt->execute([$customerId]);
$addresses = $addrStmt->fetchAll();

// Lấy danh sách banking
$bankingSql = "
    SELECT 
        BankingID,
        IDNumber,
        AccountNumber,
        AccountHolderName,
        BankName,
        BankBranchName,
        BankDefault
    FROM BANKING
    WHERE CustomerID = ?
    ORDER BY BankDefault DESC
";
$bankStmt = $pdo->prepare($bankingSql);
$bankStmt->execute([$customerId]);
$bankings = $bankStmt->fetchAll();

// Lấy lịch sử đơn hàng với sản phẩm
$ordersSql = "
    SELECT 
        o.OrderID,
        o.OrderDate,
        o.OrderStatus,
        o.ShippingFee,
        COALESCE(
            GROUP_CONCAT(
                CONCAT(
                    COALESCE(p.ProductName, 'Sản phẩm không xác định'), 
                    ' (', COALESCE(s.Attribute, 'N/A'), ') x', od.OrderQuantity
                ) 
                SEPARATOR ', '
            ),
            'Không có sản phẩm'
        ) AS Products,
        COALESCE(SUM(COALESCE(s.PromotionPrice, s.OriginalPrice, 0) * od.OrderQuantity), 0) AS TotalPrice
    FROM ORDERS o
    LEFT JOIN ORDER_DETAIL od ON o.OrderID = od.OrderID
    LEFT JOIN SKU s ON od.SKUID = s.SKUID
    LEFT JOIN PRODUCT p ON s.ProductID = p.ProductID
    WHERE o.CustomerID = ?
    GROUP BY o.OrderID, o.OrderDate, o.OrderStatus, o.ShippingFee
    ORDER BY o.OrderDate DESC
";
$ordersStmt = $pdo->prepare($ordersSql);
$ordersStmt->execute([$customerId]);
$orders = $ordersStmt->fetchAll();

// Tính tổng chi tiêu từ tất cả đơn hàng Complete (bao gồm cả ShippingFee)
$totalSpent = array_sum(array_map(function($o) {
    if ($o['OrderStatus'] === 'Complete') {
        return (float)$o['TotalPrice'] + (float)$o['ShippingFee'];
    }
    return 0;
}, $orders));
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?action=customers" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
        <h4 class="mb-0">Chi tiết khách hàng</h4>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Thông tin cá nhân & tài khoản -->
    <div class="col-lg-4">
        <!-- Thông tin cá nhân -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Thông tin cá nhân</h6>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($customer['Avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($customer['Avatar']); ?>" 
                         alt="Avatar"
                         class="rounded-circle mb-3" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-person text-white fs-1"></i>
                    </div>
                <?php endif; ?>
                
                <h5 class="mb-1"><?php echo htmlspecialchars(trim($customer['FirstName'] . ' ' . $customer['LastName'])); ?></h5>
                
                <?php 
                $status = $customer['AccountStatus'] ?? 'Unknown';
                $statusClass = match($status) {
                    'Active' => 'bg-success',
                    'Inactive' => 'bg-warning text-dark',
                    'Banned' => 'bg-danger',
                    default => 'bg-secondary'
                };
                $statusText = match($status) {
                    'Active' => 'Hoạt động',
                    'Inactive' => 'Không hoạt động',
                    'Banned' => 'Bị cấm',
                    default => 'Không xác định'
                };
                ?>
                <span class="badge <?php echo $statusClass; ?> mb-3"><?php echo $statusText; ?></span>
                
                <table class="table table-borderless table-sm text-start">
                    <tr>
                        <th class="text-muted" width="40%">Mã KH:</th>
                        <td><code><?php echo htmlspecialchars($customer['CustomerID']); ?></code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Mã TK:</th>
                        <td><code><?php echo htmlspecialchars($customer['AccountID']); ?></code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Giới tính:</th>
                        <td><?php echo htmlspecialchars($customer['CustomerGender'] ?? 'Chưa cập nhật'); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Ngày sinh:</th>
                        <td>
                            <?php 
                            if (!empty($customer['CustomerBirth'])) {
                                echo date('d/m/Y', strtotime($customer['CustomerBirth']));
                            } else {
                                echo 'Chưa cập nhật';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Thông tin tài khoản -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Thông tin tài khoản</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th class="text-muted" width="40%">Email:</th>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($customer['Email']); ?>">
                                <?php echo htmlspecialchars($customer['Email']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Tổng đơn hàng:</th>
                        <td><span class="badge bg-info"><?php echo count($orders); ?> đơn</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Tổng chi tiêu:</th>
                        <td><span class="text-success fw-bold"><?php echo number_format($totalSpent, 0, ',', '.'); ?>đ</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cột phải: Địa chỉ, Banking, Đơn hàng -->
    <div class="col-lg-8">
        <!-- Địa chỉ giao hàng -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Địa chỉ giao hàng (<?php echo count($addresses); ?>)</h6>
                <button onclick="location.reload()" class="btn btn-sm btn-light py-0" title="Làm mới dữ liệu"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="card-body">
                <?php if (empty($addresses)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-geo-alt text-muted fs-2"></i>
                        <p class="text-muted mb-0 mt-2">Chưa có địa chỉ nào được lưu.</p>
                        <small class="text-muted">ID: <?php echo htmlspecialchars($customerId); ?></small>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($addresses as $addr): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 <?php echo ($addr['AddressDefault'] ?? 'No') === 'Yes' ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <?php if (($addr['AddressDefault'] ?? 'No') === 'Yes'): ?>
                                        <span class="badge bg-success float-end">Mặc định</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($addr['Alias'])): ?>
                                        <small class="text-muted text-uppercase fw-bold"><?php echo htmlspecialchars($addr['Alias']); ?></small><br>
                                    <?php endif; ?>
                                    
                                    <strong class="text-primary"><?php echo htmlspecialchars($addr['Fullname']); ?></strong><br>
                                    <small class="text-muted py-1 d-block">
                                        <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($addr['Phone']); ?>
                                    </small>
                                    <div class="small bg-light p-2 rounded mt-1">
                                        <?php echo htmlspecialchars($addr['Address']); ?><br>
                                        <?php echo htmlspecialchars($addr['CityState']); ?>, <?php echo htmlspecialchars($addr['Country']); ?>
                                        <?php if (!empty($addr['PostalCode'])): ?>
                                            - <?php echo htmlspecialchars($addr['PostalCode']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Thông tin ngân hàng -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-bank me-2"></i>Thông tin ngân hàng (<?php echo count($bankings); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($bankings)): ?>
                    <p class="text-muted text-center mb-0">Chưa có thông tin ngân hàng nào được lưu.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngân hàng</th>
                                    <th>Số tài khoản</th>
                                    <th>Chủ tài khoản</th>
                                    <th>Chi nhánh</th>
                                    <th class="text-center">Mặc định</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bankings as $bank): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($bank['BankName']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($bank['AccountNumber']); ?></code></td>
                                    <td><?php echo htmlspecialchars($bank['AccountHolderName']); ?></td>
                                    <td><?php echo htmlspecialchars($bank['BankBranchName'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <?php if ($bank['BankDefault'] === 'Yes'): ?>
                                            <span class="badge bg-success">Có</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Lịch sử đơn hàng -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Lịch sử đơn hàng (<?php echo count($orders); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p class="text-muted text-center mb-0">Chưa có đơn hàng nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="orderHistoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Giá tiền</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <code class="text-primary"><?php echo htmlspecialchars($order['OrderID']); ?></code>
                                        <br>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['OrderDate'])); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(mb_substr($order['Products'] ?? 'N/A', 0, 80)); ?><?php echo strlen($order['Products'] ?? '') > 80 ? '...' : ''; ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-semibold">
                                            <?php echo number_format((float)$order['TotalPrice'] + (float)$order['ShippingFee'], 0, ',', '.'); ?>đ
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $orderStatus = $order['OrderStatus'];
                                        $orderStatusClass = match($orderStatus) {
                                            'Complete' => 'bg-success',
                                            'On Shipping' => 'bg-info',
                                            'Pending Confirmation' => 'bg-warning text-dark',
                                            'Waiting Payment' => 'bg-secondary',
                                            'Cancelled', 'Canceled' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $orderStatusText = match($orderStatus) {
                                            'Complete' => 'Hoàn thành',
                                            'On Shipping' => 'Đang giao',
                                            'Pending Confirmation' => 'Chờ xác nhận',
                                            'Waiting Payment' => 'Chờ thanh toán',
                                            'Cancelled', 'Canceled' => 'Đã hủy',
                                            default => $orderStatus
                                        };
                                        ?>
                                        <span class="badge <?php echo $orderStatusClass; ?>"><?php echo $orderStatusText; ?></span>
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
</div>

<script>
$(document).ready(function() {
    if ($('#orderHistoryTable tbody tr').length > 0) {
        $('#orderHistoryTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            pageLength: 5,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [1] }
            ]
        });
    }
});
</script>
