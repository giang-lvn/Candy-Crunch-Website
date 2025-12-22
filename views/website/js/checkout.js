

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
