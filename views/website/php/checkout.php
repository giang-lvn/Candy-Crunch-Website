<?php
$ROOT = '/Candy-Crunch-Website';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['AccountID'])) {
    header('Location: ' . $ROOT . '/views/website/php/login.php');
    exit;
}

// Load CartModel to get cart data
require_once __DIR__ . '/../../../models/db.php';
require_once __DIR__ . '/../../../models/website/CartModel.php';

$cartModel = new CartModel();
$cartId = $_SESSION['cart_id'] ?? null;
$cartItems = [];

// Get cart items from database
if ($cartId) {
    $cartItems = $cartModel->getCartItems($cartId);
}

// Check if cart is empty - redirect to shop page with alert
if (empty($cartItems)) {
    echo "<script>
        alert('Your cart is empty. Please add products to your cart before checkout.');
        window.location.href = '" . $ROOT . "/views/website/php/shop.php';
    </script>";
    exit;
}

// Calculate subtotal and discount using CartModel (same logic as cart page)
$amount = $cartModel->calculateCartAmount($cartItems);
$subtotal = $amount['subtotal']; // Total based on OriginalPrice
$discount = $amount['discount']; // Discount from PromotionPrice

// Check and apply voucher from session
$promo = 0;
$voucherCode = $_SESSION['voucher_code'] ?? '';
$voucherId = null;
if (!empty($voucherCode)) {
    $voucher = $cartModel->findVoucherByCode($voucherCode);
    if ($voucher) {
        $effectiveSubtotal = $subtotal - $discount; // Amount after product discount
        $voucherValid = $cartModel->validateVoucher($voucher, $effectiveSubtotal);
        if ($voucherValid['success']) {
            $promo = $cartModel->calculateVoucherDiscount($voucher, $effectiveSubtotal);
            $voucherId = $voucher['VoucherID'];
        } else {
            // Invalid voucher, clear it
            unset($_SESSION['voucher_code']);
            $voucherCode = '';
        }
    } else {
        unset($_SESSION['voucher_code']);
        $voucherCode = '';
    }
}

// Calculate Shipping Fee
$effectiveTotal = $subtotal - $discount - $promo;
if ($effectiveTotal > 200000) {
    $shippingFee = 0;
} else {
    $shippingFee = 30000; // Default Standard shipping
}

// Calculate total (shipping will be updated by JS based on delivery method)
$total = $subtotal - $discount - $promo + $shippingFee;

// Get user addresses from session
$addresses = $_SESSION['user_addresses'] ?? [];
$selectedAddressId = $_SESSION['selected_shipping_address'] ?? null;

// Get user banking info from session
$banking = $_SESSION['user_banking'] ?? [];

// Find default or selected address
$currentAddress = null;
if (!empty($addresses)) {
    foreach ($addresses as $addr) {
        if ($selectedAddressId && $addr['AddressID'] == $selectedAddressId) {
            $currentAddress = $addr;
            break;
        }
        if (($addr['IsDefault'] ?? '') === 'Yes') {
            $currentAddress = $addr;
        }
    }
    // If no default, use first address
    if (!$currentAddress) {
        $currentAddress = $addresses[0];
    }
}

include(__DIR__ . '/../../../partials/header.php');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Candy Crunch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/my_account.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/checkout.css">
</head>

