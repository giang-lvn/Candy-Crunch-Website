// ==================================================
// GLOBAL STATE
// ==================================================
let selectedGender = 'female';
let activeMenu = null;
let currentShippingCard = null;
const ROOT = '';

// ==================================================
// DOM READY
// ==================================================
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    initEditProfile();
    initEditModalOverlay();
    initImageUpload();
    initBankingToggle();
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
            if (window.location.pathname.includes('account') || window.location.pathname.includes('my_account')) {
                document.querySelector('.profile')?.scrollIntoView({ behavior: 'smooth' });
            } else { 
                window.location.href = ROOT + '/views/website/php/my_account.php';
            }
            break;
        case 'Change Password':
            window.location.href = ROOT + '/views/website/php/changepass.php';
            break;
        case 'My Orders':
            window.location.href = ROOT + '/views/website/php/my_orders.php';
            break;
        case 'My Vouchers':
            window.location.href = ROOT + '/views/website/php/my_vouchers.php';
            break;
        case 'Log out':
            if (confirm('Are you sure you want to log out?')) window.location.href = ROOT + '/views/website/php/login.html';
            break;
    }
}

// ==================================================
// EDIT PROFILE
// ==================================================
function initEditProfile() {
    document.getElementById('editProfileBtn')?.addEventListener('click', openEditModal);
    document.getElementById('cancelBtn')?.addEventListener('click', closeEditModal);
    document.getElementById('saveBtn')?.addEventListener('click', saveProfile);

    // GENDER RADIO BUTTON
    ['male','female','other'].forEach(g => {
        const el = document.getElementById(`radio-${g}`);
        el?.parentElement.addEventListener('click', () => selectGender(g));
    });
}

function initEditModalOverlay() {
    document.getElementById('editModal')?.addEventListener('click', e => {
        if (e.target.id === 'editModal') closeEditModal();
    });
}

function openEditModal() {
    loadProfileToModal();
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
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
    const data = {
        firstName: getValue('editFirstName'),
        lastName: getValue('editLastName'),
        email: getValue('editEmail'),
        dob: getValue('editDOB'),
        gender: selectedGender
    };

    const formData = new FormData();
    formData.append('action', 'updateProfile');
    formData.append('first_name', data.firstName);
    formData.append('last_name', data.lastName);
    formData.append('email', data.email);
    formData.append('birth', data.dob);
    formData.append('gender', data.gender);

    // Gửi AJAX tới controller
    fetch(ROOT + '/controllers/website/account_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            setText('displayFirstName', data.firstName);
            setText('displayLastName', data.lastName);
            setText('displayEmail', data.email);
            setText('displayDOB', data.dob);
            setText('displayGender', capitalize(data.gender));
            alert('Profile updated successfully!');
            closeEditModal();
        } else {
            alert('Update failed: ' + (res.message || 'Unknown error'));
        }
    })
    .catch(err => console.error(err));
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

        showBtn.onclick = () => {
            value.textContent = original;
            showBtn.style.display = 'none';
            hideBtn.style.display = 'inline';
        };

        hideBtn.onclick = () => {
            value.textContent = mask(original);
            hideBtn.style.display = 'none';
            showBtn.style.display = 'inline';
        };
    });
}

const mask = txt => '•'.repeat(txt.length);

// ==================================================
// IMAGE UPLOAD
// ==================================================
function initImageUpload() {
    document.querySelectorAll('.button2').forEach(btn => {
        btn.onclick = () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            input.onchange = e => {
                const file = e.target.files[0];
                if (!file || file.size > 1024 * 1024) return alert('Max 1MB');
                const reader = new FileReader();
                reader.onload = ev => btn.previousElementSibling.src = ev.target.result;
                reader.readAsDataURL(file);
            };
            input.click();
        };
    });
}

// ==================================================
// SHIPPING
// ==================================================
function initShipping() {
    document.querySelectorAll('.frame-container').forEach(card => {
        card.onclick = () => {
            currentShippingCard = card;

            document.querySelectorAll('.frame-container .status-tag').forEach(t => {
                t.classList.remove('status-tag');
                t.classList.add('status-tag2');
                t.textContent = '';
            });

            const tag = card.querySelector('.status-tag2');
            if (tag) {
                tag.classList.remove('status-tag2');
                tag.classList.add('status-tag');
                tag.textContent = 'Default';
            }
        };
    });

    document.querySelectorAll('.shipping .button6').forEach(btn => {
        btn.onclick = e => {
            e.stopPropagation();
            if (!currentShippingCard) return alert('Please select an address first');
            openShippingModal('edit');
        };
    });

    document.querySelector('.shipping .button4')?.addEventListener('click', () => {
        currentShippingCard = null;
        openShippingModal('add');
    });

    document.getElementById('saveShippingBtn')?.addEventListener('click', saveShipping);
    document.getElementById('cancelShippingBtn')?.addEventListener('click', closeShippingModal);
    document.getElementById('deleteShippingBtn')?.addEventListener('click', deleteShipping);

    document.getElementById('ShippingModal')?.addEventListener('click', e => {
        if (e.target.id === 'ShippingModal') closeShippingModal();
    });
}

