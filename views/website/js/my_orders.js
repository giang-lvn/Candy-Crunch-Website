/*********************************
 * GLOBAL STATE
 *********************************/
let ordersData = [];
let currentStatusFilter = 'all';
let currentTimeFilter = '30';

/*********************************
 * CANCEL & RETURN REASONS
 *********************************/
const cancelReasons = [
    { value: 'voucher', text: 'Tôi tìm thấy thêm voucher cho đơn hàng', redirectToCheckout: true },
    { value: 'no_need', text: 'Tôi không có nhu cầu mua nữa', redirectToCheckout: false },
    { value: 'edit_order', text: 'Tôi muốn chỉnh sửa lại chi tiết đơn hàng', redirectToCheckout: true }
];

const returnReasons = [
    { value: 'not_as_expected', text: 'Sản phẩm không như mong đợi của tôi' },
    { value: 'no_need', text: 'Tôi không còn nhu cầu sử dụng nữa' },
    { value: 'unsatisfied', text: 'Tôi không hài lòng với dịch vụ của Candy Crunch' }
];

/*********************************
 * INIT
 *********************************/
document.addEventListener('DOMContentLoaded', () => {
    initMenuNavigation();
    setupDropdowns();
    loadOrders();
    createCancelModal();
    createReturnModal();
});

/*********************************
 * LOAD ORDERS FROM API
 *********************************/
function loadOrders() {
    fetch('/Candy-Crunch-Website/controllers/website/orders_controller.php')
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
 * RENDER ORDERS - Hiển thị nhiều sản phẩm trong 1 thẻ
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
                ${renderProducts(order.products)}
            </div>

            <footer class="order-action">
                <div class="order-action-left">
                    ${renderButtons(order)}
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

    // Rebind button events
    bindOrderButtons();
}

/*********************************
 * RENDER PRODUCTS - Hiển thị danh sách sản phẩm trong đơn hàng
 *********************************/
function renderProducts(products) {
    if (!products || products.length === 0) {
        return '<div class="product"><p>No products found</p></div>';
    }

    return products.map((product, index) => `
        <div class="product ${index > 0 ? 'product-border-top' : ''}">
            <img class="product-img" src="${product.image || '../img/pr2.svg'}" alt="${product.name}" onerror="this.src='../img/pr2.svg'">

            <div class="product-info">
                <div class="fruit-filled-candy">${product.name}</div>

                <div class="product-meta">
                    <div class="unit-related-product">
                        <div class="g-wrapper">
                            <span class="g">${product.weight}</span>
                        </div>
                    </div>
                    <div class="quantity-text">
                        Quantity: <b>${product.quantity}</b>
                    </div>
                </div>
            </div>

            <div class="price">
                <div class="new">${product.itemTotal}</div>
            </div>
        </div>
    `).join('');
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

function renderButtons(order) {
    return order.buttons.map(text => {
        const className = buttonClassMap[text] || 'btn-outline';
        return `<button class="${className}" data-action="${text}" data-order-id="${order.id}">${text}</button>`;
    }).join('');
}

function bindOrderButtons() {
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.dataset.action;
            const orderId = this.dataset.orderId;
            handleOrderAction(action, orderId);
        });
    });
}

function handleOrderAction(action, orderId) {
    switch (action) {
        case 'Cancel':
            showCancelModal(orderId);
            break;
        case 'Return':
            showReturnModal(orderId);
            break;
        case 'Contact':
            window.location.href = '/Candy-Crunch-Website/views/website/policy.php';
            break;
        case 'Buy Again':
            // TODO: Implement buy again
            alert('Buy Again functionality coming soon!');
            break;
        case 'Pay Now':
            window.location.href = `/Candy-Crunch-Website/views/website/php/checkout.php?order_id=${orderId}`;
            break;
        case 'Write Review':
            // TODO: Implement review
            alert('Write Review functionality coming soon!');
            break;
        default:
            console.log('Unknown action:', action);
    }
}

/*********************************
 * CANCEL MODAL
 *********************************/
