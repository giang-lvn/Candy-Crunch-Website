/*********************************
 * GLOBAL STATE
 *********************************/
let ordersData = [];
let currentStatusFilter = 'all';
let currentTimeFilter = '30';

/*********************************
 * INIT
 *********************************/
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    setupDropdowns();
    loadOrders();
});

/*********************************
 * LOAD ORDERS FROM API
 *********************************/
function loadOrders() {
    fetch('/../controllers/website/orders_controller.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error('API error:', data.message);
                return;
            }
            ordersData = data.orders;
            renderOrders();
        })
        .catch(err => console.error('Fetch orders failed:', err));
}

/*********************************
 * MENU NAVIGATION
 *********************************/
function initMenuNavigation() {
    const menus = document.querySelectorAll('.account-menu');

    menus.forEach(menu => {
        menu.addEventListener('click', e => {
            e.preventDefault();
            const text = menu.querySelector('.my-orders2')?.textContent.trim();
            handleMenuAction(text);
        });
    });

    const currentPage = window.location.pathname;
    menus.forEach(m => m.classList.remove('active'));

    menus.forEach(menu => {
        const text = menu.querySelector('.my-orders2')?.textContent.trim();
        if (text === 'My Orders' && currentPage.includes('orders')) {
            menu.classList.add('active');
        }
    });
}

function handleMenuAction(action) {
    switch (action) {
        case 'My Account':
            window.location.href = 'my_account.php';
            break;
        case 'Change Password':
            window.location.href = 'changepass.php';
            break;
        case 'My Orders':
            window.location.href = 'my_orders.php';
            break;
        case 'My Vouchers':
            window.location.href = 'my_vouchers.php';
            break;
        case 'Log out':
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
            break;
    }
}

/*********************************
 * RENDER ORDERS
 *********************************/
function renderOrders() {
    const orderList = document.getElementById('orderList');
    if (!orderList) return;

    const filteredOrders = ordersData.filter(order =>
        currentStatusFilter === 'all' || order.status === currentStatusFilter
    );

    orderList.innerHTML = filteredOrders.map(order => `
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
                            </div>
                            <div class="quantity-text">
                                Quantity: <b>${order.quantity}</b>
                            </div>
                        </div>
                    </div>

                    <div class="price">
                        <div class="new">${order.total}</div>
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
    `).join('');

    const totalOrders = document.getElementById('totalOrders');
    if (totalOrders) {
        totalOrders.textContent = `${filteredOrders.length} Orders`;
    }
}

/*********************************
 * BUTTONS
 *********************************/
const buttonClassMap = {
    'Pay Now': 'btn-error-outline-small',
    'Buy Again': 'btn-primary-medium',
    'Change Method': 'btn-primary-outline-small',
    'Return': 'btn-secondary-outline-small',
    'Cancel': 'btn-secondary-small',
    'Contact': 'btn-error-outline-small',
    'Confirmed': 'btn-error-small',
    'Write Review': 'btn-primary-outline-small'
};

function renderButtons(buttons = []) {
    return buttons.map(text => {
        const className = buttonClassMap[text] || 'btn-outline';
        return `<button class="${className}">${text}</button>`;
    }).join('');
}

/*********************************
 * DROPDOWNS
 *********************************/
function setupDropdowns() {
    setupDropdown('statusFilter', 'statusMenu', 'statusLabel', value => {
        currentStatusFilter = value;
        renderOrders();
    });

    setupDropdown('timeFilter', 'timeMenu', 'timeLabel', value => {
        currentTimeFilter = value;
    });

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