function openShippingModal(mode) {
    document.getElementById('shippingModalTitle').textContent =
        mode === 'add' ? 'Add New Address' : 'Edit Shipping Address';

    if (mode === 'edit') loadShippingFromCard();
    else clearShippingForm();

    document.getElementById('ShippingModal').style.display = 'flex';
}

function closeShippingModal() {
    document.getElementById('ShippingModal').style.display = 'none';
    currentShippingCard = null;
}

function saveShipping() {
    const d = getShippingFormData();
    if (!d.address || !d.city || !d.country) return alert('Address, City, Country are required');

    currentShippingCard ? updateShippingCard(d) : createShippingCard(d);
    closeShippingModal();
}

function deleteShipping() {
    if (!currentShippingCard) return alert('No address selected to delete');
    if (!confirm('Are you sure you want to delete this address?')) return;

    currentShippingCard.remove();
    currentShippingCard = null;
    closeShippingModal();
}

function getShippingFormData() {
    return {
        name: getValue('shipName'),
        phone: getValue('shipPhone'),
        address: getValue('shipAddress'),
        city: getValue('shipCity'),
        country: getValue('shipCountry'),
    };
}

function clearShippingForm() {
    ['shipName','shipPhone','shipAddress','shipCity','shipCountry']
        .forEach(id => setValue(id, ''));
}

function loadShippingFromCard() {
    if (!currentShippingCard) return;

    setValue('shipName', currentShippingCard.querySelector('.ship-name')?.textContent || '');
    const addrEl = currentShippingCard.querySelector('.ship-address');
    if (addrEl) {
        const parts = addrEl.textContent.split(',').map(p => p.trim());
        setValue('shipAddress', parts[0] || '');
        setValue('shipCity', parts[1] || '');
        setValue('shipCountry', parts[2] || '');
    }
}

function updateShippingCard(d) {
    if (!currentShippingCard) return;
    currentShippingCard.querySelector('.ship-name').textContent = d.name || 'Unnamed';
    currentShippingCard.querySelector('.ship-address').textContent = `${d.address}, ${d.city}, ${d.country}`;
}

function createShippingCard(d) {
    const groups = document.querySelectorAll('.shipping .frame-group');
    let target = [...groups].find(g => g.children.length < 2) || groups[0];

    const card = document.createElement('div');
    card.className = 'frame-container';
    card.innerHTML = `
        <div class="frame-div">
            <div class="frame-parent5">
                <div class="john-doe-wrapper">
                    <div class="text4 ship-name">${d.name}</div>
                </div>
            </div>
            <div class="status-tag2"></div>
        </div>
        <div class="sunset-boulevard-los-angeles-wrapper">
            <div class="gender ship-address" data-city="${d.city}" data-country="${d.country}">
                ${d.address}, ${d.city}, ${d.country}
            </div>
        </div>
    `;
    target.appendChild(card);

    card.onclick = () => {
        currentShippingCard = card;
        document.querySelectorAll('.frame-container .status-tag').forEach(t => {
            t.classList.remove('status-tag');
            t.classList.add('status-tag2');
            t.textContent = '';
        });

        const tag = card.querySelector('.status-tag2');
        if (tag) {
            tag.classList.remove('status-tag2');
            tag.classList.add('status-tag');
            tag.textContent = 'Default';
        }
    };
}

// ==================================================
// RESPONSIVE
// ==================================================
window.addEventListener('resize', debounce(handleResize, 200));
function handleResize() {
    const sidebar = document.querySelector('.card-account');
    if (sidebar) sidebar.style.position = window.innerWidth < 1200 ? 'static' : 'sticky';
}

// ==================================================
// UTIL
// ==================================================
const getText = id => document.getElementById(id)?.textContent.trim() || '';
const setText = (id, v) => document.getElementById(id) && (document.getElementById(id).textContent = v);
const getValue = id => document.getElementById(id)?.value || '';
const setValue = (id, v) => document.getElementById(id) && (document.getElementById(id).value = v);
const capitalize = s => s.charAt(0).toUpperCase() + s.slice(1);
function debounce(fn, t) {
    let timer;
    return (...a) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...a), t);
    };
}

console.log('✅ My Account JS loaded – FULL CLEAN');
