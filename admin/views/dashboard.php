<?php
// admin/views/dashboard.php
?>
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary p-3 me-3">
                    <i class="bi bi-cash-coin text-white fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo number_format($stats['total_sales']); ?>đ</h3>
                    <small class="text-muted">Tổng doanh thu</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success p-3 me-3">
                    <i class="bi bi-cart-check text-white fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['total_orders']; ?></h3>
                    <small class="text-muted">Tổng đơn hàng</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info p-3 me-3">
                    <i class="bi bi-box-seam text-white fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['total_products']; ?></h3>
                    <small class="text-muted">Sản phẩm</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning p-3 me-3">
                    <i class="bi bi-people text-white fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['total_customers']; ?></h3>
                    <small class="text-muted">Khách hàng</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tổng quan hệ thống</h5>
                <p>Chào mừng bạn đến với trang quản trị hệ thống.</p>
                
                <div class="mt-4">
                    <h6>Các chức năng chính:</h6>
                    <ul>
                        <li><strong>Quản lý sản phẩm:</strong> Thêm, sửa, xóa sản phẩm và quản lý SKU</li>
                        <li><strong>Quản lý đơn hàng:</strong> Xem và cập nhật trạng thái đơn hàng</li>
                        <li><strong>Quản lý khách hàng:</strong> Xem thông tin khách hàng và lịch sử mua hàng</li>
                        <li><strong>Khuyến mãi:</strong> Tạo và quản lý mã giảm giá</li>
                        <li><strong>Báo cáo:</strong> Thống kê doanh thu và sản phẩm bán chạy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Thông báo</h5>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Hệ thống đang hoạt động ổn định</strong>
                </div>
                
                <?php if ($stats['pending_orders'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Có <strong><?php echo $stats['pending_orders']; ?></strong> đơn hàng đang chờ xử lý
                </div>
                <?php endif; ?>
                
                <?php if ($stats['low_stock'] > 0): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon me-2"></i>
                    Có <strong><?php echo $stats['low_stock']; ?></strong> sản phẩm sắp hết hàng
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>