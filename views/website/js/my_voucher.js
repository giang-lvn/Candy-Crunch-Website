// ===============================
// CONFIG
// ===============================
const VOUCHER_API = '/Candy-Crunch-Website/controllers/website/voucher_controller.php';

// ===============================
// INIT
// ===============================
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    initDropdownFilter();
    // loadVouchers('all'); // Disabled: SSR handles initial load
});


// ===============================
// MENU NAVIGATION
// ===============================
function initMenuNavigation() {
    document.querySelectorAll('.account-menu').forEach(menu => {
        menu.addEventListener('click', e => {
            const text = menu.querySelector('div')?.textContent.trim();
            if (!text) return;
            createRipple(e, menu);
            setTimeout(() => handleMenuAction(text), 200);
        });
    });
    highlightActiveMenu();
}
const ROOT = '/Candy-Crunch-Website/views/website/php';
function handleMenuAction(action) {
    const routes = {
        'My Account': 'my_account.php',
        'Change Password': 'changepass.php',
        'My Orders': 'my_orders.php',
        'My Vouchers': 'my_vouchers.php',
        'Log out': 'login.php'
    };

    if (action === 'Log out') {
        if (confirm('Log out?')) location.href = ROOT + '/' + routes[action];
        return;
    }

    const targetFile = routes[action];
    const currentFile = window.location.pathname.split('/').pop(); // lấy file hiện tại

    if (currentFile === targetFile) {
        // Nếu đang ở chính trang đó, không reload
        console.log('Already on page:', currentFile);
        return;
    }

    if (targetFile) {
        location.href = ROOT + '/' + targetFile;
    }
}

function highlightActiveMenu() {
    const page = location.pathname.split('/').pop();
    const map = {
        'my_vouchers.php': 'My Vouchers',
        'my_account.php': 'My Account',
        'my_orders.php': 'My Orders',
        'changepass.php': 'Change Password'
    };
    document.querySelectorAll('.account-menu').forEach(m => {
        const t = m.querySelector('div')?.textContent.trim();
        m.classList.toggle('active', map[page] === t);
    });
}

// ===============================
// FILTER DROPDOWN
// ===============================
function initDropdownFilter() {
    const dropdown = document.querySelector('.status-dropdown');
    if (!dropdown) return;

    const selected = dropdown.querySelector('.selected');
    const list = dropdown.querySelector('.status-list');

    selected.onclick = e => {
        e.stopPropagation();
        list.classList.toggle('show');
    };

    list.querySelectorAll('li').forEach(item => {
        item.onclick = e => {
            e.stopPropagation();
            const text = item.textContent.trim();
            // Update selected text but keep the icon
            selected.childNodes[0].nodeValue = text + ' ';

            // Map text to filter value
            let filterVal = 'all';
            if (text === 'Active') filterVal = 'active';
            if (text === 'Expiring Soon') filterVal = 'expiring';
            if (text === 'Upcoming') filterVal = 'upcoming';

            loadVouchers(filterVal);

            list.classList.remove('show');
        };
    });

    document.addEventListener('click', () => list.classList.remove('show'));
}

// ===============================
// LOAD VOUCHERS (FETCH)
// ===============================
function loadVouchers(filter = 'all') {
    const container = document.querySelector('.vouchers-line .line');
    container.innerHTML = '<p>Loading...</p>';

    fetch(`${VOUCHER_API}?action=list&filter=${filter}`)
        .then(r => r.json())
        .then(res => {
            container.innerHTML = '';

            if (!res.success || !res.data || res.data.length === 0) {
                container.innerHTML = '<p>No vouchers available.</p>';
                return;
            }

            res.data.forEach(v => container.appendChild(createVoucherCard(v)));
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p>Error loading vouchers</p>';
        });
}

// ===============================
// CREATE CARD
// ===============================
function createVoucherCard(v) {
    const card = document.createElement('div');
    card.className = `voucher-card ${v.isUpcoming ? 'disabled' : ''}`;

    let badge = '';
    if (v.badge) {
        const badgeClass = v.badge === 'Upcoming' ? 'expire-badge upcoming' : 'expire-badge';
        badge = `<div class="${badgeClass}">${v.badge}</div>`;
    }

    // Determine date text
    let dateText = v.isUpcoming ? `Starts: ${v.startDate}` : `Expire date: ${v.expireDate}`;

    card.innerHTML = `
        ${badge}
        <img src="/Candy-Crunch-Website/views/website/img/voutick.svg" alt="voucher-icon">

        <div>
            <div class="voucher-info">
                <div class="voucher-code">
                    ${v.code}
                </div>

                <div class="voucher-discount">
                    ${v.discountText}
                </div>

                <div class="voucher-condition">
                    For orders over ${v.minOrder}
                </div>
            </div>

            <div>
                ${dateText}
            </div>
        </div>

        <button ${v.isUpcoming ? 'disabled' : ''} data-id="${v.id}">
            Apply
        </button>
    `;

    if (!v.isUpcoming) {
        card.querySelector('button').onclick = e =>
            applyVoucher(e.target.dataset.id, e.target);
    }

    return card;
}

// ===============================
// APPLY VOUCHER
// ===============================
function applyVoucher(voucherId, btn) {
    const orderTotal = 1000000;

    btn.disabled = true;
    btn.textContent = 'Applying...';

    fetch(`${VOUCHER_API}?action=apply`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            voucher_id: voucherId,
            order_total: orderTotal
        })
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                showNotification(res.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Apply';
                return;
            }

            localStorage.setItem('appliedVoucher', JSON.stringify(res.data));
            btn.textContent = '✓ Applied';
            btn.style.background = '#28a745';
            showNotification('Voucher applied!', 'success');
        });
}

// ===============================
// UTIL
// ===============================
function formatDate(date) {
    return new Date(date).toLocaleDateString('vi-VN');
}

function createRipple(e, el) {
    const r = document.createElement('span');
    const d = Math.max(el.offsetWidth, el.offsetHeight);
    r.style.cssText = `
        width:${d}px;height:${d}px;
        left:${e.offsetX - d / 2}px;
        top:${e.offsetY - d / 2}px;
        position:absolute;border-radius:50%;
        background:rgba(255,255,255,.6);
        animation:ripple .6s ease-out;
    `;
    el.appendChild(r);
    setTimeout(() => r.remove(), 600);
}

function showNotification(msg, type = 'success') {
    const n = document.createElement('div');
    n.className = `notification ${type}`;
    n.textContent = msg;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}