function createCancelModal() {
    const modal = document.createElement('div');
    modal.id = 'cancelModal';
    modal.className = 'order-modal';
    modal.innerHTML = `
        <div class="order-modal-content">
            <div class="order-modal-header">
                <h3>Xác nhận hủy đơn hàng</h3>
                <button class="order-modal-close" onclick="closeCancelModal()">&times;</button>
            </div>
            <div class="order-modal-body">
                <p>Vui lòng chọn lý do hủy đơn hàng:</p>
                <input type="hidden" id="cancelOrderId" value="">
                <div class="reason-select-container">
                    <select id="cancelReasonSelect" class="reason-select">
                        <option value="">-- Chọn lý do --</option>
                        ${cancelReasons.map(r => `<option value="${r.value}" data-redirect="${r.redirectToCheckout}">${r.text}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="order-modal-footer">
                <button class="btn-modal-secondary" onclick="closeCancelModal()">Đóng</button>
                <button class="btn-modal-primary" onclick="submitCancelRequest()" id="confirmCancelBtn">Xác nhận hủy</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function showCancelModal(orderId) {
    document.getElementById('cancelOrderId').value = orderId;
    document.getElementById('cancelReasonSelect').value = '';
    document.getElementById('cancelModal').classList.add('show');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('show');
}

function submitCancelRequest() {
    const orderId = document.getElementById('cancelOrderId').value;
    const selectEl = document.getElementById('cancelReasonSelect');
    const reason = selectEl.options[selectEl.selectedIndex]?.text || '';
    const reasonValue = selectEl.value;

    if (!reasonValue) {
        alert('Vui lòng chọn lý do hủy đơn hàng!');
        return;
    }

    // Disable button while processing
    const confirmBtn = document.getElementById('confirmCancelBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Đang xử lý...';

    // Send cancel request
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('reason', reason);

    fetch('/Candy-Crunch-Website/controllers/website/CancelController.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeCancelModal();

                // Redirect based on reason
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const redirectToCheckout = selectedOption.dataset.redirect === 'true';

                if (redirectToCheckout) {
                    window.location.href = `/Candy-Crunch-Website/views/website/php/checkout.php?order_id=${orderId}`;
                } else {
                    window.location.reload();
                }
            } else {
                alert('Error: ' + data.message);
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận hủy';
            }
        })
        .catch(err => {
            console.error('Cancel request failed:', err);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Xác nhận hủy';
        });
}

/*********************************
 * RETURN MODAL
 *********************************/
function createReturnModal() {
    const modal = document.createElement('div');
    modal.id = 'returnModal';
    modal.className = 'order-modal';
    modal.innerHTML = `
        <div class="order-modal-content">
            <div class="order-modal-header">
                <h3>Yêu cầu trả hàng</h3>
                <button class="order-modal-close" onclick="closeReturnModal()">&times;</button>
            </div>
            <div class="order-modal-body">
                <p>Vui lòng chọn lý do trả hàng:</p>
                <input type="hidden" id="returnOrderId" value="">
                <div class="reason-select-container">
                    <select id="returnReasonSelect" class="reason-select">
                        <option value="">-- Chọn lý do --</option>
                        ${returnReasons.map(r => `<option value="${r.value}">${r.text}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="order-modal-footer">
                <button class="btn-modal-secondary" onclick="closeReturnModal()">Đóng</button>
                <button class="btn-modal-primary" onclick="submitReturnRequest()" id="confirmReturnBtn">Gửi yêu cầu</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function showReturnModal(orderId) {
    document.getElementById('returnOrderId').value = orderId;
    document.getElementById('returnReasonSelect').value = '';
    document.getElementById('returnModal').classList.add('show');
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.remove('show');
}

function submitReturnRequest() {
    const orderId = document.getElementById('returnOrderId').value;
    const selectEl = document.getElementById('returnReasonSelect');
    const reason = selectEl.options[selectEl.selectedIndex]?.text || '';
    const reasonValue = selectEl.value;

    if (!reasonValue) {
        alert('Vui lòng chọn lý do trả hàng!');
        return;
    }

    // Disable button while processing
    const confirmBtn = document.getElementById('confirmReturnBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Đang xử lý...';

    // Send return request
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('reason', reason);

    fetch('/Candy-Crunch-Website/controllers/website/ReturnApiController.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeReturnModal();
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Gửi yêu cầu';
            }
        })
        .catch(err => {
            console.error('Return request failed:', err);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Gửi yêu cầu';
        });
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

    const attribute = filter.querySelector('.attribute2');
    if (attribute) {
        attribute.addEventListener('click', e => {
            e.stopPropagation();
            menu.classList.toggle('show');
            filter.classList.toggle('active');
        });
    }

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
