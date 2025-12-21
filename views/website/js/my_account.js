// ==================================================
// GLOBAL STATE
// ==================================================
let selectedGender = 'female';
let activeMenu = null;
let currentShippingCard = null;
let currentShippingId = null;
let currentBankingCard = null;
let currentBankingId = null;
let currentBankingMode = null;

// ==================================================
// DOM READY
// ==================================================
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    initEditProfile();
    initEditModalOverlay();
    initBankingToggle();
    initBanking();
    initShipping();
    handleResize();
});

// ==================================================
// MENU NAVIGATION
// ==================================================
function initMenuNavigation() {
    const menus = document.querySelectorAll('.account-menu');
    menus.forEach(menu => {
        menu.addEventListener('click', e => {
            e.preventDefault();
            const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();
            handleMenuAction(text);
        });
    });

    const currentPage = window.location.pathname;
    menus.forEach(m => m.classList.remove('active'));
    menus.forEach(menu => {
        const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();
        if (text === 'Change Password' && currentPage.includes('changepass')) menu.classList.add('active');
        else if (text === 'My Orders' && currentPage.includes('orders')) menu.classList.add('active');
        else if (text === 'My Vouchers' && currentPage.includes('vouchers')) menu.classList.add('active');
        else if (text === 'My Account' && (currentPage.includes('account') || currentPage.includes('profile'))) menu.classList.add('active');
    });
}

function handleMenuAction(action) {
    switch (action) {
        case 'My Account':
            window.location.href = 'my_account.php';
            break;
        case 'Change Password':
            // Already on this page, just scroll to top or do nothing
            window.location.href = 'changepass.php'
            break;
        case 'My Orders':
            window.location.href = 'my_orders.php';
            break;
        case 'My Vouchers':
            window.location.href = 'my_vouchers.php';
            break;
        case 'Log out':
            if (confirm('Are you sure you want to log out?')) window.location.href = '/Candy-Crunch-Website/views/website/php/login.php';
            break;
    }
}

// ==================================================
// VALIDATION HELPERS
// ==================================================
function showError(inputId, message) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const oldError = input.parentElement.querySelector('.error-message');
    if (oldError) oldError.remove();
    
    input.classList.add('error');
    const error = document.createElement('div');
    error.className = 'error-message';
    error.style.color = 'red';
    error.style.fontSize = '12px';
    error.style.marginTop = '4px';
    error.textContent = message;
    input.parentElement.appendChild(error);
}

function clearError(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.classList.remove('error');
    const error = input.parentElement.querySelector('.error-message');
    if (error) error.remove();
}

function clearAllErrors(fieldIds) {
    fieldIds.forEach(id => clearError(id));
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10,11}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// ==================================================
// EDIT PROFILE
// ==================================================
function initEditProfile() {
    document.getElementById('editProfileBtn')?.addEventListener('click', openEditModal);
    document.getElementById('cancelBtn')?.addEventListener('click', closeEditModal);
    document.getElementById('saveBtn')?.addEventListener('click', saveProfile);

    ['male','female','other'].forEach(g => {
        const el = document.getElementById(`radio-${g}`);
        el?.parentElement.addEventListener('click', () => selectGender(g));
    });

    ['editFirstName', 'editLastName', 'editEmail', 'editDOB'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => clearError(id));
    });
}

function initEditModalOverlay() {
    document.getElementById('editModal')?.addEventListener('click', e => {
        if (e.target.id === 'editModal') closeEditModal();
    });
}

function openEditModal() {
    loadProfileToModal();
    clearAllErrors(['editFirstName', 'editLastName', 'editEmail', 'editDOB']);
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    clearAllErrors(['editFirstName', 'editLastName', 'editEmail', 'editDOB']);
}

function loadProfileToModal() {
    setValue('editFirstName', getText('displayFirstName'));
    setValue('editLastName', getText('displayLastName'));
    setValue('editEmail', getText('displayEmail'));
    setValue('editDOB', getText('displayDOB'));

    selectedGender = getText('displayGender').toLowerCase() || 'female';
    updateGenderUI();
}

function selectGender(g) {
    selectedGender = g;
    updateGenderUI();
}

