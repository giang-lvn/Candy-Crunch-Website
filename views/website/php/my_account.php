<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$customer  = $_SESSION['user_data'] ?? null;
$addresses = $_SESSION['user_addresses'] ?? [];
$banking   = $_SESSION['user_banking'] ?? [];

if (!$customer) {
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
                        <div class="sidebar-ele"><div class="my-orders">My Account</div></div>
                    </div>
                    <div class="account-menu" id="menuChangePassword">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/key.svg" alt="change">
                        <div class="sidebar-ele"><div class="my-orders2">Change Password</div></div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/order.svg" alt="orders">
                        <div class="sidebar-ele"><div class="my-orders2">My Orders</div></div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-key" src="<?php echo $ROOT; ?>/views/website/img/voucher.svg" alt="voucher">
                        <div class="sidebar-ele"><div class="my-orders2">My Vouchers</div></div>
                    </div>
                    <div class="account-menu">
                        <img class="icon-account-outline" src="<?php echo $ROOT; ?>/views/website/img/logout.svg" alt="logout">
                        <div class="sidebar-ele"><div class="my-orders2">Log out</div></div>
                    </div>
                </div>
            </div>
            
            <div class="profile-parent">
                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3"><div class="text">My profile</div></div>
                            <div class="button" id="editProfileBtn">
                                <div class="texttitle"><div class="text4">Edit Information</div></div>
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
                                <div class="button2"><div class="texttitle"><div class="text4">Choose image</div></div></div>
                                <div class="caption"><div class="gender">Max: 1 MB (.JPEG, .PNG)</div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3"><div class="text">Banking Information</div></div>
                            <div class="button-group">
                            <div class="button3" id="editBankingInfoBtn">
                                    <div class="texttitle"><div class="text4">Edit Information</div></div>
                            </div>
                            <div class="button4" id="addBankingBtn">
                                <div class="texttitle"><div class="text4">Add Bank Account</div></div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="profile4">
                        <div class="frame-parent">
                            <div class="frame-group">
                                <?php if (!empty($banking)): ?>
                                    <?php foreach ($banking as $b): ?>
                                        <div class="frame-container banking-item"
                                            data-banking-id="<?php echo htmlspecialchars($b['BankingID'] ?? ''); ?>"
                                            data-account-number="<?php echo htmlspecialchars($b['AccountNumber'] ?? ''); ?>"
                                            data-bank-name="<?php echo htmlspecialchars($b['BankName'] ?? ''); ?>"
                                            data-bank-branch="<?php echo htmlspecialchars($b['BankBranchName'] ?? ''); ?>"
                                            data-holder-name="<?php echo htmlspecialchars($b['AccountHolderName'] ?? ''); ?>"
                                            data-id-number="<?php echo htmlspecialchars($b['IDNumber'] ?? ''); ?>">

                                            <div class="frame-div">
                                                <div class="frame-parent5">
                                                    <div class="john-doe-wrapper">
                                                        <div class="text4"><?php echo htmlspecialchars($b['BankName'] ?? 'Bank Account'); ?></div>
                                                    </div>
                                                </div>
                                                <?php if (($b['IsDefault'] ?? '') === 'Yes'): ?>
                                                    <div class="status-tag">
                                                        <div class="completed">Default</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="sunset-boulevard-los-angeles-wrapper">
                                                <div class="gender">
                                                    <div class="account-row" style="display:flex; align-items:center; gap:8px;">
                                                        <span class="acc-val" data-original="<?php echo htmlspecialchars($b['AccountNumber'] ?? ''); ?>">
                                                            ************<?php echo substr($b['AccountNumber'] ?? '', -4); ?>
                                                        </span>
                                                    </div>
                                                    <br>
                                                    <div>Owner: <?php echo htmlspecialchars($b['AccountHolderName'] ?? '-'); ?></div>
                                                    <br>
                                                    
                                                    <div class="id-row" style="display:flex; align-items:center; gap:8px;">
                                                        ID: <span class="id-val" data-original="<?php echo htmlspecialchars($b['IDNumber'] ?? ''); ?>">************<?php echo substr($b['IDNumber'] ?? '', -4); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="text-align: center; color: #999; padding: 20px; width:100%;">No banking info found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile">
                    <div class="title2">
                        <div class="heading">
                            <div class="title3"><div class="text">Shipping Information</div></div>
                            <div class="button-group">
                                <div class="button3" id="editAddressBtn">
                                    <div class="texttitle"><div class="text4">Edit Information</div></div>
                                </div>
                                <div class="button4" id="addAddressBtn">
                                    <div class="texttitle"><div class="text4">Add New Address</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile4">
                        <div class="frame-parent">
                            <div class="frame-group">
                                <?php if (!empty($addresses)): ?>
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="frame-container address-item" data-address-id="<?= $address['AddressID'] ?>">
                                            <div class="frame-div">
                                                <div class="frame-parent5">
                                                    <div class="john-doe-wrapper">
                                                        <div class="text4 ship-name"><?php echo htmlspecialchars($address['Fullname'] ?? '-'); ?></div>
                                                    </div>
                                                </div>
                                                <?php if (($address['IsDefault'] ?? '') === 'Yes'): ?>
                                                    <div class="status-tag">
                                                        <div class="completed">Default</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="sunset-boulevard-los-angeles-wrapper">
                                                <div class="gender ship-address">
                                                    <?php 
                                                        $addr = array_filter([$address['Address']??'', $address['City']??'', $address['Country']??'']);
                                                        echo htmlspecialchars(implode(', ', $addr) ?: '-');
                                                    ?>
                                                    <br>
                                                    <br>
                                                    Phone: <?php echo htmlspecialchars($address['Phone'] ?? '-'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="text-align: center; color: #999; padding: 20px; width:100%;">No addresses found.</p>
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
                        <div class="icon-radio-picked-parent" onclick="selectGender('male')" style="cursor:pointer;">
                            <div class="icon-radio-picked" id="radio-male">
                                <img class="vector-icon" alt="">
                            </div>
                            <div class="male">Male</div>
                        </div>
                        <div class="icon-radio-picked-parent" onclick="selectGender('female')">
                            <div class="icon-radio-picked" id="radio-female">
                                <img class="vector-icon" alt="">
                            </div>
                            <div class="male">Female</div>
                        </div>
                        <div class="icon-radio-picked-parent" onclick="selectGender('other')">
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

    <!-- BANKING MODAL -->
    <div class="modal-overlay" id="BankingModal">
        <div class="new-address" style="max-width:520px">
            <div class="edit-my-profile-wrapper">
                <div class="edit-my-profile" id="bankingModalTitle">Edit Banking Account</div>
            </div>
            <div class="frame-parent-modal">
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Account Number</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="bankAccountNumber" placeholder="Your Account Number">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Bank</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="bankName" placeholder="Bank Name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Bank Branch</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="bankBranch" placeholder="Bank Branch">
                        </div>
                    </div>
                </div>
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Account Holder Name</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="holderName" placeholder="Account Holder Name">
                        </div>
                    </div>
                </div>
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">ID Number</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="idNumber" placeholder="ID Number">
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-parent">
                    <button class="btn-primary-medium" id="saveBankingBtn" style="flex: 1;">Save</button>
                    <button class="btn-secondary-outline-medium" id="cancelBankingBtn">Cancel</button>
                    <button class="btn-error-outline-medium" id="deleteBankingBtn">Delete</button>
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
                    <div class="input3">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Full Name</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="shipName" placeholder="John Doe">
                            </div>
                        </div>
                    </div>
                    <div class="input3">
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
                    <button class="btn-secondary-outline-medium" id="cancelShippingBtn" style="flex: 1;">Cancel</button>
                    <button class="btn-error-outline-medium" id="deleteShippingBtn">Delete</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $ROOT; ?>/views/website/js/my_account.js"></script>
</body>
</html>
<?php include __DIR__ . '/../../../partials/footer_kovid.php'; ?>