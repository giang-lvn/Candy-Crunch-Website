<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy dữ liệu từ Session mà Controller đã chuẩn bị
$customer  = $_SESSION['user_data'] ?? null;
$addresses = $_SESSION['user_addresses'] ?? [];
$banking   = $_SESSION['user_banking'] ?? [];

// Nếu user truy cập trực tiếp file view mà chưa qua Controller xử lý dữ liệu
if (!$customer) {
    // Chuyển hướng ngược lại Controller để nạp dữ liệu vào Session
    header('Location: /../../controllers/website/account_controller.php'); 
    exit;
}
$ROOT = '';
require_once __DIR__ . '/../../../partials/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>My Account</title>
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/my_account.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="breadcrumb-container">
        <div class="breadcrumb">
            <a href="index.html" class="breadcrumb-item home-icon">
                <i class="fas fa-home"></i>
            </a>
            <span class="separator"></span>
            <span class="breadcrumb-item active">My Account</span>
        </div>          
    </div> 
    
    <div class="my-account-profile">
        <div class="title">
            <div class="my-account">MY ACCOUNT</div>
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
                    <div class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/account.svg" alt="my account">
                        <div class="sidebar-ele">
                            <div class="my-orders">My Account</div>
                        </div>
                    </div>
                    <div class="account-menu" id="menuChangePassword">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/key.svg" alt="change">
                        <div class="sidebar-ele">
                            <div class="my-orders2">Change Password</div>
                        </div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/order.svg" alt="orders">
                        <div class="sidebar-ele">
                            <div class="my-orders2">My Orders</div>
                        </div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/voucher.svg" alt="voucher">
                        <div class="sidebar-ele">
                            <div class="my-orders2">My Vouchers</div>
                        </div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/logout.svg" alt="logout">
                        <div class="sidebar-ele">
                            <div class="my-orders2">Log out</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-parent">
                <!-- MY PROFILE -->
                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3">
                                <div class="text">My profile</div>
                            </div>
                            <div class="button" id="editProfileBtn">
                                <div class="texttitle">
                                    <div class="text2">
                                        <div class="text4">Edit Information</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile2">
                        <div class="frame-div">
                            <div class="info">
                                <div class="line">
                                    <div class="label"><div class="gender">Email</div></div>
                                    <div class="value"><div class="male" id="displayEmail"><?php echo htmlspecialchars($customer['Email'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label"><div class="gender">First name</div></div>
                                    <div class="value"><div class="male" id="displayFirstName"><?php echo htmlspecialchars($customer['FirstName'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label"><div class="gender">Last name</div></div>
                                    <div class="value"><div class="male" id="displayLastName"><?php echo htmlspecialchars($customer['LastName'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label"><div class="gender">Gender</div></div>
                                    <div class="value"><div class="male" id='displayGender'><?php echo htmlspecialchars($customer['CustomerGender'] ?? 'Other'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label"><div class="gender">Date of Birth</div></div>
                                    <div class="value"><div class="male" id="displayDOB"><?php echo htmlspecialchars($customer['CustomerBirth'] ?? '-'); ?></div></div>
                                </div>
                            </div>
                            <div class="avatar">
                                <img class="avatar-icon2" src="<?php echo $ROOT; ?>/views/website/img/ot-longvo.png" alt="avatar">
                                <div class="button2">
                                    <div class="texttitle">
                                        <div class="text2">
                                            <div class="text4">Choose image</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="caption">
                                    <div class="gender">Maximum file size: 1 MB<br>Supported formats: .JPEG, .PNG</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BANKING INFORMATION -->
                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3">
                                <div class="text">Banking Information</div>
                            </div>
                            <div class="button-group">
                                <div class="button3">
                                    <div class="texttitle">
                                        <div class="text2">
                                            <div class="text4">Edit Information</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="button4">
                                    <div class="texttitle">
                                        <div class="text2">
                                            <div class="text4">Link Bank Account</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile3">
                        <div class="content3">
                            <?php if (!empty($banking)): ?>
                            <div class="info2">
                                <div class="line2">
                                    <div class="label7"><div class="gender">Account Number</div></div>
                                    <div class="value6">
                                        <div class="gender">
                                            <?php 
                                                $accountNum = $banking['AccountNumber'] ?? '';
                                                echo $accountNum ? '************' . substr($accountNum, -4) : '-';
                                            ?>
                                        </div>
                                    </div>
                                    <img id="toggleAccountButton" class="icon-toggle" src="<?php echo $ROOT; ?>/views/website/img/eye-closed.svg" alt="Show" title="Click to show" style="cursor:pointer;">
                                    <img id="toggleAccountClose" class="icon-toggle" src="<?php echo $ROOT; ?>/views/website/img/eye-open.svg" alt="Hide" title="Click to hide" style="cursor: pointer; display:none;">
                                </div>
                                <div class="line2">
                                    <div class="label8"><div class="gender">Bank</div></div>
                                    <div class="value7"><div class="gender"><?php echo htmlspecialchars($banking['BankName'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label8"><div class="gender">Bank Branch</div></div>
                                    <div class="value7"><div class="gender"><?php echo htmlspecialchars($banking['BankBranchName'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label7"><div class="gender">Account Holder Name</div></div>
                                    <div class="value9"><div class="gender"><?php echo htmlspecialchars($banking['AccountHolderName'] ?? '-'); ?></div></div>
                                </div>
                                <div class="line2">
                                    <div class="label7"><div class="gender">ID Number</div></div>
                                    <div class="value9">
                                        <div class="gender">
                                            <?php 
                                                $idNum = $banking['IDNumber'] ?? '';
                                                echo $idNum ? '************' . substr($idNum, -4) : '-';
                                            ?>
                                        </div>
                                    </div>
                                    <img id="toggleAccountButton" class="icon-toggle" src="<?php echo $ROOT; ?>/views/website/img/eye-closed.svg" alt="Show" title="Click to show" style="cursor:pointer;">
                                    <img id="toggleAccountClose" class="icon-toggle" src="<?php echo $ROOT; ?>/views/website/img/eye-open.svg" alt="Hide" title="Click to hide" style="cursor: pointer; display:none;">
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="info2">
                                <p style="text-align: center; color: #999; padding: 20px;">No banking information found. Please link your bank account.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- SHIPPING INFORMATION -->
                <div class="shipping">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3">
                                <div class="text">Shipping Information</div>
                            </div>
                            <div class="button-group">
                                <div class="button6">
                                    <div class="texttitle">
                                        <div class="text2">
                                            <div class="text4">Edit Information</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="button4">
                                    <div class="texttitle">
                                        <div class="text2">
                                            <div class="text4">Add New Address</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile4">
                        <div class="frame-parent">
                            <div class="frame-group">
                                <?php if (!empty($addresses)): ?>
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="frame-container">
                                            <div class="frame-div">
                                                <div class="frame-parent5">
                                                    <div class="john-doe-wrapper">
                                                        <div class="text4 ship-name"><?php echo htmlspecialchars($address['Fullname'] ?? '-'); ?></div>
                                                    </div>
                                                </div>
                                                <?php if (isset($address['AddressDefault']) && $address['AddressDefault'] === 'Yes'): ?>
                                                    <div class="status-tag">
                                                        <div class="completed">Default</div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="status-tag2"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="sunset-boulevard-los-angeles-wrapper">
                                                <div class="gender ship-address" 
                                                     data-phone="<?php echo htmlspecialchars($address['Phone'] ?? ''); ?>">
                                                    <?php 
                                                        $addressParts = array_filter([
                                                            $address['Address'] ?? '',
                                                            $address['CityState'] ?? '',
                                                            $address['Country'] ?? ''
                                                        ]);
                                                        echo htmlspecialchars(implode(', ', $addressParts) ?: '-');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="text-align: center; color: #999; padding: 20px;">No shipping addresses found. Please add a new address.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT PROFILE MODAL -->
    <div class="modal-overlay" id="editModal">
        <div class="new-address">
            <div class="edit-my-profile-wrapper">
                <div class="edit-my-profile">Edit My Profile</div>
            </div>
            <div class="frame-parent-modal">
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="texttitle-edit">
                                    <div class="text-edit">
                                        <div class="male">First name</div>
                                    </div>
                                </div>
                                <div class="texttitle2">
                                    <div class="text4">
                                        <div class="text-edit">
                                            <div class="text6">(optional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="editFirstName" placeholder="First name">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="texttitle-edit">
                                    <div class="text-edit">
                                        <div class="male">Last name</div>
                                    </div>
                                </div>
                                <div class="texttitle2">
                                    <div class="text4">
                                        <div class="text-edit">
                                            <div class="text6">(optional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="editLastName" placeholder="Last name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="input3">
                    <div class="head">
                        <div class="label3">
                            <div class="texttitle-edit">
                                <div class="text-edit">
                                    <div class="male">Email</div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <input type="email" class="gender" id="editEmail" placeholder="Email">
                        </div>
                    </div>
                </div>
                <div class="input3">
                    <div class="head">
                        <div class="label3">
                            <div class="texttitle-edit">
                                <div class="text-edit">
                                    <div class="male">Date of Birth</div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="editDOB" placeholder="YYYY/MM/DD">
                        </div>
                    </div>
                </div>
                <div class="line-edit">
                    <div class="label5">
                        <div class="gender">Gender</div>
                    </div>
                    <div class="radio-edit">
                        <div class="icon-radio-picked-parent" onclick="selectGender('Male')" style="cursor:pointer;">
                            <div class="icon-radio-picked" id="radio-male">
                                <img class="vector-icon" alt="">
                            </div>
                            <div class="male">Male</div>
                        </div>
                        <div class="icon-radio-picked-parent" onclick="selectGender('Female')">
                            <div class="icon-radio-picked" id="radio-female">
                                <img class="vector-icon" alt="">
                            </div>
                            <div class="male">Female</div>
                        </div>
                        <div class="icon-radio-picked-parent" onclick="selectGender('Other')">
                            <div class="icon-radio-picked" id="radio-other">
                                <img class="vector-icon" alt="">
                            </div>
                            <div class="male">Other</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-parent">
                <div class="button-save" id="saveBtn">
                    <div class="texttitle">
                        <div class="text-edit">
                            <div class="text33">Save</div>
                        </div>
                    </div>
                </div>
                <div class="button-cancel" id="cancelBtn">
                    <div class="texttitle">
                        <div class="text-edit">
                            <div class="text33">Cancel</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SHIPPING MODAL -->
    <div class="modal-overlay" id="ShippingModal">
        <div class="new-address" style="max-width:520px">
            <div class="edit-my-profile-wrapper">
                <div class="edit-my-profile" id="shippingModalTitle">Edit Shipping Address</div>
            </div>
            <div class="frame-parent-modal">
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Full Name</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="shipName" placeholder="John Doe">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Phone Number</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="shipPhone" placeholder="+1 234 567 8900">
                            </div>
                        </div>
                    </div>
                    <div class="input3">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Address</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="shipAddress" placeholder="123 Sunset Boulevard">
                            </div>
                        </div>
                    </div>
                    <div class="input-parent">
                        <div class="input">
                            <div class="head">
                                <div class="label-edit">
                                    <div class="male">City/State</div>
                                </div>
                                <div class="field">
                                    <input type="text" class="gender" id="shipCity" placeholder="Los Angeles">
                                </div>
                            </div>
                        </div>
                        <div class="input">
                            <div class="head">
                                <div class="label-edit">
                                    <div class="male">Country</div>
                                </div>
                                <div class="field">
                                    <input type="text" class="gender" id="shipCountry" placeholder="United States">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-parent">
                <button class="btn-primary-medium" id="saveShippingBtn">Save</button>
                <button class="btn-secondary-outline-medium" id="cancelShippingBtn">Cancel</button>
                <button class="btn-error-outline-medium" id="deleteShippingBtn" style="margin-top:12px; width:100%">Delete</button>
            </div>
        </div>
    </div>
    
    <script src="<?php echo $ROOT; ?>/views/website/js/my_account.js"></script>
</body>
</html>
<?php
include __DIR__ . '/../../../partials/footer_kovid.php';
?>