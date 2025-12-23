

// ======================================
// PLACE ORDER (CHECKOUT BUTTON)
// ======================================
document.addEventListener('DOMContentLoaded', () => {
  const ROOT = '/Candy-Crunch-Website';
  const checkoutBtn = document.getElementById('checkoutBtn');
  const termsCheckbox = document.getElementById('termsCheckbox');

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      // Validate terms checkbox
      if (!termsCheckbox || !termsCheckbox.checked) {
        alert('Please agree to the Terms and Conditions before proceeding.');
        return;
      }

      // Get selected address
      const selectedAddressId = document.getElementById('selectedAddressId')?.value;
      if (!selectedAddressId) {
        alert('Please select a shipping address.');
        return;
      }

      // Get delivery method
      const deliveryInput = document.querySelector('.delivery-method input[name="delivery"]:checked');
      const deliveryMethod = deliveryInput?.value || 'standard';

      // Get payment method
      const paymentInput = document.querySelector('.payment-method input[name="payment"]:checked');
      const paymentMethod = paymentInput?.value || 'cod';

      // Get selected banking (if bank transfer)
      let bankingId = null;
      if (paymentMethod === 'bank') {
        bankingId = document.getElementById('selectedBankingId')?.value;
        if (!bankingId) {
          alert('Please select a banking account for bank transfer.');
          return;
        }
      }

      // Disable button and show loading
      checkoutBtn.disabled = true;
      checkoutBtn.textContent = 'Processing...';

      // Send order to server
      const formData = new FormData();
      formData.append('action', 'place_order');
      formData.append('address_id', selectedAddressId);
      formData.append('delivery_method', deliveryMethod);
      formData.append('payment_method', paymentMethod === 'bank' ? 'Bank Transfer' : 'COD');
      if (bankingId) {
        formData.append('banking_id', bankingId);
      }

      fetch(ROOT + '/controllers/website/OrderSuccessController.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Redirect to order success page
            window.location.href = data.redirect;
          } else {
            alert(data.message || 'Failed to place order. Please try again.');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Checkout';
          }
        })
        .catch(error => {
          console.error('Error placing order:', error);
          alert('An error occurred. Please try again.');
          checkoutBtn.disabled = false;
          checkoutBtn.textContent = 'Checkout';
        });
    });
  }
});


// ======================================
// SHIPPING FEE CALCULATION
// ======================================
document.addEventListener('DOMContentLoaded', () => {
  const SHIPPING_STANDARD = 30000;  // Standard shipping = 30,000 VND
  const SHIPPING_FAST = 50000;      // X-Treme Fast = 50,000 VND

  // Get delivery method radios
  const deliveryRadios = document.querySelectorAll('.delivery-method .radio');
  const summaryShipping = document.getElementById('summaryShipping');
  const summaryTotal = document.getElementById('summaryTotal');
  const summarySubtotal = document.getElementById('summarySubtotal');
  const summaryDiscount = document.getElementById('summaryDiscount');
  const summaryPromo = document.getElementById('summaryPromo');

  // Helper function to parse VND format to number
  function parseVND(text) {
    if (!text) return 0;
    return parseInt(text.replace(/[^\d-]/g, '')) || 0;
  }

  // Helper function to format number to VND
  function formatVND(amount) {
    return new Intl.NumberFormat('vi-VN').format(Math.abs(amount)) + ' VND';
  }

  // Function to update shipping and total
  function updateShippingFee(shippingAmount) {
    const subtotal = parseVND(summarySubtotal?.textContent);
    let discount = parseVND(summaryDiscount?.textContent);

    // Handle negative discount display
    if (summaryDiscount?.textContent?.includes('-')) {
      discount = Math.abs(discount);
    }

    // Rule: if (subtotal - discount) > 200,000 => Free Shipping
    if ((subtotal - discount) > 200000) {
      shippingAmount = 0;
    }

    if (summaryShipping) {
      summaryShipping.textContent = formatVND(shippingAmount);
    }

    // Recalculate total
    let promo = parseVND(summaryPromo?.textContent);
    if (summaryPromo?.textContent?.includes('-')) {
      promo = Math.abs(promo);
    }

    const total = subtotal - discount - promo + shippingAmount;

    if (summaryTotal) {
      summaryTotal.textContent = formatVND(total);
    }
  }

  // Listen for delivery method changes
  deliveryRadios.forEach(component => {
    component.addEventListener('radio-change', (e) => {
      const value = e.detail.value;

      if (value === 'fast') {
        updateShippingFee(SHIPPING_FAST);
      } else {
        updateShippingFee(SHIPPING_STANDARD);
      }
    });
  });
});