function updateGenderUI() {
    ['male','female','other'].forEach(g => {
        const el = document.getElementById(`radio-${g}`);
        if(!el) return;
        if(g === selectedGender) el.classList.add('checked');
        else el.classList.remove('checked');
    });
}

function saveProfile() {
    console.log('🔵 saveProfile() called');
    
    const data = {
        firstName: getValue('editFirstName').trim(),
        lastName: getValue('editLastName').trim(),
        email: getValue('editEmail').trim(),
        dob: getValue('editDOB').trim(),
        gender: selectedGender
    };

    console.log('📦 Data to send:', data);

    clearAllErrors(['editFirstName', 'editLastName', 'editEmail', 'editDOB']);

    let hasError = false;

    if (!data.firstName) {
        showError('editFirstName', 'First name is required');
        hasError = true;
    }

    if (!data.lastName) {
        showError('editLastName', 'Last name is required');
        hasError = true;
    }

    if (!data.email) {
        showError('editEmail', 'Email is required');
        hasError = true;
    } else if (!validateEmail(data.email)) {
        showError('editEmail', 'Invalid email format');
        hasError = true;
    }

    if (!data.dob) {
        showError('editDOB', 'Date of birth is required');
        hasError = true;
    }

    if (hasError) {
        console.log('❌ Validation failed');
        return;
    }

    console.log('✅ Validation passed, sending request...');

    const formData = new FormData();
    formData.append('action', 'updateProfile');
    formData.append('first_name', data.firstName);
    formData.append('last_name', data.lastName);
    formData.append('email', data.email);
    formData.append('birth', data.dob);
    formData.append('gender', data.gender);

    // 🔍 DEBUG: Log FormData
    console.log('📤 FormData contents:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('/Candy-Crunch-Website/controllers/website/account_controller.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => {
        console.log('📥 Response status:', res.status);
        return res.text(); // Đổi sang .text() để xem raw response
    })
    .then(text => {
        console.log('📄 Raw response:', text);
        try {
            const res = JSON.parse(text);
            console.log('✅ Parsed JSON:', res);
            
            if (res.success) {
                // ✅ Update UI với data từ server
                if (res.data) {
                    setText('displayFirstName', res.data.firstName || data.firstName);
                    setText('displayLastName', res.data.lastName || data.lastName);
                    setText('displayEmail', res.data.email || data.email);
                    setText('displayDOB', res.data.dob || data.dob);
                    setText('displayGender', capitalize(res.data.gender || data.gender));
                }
                alert('Profile updated successfully!');
                closeEditModal();
            } else {
                console.error('❌ Update failed:', res.message);
                alert('Update failed: ' + (res.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('❌ JSON Parse Error:', e);
            console.error('Response was not valid JSON. Raw text:', text);
            alert('Server error: Invalid response format');
        }
    })
    .catch(err => {
        console.error('❌ Network error:', err);
        alert('Network error. Please try again.');
    });
}

// ==================================================
// BANKING TOGGLE
// ==================================================
function initBankingToggle() {
    document.querySelectorAll('.line2').forEach(line => {
        const value = line.querySelector('.value6 .gender, .value9 .gender');
        const showBtn = line.querySelector('#toggleAccountButton');
        const hideBtn = line.querySelector('#toggleAccountClose');
        if (!value || !showBtn || !hideBtn) return;
        const original = value.textContent.trim();
        value.textContent = mask(original);
        showBtn.onclick = () => { value.textContent = original; showBtn.style.display = 'none'; hideBtn.style.display = 'inline'; };
        hideBtn.onclick = () => { value.textContent = mask(original); hideBtn.style.display = 'none'; showBtn.style.display = 'inline'; };
    });
}

const mask = txt => '•'.repeat(txt.length);

// ==================================================
// BANKING INFORMATION
// ==================================================
function initBanking() {
    document.querySelectorAll('.banking-item').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.banking-item').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            currentBankingCard = card;
            currentBankingId = card.dataset.bankingId;
        });
    });

    document.getElementById('editBankingInfoBtn')?.addEventListener('click', () => {
        if (!currentBankingCard) return alert('Please select a banking card to edit');
        currentBankingMode = 'edit';
        loadBankingToModal(currentBankingCard);
        document.getElementById('deleteBankingBtn').style.display = 'block';
        openBankingModal();
    });

    document.getElementById('addBankingBtn')?.addEventListener('click', () => {
        currentBankingMode = 'add';
        currentBankingId = null;
        currentBankingCard = null;
        setValue('bankAccountNumber','');
        setValue('bankName','');
        setValue('bankBranch','');
        setValue('holderName','');
        setValue('idNumber','');
        clearAllErrors(['bankAccountNumber', 'bankName', 'bankBranch', 'holderName', 'idNumber']);
        document.getElementById('deleteBankingBtn').style.display = 'none';
        openBankingModal();
    });

    document.getElementById('saveBankingBtn')?.addEventListener('click', saveBanking);
    document.getElementById('cancelBankingBtn')?.addEventListener('click', closeBankingModal);
    document.getElementById('deleteBankingBtn')?.addEventListener('click', deleteBanking);

    ['bankAccountNumber', 'bankName', 'bankBranch', 'holderName', 'idNumber'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => clearError(id));
    });
}

