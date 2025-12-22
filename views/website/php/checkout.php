<?php
$ROOT = '/Candy-Crunch-Website';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user addresses from session
$addresses = $_SESSION['user_addresses'] ?? [];
$selectedAddressId = $_SESSION['selected_shipping_address'] ?? null;

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

                <!-- Bank Accounts List (Hidden by default) -->
                <div class="bank-accounts-container" id="bankAccountsContainer">
                    <div class="card-container">
                        <div class="bank-account-card">
                            <span class="bank-account-name">Shinhan Bank</span>
                            <p class="bank-account-number">1234567890</p>
                        </div>

                        <div class="bank-account-card">
                            <span class="bank-account-name">Shinhan Bank</span>
                            <p class="bank-account-number">1234567890</p>
                        </div>
                    </div>

                    <button class="btn-primary-outline-small">Add Banking Account</button>
                </div>

            </div>
        </div>

        <!-- Checkout Right -->
        <div class="checkout-right">

            <!-- Cart Info -->
            <div class="cart-info">
                <h1>ORDER SUMMARY</h1>

                <div class="cart-container">
                    <!-- Card 1 -->
                    <div class="cart-card">
                        <div class="left">
                            <img src="../img/product-img/main-thumb-example.png" alt="Product Image">
                            <div class="product-info">
                                <h3 class="product-name">Product Name</h3>
                                <div class="attribute-quantity">
                                    <span class="attribute">175g</span>
                                    <span class="quantity">x 1</span>
                                </div>

                            </div>
                        </div>

                        <div class="right">
                            <button class="remove">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <path
                                        d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7"
                                        stroke="black" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>

                            <div class="price">
                                <span class="price-old">100,000 VND</span>
                                <span class="price-new">90,000 VND</span>
                            </div>
                        </div>
                    </div>



                    <!-- Card 2 -->
                    <div class="cart-card">
                        <div class="left">
                            <img src="../img/product-img/main-thumb-example.png" alt="Product Image">
                            <div class="product-info">
                                <h3 class="product-name">Product Name</h3>
                                <div class="attribute-quantity">
                                    <span class="attribute">175g</span>
                                    <span class="quantity">x 1</span>
                                </div>

                            </div>
                        </div>

                        <div class="right">
                            <button class="remove">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <path
                                        d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7"
                                        stroke="black" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>

                            <div class="price">
                                <span class="price-old">100,000 VND</span>
                                <span class="price-new">90,000 VND</span>
                            </div>
                        </div>
                    </div>




                </div>


            </div>


            <!-- Summary Container -->
            <div class="summary-container">
                <div class="order-summary">
                    <div class="summary-header">
                        <div class="subtotal">
                            <span class="label">Subtotal</span>
                            <span class="value">100,000 VND</span>
                        </div>
                        <div class="discount">
                            <span class="label">Discount</span>
                            <span class="value">- 10,000 VND</span>
                        </div>
                        <div class="shipping">
                            <span class="label">Delivery Fee</span>
                            <span class="value">10,000 VND</span>
                        </div>
                        <div class="promo">
                            <span class="label">Promo</span>
                            <span class="value">110,000 VND</span>
                        </div>
                        <div class="input" data-type="text" data-state="default" data-size="medium">
                            <div class="input-field">
                                <input type="text" placeholder="Enter promo code">
                                <button class="btn-primary-outline-small">Apply</button>
                            </div>
                        </div>
                    </div>

                    <div class="summary-footer">
                        <span class="total-label">Total</span>
                        <span class="total-value">110,000 VND</span>
                    </div>

                </div>

                <label class="checkbox-item">
                    <input type="checkbox" name="invoice">
                    I agree to the<a href="policy.php">Terms and Conditions</a>&<a href="policy.php">Privacy Policy</a>
                </label>

                <button class="btn-primary-large">Checkout</button>
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

    <script src="<?php echo $ROOT; ?>/views/website/js/checkout.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/main.js"></script>


</body>

</html>

<?php

include __DIR__ . '/../../../partials/footer_kovid.php';
?>