<body>
    <main class="checkout-container">
        <div class="checkout-left">
            <!-- Delivery Address -->
            <div class="delivery-address">
                <div class="delivery-address-header">
                    <h1>Delivery Address</h1>
                    <button class="btn-primary-outline-small" id="changeAddressBtn">Change</button>
                </div>
                <div class="delivery-address-card" id="currentAddressCard">
                    <?php if ($currentAddress): ?>
                        <div class="delivery-address-card-header">
                            <h2 id="displayName"><?php echo htmlspecialchars($currentAddress['Fullname'] ?? 'No Name'); ?>
                            </h2>
                            <p id="displayPhone"><?php echo htmlspecialchars($currentAddress['Phone'] ?? ''); ?></p>
                        </div>
                        <span class="delivery-address-card-content" id="displayAddress">
                            <?php
                            $addrParts = array_filter([
                                $currentAddress['Address'] ?? '',
                                $currentAddress['City'] ?? '',
                                $currentAddress['Country'] ?? ''
                            ]);
                            echo htmlspecialchars(implode(', ', $addrParts) ?: 'No address');
                            ?>
                        </span>
                        <input type="hidden" id="selectedAddressId"
                            value="<?php echo htmlspecialchars($currentAddress['AddressID'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="delivery-address-card-header">
                            <h2 id="displayName">No address found</h2>
                            <p id="displayPhone"></p>
                        </div>
                        <span class="delivery-address-card-content" id="displayAddress">
                            Please add a shipping address
                        </span>
                        <input type="hidden" id="selectedAddressId" value="">
                    <?php endif; ?>
                </div>
            </div>


            <!-- Delivery Method -->
            <div class="delivery-method">
                <h1>Delivery Method</h1>

                <label class="radio" data-checked="true">
                    <input type="radio" name="delivery" value="standard" class="radio-input" checked>
                    <img src="<?php echo $ROOT; ?>/views/website/img/radio-checked.svg" alt="radio" class="radio-icon">
                    <span class="radio-label">Standard</span>
                </label>

                <label class="radio" data-checked="false">
                    <input type="radio" name="delivery" value="fast" class="radio-input">
                    <img src="<?php echo $ROOT; ?>/views/website/img/radio-unchecked.svg" alt="radio"
                        class="radio-icon">
                    <span class="radio-label">X-Treme Fast</span>
                </label>




                <label class="checkbox-item">
                    <input type="checkbox" name="invoice">
                    Request for issuing an electronic invoice
                </label>

            </div>


            <!-- Payment Method -->
            <div class="payment-method">
                <h1>Payment Method</h1>
                <label class="radio" data-checked="true">
                    <input type="radio" name="payment" value="cod" class="radio-input" checked>
                    <img src="<?php echo $ROOT; ?>/views/website/img/radio-checked.svg" alt="radio" class="radio-icon">
                    <span class="radio-label">Cash On Delivery (COD)</span>
                </label>

                <label class="radio" data-checked="false">
                    <input type="radio" name="payment" value="bank" class="radio-input">
                    <img src="<?php echo $ROOT; ?>/views/website/img/radio-unchecked.svg" alt="radio"
                        class="radio-icon">
                    <span class="radio-label">Bank Transfer</span>
                </label>

                <!-- Selected Banking Account Display (similar to Delivery Address) -->
                <?php
                // Find default or selected banking
                $currentBanking = null;
                $selectedBankingId = $_SESSION['selected_banking'] ?? null;
                if (!empty($banking)) {
                    foreach ($banking as $bank) {
                        if ($selectedBankingId && $bank['BankingID'] == $selectedBankingId) {
                            $currentBanking = $bank;
                            break;
                        }
                        if (($bank['IsDefault'] ?? '') === 'Yes') {
                            $currentBanking = $bank;
                        }
                    }
                    // If no default, use first banking
                    if (!$currentBanking) {
                        $currentBanking = $banking[0];
                    }
                }
                ?>
                <div class="bank-accounts-container" id="bankAccountsContainer">
                    <!-- Selected Banking Card -->
                    <div class="selected-banking-card" id="selectedBankingCard"
                        style="<?php echo $currentBanking ? '' : 'display:none;'; ?>">
                        <div class="selected-banking-header">
                            <h2>Banking Account</h2>
                            <button class="btn-primary-outline-small" id="changeBankingBtn">Change</button>
                        </div>
                        <div class="selected-banking-info">
                            <span class="bank-name"
                                id="displayBankName"><?php echo htmlspecialchars($currentBanking['BankName'] ?? ''); ?></span>
                            <span class="account-number"
                                id="displayAccountNumber">****<?php echo substr($currentBanking['AccountNumber'] ?? '', -4); ?></span>
                        </div>
                        <input type="hidden" id="selectedBankingId"
                            value="<?php echo htmlspecialchars($currentBanking['BankingID'] ?? ''); ?>">
                    </div>

                    <!-- Banking Selection List (Hidden when a banking is selected) -->
                    <div class="banking-selection-list" id="bankingSelectionList"
                        style="<?php echo $currentBanking ? 'display:none;' : ''; ?>">
                        <div class="card-container">
                            <?php if (!empty($banking)): ?>
                                <?php foreach ($banking as $bank): ?>
                                    <div class="bank-account-card"
                                        data-banking-id="<?php echo htmlspecialchars($bank['BankingID'] ?? ''); ?>"
                                        data-bank-name="<?php echo htmlspecialchars($bank['BankName'] ?? ''); ?>"
                                        data-account-number="<?php echo htmlspecialchars($bank['AccountNumber'] ?? ''); ?>">
                                        <span
                                            class="bank-account-name"><?php echo htmlspecialchars($bank['BankName'] ?? 'Unknown Bank'); ?></span>
                                        <p class="bank-account-number">
                                            ****<?php echo substr($bank['AccountNumber'] ?? '', -4); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-banking">No banking accounts found.</p>
                            <?php endif; ?>
                        </div>

                        <button class="btn-primary-outline-small" id="addNewBankingBtn">Add Banking Account</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Checkout Right -->
        <div class="checkout-right">

            <!-- Cart Info -->
            <div class="cart-info">
                <h1>ORDER SUMMARY</h1>

                <div class="cart-container">
                    <?php if (!empty($cartItems)): ?>
                        <?php foreach ($cartItems as $item): ?>
                            <!-- Cart Card -->
                            <div class="cart-card" data-skuid="<?= htmlspecialchars($item['SKUID']) ?>">
                                <div class="left">
                                    <img src="<?= htmlspecialchars($item['Image'] ?? $ROOT . '/views/website/img/product-img/main-thumb-example.png') ?>" 
                                         alt="<?= htmlspecialchars($item['ProductName']) ?>">
                                    <div class="product-info">
                                        <h3 class="product-name"><?= htmlspecialchars($item['ProductName']) ?></h3>
                                        <div class="attribute-quantity">
                                            <span class="attribute"><?= htmlspecialchars($item['Attribute'] ?? '') ?></span>
                                            <span class="quantity">x <?= (int)$item['CartQuantity'] ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="right">
                                    <div class="price">
                                        <?php if (!empty($item['PromotionPrice']) && $item['PromotionPrice'] < $item['OriginalPrice']): ?>
                                            <span class="price-old"><?= number_format($item['OriginalPrice'], 0, ',', '.') ?> VND</span>
                                            <span class="price-new"><?= number_format($item['PromotionPrice'] * $item['CartQuantity'], 0, ',', '.') ?> VND</span>
                                        <?php else: ?>
                                            <span class="price-new"><?= number_format($item['OriginalPrice'] * $item['CartQuantity'], 0, ',', '.') ?> VND</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-cart">Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>


            <!-- Summary Container -->
            <div class="summary-container">
                <div class="order-summary">
                    <div class="summary-header">
                        <div class="subtotal">
                            <span class="label">Subtotal</span>
                            <span class="value" id="summarySubtotal"><?= number_format($subtotal, 0, ',', '.') ?> VND</span>
                        </div>
                        <div class="discount">
                            <span class="label">Discount</span>
                            <span class="value" id="summaryDiscount"><?= $discount > 0 ? '- ' : '' ?><?= number_format($discount, 0, ',', '.') ?> VND</span>
                        </div>
                        <div class="shipping">
                            <span class="label">Shipping Fee</span>
                            <span class="value" id="summaryShipping"><?= number_format($shippingFee, 0, ',', '.') ?> VND</span>
                        </div>
                        <div class="promo">
                            <span class="label">Promo</span>
                            <span class="value" id="summaryPromo"><?= $promo > 0 ? '- ' : '' ?><?= number_format($promo, 0, ',', '.') ?> VND</span>
                        </div>
                        <div class="input" data-type="text" data-state="default" data-size="medium">
                            <div class="input-field">
                                <input type="text" placeholder="Enter promo code" id="promoCodeInput" value="<?= htmlspecialchars($voucherCode) ?>" <?= !empty($voucherCode) ? 'readonly' : '' ?>>
                                <button class="btn-primary-outline-small" id="applyPromoBtn" data-action="<?= !empty($voucherCode) ? 'remove' : 'apply' ?>"><?= !empty($voucherCode) ? 'Remove' : 'Apply' ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="summary-footer">
                        <span class="total-label">Total</span>
                        <span class="total-value" id="summaryTotal"><?= number_format($total, 0, ',', '.') ?> VND</span>
                    </div>

                </div>

                <label class="checkbox-item">
                    <input type="checkbox" name="terms" id="termsCheckbox">
                    I agree to the<a href="policy.php">Terms and Conditions</a>&<a href="policy.php">Privacy Policy</a>
                </label>

                <button class="btn-primary-large" id="checkoutBtn">Checkout</button>
            </div>
        </div>

    </main>

    <!-- ADDRESS SELECTION MODAL -->
    <div class="modal-overlay" id="addressSelectModal">
        <div class="modal-content address-modal">
            <div class="modal-header">
                <h2>Select Delivery Address</h2>
            </div>
            <div class="modal-body">
                <div class="address-list" id="addressList">
                    <?php if (!empty($addresses)): ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-select-card"
                                data-address-id="<?php echo htmlspecialchars($address['AddressID']); ?>"
                                data-name="<?php echo htmlspecialchars($address['Fullname'] ?? ''); ?>"
                                data-phone="<?php echo htmlspecialchars($address['Phone'] ?? ''); ?>"
                                data-address="<?php echo htmlspecialchars($address['Address'] ?? ''); ?>"
                                data-city="<?php echo htmlspecialchars($address['City'] ?? ''); ?>"
                                data-country="<?php echo htmlspecialchars($address['Country'] ?? ''); ?>">
                                <div class="address-select-card-header">
                                    <h3><?php echo htmlspecialchars($address['Fullname'] ?? 'No Name'); ?></h3>
                                    <span class="phone"><?php echo htmlspecialchars($address['Phone'] ?? ''); ?></span>
                                </div>
                                <p class="address-text">
                                    <?php
                                    $addrParts = array_filter([
                                        $address['Address'] ?? '',
                                        $address['City'] ?? '',
                                        $address['Country'] ?? ''
                                    ]);
                                    echo htmlspecialchars(implode(', ', $addrParts) ?: 'No address');
                                    ?>
                                </p>
                                <?php if (($address['IsDefault'] ?? '') === 'Yes'): ?>
                                    <span class="default-tag">Default</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-address">No saved addresses found.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-primary-medium" id="addNewAddressBtn">Add Shipping Address</button>
                <button class="btn-secondary-outline-medium" id="cancelAddressSelectBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- ADD NEW ADDRESS MODAL -->
    <div class="modal-overlay" id="addAddressModal">
        <div class="new-address" style="max-width:520px">
            <div class="edit-my-profile-wrapper">
                <div class="edit-my-profile">Add Shipping Address</div>
            </div>
            <div class="frame-parent-modal">
                <!-- Row 1: Full Name & Phone Number (2 columns) -->
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Full Name</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="newName" placeholder="Full Name">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Phone Number</div>
                            </div>
                            <div class="field">
                                <input type="tel" class="gender" id="newPhone" placeholder="Phone Number">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Address (full width) -->
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Address</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="newAddress" placeholder="Address">
                        </div>
                    </div>
                </div>

                <!-- Row 3: City/State & Country (2 columns) -->
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">City/State</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="newCity" placeholder="City/State">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Country</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="newCountry" placeholder="Country">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Postal Code (full width) -->
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Postal Code</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="newPostalCode" placeholder="Postal Code">
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-parent">
                <button class="btn-primary-medium" id="saveNewAddressBtn">Save</button>
                <button class="btn-secondary-outline-medium" id="cancelAddAddressBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- ADD NEW BANKING MODAL -->
    <div class="modal-overlay" id="addBankingModal">
        <div class="new-address" style="max-width:520px">
            <div class="edit-my-profile-wrapper">
                <div class="edit-my-profile">Add Banking Account</div>
            </div>
            <div class="frame-parent-modal">
                <!-- Row 1: Account Number & Bank Name (2 columns) -->
                <div class="input-parent">
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Account Number</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="newAccountNumber"
                                    placeholder="Your Account Number">
                            </div>
                        </div>
                    </div>
                    <div class="input">
                        <div class="head">
                            <div class="label-edit">
                                <div class="male">Bank</div>
                            </div>
                            <div class="field">
                                <input type="text" class="gender" id="newBankName" placeholder="Bank Name">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Bank Branch (full width) -->
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Bank Branch</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="newBankBranch" placeholder="Bank Branch">
                        </div>
                    </div>
                </div>

                <!-- Row 3: Account Holder Name (full width) -->
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">Account Holder Name</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="newAccountHolderName"
                                placeholder="Account Holder Name">
                        </div>
                    </div>
                </div>

                <!-- Row 4: ID Number (full width) -->
                <div class="input3">
                    <div class="head">
                        <div class="label-edit">
                            <div class="male">ID Number</div>
                        </div>
                        <div class="field">
                            <input type="text" class="gender" id="newIDNumber" placeholder="ID Number">
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-parent">
                <button class="btn-primary-medium" id="saveNewBankingBtn">Save</button>
                <button class="btn-secondary-outline-medium" id="cancelAddBankingBtn">Cancel</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $ROOT; ?>/views/website/js/checkout.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/main.js"></script>


</body>

</html>

<?php

include __DIR__ . '/../../../partials/footer_kovid.php';
?>