function loadBankingToModal(card) {
    setValue('bankAccountNumber', card.dataset.accountNumber);
    setValue('bankName', card.dataset.bankName);
    setValue('bankBranch', card.dataset.bankBranch);
    setValue('holderName', card.dataset.holderName);
    setValue('idNumber', card.dataset.idNumber);
}

function saveBanking() {
    console.log('🔵 saveBanking() called');
    
    const data = {
        accountNumber: getValue('bankAccountNumber').trim(),
        bankName: getValue('bankName').trim(),
        bankBranch: getValue('bankBranch').trim(),
        holderName: getValue('holderName').trim(),
        idNumber: getValue('idNumber').trim()
    };

    console.log('📦 Banking data:', data);

    clearAllErrors(['bankAccountNumber', 'bankName', 'bankBranch', 'holderName', 'idNumber']);

    let hasError = false;

    if (!data.accountNumber) {
        showError('bankAccountNumber', 'Account number is required');
        hasError = true;
    } else if (!/^[0-9]+$/.test(data.accountNumber)) {
        showError('bankAccountNumber', 'Account number must contain only numbers');
        hasError = true;
    }

    if (!data.bankName) {
        showError('bankName', 'Bank name is required');
        hasError = true;
    }

    if (!data.bankBranch) {
        showError('bankBranch', 'Bank branch is required');
        hasError = true;
    }

    if (!data.holderName) {
        showError('holderName', 'Account holder name is required');
        hasError = true;
    }

    if (!data.idNumber) {
        showError('idNumber', 'ID number is required');
        hasError = true;
    } else if (!/^[0-9]+$/.test(data.idNumber)) {
        showError('idNumber', 'ID number must contain only numbers');
        hasError = true;
    }

    if (hasError) {
        console.log('❌ Banking validation failed');
        return;
    }

    console.log('✅ Banking validation passed');

    const formData = new FormData();
    formData.append('action', currentBankingMode === 'edit' ? 'updateBanking' : 'addBanking');
    if (currentBankingMode === 'edit') formData.append('banking_id', currentBankingId);

    formData.append('account_number', data.accountNumber);
    formData.append('bank_branch', data.bankBranch);
    formData.append('bank_name', data.bankName);
    formData.append('holder_name', data.holderName);
    formData.append('id_number', data.idNumber);
    formData.append('is_default', 'No');

    console.log('📤 Sending banking request...');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('/Candy-Crunch-Website/controllers/website/account_controller.php', { method: 'POST', body: formData })
    .then(res => {
        console.log('📥 Banking response status:', res.status);
        return res.text();
    })
    .then(text => {
        console.log('📄 Banking raw response:', text);
        try {
            const res = JSON.parse(text);
            console.log('✅ Banking parsed:', res);
            if(res.success) {alert('Banking information saved!');window.location.reload();}
            else alert('Error: ' + (res.message || 'Unknown error'));
        } catch(e) {
            console.error('❌ Banking JSON parse error:', e);
            alert('Server error: Invalid response');
        }
    })
    .catch(err => {
        console.error('❌ Banking network error:', err);
        alert('Server error: ' + err);
    });
}

