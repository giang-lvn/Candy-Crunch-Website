// ===============================
// INITIALIZATION
// ===============================
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    initDropdownFilter();
    loadVouchers();
});

// ===============================
// VOUCHER DATA
// ===============================
const vouchersData = [
    {
        id: 1,
        discount: '10% off',
        minOrder: '50.000đ',
        expireDate: '31/12/2025',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 15
    },
    {
        id: 2,
        discount: '15% off',
        minOrder: '100.000đ',
        expireDate: '25/12/2025',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 9
    },
    {
        id: 3,
        discount: '20% off',
        minOrder: '200.000đ',
        expireDate: '20/12/2025',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 4
    },
    {
        id: 4,
        discount: '5% off',
        minOrder: '30.000đ',
        expireDate: '15/01/2026',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 30
    },
    {
        id: 5,
        discount: '25% off',
        minOrder: '300.000đ',
        expireDate: '10/01/2026',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 25
    },
    {
        id: 6,
        discount: '30% off',
        minOrder: '500.000đ',
        expireDate: '05/01/2026',
        image: '../img/voutick.svg',
        status: 'active',
        daysUntilExpire: 20
    }
];

// ===============================
// MENU NAVIGATION
// ===============================
function initMenuNavigation() {
    const menus = document.querySelectorAll('.account-menu');

    menus.forEach(menu => {
        menu.addEventListener('click', function(e) {
            const menuText = this.querySelector('div')?.textContent.trim();
            if (!menuText) return;

            createRipple(e, this);
            setTimeout(() => handleMenuAction(menuText), 200);
        });
    });

    highlightActiveMenu();
}

function handleMenuAction(action) {
    const routes = {
        'My Account': '../php/my_account.php',
        'Change Password': '../php/changepass.php',
        'My Orders': '../php/my_orders.php',
        'My Vouchers': '../php/my_vouchers.php',
        'Log out': '../php/login.php'
    };

    if (action === 'Log out') {
        if (confirm('Are you sure you want to log out?')) {
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = routes[action];
        }
    } else if (routes[action]) {
        window.location.href = routes[action];
    }
}

function highlightActiveMenu() {
    const currentPage = window.location.pathname.split('/').pop();
    const menus = document.querySelectorAll('.account-menu');

    const pageMenuMap = {
        'my_vouchers.php': 'My Vouchers',
        'my_account.php': 'My Account',
        'my_orders.php': 'My Orders',
        'changepass.php': 'Change Password'
    };

    menus.forEach(menu => {
        menu.classList.remove('active');
        const menuDiv = menu.querySelector('div');
        
        if (menuDiv && pageMenuMap[currentPage] === menuDiv.textContent.trim()) {
            menu.classList.add('active');
        }
    });
}

// ===============================
// DROPDOWN FILTER
// ===============================
function initDropdownFilter() {
    const dropdown = document.querySelector('.status-dropdown');
    if (!dropdown) return;

    const selected = dropdown.querySelector('.selected');
    const list = dropdown.querySelector('.status-list');
    const items = list.querySelectorAll('li');

    // Toggle dropdown on click
    selected.addEventListener('click', (e) => {
        e.stopPropagation();
        list.classList.toggle('show');
    });

    // Handle item selection
    items.forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            
            const filterValue = item.textContent.trim();
            const iconHTML = '<img class="icon-dropdown" src="../img/dropdown.svg" alt="">';
            
            // Update selected text with fixed format
            selected.innerHTML = `${filterValue} ${iconHTML}`;
            
            // Apply filter
            filterVouchers(filterValue);
            
            // Update UI
            list.classList.remove('show');
            items.forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
            list.classList.remove('show');
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            list.classList.remove('show');
        }
    });
}

// ===============================
// FILTER VOUCHERS
// ===============================
function filterVouchers(filter) {
    const container = document.querySelector('.vouchers-line .line');
    if (!container) return;

    const filteredVouchers = vouchersData.filter(voucher => {
        switch(filter) {
            case 'Latest':
                return voucher.id >= 4;
            case 'Expiring Soon':
                return voucher.daysUntilExpire <= 10;
            case 'All':
            default:
                return true;
        }
    });

    // Clear and reload with animation
    container.innerHTML = '';
    
    filteredVouchers.forEach((voucher, index) => {
        const card = createVoucherCard(voucher);
        container.appendChild(card);
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Show message if no vouchers found
    if (filteredVouchers.length === 0) {
        container.innerHTML = '<p style="text-align: center; width: 100%; color: #616161;">No vouchers found</p>';
    }
}

// ===============================
// LOAD VOUCHERS
// ===============================
function loadVouchers() {
    filterVouchers('All');
}

function createVoucherCard(voucher) {
    const card = document.createElement('div');
    card.className = 'voucher-card';
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.3s ease';
    
    const badge = voucher.daysUntilExpire <= 10 
        ? '<div class="expire-badge">Expiring Soon</div>' 
        : '';
    
    card.innerHTML = `
        ${badge}
        <img src="${voucher.image}" alt="Voucher" onerror="this.src='https://via.placeholder.com/235x115/017e6a/ffffff?text=Voucher'">
        <div>
            <div>
                <div>${voucher.discount}</div>
                <div>For orders over ${voucher.minOrder}</div>
            </div>
            <div>Expire date: ${voucher.expireDate}</div>
        </div>
        <button>Apply</button>
    `;
    
    // Add event listener to Apply button
    const button = card.querySelector('button');
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        applyVoucher(voucher.id, button);
    });
    
    return card;
}

// ===============================
// APPLY VOUCHER
// ===============================
function applyVoucher(voucherId, button) {
    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Applying...';
    button.style.backgroundColor = '#015a4d';

    setTimeout(() => {
        button.textContent = '✓ Applied';
        button.style.backgroundColor = '#28a745';
        
        showNotification('Voucher applied successfully!', 'success');
        saveAppliedVoucher(voucherId);
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.backgroundColor = '#017e6a';
            button.disabled = false;
        }, 2000);
    }, 1000);
}

function saveAppliedVoucher(voucherId) {
    const appliedVouchers = JSON.parse(localStorage.getItem('appliedVouchers') || '[]');
    if (!appliedVouchers.includes(voucherId)) {
        appliedVouchers.push(voucherId);
        localStorage.setItem('appliedVouchers', JSON.stringify(appliedVouchers));
    }
}

// ===============================
// UTILITY FUNCTIONS
// ===============================
function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

function showNotification(message, type = 'success') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.animation = 'slideIn 0.3s ease';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}