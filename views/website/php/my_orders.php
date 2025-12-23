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
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/rating.css">
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
                    <img class="avatar-icon" src="<?php echo !empty($customer['Avatar']) ? $ROOT . $customer['Avatar'] : $ROOT . '/views/website/img/default-avatar.png'; ?>" alt="avatar" onerror="this.src='<?php echo $ROOT; ?>/views/website/img/default-avatar.png'">
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
                    <a href="<?php echo $ROOT; ?>/views/website/my_orders.php" class="account-menu active">
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
                                <li data-value="pending-cancel">Pending Cancel</li>
                                <li data-value="pending-return">Pending Return</li>
                                <li data-value="return">Returned</li>
                                <li data-value="cancel">Cancelled</li>
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

    <!-- Rating Popup -->
    <div id="rating-overlay" class="overlay hidden">
        <div class="rating-popup">
            <button class="close-btn" id="closeRatingPopup">&times;</button>
            <h2 class="rating-title">Rating</h2>
            <p class="rating-desc">Share your thoughts and help Candy Crunch get even sweeter!</p>
            
            <input type="hidden" id="rating-order-id" value="">
            
            <div class="input">
                <label class="input-label">Select Product</label>
                <div class="input-field">
                    <select id="rating-product-select">
                        <!-- Products will be populated dynamically -->
                    </select>
                </div>
            </div>

            <div class="input">
                <label class="input-label">Your Rating</label>
                <div class="star-rating" data-rating="0">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
            </div>

            <div class="input">
                <label class="input-label">Product Review</label>
                <div class="input-field">
                    <textarea id="rating-review-text" placeholder="Provide a detailed review..." rows="3"></textarea>
                </div>
            </div>

            <div class="return-submit">
                <button class="btn-primary-medium" id="submitRating">Submit</button>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="<?php echo $ROOT; ?>/views/website/js/my_orders.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/rating.js"></script>
</body>
</html>
<?php
include '../../../partials/footer_kovid.php';
?>