// BANKING ACCOUNT======================
// ======================================
// Toggle bank accounts container when Banking Account is selected
document.addEventListener('DOMContentLoaded', () => {
  const bankAccountsContainer = document.getElementById('bankAccountsContainer');
  if (!bankAccountsContainer) return;

  // Chỉ lấy các radio trong phần Payment Method
  const paymentRadios = document.querySelectorAll('.payment-method .radio');

  paymentRadios.forEach(component => {
    component.addEventListener('radio-change', (e) => {
      const value = e.detail.value; // chính là value của input (cod / bank)

      if (value === 'bank') {
        bankAccountsContainer.classList.add('active');
      } else {
        bankAccountsContainer.classList.remove('active');
      }
    });
  });
});


// ======================================
// ADD NEW BANKING MODAL & BANKING SELECTION
// ======================================
document.addEventListener('DOMContentLoaded', () => {
  const ROOT = '/Candy-Crunch-Website';

  // Modal elements
  const addBankingModal = document.getElementById('addBankingModal');
  const addNewBankingBtn = document.getElementById('addNewBankingBtn');
  const cancelAddBankingBtn = document.getElementById('cancelAddBankingBtn');
  const saveNewBankingBtn = document.getElementById('saveNewBankingBtn');

  // Banking selection elements
  const selectedBankingCard = document.getElementById('selectedBankingCard');
  const bankingSelectionList = document.getElementById('bankingSelectionList');
  const changeBankingBtn = document.getElementById('changeBankingBtn');
  const displayBankName = document.getElementById('displayBankName');
  const displayAccountNumber = document.getElementById('displayAccountNumber');
  const selectedBankingIdInput = document.getElementById('selectedBankingId');

  // ======================================
  // BANKING CARD SELECTION
  // ======================================
  const bankingCards = document.querySelectorAll('.bank-account-card');

  bankingCards.forEach(card => {
    card.addEventListener('click', () => {
      selectBankingCard(card);
    });
  });

  function selectBankingCard(card) {
    const bankingId = card.dataset.bankingId;
    const bankName = card.dataset.bankName;
    const accountNumber = card.dataset.accountNumber;
    const maskedNumber = '****' + (accountNumber || '').slice(-4);

    // Update selected banking display
    if (displayBankName) displayBankName.textContent = bankName || '';
    if (displayAccountNumber) displayAccountNumber.textContent = maskedNumber;
    if (selectedBankingIdInput) selectedBankingIdInput.value = bankingId || '';

    // Show selected card, hide selection list
    if (selectedBankingCard) selectedBankingCard.style.display = '';
    if (bankingSelectionList) bankingSelectionList.style.display = 'none';

    // Save to session via AJAX
    saveSelectedBanking(bankingId);

    // Add selected class to card
    document.querySelectorAll('.bank-account-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
  }

  // ======================================
  // CHANGE BANKING BUTTON
  // ======================================
  if (changeBankingBtn) {
    changeBankingBtn.addEventListener('click', () => {
      // Hide selected card, show selection list
      if (selectedBankingCard) selectedBankingCard.style.display = 'none';
      if (bankingSelectionList) bankingSelectionList.style.display = '';
    });
  }

  // ======================================
  // OPEN ADD BANKING MODAL
  // ======================================
  if (addNewBankingBtn) {
    addNewBankingBtn.addEventListener('click', () => {
      openBankingModal(addBankingModal);
      clearBankingForm();
    });
  }

  // ======================================
  // CLOSE ADD BANKING MODAL
  // ======================================
  if (cancelAddBankingBtn) {
    cancelAddBankingBtn.addEventListener('click', () => {
      closeBankingModal(addBankingModal);
    });
  }

  if (addBankingModal) {
    addBankingModal.addEventListener('click', (e) => {
      if (e.target === addBankingModal) {
        closeBankingModal(addBankingModal);
      }
    });
  }

  // ======================================
  // SAVE NEW BANKING
  // ======================================
  if (saveNewBankingBtn) {
    saveNewBankingBtn.addEventListener('click', () => {
      saveNewBanking();
    });
  }

  function saveNewBanking() {
    const accountNumberInput = document.getElementById('newAccountNumber');
    const bankNameInput = document.getElementById('newBankName');
    const bankBranchInput = document.getElementById('newBankBranch');
    const holderNameInput = document.getElementById('newAccountHolderName');
    const idNumberInput = document.getElementById('newIDNumber');

    const accountNumber = accountNumberInput?.value.trim();
    const bankName = bankNameInput?.value.trim();
    const bankBranch = bankBranchInput?.value.trim();
    const holderName = holderNameInput?.value.trim();
    const idNumber = idNumberInput?.value.trim();

    // Clear previous errors
    clearBankingFormErrors();

    let hasError = false;

    // Validation - required fields
    if (!accountNumber) {
      setBankingInputError(accountNumberInput, 'Account Number is required');
      hasError = true;
    } else if (!/^[0-9]+$/.test(accountNumber)) {
      setBankingInputError(accountNumberInput, 'Account Number must contain only numbers');
      hasError = true;
    }

    if (!bankName) {
      setBankingInputError(bankNameInput, 'Bank Name is required');
      hasError = true;
    }

    if (!holderName) {
      setBankingInputError(holderNameInput, 'Account Holder Name is required');
      hasError = true;
    }

    // Optional field validation - ID Number must be numbers if provided
    if (idNumber && !/^[0-9]+$/.test(idNumber)) {
      setBankingInputError(idNumberInput, 'ID Number must contain only numbers');
      hasError = true;
    }

    if (hasError) {
      return;
    }

    // Send to server via AJAX
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('account_number', accountNumber);
    formData.append('bank_name', bankName);
    formData.append('bank_branch', bankBranch || '');
    formData.append('holder_name', holderName);
    formData.append('id_number', idNumber || '');

    fetch(ROOT + '/controllers/website/BankingController.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Add new bank account card to the list
          addNewBankingCard(data.banking);

          // Close modal
          closeBankingModal(addBankingModal);

          // Auto-select the new banking account
          const newCard = document.querySelector(`.bank-account-card[data-banking-id="${data.banking.BankingID}"]`);
          if (newCard) {
            selectBankingCard(newCard);
          }

          // Show success message
          alert('Banking account added successfully!');
        } else {
          alert(data.message || 'Failed to add banking account. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error adding banking account:', error);
        alert('An error occurred. Please try again.');
      });
  }

  function addNewBankingCard(bankingData) {
    const cardContainer = document.querySelector('#bankingSelectionList .card-container');
    if (!cardContainer) return;

    // Remove "no banking" message if exists
    const noBankingMsg = cardContainer.querySelector('.no-banking');
    if (noBankingMsg) {
      noBankingMsg.remove();
    }

    const maskedAccount = '****' + (bankingData.AccountNumber || '').slice(-4);

    const cardHTML = `
      <div class="bank-account-card" 
           data-banking-id="${escapeHtmlBanking(bankingData.BankingID)}"
           data-bank-name="${escapeHtmlBanking(bankingData.BankName)}"
           data-account-number="${escapeHtmlBanking(bankingData.AccountNumber)}">
        <span class="bank-account-name">${escapeHtmlBanking(bankingData.BankName)}</span>
        <p class="bank-account-number">${maskedAccount}</p>
      </div>
    `;

    cardContainer.insertAdjacentHTML('beforeend', cardHTML);

    // Add click handler to new card
    const newCard = cardContainer.lastElementChild;
    newCard.addEventListener('click', () => selectBankingCard(newCard));
  }

  function saveSelectedBanking(bankingId) {
    const formData = new FormData();
    formData.append('action', 'select');
    formData.append('banking_id', bankingId);

    fetch(ROOT + '/controllers/website/BankingController.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          console.error('Failed to save selected banking');
        }
      })
      .catch(error => {
        console.error('Error saving selected banking:', error);
      });
  }

  function clearBankingForm() {
    const inputs = ['newAccountNumber', 'newBankName', 'newBankBranch', 'newAccountHolderName', 'newIDNumber'];
    inputs.forEach(id => {
      const input = document.getElementById(id);
      if (input) input.value = '';
    });
    clearBankingFormErrors();
  }

  // ======================================
  // HELPER FUNCTIONS
  // ======================================
  function openBankingModal(modal) {
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeBankingModal(modal) {
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  function escapeHtmlBanking(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ======================================
  // FORM VALIDATION HELPERS FOR BANKING
  // ======================================
  function setBankingInputError(inputElement, message) {
    if (!inputElement) return;

    // Find the .field parent and add error class
    const fieldContainer = inputElement.closest('.field');
    if (fieldContainer) {
      fieldContainer.classList.add('error');
    }
    inputElement.classList.add('error');

    // Add error message if not exists
    const headContainer = inputElement.closest('.head');
    if (headContainer && !headContainer.querySelector('.error-message')) {
      const errorSpan = document.createElement('span');
      errorSpan.className = 'error-message';
      errorSpan.textContent = message;
      headContainer.appendChild(errorSpan);
    }
  }

  function clearBankingInputError(inputElement) {
    if (!inputElement) return;

    const fieldContainer = inputElement.closest('.field');
    if (fieldContainer) {
      fieldContainer.classList.remove('error');
    }
    inputElement.classList.remove('error');

    const headContainer = inputElement.closest('.head');
    const errorMsg = headContainer?.querySelector('.error-message');
    if (errorMsg) {
      errorMsg.remove();
    }
  }

  function clearBankingFormErrors() {
    // Clear all error classes and messages in the add banking modal
    const modal = document.getElementById('addBankingModal');
    if (!modal) return;

    modal.querySelectorAll('.field.error').forEach(el => el.classList.remove('error'));
    modal.querySelectorAll('input.error').forEach(el => el.classList.remove('error'));
    modal.querySelectorAll('.error-message').forEach(el => el.remove());
  }

  // Add real-time validation listeners for banking fields
  const accountNumberInput = document.getElementById('newAccountNumber');
  const idNumberInput = document.getElementById('newIDNumber');

  if (accountNumberInput) {
    accountNumberInput.addEventListener('input', () => {
      if (accountNumberInput.value && !/^[0-9]*$/.test(accountNumberInput.value)) {
        setBankingInputError(accountNumberInput, 'Numbers only');
      } else {
        clearBankingInputError(accountNumberInput);
      }
    });
  }

  if (idNumberInput) {
    idNumberInput.addEventListener('input', () => {
      if (idNumberInput.value && !/^[0-9]*$/.test(idNumberInput.value)) {
        setBankingInputError(idNumberInput, 'Numbers only');
      } else {
        clearBankingInputError(idNumberInput);
      }
    });
  }
});


// ======================================
// ADDRESS SELECTION MODAL
// ======================================
document.addEventListener('DOMContentLoaded', () => {
  const ROOT = '/Candy-Crunch-Website';

  // Modal elements
  const addressSelectModal = document.getElementById('addressSelectModal');
  const addAddressModal = document.getElementById('addAddressModal');
  const changeAddressBtn = document.getElementById('changeAddressBtn');
  const cancelAddressSelectBtn = document.getElementById('cancelAddressSelectBtn');
  const addNewAddressBtn = document.getElementById('addNewAddressBtn');
  const cancelAddAddressBtn = document.getElementById('cancelAddAddressBtn');
  const saveNewAddressBtn = document.getElementById('saveNewAddressBtn');

  // Display elements
  const displayName = document.getElementById('displayName');
  const displayPhone = document.getElementById('displayPhone');
  const displayAddress = document.getElementById('displayAddress');
  const selectedAddressIdInput = document.getElementById('selectedAddressId');

  // Current selected address in modal
  let selectedAddressCard = null;

  // ======================================
  // OPEN ADDRESS SELECTION MODAL
  // ======================================
  if (changeAddressBtn) {
    changeAddressBtn.addEventListener('click', () => {
      openModal(addressSelectModal);

      // Pre-select current address
      const currentId = selectedAddressIdInput?.value;
      if (currentId) {
        const card = document.querySelector(`.address-select-card[data-address-id="${currentId}"]`);
        if (card) {
          selectAddressCard(card);
        }
      }
    });
  }

  // ======================================
  // CLOSE ADDRESS SELECTION MODAL
  // ======================================
  if (cancelAddressSelectBtn) {
    cancelAddressSelectBtn.addEventListener('click', () => {
      closeModal(addressSelectModal);
      clearAddressSelection();
    });
  }

  // Close modal when clicking outside
  if (addressSelectModal) {
    addressSelectModal.addEventListener('click', (e) => {
      if (e.target === addressSelectModal) {
        closeModal(addressSelectModal);
        clearAddressSelection();
      }
    });
  }

  // ======================================
  // ADDRESS CARD SELECTION
  // ======================================
  const addressCards = document.querySelectorAll('.address-select-card');

  addressCards.forEach(card => {
    card.addEventListener('click', () => {
      selectAddressCard(card);
    });

    // Double click to select and confirm
    card.addEventListener('dblclick', () => {
      selectAddressCard(card);
      confirmAddressSelection();
    });
  });

  function selectAddressCard(card) {
    // Remove selection from all cards
    document.querySelectorAll('.address-select-card').forEach(c => {
      c.classList.remove('selected');
    });

    // Select this card
    card.classList.add('selected');
    selectedAddressCard = card;
  }

  function clearAddressSelection() {
    document.querySelectorAll('.address-select-card').forEach(c => {
      c.classList.remove('selected');
    });
    selectedAddressCard = null;
  }

  function confirmAddressSelection() {
    if (!selectedAddressCard) return;

    const addressId = selectedAddressCard.dataset.addressId;
    const name = selectedAddressCard.dataset.name;
    const phone = selectedAddressCard.dataset.phone;
    const address = selectedAddressCard.dataset.address;
    const city = selectedAddressCard.dataset.city;
    const country = selectedAddressCard.dataset.country;

    // Update display
    updateDeliveryAddressDisplay(name, phone, address, city, country, addressId);

    // Save to session via AJAX
    saveSelectedAddress(addressId);

    // Close modal
    closeModal(addressSelectModal);
    clearAddressSelection();
  }

  // Add click handler for selection confirmation (using Add Shipping Address button position)
  // We'll add a confirm button dynamically or use double-click

  // ======================================
  // OPEN ADD NEW ADDRESS MODAL
  // ======================================
  if (addNewAddressBtn) {
    addNewAddressBtn.addEventListener('click', () => {
      closeModal(addressSelectModal);
      openModal(addAddressModal);
      clearAddAddressForm();
    });
  }

  // ======================================
  // CLOSE ADD ADDRESS MODAL
  // ======================================
  if (cancelAddAddressBtn) {
    cancelAddAddressBtn.addEventListener('click', () => {
      closeModal(addAddressModal);
      openModal(addressSelectModal);
    });
  }

  if (addAddressModal) {
    addAddressModal.addEventListener('click', (e) => {
      if (e.target === addAddressModal) {
        closeModal(addAddressModal);
      }
    });
  }

  // ======================================
  // SAVE NEW ADDRESS
  // ======================================
  if (saveNewAddressBtn) {
    saveNewAddressBtn.addEventListener('click', () => {
      saveNewAddress();
    });
  }

  function saveNewAddress() {
    const nameInput = document.getElementById('newName');
    const phoneInput = document.getElementById('newPhone');
    const addressInput = document.getElementById('newAddress');
    const cityInput = document.getElementById('newCity');
    const countryInput = document.getElementById('newCountry');
    const postalCodeInput = document.getElementById('newPostalCode');
    const isDefault = document.getElementById('setAsDefault')?.checked;

    const name = nameInput?.value.trim();
    const phone = phoneInput?.value.trim();
    const address = addressInput?.value.trim();
    const city = cityInput?.value.trim();
    const country = countryInput?.value.trim();
    const postalCode = postalCodeInput?.value.trim();

    // Clear previous errors
    clearFormErrors();

    let hasError = false;

    // Validation - required fields
    if (!name) {
      setInputError(nameInput, 'Full Name is required');
      hasError = true;
    }

    if (!phone) {
      setInputError(phoneInput, 'Phone Number is required');
      hasError = true;
    } else if (!/^[0-9+\-\s()]+$/.test(phone)) {
      setInputError(phoneInput, 'Phone Number must contain only numbers');
      hasError = true;
    }

    if (!address) {
      setInputError(addressInput, 'Address is required');
      hasError = true;
    }

    // Optional field validation - postal code must be numbers if provided
    if (postalCode && !/^[0-9]+$/.test(postalCode)) {
      setInputError(postalCodeInput, 'Postal Code must contain only numbers');
      hasError = true;
    }

    if (hasError) {
      return;
    }

    // Send to server via AJAX
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('fullname', name);
    formData.append('phone', phone);
    formData.append('address', address);
    formData.append('city', city || '');
    formData.append('country', country || '');
    formData.append('postal_code', postalCode || '');
    formData.append('is_default', isDefault ? 'Yes' : 'No');

    fetch(ROOT + '/controllers/website/AddressController.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Add new card to the list
          addNewAddressCard(data.address);

          // Update display if this is the only address or set as default
          if (data.address.IsDefault === 'Yes' || document.querySelectorAll('.address-select-card').length === 0) {
            updateDeliveryAddressDisplay(
              data.address.Fullname,
              data.address.Phone,
              data.address.Address,
              data.address.City,
              data.address.Country,
              data.address.AddressID
            );
          }

          // Close add modal and go back to selection
          closeModal(addAddressModal);
          openModal(addressSelectModal);

          // Remove "no address" message if exists
          const noAddressMsg = document.querySelector('.no-address');
          if (noAddressMsg) {
            noAddressMsg.remove();
          }
        } else {
          alert(data.message || 'Failed to add address. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error adding address:', error);
        alert('An error occurred. Please try again.');
      });
  }

  function addNewAddressCard(addressData) {
    const addressList = document.getElementById('addressList');
    if (!addressList) return;

    const fullAddress = [addressData.Address, addressData.City, addressData.Country]
      .filter(Boolean)
      .join(', ');

    const cardHTML = `
            <div class="address-select-card" 
                 data-address-id="${escapeHtml(addressData.AddressID)}"
                 data-name="${escapeHtml(addressData.Fullname)}"
                 data-phone="${escapeHtml(addressData.Phone)}"
                 data-address="${escapeHtml(addressData.Address)}"
                 data-city="${escapeHtml(addressData.City)}"
                 data-country="${escapeHtml(addressData.Country)}">
                <div class="address-select-card-header">
                    <h3>${escapeHtml(addressData.Fullname)}</h3>
                    <span class="phone">${escapeHtml(addressData.Phone)}</span>
                </div>
                <p class="address-text">${escapeHtml(fullAddress)}</p>
                ${addressData.IsDefault === 'Yes' ? '<span class="default-tag">Default</span>' : ''}
            </div>
        `;

    addressList.insertAdjacentHTML('beforeend', cardHTML);

    // Add click handlers to new card
    const newCard = addressList.lastElementChild;
    newCard.addEventListener('click', () => selectAddressCard(newCard));
    newCard.addEventListener('dblclick', () => {
      selectAddressCard(newCard);
      confirmAddressSelection();
    });
  }

  function clearAddAddressForm() {
    const form = document.getElementById('addAddressForm');
    if (form) form.reset();
  }

  // ======================================
  // HELPER FUNCTIONS
  // ======================================
  function openModal(modal) {
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(modal) {
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  function updateDeliveryAddressDisplay(name, phone, address, city, country, addressId) {
    if (displayName) displayName.textContent = name || 'No Name';
    if (displayPhone) displayPhone.textContent = phone || '';

    const fullAddress = [address, city, country].filter(Boolean).join(', ');
    if (displayAddress) displayAddress.textContent = fullAddress || 'No address';

    if (selectedAddressIdInput) selectedAddressIdInput.value = addressId || '';
  }

  function saveSelectedAddress(addressId) {
    const formData = new FormData();
    formData.append('action', 'select');
    formData.append('address_id', addressId);

    fetch(ROOT + '/controllers/website/AddressController.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          console.error('Failed to save selected address');
        }
      })
      .catch(error => {
        console.error('Error saving selected address:', error);
      });
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ======================================
  // FORM VALIDATION HELPERS
  // ======================================
  function setInputError(inputElement, message) {
    if (!inputElement) return;

    // Find the .field parent and add error class
    const fieldContainer = inputElement.closest('.field');
    if (fieldContainer) {
      fieldContainer.classList.add('error');
    }
    inputElement.classList.add('error');

    // Add error message if not exists
    const headContainer = inputElement.closest('.head');
    if (headContainer && !headContainer.querySelector('.error-message')) {
      const errorSpan = document.createElement('span');
      errorSpan.className = 'error-message';
      errorSpan.textContent = message;
      headContainer.appendChild(errorSpan);
    }
  }

  function clearInputError(inputElement) {
    if (!inputElement) return;

    const fieldContainer = inputElement.closest('.field');
    if (fieldContainer) {
      fieldContainer.classList.remove('error');
    }
    inputElement.classList.remove('error');

    const headContainer = inputElement.closest('.head');
    const errorMsg = headContainer?.querySelector('.error-message');
    if (errorMsg) {
      errorMsg.remove();
    }
  }

  function clearFormErrors() {
    // Clear all error classes and messages in the add address modal
    const modal = document.getElementById('addAddressModal');
    if (!modal) return;

    modal.querySelectorAll('.field.error').forEach(el => el.classList.remove('error'));
    modal.querySelectorAll('input.error').forEach(el => el.classList.remove('error'));
    modal.querySelectorAll('.error-message').forEach(el => el.remove());
  }

  // Add real-time validation listeners
  const phoneInput = document.getElementById('newPhone');
  const postalInput = document.getElementById('newPostalCode');

  if (phoneInput) {
    phoneInput.addEventListener('input', () => {
      if (phoneInput.value && !/^[0-9+\-\s()]+$/.test(phoneInput.value)) {
        setInputError(phoneInput, 'Numbers only');
      } else {
        clearInputError(phoneInput);
      }
    });
  }

  if (postalInput) {
    postalInput.addEventListener('input', () => {
      if (postalInput.value && !/^[0-9]*$/.test(postalInput.value)) {
        setInputError(postalInput, 'Numbers only');
      } else {
        clearInputError(postalInput);
      }
    });
  }
});
