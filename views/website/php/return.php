

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Form</title>
    <!-- Preload Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/return.css">
    <link rel="stylesheet" href="../css/main.css">

</head>
<body>
    <!-- BREADCRUMB 
    <div class="breadcrumb">
        <nav>
            <a href="index.html">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 10V17.5H16.5V10M6 11.5L12.25 6L18.5 11.5" stroke="#9E9E9E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="5" height="8" viewBox="0 0 5 8" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.561 3.854L0.707 7.708L0 7.001L3.147 3.854L0 0.708L0.707 0L4.561 3.854Z" fill="#9E9E9E"/>
                </svg>
            </span>
            <a href="#">Order</a>
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="5" height="8" viewBox="0 0 5 8" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.561 3.854L0.707 7.708L0 7.001L3.147 3.854L0 0.708L0.707 0L4.561 3.854Z" fill="#9E9E9E"/>
                </svg>
            </span>
            <span>Return</span>
        </nav>
    </div> -->
    
    <!-- TITLE -->
    <div class="return-title">
        <h2>RETURN ORDER</h2>
    </div>

    <!-- FORM -->
    <form class="return-form" method="POST" action="/index.php?controller=return&action=submitReturn" enctype="multipart/form-data">
        
        <!-- ← THÊM: Hidden input chứa OrderID -->
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($data['orderId']) ?>">
        
        <!-- PRODUCTS -->
        <div class="return-products">
            <div class="return-products-title">
                <h3>Product (Order #<?= htmlspecialchars($data['orderId']) ?>)</h3>
            </div>
        
            <div class="return-products-grid">
                <?php if (!empty($data['products'])): ?>
                    <?php foreach ($data['products'] as $product): ?>
                        <div class="return-product-item">
                            <div class="single-product">
                                <!-- ← XÓA: Bỏ checkbox -->
                                
                                <div class="product-info">
                                    <div class="product-image">
                                        <img src="<?= htmlspecialchars($product['Image'] ?? '../img/default.jpg') ?>" alt="Product">
                                    </div>
                                
                                    <div class="product-details">
                                        <div class="product-name"><?= htmlspecialchars($product['ProductName']) ?></div>
                                        <div class="product-attribute"><?= htmlspecialchars($product['Attribute']) ?>g</div>
                                        <div class="product-price-qty">
                                            <div class="product-quantity">Qty: <?= htmlspecialchars($product['OrderQuantity']) ?></div>
                                            <div class="product-price">
                                                <span class="price-old"><?= number_format($product['OriginalPrice'], 0, ',', '.') ?> VND</span>
                                                <span class="price-new"><?= number_format($product['PromotionPrice'], 0, ',', '.') ?> VND</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products found in this order.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- RETURN INFO -->
        <div class="refurn">
    
            <div class="return-products-title">
                <h3>Return Request Form</h3>
            </div>
        
            <!-- Chọn lý do -->
            <div class="input" data-type="dropdown" data-size="medium">
                <label class="input-label">Return reason</label>
                <div class="input-field">
                  <div class="dropdown-trigger">
                    <span class="dropdown-text">Select a return reason</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="dropdown-arrow">
                      <path d="M18 9L12 15L6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="dropdown-menu">
                    <button class="dropdown-option" data-value="1">Option 1</button>
                    <button class="dropdown-option" data-value="2">Option 2</button>
                    <button class="dropdown-option" data-value="3">Option 3</button>
                  </div>
                </div>
            </div>
        
            <!-- Viết mô tả -->
            <div class="input" data-optional="true" data-size="medium">
                <label class="input-label">Description</label>
                <div class="input-field">
                  <input type="text" placeholder="Describe your problem">
                </div>
            </div>
        
            <!-- Upload ảnh (Ngôn ngữ hiển thị theo trình duyệt )-->
             <div class="input" data-type="upload" data-size="medium">
                <label class="input-label">Upload image</label>
                <div class="input-field">
                    <input type="file" class="file-input">
                </div>
            </div>

            <!-- Tùy chỉnh phần chữ hiển thị 
            <div class="input" data-type="upload" data-size="medium">
                <label class="input-label">Upload image</label>
                <div class="input-field">
                    <label class="custom-upload">
                        <span class="upload-text">No file selected</span>
                        <input type="file" accept="image/*" hidden>
                    </label>
                </div>
            </div> -->
        
            <!-- Chọn phương thức hoàn trả -->
            <div class="input" data-type="dropdown" data-size="medium">
                <label class="input-label">Refund method</label>
                <div class="input-field">
                  <div class="dropdown-trigger">
                    <span class="dropdown-text">Select a refund method</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="dropdown-arrow">
                      <path d="M18 9L12 15L6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="dropdown-menu">
                    <button class="dropdown-option" data-value="Product is crushed or deformed">Product is crushed or deformed</button>
                    <button class="dropdown-option" data-value="Product is expired">Product is expired</button>
                    <button class="dropdown-option" data-value="Wrong Item Received">Wrong Item Received</button>
                    <button class="dropdown-option" data-value="Packaging has been tampered with">Packaging has been tampered with</button>
                    <button class="dropdown-option" data-value="Other">Other</button>
                  </div>
                </div>
            </div>
        
            <div class="return-submit">
                <button class="btn-primary-large">Send Request</button>
            </div>

        </div>
    </form>
    
    <!-- Script -->
    <script src="../js/main.js"></script>
    <script src="../js/return.js"></script>
    
</body>
</html>

<?php include '../../../partials/footer_kovid.php'; ?>