function deleteBanking() {
    if (!currentBankingId) return alert('Please select a banking card to delete.');
    if (!confirm('Are you sure you want to delete this bank account?')) return;

    const formData = new FormData();
    formData.append('action','deleteBanking');
    formData.append('banking_id', currentBankingId);

    fetch('/Candy-Crunch-Website/controllers/website/account_controller.php', { method:'POST', body:formData })
    .then(res=>res.json())
    .then(res=>{ if(res.success){alert('Banking information saved!'); window.location.reload();} else alert('Error: '+(res.message||'Unknown error')); })
    .catch(err=>alert('Server error: '+err));
}

// ==================================================
// SHIPPING INFORMATION
// ==================================================
function initShipping() {
    document.querySelectorAll('.address-item').forEach(card=>{
        card.addEventListener('click', ()=>{
            document.querySelectorAll('.address-item').forEach(c=>c.classList.remove('selected'));
            card.classList.add('selected');
            currentShippingCard = card;
            currentShippingId = card.dataset.addressId;
        });
    });

    document.getElementById('editAddressBtn')?.addEventListener('click', ()=>{
        if(!currentShippingCard) return alert('Please select an address to edit');
        loadShippingFromCard(currentShippingCard);
        document.getElementById('deleteShippingBtn').style.display='block';
        openShippingModal();
    });

    document.getElementById('addAddressBtn')?.addEventListener('click', ()=>{
        currentShippingCard = null;
        currentShippingId = null;
        setValue('shipName','');   
        setValue('shipPhone','');
        setValue('shipAddress','');
        setValue('shipCity','');
        setValue('shipCountry','');
        clearAllErrors(['shipName', 'shipPhone', 'shipAddress', 'shipCity', 'shipCountry']);
        document.getElementById('deleteShippingBtn').style.display='none';
        openShippingModal();
    });

    document.getElementById('saveShippingBtn')?.addEventListener('click', saveShipping);
    document.getElementById('cancelShippingBtn')?.addEventListener('click', closeShippingModal);
    document.getElementById('deleteShippingBtn')?.addEventListener('click', deleteShipping);

    ['shipName', 'shipPhone', 'shipAddress', 'shipCity', 'shipCountry'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => clearError(id));
    });
}

function loadShippingFromCard(card) {
    currentShippingId = card.dataset.addressId;
    setValue('shipName', card.querySelector('.ship-name').textContent);
    const addrElement = card.querySelector('.ship-address');
    const fullText = addrElement.textContent.trim();
    const phone = addrElement.dataset.phone;
    const parts = fullText.split(',').map(p=>p.trim());
    setValue('shipAddress', parts[0]||'');
    setValue('shipCity', parts[1]||'');
    setValue('shipCountry', parts[2]||'');
    setValue('shipPhone', phone||'');
}

function saveShipping() {
    console.log('🔵 saveShipping() called');
    
    const data = {
        fullname: getValue('shipName').trim(),
        phone: getValue('shipPhone').trim(),
        address: getValue('shipAddress').trim(),
        city: getValue('shipCity').trim(),
        country: getValue('shipCountry').trim()
    };

    console.log('📦 Shipping data:', data);

    clearAllErrors(['shipName', 'shipPhone', 'shipAddress', 'shipCity', 'shipCountry']);

    let hasError = false;

    if (!data.fullname) {
        showError('shipName', 'Full name is required');
        hasError = true;
    }

    if (!data.phone) {
        showError('shipPhone', 'Phone number is required');
        hasError = true;
    } else if (!validatePhone(data.phone)) {
        showError('shipPhone', 'Phone number must be 10-11 digits');
        hasError = true;
    }

    if (!data.address) {
        showError('shipAddress', 'Address is required');
        hasError = true;
    }

    if (!data.city) {
        showError('shipCity', 'City is required');
        hasError = true;
    }

    if (!data.country) {
        showError('shipCountry', 'Country is required');
        hasError = true;
    }

    if (hasError) {
        console.log('❌ Shipping validation failed');
        return;
    }

    console.log('✅ Shipping validation passed');

    const formData = new FormData();
    formData.append('action', currentShippingCard ? 'updateAddress' : 'addAddress');
    if (currentShippingCard) formData.append('address_id', currentShippingId);

    formData.append('fullname', data.fullname);
    formData.append('phone', data.phone);
    formData.append('address', data.address);
    formData.append('city', data.city);
    formData.append('country', data.country);

    console.log('📤 Sending shipping request...');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('/Candy-Crunch-Website/controllers/website/account_controller.php', { method:'POST', body:formData })
    .then(res=>{
        console.log('📥 Shipping response status:', res.status);
        return res.text();
    })
    .then(text=>{
        console.log('📄 Shipping raw response:', text);
        try {
            const res = JSON.parse(text);
            console.log('✅ Shipping parsed:', res);
            if(res.success){alert('Shipping information saved!'); window.location.reload();}
            else alert('Error: ' + (res.message||'Unknown error'));
        } catch(e) {
            console.error('❌ Shipping JSON parse error:', e);
            alert('Server error: Invalid response');
        }
    })
    .catch(err=>{
        console.error('❌ Shipping network error:', err);
        alert('Server error: '+err);
    });
}

