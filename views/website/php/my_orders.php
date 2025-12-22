<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customer = $_SESSION['user_data'] ?? null;
if (!$customer) {
    // Chuyển hướng ngược lại Controller để nạp dữ liệu vào Session
    header('Location: /Candy-Crunch-Website/controllers/website/account_controller.php'); 
    exit;
}
$ROOT = '/Candy-Crunch-Website'; // hoặc '' nếu chạy ở root domain
require_once('../../../partials/header.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/my_account.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/my_orders.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/cancel.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@300;400;500;600;700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>My Orders</title>
</head>
<body>
    <!-- BREADCRUMB -->
    <div class="breadcrumb-container">
        <div class="breadcrumb">
            <a href="<?php echo $ROOT; ?>/index.php" class="breadcrumb-item home-icon">
            <i class="fas fa-home"></i>
            </a>
            <span class="separator"></span>
            <a href="<?php echo $ROOT; ?>/views/website/my_account.php" class="breadcrumb-item">
                My Account
            </a>
            <span class="separator"></span>
            <span class="breadcrumb-item active">
                My Orders
            </span>
        </div>
    </div>

    <div class="my-account-profile">
        <div class="title">
            <div class="my-account">MY ORDERS</div>
        </div>

        <div class="content">
            <!-- SIDEBAR -->
            <div class="card-account">
                <div class="user-card">
                    <img class="avatar-icon" src="<?php echo !empty($customer['Avatar']) ? htmlspecialchars($customer['Avatar']) : $ROOT . '/views/website/img/ot-longvo.png'; ?>" alt="avatar">
                    <div class="user-name">
                        <div class="john-doe">
                            <?php
                            $fullName = trim(($customer['FirstName'] ?? '') . ' ' . ($customer['LastName'] ?? ''));
                            echo htmlspecialchars($fullName ?: 'Guest User'); 
                            ?>
                        </div>
                    </div>
                </div>
                <div class="menus">
                    <a href="<?php echo $ROOT; ?>/views/website/my_account.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/account.svg" alt="my account">
                        <div class="sidebar-ele"><div class="my-orders2">My Account</div></div>
                    </a>
                    <a href="<?php echo $ROOT; ?>/views/website/changepass.php" class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/key.svg" alt="change password">
                        <div class="sidebar-ele"><div class="my-orders2">Change Password</div></div>
                    </a>
                    <a href="<?php echo $ROOT; ?>/views/website/my_orders.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/order.svg" alt="orders">
                        <div class="sidebar-ele"><div class="my-orders2">My Orders</div></div>
                    </a>
                    <a href="<?php echo $ROOT; ?>/views/website/my_vouchers.php" class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/voucher.svg" alt="voucher">
                        <div class="sidebar-ele"><div class="my-orders2">My Vouchers</div></div>
                    </a>
                    <a href="<?php echo $ROOT; ?>/views/website/logout.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/logout.svg" alt="logout">
                        <div class="sidebar-ele"><div class="my-orders2">Log out</div></div>
                    </a>
                </div>
            </div>

            <!-- RIGHT CONTENT -->
            <section class="right">
                <!-- FILTER -->
                <div class="filter-parent">
                    <div class="filter">
                        <span>Total: <b id="totalOrders">12 Orders</b></span>
                    </div>

                    <div class="filters">
                        <div class="filter2" id="statusFilter">
                            <span>Status:</span>
                            <div class="attribute2">
                                <span id="statusLabel">All</span>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E">
                            </div>
                            <ul class="dropdown-menu" id="statusMenu">
                                <li data-value="all">All</li>
                                <li data-value="pending-confirm">Pending Confirmation</li>
                                <li data-value="waiting-payment">Waiting Payment</li>
                                <li data-value="pending">Pending</li>
                                <li data-value="on-shipping">On Shipping</li>
                                <li data-value="completed">Completed</li>
                                <li data-value="return">Return</li>
                                <li data-value="cancel">Cancel</li>
                            </ul>
                        </div>
                        <div class="filter2" id="timeFilter">
                            <span>Time:</span>
                            <div class="attribute2">
                                <span id="timeLabel">Last 30 days</span>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E">
                            </div>
                            <ul class="dropdown-menu" id="timeMenu">
                                <li data-value="7">Last 7 days</li>
                                <li data-value="30">Last 30 days</li>
                                <li data-value="90">Last 3 months</li>
                                <li data-value="365">Last year</li>
                                <li data-value="all">All time</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ORDER LIST -->
                <div class="order-list" id="orderList"></div>
            </section>
        </div>
    </div>

    <!-- JS -->
    <script src="<?php echo $ROOT; ?>/views/website/js/my_orders.js"></script>

    <!-- Popup Cancel Order -->
    <div id="cancel-order-overlay" class="cancel-overlay hidden">
        <div class="cancel-popup">
            <button class="close-btn" id="cancelPopupClose">&times;</button>
            <h2 class="cancel-title">Cancel Order</h2>
            <p class="cancel-desc">
                Please let Candy Crunch know the reason for canceling your order.
                Paid orders will be refunded according to our refund policy.
            </p>
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
            <input type="hidden" id="cancelOrderID" value="">
            <div class="return-submit">
                <button class="btn-primary-medium" id="submitCancelOrder">Send Request</button>
            </div>
            <p id="cancelMessage" style="text-align: center; margin-top: 10px;"></p>
        </div>
    </div>
</body>
</html>
<?php
include '../../../partials/footer_kovid.php';
?>
