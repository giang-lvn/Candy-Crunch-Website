document.addEventListener('DOMContentLoaded', () => {
    // ================================================
    // MENU NAVIGATION - WITHOUT CHANGE PASSWORD
    // ================================================
    initMenuNavigation();

    // ===============================
    // ORDERS SECTION
    // ===============================
    setupDropdowns();
    renderOrders();
});

/* ===============================
   MENU NAVIGATION
   =============================== */
function initMenuNavigation() {
    const menus = document.querySelectorAll('.account-menu');

    // Click handler
    menus.forEach(menu => {
        menu.addEventListener('click', e => {
            e.preventDefault();
            const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();
            handleMenuAction(text);
        });
    });

    // Set active menu based on current URL
    const currentPage = window.location.pathname;

    menus.forEach(m => m.classList.remove('active'));

    menus.forEach(menu => {
        const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();

        if (text === 'My Orders' && currentPage.includes('orders')) {
            menu.classList.add('active');
        } else if (text === 'My Vouchers' && currentPage.includes('vouchers')) {
            menu.classList.add('active');
        } else if (text === 'My Account' && (currentPage.includes('account') || currentPage.includes('my_account'))) {
            menu.classList.add('active');
        }
    });
}

function handleMenuAction(action) {
    switch (action) {
        case 'My Account':
            window.location.href = '../php/my_account.php';
            break;
        case 'Change Password':
            window.location.href = '../php/changepass.php';
            break;
        case 'My Orders':
            window.location.href = '../php/my_orders.php';
            break;
        case 'My Vouchers':
            window.location.href = '../php/my_vouchers.php';
            break;
        case 'Log out':
            if (confirm('Are you sure you want to log out?')) window.location.href = '../php/login.html';
            break;
    }
}

/* ===============================
   ORDERS DATA
   =============================== */
const ordersData = [
    {
        id: 'CTH-984399',
        status: 'waiting-payment',
        statusText: 'Waiting Payment',
        date: '26 December 2025',
        product: 'Fruit-Filled Candy',
        weight: '175g',
        quantity: 1,
        oldPrice: '150.000 VND',
        newPrice: '150.000 VND',
        total: '150.000 VND',
        buttons: ['Pay Now', 'Change Method']
    },
    {
        id: 'CTH-984400',
        status: 'completed',
        statusText: 'Completed',
        date: '20 December 2025',
        product: 'Fruit Filled Candy',
        weight: '175g',
        quantity: 2,
        oldPrice: '300.000 VND',
        newPrice: '280.000 VND',
        total: '280.000 VND',
        buttons: ['Buy Again', 'Return', 'Write Review']
    },
    {
        id: 'CTH-984401',
        status: 'pending',
        statusText: 'Pending',
        date: '25 December 2025',
        product: 'Fruit Filled Candy',
        weight: '175g',
        quantity: 3,
        oldPrice: '200.000 VND',
        newPrice: '180.000 VND',
        total: '540.000 VND',
        buttons: ['Cancel', 'Contact']
    },
    {
        id: 'CTH-984402',
        status: 'on-shipping',
        statusText: 'On Shipping',
        date: '24 December 2025',
        product: 'Fruit Filled Candy',
        weight: '175g',
        quantity: 1,
        oldPrice: '400.000 VND',
        newPrice: '350.000 VND',
        total: '350.000 VND',
        buttons: ['Cancel', 'Contact']
    },
    {
        id: 'CTH-984403',
        status: 'return',
        statusText: 'Return',
        date: '15 December 2025',
        product: 'Fruit Filled Candy',
        weight: '175g',
        quantity: 2,
        oldPrice: '300.000 VND',
        newPrice: '250.000 VND',
        total: '500.000 VND',
        buttons: ['Contact']
    },
    {
        id: 'CTH-984404',
        status: 'cancel',
        statusText: 'Cancel',
        date: '10 December 2025',
        product: 'Fruit Filled Candy',
        weight: '175g',
        quantity: 1,
        oldPrice: '200.000 VND',
        newPrice: '180.000 VND',
        total: '180.000 VND',
        buttons: ['Contact', 'Buy Again']
    }
];

