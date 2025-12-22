<?php
// admin/views/add_order.php

$message = '';
$messageType = '';

// Không cần load tất cả khách hàng - sử dụng AJAX search

// Lấy danh sách voucher còn hiệu lực
$vouchersSql = "
    SELECT VoucherID, Code, VoucherDescription, DiscountPercent, DiscountAmount, MinOrder 
    FROM VOUCHER 
    WHERE VoucherStatus = 'Active' 
    AND StartDate <= CURDATE() 
    AND EndDate >= CURDATE()
    ORDER BY Code
";
$vouchers = $pdo->query($vouchersSql)->fetchAll();

// Lấy danh sách SKU (sản phẩm)
$skusSql = "
    SELECT 
        s.SKUID, 
        s.Attribute, 
        s.OriginalPrice, 
        s.PromotionPrice,
        p.ProductID,
        p.ProductName,
        p.Image,
        COALESCE(i.Stock, 0) as Stock
    FROM SKU s
    JOIN PRODUCT p ON s.ProductID = p.ProductID
    LEFT JOIN INVENTORY i ON s.InventoryID = i.InventoryID
    WHERE i.Stock > 0 OR i.Stock IS NULL
    ORDER BY p.ProductName, s.Attribute
";
$skus = $pdo->query($skusSql)->fetchAll();

