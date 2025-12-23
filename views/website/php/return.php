<?php 
// Đảm bảo session được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy orderId từ URL nếu chưa có từ controller
if (!isset($data)) {
    $data = [
        'orderId' => $_GET['order_id'] ?? '',
        'products' => []
    ];
}

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_data']) || isset($_SESSION['customer_id']) || isset($_SESSION['CustomerID']);

include '../../../partials/header.php'; 
?>

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
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="breadcrumb">
            <a href="index.php" class="breadcrumb-item home-icon">
                <img src="../img/home.svg">
                <i class="fas fa-home"></i>
            </a>
            <span class="separator"></span>
            <a href="my_account.php" class="breadcrumb-item">
                My Account
            </a>
            <span class="separator"></span>
            <a href="my_orders.php" class="breadcrumb-item">
                My Orders
            </a>
            <span class="separator"></span>
            <span class="breadcrumb-item active">
                Order Detail
            </span> 
        </div>
    </div>
    
    <!-- TITLE -->
    <div class="return-title">
        <h2>RETURN ORDER</h2>
    </div>

    <!-- FORM -->
    <form class="return-form" method="POST" action="/Candy-Crunch-Website/controllers/website/ReturnController.php?action=submitReturn" enctype="multipart/form-data" id="returnForm">
        
        <!-- Hidden input chứa OrderID -->
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($data['orderId'] ?? ($_GET['order_id'] ?? '')) ?>">
        <input type="hidden" name="refund_reason" id="refundReasonInput" value="">
        <input type="hidden" name="refund_method" id="refundMethodInput" value="">
        
        <!-- Chọn lý do -->
        <div class="input" data-type="dropdown" data-size="medium">
            <label class="input-label">Return reason</label>
            <div class="input-field">
                <div class="dropdown-trigger" id="returnReasonTrigger">
                    <span class="dropdown-text">Select a return reason</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="dropdown-arrow">
                    <path d="M18 9L12 15L6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="dropdown-menu" id="returnReasonMenu">
                    <button type="button" class="dropdown-option" data-value="Product is crushed or deformed">Product is crushed or deformed</button>
                    <button type="button" class="dropdown-option" data-value="Product is expired">Product is expired</button>
                    <button type="button" class="dropdown-option" data-value="Wrong Item Received">Wrong Item Received</button>
                    <button type="button" class="dropdown-option" data-value="Packaging has been tampered with">Packaging has been tampered with</button>
                    <button type="button" class="dropdown-option" data-value="Other">Other</button>
                </div>
            </div>
        </div>
    
        <!-- Viết mô tả -->
        <div class="input" data-optional="true" data-size="medium">
            <label class="input-label">Description</label>
            <div class="input-field">
              <input type="text" name="refund_description" placeholder="Describe your problem">
            </div>
        </div>
    
        <!-- Upload ảnh -->
         <div class="input" data-type="upload" data-size="medium">
            <label class="input-label">Upload image</label>
            <div class="input-field">
                <input type="file" name="refund_image" class="file-input" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>
    
        <!-- Chọn phương thức hoàn trả -->
        <div class="input" data-type="dropdown" data-size="medium">
            <label class="input-label">Refund method</label>
            <div class="input-field">
                <div class="dropdown-trigger" id="refundMethodTrigger">
                    <span class="dropdown-text">Select a refund method</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="dropdown-arrow">
                    <path d="M18 9L12 15L6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="dropdown-menu" id="refundMethodMenu">
                    <button type="button" class="dropdown-option" data-value="Refund via Bank transfer">Refund via Bank transfer</button>
                    <button type="button" class="dropdown-option" data-value="Issue a Gift Card">Issue a Gift Card</button>
                </div>
            </div>
        </div>
    
        <div class="return-submit">
            <button type="submit" class="btn-primary-large">Send Request</button>
        </div>
    </form>
    
    <!-- Script -->
    <script src="../js/main.js"></script>
    <script src="../js/return.js"></script>
    
</body>
</html>

<?php include '../../../partials/footer_kovid.php'; ?>
