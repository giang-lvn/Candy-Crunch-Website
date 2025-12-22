<?php
// admin/index.php

// Đảm bảo session được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load các file cần thiết
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Kiểm tra đăng nhập
checkAdminAuth();

// Thiết lập action mặc định
$action = $_GET['action'] ?? 'dashboard';

// Lấy thống kê cơ bản
$stats = [
    'total_sales' => 0,
    'total_orders' => 0,
    'total_products' => 0,
    'total_customers' => 0,
    'pending_orders' => 0,
    'low_stock' => 0
];

try {
    // Kiểm tra bảng tồn tại trước khi query
    $tables = ['ORDERS', 'PRODUCT', 'CUSTOMER', 'INVENTORY'];
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() == 0) {
            throw new Exception("Bảng $table không tồn tại");
        }
    }
    
    // Lấy thống kê
    $stats['total_sales'] = $pdo->query("
        SELECT COALESCE(SUM(s.OriginalPrice * od.OrderQuantity), 0) 
        FROM ORDER_DETAIL od 
        JOIN SKU s ON od.SKUID = s.SKUID
    ")->fetchColumn();
    
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM ORDERS")->fetchColumn();
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM PRODUCT")->fetchColumn();
    $stats['total_customers'] = $pdo->query("SELECT COUNT(*) FROM CUSTOMER")->fetchColumn();
    $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM ORDERS WHERE OrderStatus = 'pending'")->fetchColumn();
    $stats['low_stock'] = $pdo->query("SELECT COUNT(*) FROM INVENTORY WHERE Stock < 10 AND InventoryStatus = 'active'")->fetchColumn() ?: 0;
    
} catch (Exception $e) {
    // Nếu có lỗi, vẫn hiển thị trang với thông báo
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo ucfirst($action); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css"> -->
    <style>
        /* CSS tạm thời nếu chưa có file admin.css */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 25px;
        }
        .content {
            padding: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h4 class="text-white mb-4">
                <i class="bi bi-shop me-2"></i>Admin Panel
            </h4>
            
            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=dashboard" 
                       class="nav-link text-white <?php echo $action == 'dashboard' ? 'active bg-white text-dark' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                
                <!-- Sản phẩm Dropdown -->
                <?php 
                $productActions = ['products', 'add_product', 'edit_product', 'view_product', 'categories', 'add_category', 'edit_category', 'feedback'];
                $isProductSection = in_array($action, $productActions);
                ?>
                <li class="nav-item mb-2">
                    <a href="#productSubmenu" 
                       class="nav-link text-white d-flex justify-content-between align-items-center <?php echo $isProductSection ? 'bg-white bg-opacity-25' : ''; ?>" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $isProductSection ? 'true' : 'false'; ?>">
                        <span><i class="bi bi-box me-2"></i> Sản phẩm</span>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="collapse <?php echo $isProductSection ? 'show' : ''; ?> nav flex-column ms-3 mt-1" id="productSubmenu">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=products" 
                               class="nav-link text-white py-1 <?php echo in_array($action, ['products', 'add_product', 'edit_product']) ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-list-ul me-2"></i> Danh sách SP
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=categories" 
                               class="nav-link text-white py-1 <?php echo in_array($action, ['categories', 'add_category', 'edit_category']) ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-tags me-2"></i> Danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=feedback" 
                               class="nav-link text-white py-1 <?php echo $action == 'feedback' ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-chat-dots me-2"></i> Đánh giá
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Orders Dropdown -->
                <?php 
                $orderActions = ['orders', 'add_order', 'view_order', 'edit_order'];
                $isOrderSection = in_array($action, $orderActions);
                ?>
                <li class="nav-item mb-2">
                    <a href="#orderSubmenu" 
                       class="nav-link text-white d-flex justify-content-between align-items-center <?php echo $isOrderSection ? 'bg-white bg-opacity-25' : ''; ?>" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $isOrderSection ? 'true' : 'false'; ?>">
                        <span><i class="bi bi-receipt me-2"></i> Đơn hàng</span>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="collapse <?php echo $isOrderSection ? 'show' : ''; ?> nav flex-column ms-3 mt-1" id="orderSubmenu">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=orders" 
                               class="nav-link text-white py-1 <?php echo $action == 'orders' ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-list-ul me-2"></i> Danh sách đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=add_order" 
                               class="nav-link text-white py-1 <?php echo $action == 'add_order' ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-plus-circle me-2"></i> Thêm đơn hàng
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=customers" 
                       class="nav-link text-white <?php echo $action == 'customers' ? 'active bg-white text-dark' : ''; ?>">
                        <i class="bi bi-people me-2"></i> Khách hàng
                    </a>
                </li>
                
                <!-- Voucher Dropdown -->
                <?php 
                $voucherActions = ['vouchers', 'add_voucher', 'edit_voucher'];
                $isVoucherSection = in_array($action, $voucherActions);
                ?>
                <li class="nav-item mb-2">
                    <a href="#voucherSubmenu" 
                       class="nav-link text-white d-flex justify-content-between align-items-center <?php echo $isVoucherSection ? 'bg-white bg-opacity-25' : ''; ?>" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $isVoucherSection ? 'true' : 'false'; ?>">
                        <span><i class="bi bi-percent me-2"></i> Khuyến mãi</span>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="collapse <?php echo $isVoucherSection ? 'show' : ''; ?> nav flex-column ms-3 mt-1" id="voucherSubmenu">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=vouchers" 
                               class="nav-link text-white py-1 <?php echo $action == 'vouchers' ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-list-ul me-2"></i> Danh sách voucher
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>index.php?action=add_voucher" 
                               class="nav-link text-white py-1 <?php echo $action == 'add_voucher' ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-plus-circle me-2"></i> Thêm voucher
                            </a>
                        </li>
                    </ul>
                </li>
                

                

                
                <li class="nav-item mt-4">
                    <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <?php 
                    $titles = [
                        'dashboard' => 'Dashboard',
                        'products' => 'Quản lý sản phẩm',
                        'add_product' => 'Thêm sản phẩm mới',
                        'edit_product' => 'Chỉnh sửa sản phẩm',
                        'view_product' => 'Chi tiết sản phẩm',
                        'categories' => 'Quản lý danh mục',
                        'add_category' => 'Thêm danh mục mới',
                        'edit_category' => 'Sửa danh mục',
                        'orders' => 'Quản lý đơn hàng',
                        'add_order' => 'Thêm đơn hàng mới',
                        'view_order' => 'Chi tiết đơn hàng',
                        'edit_order' => 'Chỉnh sửa đơn hàng',
                        'customers' => 'Quản lý khách hàng',
                        'view_customer' => 'Chi tiết khách hàng',
                        'vouchers' => 'Quản lý Voucher',
                        'add_voucher' => 'Thêm voucher mới',
                        'edit_voucher' => 'Chỉnh sửa voucher',
                        'feedback' => 'Phản hồi'
                    ];
                    echo $titles[$action] ?? 'Dashboard';
                    ?>
                </h1>
                <div>
                    <span class="me-3">Xin chào, <?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <div class="content">
            <?php if (isset($db_error)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Có lỗi database: <?php echo htmlspecialchars($db_error); ?>
                    <br><small>Vui lòng kiểm tra lại database của bạn.</small>
                </div>
            <?php endif; ?>
            
            <?php
            // Load nội dung dựa trên action
            $view_file = __DIR__ . '/views/' . $action . '.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                // Nếu không tìm thấy file view, hiển thị dashboard
                include __DIR__ . '/views/dashboard.php';
            }
            ?>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="adminToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Thông báo</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    // Function hiển thị Toast thay cho alert()
    function showToast(message, type = 'info') {
        var toast = document.getElementById('adminToast');
        var toastBody = document.getElementById('toastBody');
        var toastIcon = document.getElementById('toastIcon');
        var toastTitle = document.getElementById('toastTitle');
        var toastHeader = toast.querySelector('.toast-header');
        
        // Reset classes
        toast.className = 'toast';
        toastHeader.className = 'toast-header';
        
        // Set content
        toastBody.textContent = message;
        
        // Set styling based on type
        switch(type) {
            case 'success':
                toastIcon.className = 'bi bi-check-circle-fill me-2 text-success';
                toastTitle.textContent = 'Thành công';
                toastHeader.classList.add('bg-success', 'text-white');
                break;
            case 'error':
            case 'danger':
                toastIcon.className = 'bi bi-exclamation-triangle-fill me-2 text-danger';
                toastTitle.textContent = 'Lỗi';
                toastHeader.classList.add('bg-danger', 'text-white');
                break;
            case 'warning':
                toastIcon.className = 'bi bi-exclamation-circle-fill me-2 text-warning';
                toastTitle.textContent = 'Cảnh báo';
                toastHeader.classList.add('bg-warning');
                break;
            default:
                toastIcon.className = 'bi bi-info-circle-fill me-2 text-info';
                toastTitle.textContent = 'Thông báo';
                toastHeader.classList.add('bg-info', 'text-white');
        }
        
        // Show toast
        var bsToast = new bootstrap.Toast(toast, { delay: 4000 });
        bsToast.show();
    }
    
    $(document).ready(function() {
        // Khởi tạo DataTable chỉ cho các bảng có class 'datatable'
        $('table.datatable').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    }
                });
            }
        });
        
        // Toggle sidebar trên mobile
        $('.sidebar-toggle').click(function() {
            $('.sidebar').toggleClass('show');
        });
    });
    </script>
</body>
</html>