// Xử lý thêm đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    try {
        $pdo->beginTransaction();
        
        // Tạo OrderID mới
        $orderId = generateOrderId($pdo);
        
        // Lấy dữ liệu từ form
        $customerId = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
        $voucherId = !empty($_POST['voucher_id']) ? $_POST['voucher_id'] : null;
        $paymentMethod = $_POST['payment_method'] ?? 'COD';
        $shippingMethod = $_POST['shipping_method'] ?? 'Standard';
        $shippingFee = floatval($_POST['shipping_fee'] ?? 0);
        $orderStatus = $_POST['order_status'] ?? 'Pending Confirmation';
        
        $skuIds = $_POST['sku_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        
        // Validate
        if (empty($skuIds) || empty(array_filter($skuIds))) {
            throw new Exception('Vui lòng chọn ít nhất một sản phẩm');
        }
        
        // Thêm đơn hàng
        $insertOrderSql = "
            INSERT INTO ORDERS (OrderID, CustomerID, VoucherID, OrderDate, PaymentMethod, ShippingMethod, ShippingFee, OrderStatus) 
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)
        ";
        $insertOrder = $pdo->prepare($insertOrderSql);
        $insertOrder->execute([$orderId, $customerId, $voucherId, $paymentMethod, $shippingMethod, $shippingFee, $orderStatus]);
        
        // Thêm chi tiết đơn hàng
        $insertDetailSql = "INSERT INTO ORDER_DETAIL (OrderID, SKUID, OrderQuantity) VALUES (?, ?, ?)";
        $insertDetail = $pdo->prepare($insertDetailSql);
        
        foreach ($skuIds as $index => $skuId) {
            if (!empty($skuId) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                $insertDetail->execute([$orderId, $skuId, $quantities[$index]]);
            }
        }
        
        $pdo->commit();
        
        $message = "Thêm đơn hàng $orderId thành công! Đang chuyển hướng...";
        $messageType = 'success';
        
        echo "<script>setTimeout(function(){ window.location.href = '" . BASE_URL . "index.php?action=view_order&id=" . $orderId . "'; }, 2000);</script>";
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Helper function để lấy thumbnail
function getProductThumb($imageData) {
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Thêm đơn hàng mới</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php?action=orders">Đơn hàng</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol></nav>
    </div>
    <a href="<?php echo BASE_URL; ?>index.php?action=orders" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" id="addOrderForm">
    <div class="row">
        <!-- Cột trái: Sản phẩm -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-box me-2"></i>Sản phẩm</h6>
                    <button type="button" class="btn btn-sm btn-light" onclick="addProductRow()">
                        <i class="bi bi-plus-circle me-1"></i>Thêm sản phẩm
                    </button>
                </div>
                <div class="card-body">
                    <div id="productsList">
                        <!-- Product Row Template -->
                        <div class="product-row row mb-3 align-items-center">
                            <div class="col-md-7">
                                <select name="sku_id[]" class="form-select sku-select" onchange="updateProductInfo(this)">
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?php foreach ($skus as $sku): 
                                        $price = $sku['PromotionPrice'] ?? $sku['OriginalPrice'];
                                    ?>
                                    <option value="<?php echo htmlspecialchars($sku['SKUID']); ?>" 
                                            data-price="<?php echo $price; ?>"
                                            data-original="<?php echo $sku['OriginalPrice']; ?>"
                                            data-stock="<?php echo $sku['Stock']; ?>">
                                        <?php echo htmlspecialchars($sku['ProductName']); ?> 
                                        <?php if ($sku['Attribute']): ?> - <?php echo htmlspecialchars($sku['Attribute']); ?>g<?php endif; ?>
                                        (<?php echo formatCurrency($price); ?>) 
                                        [Kho: <?php echo $sku['Stock']; ?>]
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="quantity[]" class="form-control quantity-input" 
                                       min="1" value="1" placeholder="SL" onchange="updateTotal()">
                            </div>
                            <div class="col-md-2">
                                <span class="item-total text-success fw-bold">0đ</span>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductRow(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td>Tạm tính:</td>
                                    <td class="text-end" id="subtotalDisplay">0đ</td>
                                </tr>
                                <tr>
                                    <td>Phí vận chuyển:</td>
                                    <td class="text-end">
                                        <span id="shippingDisplay">30.000đ</span>
                                        <small id="freeShipNote" class="text-success d-block" style="display:none !important;"><i class="bi bi-check-circle"></i> Free ship</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Voucher:</td>
                                    <td class="text-end text-danger" id="voucherDisplay">-0đ</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Tổng cộng:</strong></td>
                                    <td class="text-end"><strong class="text-success fs-5" id="totalDisplay">0đ</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cột phải: Thông tin đơn hàng -->
        <div class="col-lg-4">
            <!-- Khách hàng -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Khách hàng (Tùy chọn)</h6>
                </div>
                <div class="card-body">
                    <select name="customer_id" class="form-select" id="customerSelect">
                        <option></option>
                    </select>
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-search me-1"></i>Gõ email hoặc tên khách hàng để tìm kiếm. Để trống nếu khách vãng lai.
                    </small>
                </div>
            </div>

            <!-- Voucher -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Voucher (Tùy chọn)</h6>
                </div>
                <div class="card-body">
                    <select name="voucher_id" class="form-select" id="voucherSelect" onchange="updateTotal()">
                        <option value="" data-discount="0" data-type="none" data-min="0">-- Không áp dụng voucher --</option>
                        <?php foreach ($vouchers as $voucher): ?>
                        <option value="<?php echo htmlspecialchars($voucher['VoucherID']); ?>"
                                data-discount="<?php echo $voucher['DiscountPercent'] ?? $voucher['DiscountAmount']; ?>"
                                data-type="<?php echo $voucher['DiscountPercent'] ? 'percent' : 'amount'; ?>"
                                data-min="<?php echo $voucher['MinOrder']; ?>">
                            <?php echo htmlspecialchars($voucher['Code']); ?> - 
                            <?php if ($voucher['DiscountPercent']): ?>
                            Giảm <?php echo $voucher['DiscountPercent']; ?>%
                            <?php else: ?>
                            Giảm <?php echo formatCurrency($voucher['DiscountAmount']); ?>
                            <?php endif; ?>
                            (Tối thiểu: <?php echo formatCurrency($voucher['MinOrder']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Phương thức thanh toán & Vận chuyển -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Thanh toán & Vận chuyển</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select">
                            <?php foreach (getPaymentMethods() as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phương thức vận chuyển</label>
                        <select name="shipping_method" class="form-select" onchange="updateShippingFee(this)">
                            <option value="Standard" data-fee="30000" selected>Giao hàng tiêu chuẩn (30.000đ)</option>
                            <option value="Express" data-fee="50000">Giao hàng nhanh (50.000đ)</option>
                            <option value="Same Day" data-fee="80000">Giao trong ngày (80.000đ)</option>
                        </select>
                        <small class="text-success mt-1 d-block">
                            <i class="bi bi-gift me-1"></i>Miễn phí ship cho đơn từ 200.000đ
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phí vận chuyển (VNĐ)</label>
                        <input type="number" name="shipping_fee" id="shippingFee" class="form-control" 
                               value="30000" min="0" step="1000" onchange="updateTotal()" readonly>
                        <small class="text-muted">Tự động tính theo giá trị đơn hàng</small>
                    </div>
                </div>
            </div>

            <!-- Trạng thái -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Trạng thái đơn hàng</h6>
                </div>
                <div class="card-body">
                    <select name="order_status" class="form-select">
                        <?php foreach (getOrderStatuses() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key === 'Pending Confirmation' ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Nút submit -->
            <div class="d-grid gap-2">
                <button type="submit" name="create_order" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Tạo đơn hàng
                </button>
                <a href="<?php echo BASE_URL; ?>index.php?action=orders" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Hủy
                </a>
            </div>
        </div>
    </div>
</form>

<script>
let productRowTemplate;

// Đợi tất cả scripts load xong (bao gồm Select2 từ CDN)
window.addEventListener('load', function() {
    // Lưu template row đầu tiên
    productRowTemplate = $('#productsList .product-row').first().clone();
    
    // Khởi tạo Select2 cho các dropdown (với delay nhỏ để đảm bảo Select2 sẵn sàng)
    setTimeout(initializeSelect2, 100);
    
    // Cập nhật tổng ban đầu
    updateTotal();
});

function initializeSelect2() {
    if (typeof $.fn.select2 !== 'undefined') {
        // Customer Select với AJAX search theo email
        $('#customerSelect').select2({
            placeholder: 'Gõ email hoặc tên khách hàng để tìm...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 1,
            language: {
                inputTooShort: function() {
                    return 'Nhập ít nhất 1 ký tự để tìm kiếm...';
                },
                noResults: function() {
                    return 'Không tìm thấy khách hàng';
                },
                searching: function() {
                    return 'Đang tìm kiếm...';
                }
            },
            ajax: {
                url: 'ajax/search_customers.php',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination && data.pagination.more
                        }
                    };
                },
                cache: true
            },
            templateResult: formatCustomerResult,
            templateSelection: formatCustomerSelection
        });
        
        // SKU Select
        $('.sku-select').select2({
            placeholder: '-- Chọn sản phẩm --',
            allowClear: true,
            width: '100%'
        });
    }
}

// Format hiển thị kết quả tìm kiếm khách hàng
function formatCustomerResult(customer) {
    if (customer.loading) {
        return customer.text;
    }
    
    var statusClass = customer.status === 'Active' ? 'success' : 'secondary';
    var statusBadge = customer.status !== 'Active' ? '<span class="badge bg-' + statusClass + ' ms-1">' + customer.status + '</span>' : '';
    
    var $container = $(
        '<div class="d-flex flex-column py-1">' +
            '<div class="fw-bold">' + (customer.email || 'No email') + statusBadge + '</div>' +
            '<small class="text-muted">' + (customer.name || 'Unknown') + (customer.phone ? ' - ' + customer.phone : '') + '</small>' +
        '</div>'
    );
    
    return $container;
}

// Format hiển thị khách hàng đã chọn
function formatCustomerSelection(customer) {
    if (!customer.id) {
        return customer.text;
    }
    return customer.email ? customer.email + ' - ' + customer.name : customer.text;
}

function addProductRow() {
    const newRow = productRowTemplate.clone();
    newRow.find('select').val('');
    newRow.find('input').val(1);
    newRow.find('.item-total').text('0đ');
    $('#productsList').append(newRow);
    
    // Reinitialize Select2 for new row
    if (typeof $.fn.select2 !== 'undefined') {
        newRow.find('.sku-select').select2({
            placeholder: '-- Chọn sản phẩm --',
            allowClear: true,
            width: '100%'
        });
    }
}

function removeProductRow(btn) {
    const rows = $('#productsList .product-row');
    if (rows.length > 1) {
        $(btn).closest('.product-row').remove();
        updateTotal();
    } else {
        showToast('Phải có ít nhất một sản phẩm', 'warning');
    }
}

function updateProductInfo(select) {
    const row = $(select).closest('.product-row');
    const selectedOption = $(select).find(':selected');
    const price = parseFloat(selectedOption.data('price')) || 0;
    const quantity = parseInt(row.find('.quantity-input').val()) || 1;
    const itemTotal = price * quantity;
    
    row.find('.item-total').text(formatCurrencyJS(itemTotal));
    updateTotal();
}

function updateShippingFee(select) {
    // Phí ship mặc định từ option được chọn
    const baseFee = $(select).find(':selected').data('fee') || 30000;
    // Lưu phí gốc để dùng trong updateTotal
    $('#shippingFee').data('base-fee', baseFee);
    updateTotal();
}

function updateTotal() {
    let subtotal = 0;
    
    // Tính tổng từ các sản phẩm
    $('#productsList .product-row').each(function() {
        const select = $(this).find('.sku-select');
        const selectedOption = select.find(':selected');
        const price = parseFloat(selectedOption.data('price')) || 0;
        const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
        const itemTotal = price * quantity;
        
        $(this).find('.item-total').text(formatCurrencyJS(itemTotal));
        subtotal += itemTotal;
    });
    
    // Phí vận chuyển - Free ship cho đơn từ 200,000đ
    const FREE_SHIP_THRESHOLD = 200000;
    const DEFAULT_SHIPPING_FEE = 30000;
    let baseFee = parseFloat($('#shippingFee').data('base-fee')) || parseFloat($('select[name="shipping_method"]').find(':selected').data('fee')) || DEFAULT_SHIPPING_FEE;
    
    let shipping = 0;
    let isFreeShip = false;
    
    if (subtotal >= FREE_SHIP_THRESHOLD) {
        shipping = 0;
        isFreeShip = true;
    } else {
        shipping = baseFee;
    }
    
    // Cập nhật giá trị input hidden
    $('#shippingFee').val(shipping);
    
    // Voucher
    let voucherDiscount = 0;
    const voucherSelect = $('#voucherSelect');
    const voucherOption = voucherSelect.find(':selected');
    const discountValue = parseFloat(voucherOption.data('discount')) || 0;
    const discountType = voucherOption.data('type');
    const minOrder = parseFloat(voucherOption.data('min')) || 0;
    
    if (subtotal >= minOrder && discountValue > 0) {
        if (discountType === 'percent') {
            voucherDiscount = subtotal * discountValue / 100;
        } else if (discountType === 'amount') {
            voucherDiscount = discountValue;
        }
    }
    
    const total = subtotal - voucherDiscount + shipping;
    
    // Cập nhật hiển thị
    $('#subtotalDisplay').text(formatCurrencyJS(subtotal));
    
    if (isFreeShip) {
        $('#shippingDisplay').html('<span class="text-decoration-line-through text-muted">' + formatCurrencyJS(baseFee) + '</span> <span class="text-success fw-bold">0đ</span>');
        $('#freeShipNote').show();
    } else {
        $('#shippingDisplay').text(formatCurrencyJS(shipping));
        $('#freeShipNote').hide();
        if (subtotal > 0) {
            const remaining = FREE_SHIP_THRESHOLD - subtotal;
            $('#shippingDisplay').append('<br><small class="text-muted">Mua thêm ' + formatCurrencyJS(remaining) + ' để được free ship</small>');
        }
    }
    
    $('#voucherDisplay').text('-' + formatCurrencyJS(voucherDiscount));
    $('#totalDisplay').text(formatCurrencyJS(total));
}

function formatCurrencyJS(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}
</script>

<style>
.product-row {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.product-row:hover {
    background: #e9ecef;
}
</style>