/* ===============================
   STATE
   =============================== */
let currentStatusFilter = 'all';
let currentTimeFilter = '30';

/* ===============================
   BUTTON CLASS MAP
   =============================== */
const buttonClassMap = {
    'Pay Now': 'btn-error-outline-small',
    'Buy Again': 'btn-primary-medium',
    'Change Method': 'btn-primary-outline-small',
    'Return': 'btn-secondary-outline-small',
    'Cancel': 'btn-secondary-small',
    'Contact': 'btn-error-outline-small'
};

/* ===============================
   RENDER BUTTONS
   =============================== */
function renderButtons(buttons) {
    return buttons
        .map(text => {
            const className = buttonClassMap[text] || 'btn-outline';
            return `<button class="${className}" data-action="${text}">${text}</button>`;
        })
        .join('');
}

/* ===============================
   RENDER ORDERS
   =============================== */
function renderOrders() {
    const orderList = document.getElementById('orderList');
    if (!orderList) return;

    const filteredOrders = ordersData.filter(order =>
        currentStatusFilter === 'all' || order.status === currentStatusFilter
    );

    orderList.innerHTML = filteredOrders
        .map(order => `
        <article class="card-order">
            <header class="header2">
                <div>
                    <div class="order-id">Order ID</div>
                    <b>${order.id}</b>
                </div>
                <div>
                    <span class="status ${order.status}">${order.statusText}</span>
                    <div>Order date: ${order.date}</div>
                </div>
            </header>

            <div class="details">
                <div class="product">
                    <img class="product-img" src="../img/pr2.svg">
                    
                    <div class="product-info">
                        <div class="fruit-filled-candy">${order.product}</div>

                        <div class="product-meta">
                            <div class="unit-related-product">
                                <div class="g-wrapper">
                                    <span class="g">${order.weight}</span>
                                </div>

                                <img class="icon-drop-down" src="../img/Icon _ Drop down.svg" alt="dropdown">
                            </div>
                            <div class="quantity-text">
                                Quantity: <b>${order.quantity}</b>
                            </div>
                        </div>
                    </div>

                    <div class="price">
                        <div class="old">${order.oldPrice}</div>
                        <div class="new">${order.newPrice}</div>
                    </div>
                </div>
            </div>

            <footer class="order-action">
                <div class="order-action-left">
                    ${renderButtons(order.buttons)}
                </div>
                <div class="order-action-right">
                   <span class="total-label">Total:</span>
                   <span class="total-price">${order.total}</span>
                </div>
            </footer>
        </article>
    `)
        .join('');

    const totalOrders = document.getElementById('totalOrders');
    if (totalOrders) {
        totalOrders.textContent = `${filteredOrders.length} Orders`;
    }
}

/* ===============================
   DROPDOWN SETUP
   =============================== */
function setupDropdowns() {
    setupDropdown('statusFilter', 'statusMenu', 'statusLabel', value => {
        currentStatusFilter = value;
        renderOrders();
    });

    setupDropdown('timeFilter', 'timeMenu', 'timeLabel', value => {
        currentTimeFilter = value;
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.filter2').forEach(f => f.classList.remove('active'));
    });
}

function setupDropdown(filterId, menuId, labelId, onSelect) {
    const filter = document.getElementById(filterId);
    const menu = document.getElementById(menuId);
    const label = document.getElementById(labelId);
    if (!filter || !menu) return;

    filter.querySelector('.attribute2').addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('show');
        filter.classList.toggle('active');
    });

    menu.addEventListener('click', e => {
        if (e.target.tagName === 'LI') {
            const value = e.target.dataset.value;
            if (label) label.textContent = e.target.textContent;
            menu.classList.remove('show');
            filter.classList.remove('active');
            onSelect(value);
        }
    });
}