function deleteShipping() {
    if (!currentShippingId) return alert('Please select a shipping address to delete.');
    if (!confirm('Are you sure you want to delete this shipping address?')) return;

    const formData = new FormData();
    formData.append('action', 'deleteAddress');
    formData.append('address_id', currentShippingId);

    console.log('🗑️ Deleting address ID:', currentShippingId);

    fetch('/Candy-Crunch-Website/controllers/website/account_controller.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(res => {
        console.log('Delete response:', res);

        if (res.success) {
            alert('Address deleted successfully!');

            // 1️⃣ Xóa thẻ DOM tương ứng
            if (currentShippingCard) {
                currentShippingCard.remove();
            }

            // 2️⃣ Reset state
            currentShippingCard = null;
            currentShippingId = null;

            // 3️⃣ (Tuỳ chọn) Cập nhật session địa chỉ nếu controller trả về addresses
            if (res.addresses) {
                // ví dụ bạn lưu session vào JS array để render lại
                // window.userAddresses = res.addresses; 
            }
        } else {
            alert('Error: ' + (res.message || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error('❌ Delete error:', err);
        alert('Server error: ' + err);
    });
}


// ==================================================
// MODAL HELPERS
// ==================================================
function openBankingModal() { 
    document.getElementById('BankingModal').style.display='flex';
    clearAllErrors(['bankAccountNumber', 'bankName', 'bankBranch', 'holderName', 'idNumber']);
}

function closeBankingModal() { 
    document.getElementById('BankingModal').style.display='none';
    clearAllErrors(['bankAccountNumber', 'bankName', 'bankBranch', 'holderName', 'idNumber']);
}

function openShippingModal() { 
    document.getElementById('ShippingModal').style.display='flex';
    clearAllErrors(['shipName', 'shipPhone', 'shipAddress', 'shipCity', 'shipCountry']);
}

function closeShippingModal() { 
    document.getElementById('ShippingModal').style.display='none';
    clearAllErrors(['shipName', 'shipPhone', 'shipAddress', 'shipCity', 'shipCountry']);
}

// ==================================================
// RESPONSIVE
// ==================================================
window.addEventListener('resize', debounce(handleResize,200));
function handleResize() {
    const sidebar = document.querySelector('.card-account');
    if(sidebar) sidebar.style.position = window.innerWidth < 1200 ? 'static':'sticky';
}

// ==================================================
// UTIL
// ==================================================
const getText = id => document.getElementById(id)?.textContent.trim()||'';
const setText = (id,v)=>document.getElementById(id)&&(document.getElementById(id).textContent=v);
const getValue = id => document.getElementById(id)?.value||'';
const setValue = (id,v)=>document.getElementById(id)&&(document.getElementById(id).value=v);
const capitalize = s=>s.charAt(0).toUpperCase()+s.slice(1);
function debounce(fn,t){let timer; return (...a)=>{clearTimeout(timer); timer=setTimeout(()=>fn(...a),t);}}