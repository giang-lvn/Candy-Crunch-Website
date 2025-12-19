<?php
<<<<<<< Updated upstream

$ROOT = 'Candy-Crunch-Website';
require_once('../../partials/header.php');
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customer = $_SESSION['user_data'] ?? null;
if (!$customer) {
    // Chuyển hướng ngược lại Controller để nạp dữ liệu vào Session
    header('Location: /../../controllers/website/account_controller.php'); 
    exit;
}
$ROOT = ''; // hoặc '' nếu chạy ở root domain
require_once('../../../partials/header.php');
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Vouchers</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $ROOT ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?= $ROOT ?>/views/website/css/my_account.css">
    <link rel="stylesheet" href="<?= $ROOT ?>/views/website/css/my_vouchers.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600;700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

<!-- ================= BREADCRUMB ================= -->
<div class="breadcrumb-container">
    <div class="breadcrumb">
        <a href="<?= $ROOT ?>/index.php" class="breadcrumb-item home-icon">
            <i class="fas fa-home"></i>
        </a>
        <span class="separator"></span>
        <a href="<?= $ROOT ?>/views/website/my_account.php" class="breadcrumb-item">
            My Account
        </a>
        <span class="separator"></span>
        <span class="breadcrumb-item active">
            My Vouchers
        </span>
    </div>
</div>

<!-- ================= MAIN ================= -->
<div class="my-account-profile">

    <div class="title">
        <div class="my-account">MY VOUCHERS</div>
    </div>

    <div class="content">

        <!-- ========== SIDEBAR ========== -->
        <div class="card-account">
            <div class="user-card">
                <img class="avatar-icon"
                     src="<?= $ROOT ?>/views/website/img/ot-longvo.png"
                     alt="avatar">

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
                <a href="<?= $ROOT ?>/views/website/php/my_account.php" class="account-menu">
                    <img src="<?= $ROOT ?>/views/website/img/account.svg" alt="">
                    <div>My Account</div>
                </a>

                <a href="<?= $ROOT ?>/views/website/php/changepass.php" class="account-menu">
                    <img src="<?= $ROOT ?>/views/website/img/key.svg" alt="">
                    <div>Change Password</div>
                </a>

                <a href="<?= $ROOT ?>/views/website/php/my_orders.php" class="account-menu">
                    <img src="<?= $ROOT ?>/views/website/img/order.svg" alt="">
                    <div>My Orders</div>
                </a>

                <a href="<?= $ROOT ?>/views/website/php/my_vouchers.php" class="account-menu active">
                    <img src="<?= $ROOT ?>/views/website/img/voucher.svg" alt="">
                    <div>My Vouchers</div>
                </a>

                <a href="<?= $ROOT ?>/views/website/php/login.html" class="account-menu">
                    <img src="<?= $ROOT ?>/views/website/img/logout.svg" alt="">
                    <div>Log out</div>
                </a>
            </div>
        </div>

        <!-- ========== VOUCHER SECTION ========== -->
        <div class="profile-parent">

            <!-- FILTER -->
            <div class="filter">
                <span>Status :</span>
                <div class="status-dropdown">
                    <span class="selected">
                        All
                        <img class="icon-dropdown" src="<?= $ROOT ?>/views/website/img/dropdown.svg">
                    </span>
                    <ul class="status-list">
                        <li>All</li>
                        <li>Latest</li>
                        <li>Expiring Soon</li>
                    </ul>
                </div>
            </div>

            <!-- VOUCHER LIST -->
            <div class="vouchers-line">
                <div class="line">

                    <?php if (!empty($vouchers)): ?>
                        <?php foreach ($vouchers as $v): ?>
                            <div class="voucher-card">
                                <img alt="">

                                <div>
                                    <div>
                                        <div>
                                            <?php if ($v['DiscountPercent'] > 0): ?>
                                                <?= $v['DiscountPercent'] ?>% off
                                            <?php else: ?>
                                                <?= number_format($v['DiscountAmount']) ?>đ off
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            For orders over <?= number_format($v['MinOrder']) ?>đ
                                        </div>
                                    </div>

                                    <div>
                                        Expire date:
                                        <?= date('d/m/Y', strtotime($v['EndDate'])) ?>
                                    </div>
                                </div>

                                <button>Apply</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No vouchers available.</p>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= JS ================= -->
<script src="<?= $ROOT ?>/views/website/js/my_voucher.js"></script>

</body>
</html>

<?php
include '../../../partials/footer_kovid.php';
?>
