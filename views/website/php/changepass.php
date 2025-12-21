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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/changepass.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/my_account.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <title>Change Password</title>
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
                Change Password
            </span>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="my-account-profile">
        <div class="title">
            <div class="my-account">CHANGE PASSWORD</div>
        </div>

        <div class="content">
            <!-- SIDEBAR -->
            <div class="card-account">
                <div class="user-card">
                    <img class="avatar-icon" src="<?php echo $ROOT; ?>/views/website/img/ot-longvo.png" alt="avatar">
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
                    <a href="my_account.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/account.svg" alt="my account">
                        <div class="sidebar-ele"><div class="my-orders">My Account</div></div>
                    </a>
                    <a href="change_password.php" class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/key.svg" alt="change password">
                        <div class="sidebar-ele"><div class="my-orders">Change Password</div></div>
                    </a>
                    <a href="my_orders.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/order.svg" alt="orders">
                        <div class="sidebar-ele"><div class="my-orders2">My Orders</div></div>
                    </a>
                    <a href="vouchers.php" class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/voucher.svg" alt="vouchers">
                        <div class="sidebar-ele"><div class="my-orders2">My Vouchers</div></div>
                    </a>
                    <a href="<?php echo $ROOT; ?>/views/website/login.php" class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/logout.svg" alt="logout">
                        <div class="sidebar-ele"><div class="my-orders2">Log out</div></div>
                    </a>
                </div>
            </div>

            <!-- FORM CHANGE PASSWORD -->
            <div class="profile-parent">
                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3">
                                <div class="text">Change Password</div>
                            </div>
                            <div class="button">
                                <button class="btn-primary-outline-medium" id="savePassBtn">Confirm</button>
                            </div>
                        </div>
                    </div>

                    <div class="profile2">
                        <div class="input" data-optional="true">
                            <label class="input-label">Current Password</label>
                            <div class="input-field">
                                <input type="password" id="currentPassword" placeholder="e.g. your current password" >
                            </div>
                        </div>
                        <div class="input" data-optional="true">
                            <label class="input-label">New Password</label>
                            <div class="input-field">
                                <input type="password" id="newPassword" placeholder="e.g. your new password" >
                            </div>
                        </div>
                        <div class="input" data-optional="true">
                            <label class="input-label">Confirm New Password</label>
                            <div class="input-field">
                                <input type="password" id="confirmPassword" placeholder="Confirm new password">                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JS -->
    <script src="<?php echo $ROOT; ?>/views/website/js/change_password.js"></script>
</body>
</html>
<?php
include '../../../partials/footer_kovid.php';
?>
