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
    setupCancelModalEvents();
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

    // Add click event to order cards
    document.querySelectorAll('.card-order').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', (e) => {
            // Prevent redirect if clicking on buttons or their children
            if (e.target.closest('button') || e.target.closest('.btn-primary-medium') || e.target.closest('.btn-secondary-medium') || e.target.closest('.btn-primary-outline-medium') || e.target.closest('.btn-secondary-outline-medium') || e.target.closest('.btn-error-outline-small') || e.target.closest('.btn-error-small')) {
                return;
            }
            const orderId = card.querySelector('b').textContent; // Assuming order ID is in the <b> tag
            window.location.href = `/Candy-Crunch-Website/controllers/website/OrderDetailController.php?id=${orderId}`;
        });
    });
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
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
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
            // Redirect to return.php (same as order_detail)
            window.location.href = `/Candy-Crunch-Website/views/website/php/return.php?order_id=${orderId}`;
            break;
        case 'Contact':
            const subject = `Support Request for Order #${orderId}`;
            const body = `Hi Candy Crunch Team,\n\nI have a question about my order #${orderId}.\n\n`;
            // Use Gmail web interface since mailto is not working reliably
            const gmailLink = `https://mail.google.com/mail/?view=cm&fs=1&to=support@candycrunch.com&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.open(gmailLink, '_blank');
            break;
        case 'Buy Again':
            handleBuyAgain(orderId);
            break;
        case 'Pay Now':
            window.location.href = `/Candy-Crunch-Website/views/website/php/checkout.php?order_id=${orderId}`;
            break;
        case 'Write Review':
            handleWriteReview(orderId);
            break;
        default:
            console.log('Unknown action:', action);
    }
}

/*********************************
 * BUY AGAIN - Thêm sản phẩm vào giỏ hàng và redirect
 *********************************/
function handleBuyAgain(orderId) {
    if (!orderId) {
        alert('Error: Order ID not found');
        return;
    }

    // Hiển thị thông báo đang xử lý
    alert('Adding products to cart...');

    // Chuyển hướng đến controller để thêm vào giỏ hàng
    window.location.href = `/Candy-Crunch-Website/index.php?controller=OrderDetail&action=reOrder&id=${orderId}`;
}

/*********************************
 * WRITE REVIEW - Mở popup rating
 *********************************/
function handleWriteReview(orderId) {
    if (!orderId) {
        alert('Error: Order ID not found');
        return;
    }

    // Tìm đơn hàng trong dữ liệu
    const order = ordersData.find(o => o.id === orderId);

    if (!order || !order.products || order.products.length === 0) {
        alert('No products found for this order.');
        return;
    }

    // Populate product select dropdown
    const productSelect = document.getElementById('rating-product-select');
    if (productSelect) {
        productSelect.innerHTML = order.products.map(product =>
            `<option value="${product.sku_id}">${product.name} - ${product.weight}</option>`
        ).join('');
    }

    // Set order ID
    const orderIdInput = document.getElementById('rating-order-id');
    if (orderIdInput) {
        orderIdInput.value = orderId;
    }

    // Reset star rating
    const starRating = document.querySelector('.star-rating');
    if (starRating) {
        starRating.dataset.rating = 0;
        document.querySelectorAll('.star-rating .star').forEach(star => {
            star.classList.remove('active');
        });
    }

    // Reset review text
    const reviewText = document.getElementById('rating-review-text');
    if (reviewText) {
        reviewText.value = '';
    }

    // Show rating popup
    const ratingOverlay = document.getElementById('rating-overlay');
    if (ratingOverlay) {
        ratingOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/*********************************
 * CANCEL MODAL - Giống như cancel.php
 *********************************/
let cancelSelectedReason = '';

// function createCancelModal() removed - using static HTML in my_orders.php

function setupCancelModalEvents() {
    const overlay = document.getElementById('cancel-order-overlay');
    const closeBtn = document.getElementById('cancelPopupClose');
    const dropdownTrigger = document.getElementById('cancelDropdownTrigger');
    const dropdownMenu = document.getElementById('cancelDropdownMenu');
    const dropdownText = dropdownTrigger ? dropdownTrigger.querySelector('.dropdown-text') : null;
    const submitBtn = document.getElementById('submitCancelOrder');


    // Đóng popup
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCancelModal);
    }

    // Đóng popup khi click ngoài popup
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeCancelModal();
            }
        });
    }

    // Dropdown lý do hủy
    if (dropdownTrigger && dropdownMenu) {
        dropdownTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        dropdownMenu.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                cancelSelectedReason = option.dataset.value;
                if (dropdownText) {
                    dropdownText.textContent = cancelSelectedReason;
                }
                dropdownMenu.classList.remove('show');
            });
        });
    }

    // Submit button
    if (submitBtn) {
        submitBtn.addEventListener('click', submitCancelRequest);
    }



    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        if (dropdownMenu) {
            dropdownMenu.classList.remove('show');
        }
    });
}

function showCancelModal(orderId) {
    const overlay = document.getElementById('cancel-order-overlay');
    const dropdownText = document.querySelector('#cancelDropdownTrigger .dropdown-text');

    document.getElementById('cancelOrderId').value = orderId;
    cancelSelectedReason = '';
    if (dropdownText) {
        dropdownText.textContent = 'Select a cancel reason';
    }

    if (overlay) {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeCancelModal() {
    const overlay = document.getElementById('cancel-order-overlay');
    if (overlay) {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function submitCancelRequest() {
    const orderId = document.getElementById('cancelOrderId').value;
    const submitBtn = document.getElementById('submitCancelOrder');

    if (!cancelSelectedReason) {
        alert('Please select a reason to cancel your order.');
        return;
    }

    // Disable button while processing
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';

    // Send cancel request
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('reason', cancelSelectedReason);

    fetch('/Candy-Crunch-Website/controllers/website/CancelController.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeCancelModal();
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Request';
            }
        })
        .catch(err => {
            console.error('Cancel request failed:', err);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Request';
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
