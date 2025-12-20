
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Giả sử CustomerID được lưu trong session khi login
$customerID = isset($_SESSION['CustomerID']) ? $_SESSION['CustomerID'] : 0;

// Giả sử orderID được truyền từ cart.php hoặc lấy động từ DB
$orderID = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/cancel.css">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <!-- Popup -->
    <div id="cancel-order-overlay" class="cancel-overlay hidden">
        <div class="cancel-popup">
            <!-- Nút đóng popup -->
            <button class="close-btn" id="cancelPopupClose">&times;</button>
        
            <!-- Title -->
            <h2 class="cancel-title">Cancel Order</h2>
        
            <!-- Description -->
            <p class="cancel-desc">
                Please let Candy Crunch know the reason for canceling your order.
                Paid orders will be refunded according to our refund policy.
            </p>
        
            <!-- Chọn lý do -->
            <div class="input" data-type="dropdown" data-size="medium">
                <label class="input-label">Return reason</label>
                <div class="input-field">
                    <div class="dropdown-trigger" id="dropdownTrigger">
                        <span class="dropdown-text">Select a return reason</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="dropdown-arrow">
                        <path d="M18 9L12 15L6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <button class="dropdown-option" data-value="Changed my mind">Changed my mind</button>
                        <button class="dropdown-option" data-value="Ordered wrong item">Ordered wrong item</button>
                        <button class="dropdown-option" data-value="Found a better price">Found a better price</button>
                        <button class="dropdown-option" data-value="Other">Other</button>
                    </div>
                </div>
            </div>

            <!-- Input ẩn để gửi order_id -->
            <input type="hidden" id="cancelOrderID" value="<?php echo $orderID; ?>">

            <div class="return-submit">
                <button class="btn-primary-medium" id="submitCancelOrder">Send Request</button>
                <button class="btn-secondary-outline-medium" id="contactBtn">Contact</button>
            </div>

            <!-- Hiển thị message trả về 
            <p id="cancelMessage" style="color: red; margin-top: 10px;"></p>-->
        </div>
    </div>
    
    <!-- Script -->
    <script src="../js/main.js"></script>
    <script src="../js/cancel.js"></script>
    
</body>
</html>
