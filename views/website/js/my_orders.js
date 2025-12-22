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
                    <b><a href="/Candy-Crunch-Website/index.php?controller=OrderDetail&action=index&id=${order.id}" style="text-decoration: none; color: inherit; cursor: pointer;">${order.id}</a></b>
                </div>
                <div>
                    <span class="status ${order.status}">${order.statusText}</span>
                    <div>Order date: ${order.date}</div>
                </div>
            </header>

            <div class="details">
                ${renderProductsHtml(order.products)}
            </div>

            <footer class="order-action">
                <div class="order-action-left">
                <div class="order-action-left">
                    ${renderButtons(order.buttons, order.id)}
                </div>
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

    // Attach event listeners to buttons
    attachButtonListeners();
}

function renderProductsHtml(products) {
    let html = '';
    products.forEach((p, index) => {
        html += `
            <div class="product">
                <img class="product-img" src="${p.image || '../img/pr2.svg'}" onerror="this.src='../img/pr2.svg'">

                <div class="product-info">
                    <div class="fruit-filled-candy">${p.name}</div>

                    <div class="product-meta">
                        <div class="unit-related-product">
                            <div class="g-wrapper">
                                <span class="g">${p.weight}</span>
                            </div>
                        </div>
                        <div class="quantity-text">
                            Quantity: <b>${p.quantity}</b>
                        </div>
                    </div>
                </div>

                <div class="price">
                    <div class="new">${p.price}</div>
                </div>
            </div>
        `;

        // Add separator if not the last item
        if (index < products.length - 1) {
            html += '<div style="margin: 10px 0; border-bottom: 1px solid #eee;"></div>';
        }
    });
    return html;
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

function renderButtons(buttons = [], orderId) {
    return buttons.map(text => {
        const className = buttonClassMap[text] || 'btn-outline';
        return `<button class="${className}" data-action="${text}" data-id="${orderId}">${text}</button>`;
    }).join('');
}

function attachButtonListeners() {
    const orderList = document.getElementById('orderList');
    if (!orderList) return;

    const buttons = orderList.querySelectorAll('button[data-action]');
    buttons.forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const action = btn.dataset.action;
            const orderId = btn.dataset.id;
            handleOrderAction(action, orderId);
        });
    });
}

function handleOrderAction(action, orderId) {
    console.log('Action:', action, 'OrderId:', orderId);
    switch (action) {
        case 'Cancel':
            showCancelPopup(orderId);
            break;
        case 'Contact':
            console.log('Contact support for order:', orderId);
            break;
        case 'Pay Now':
            console.log('Pay now for order:', orderId);
            break;
        case 'Buy Again':
            // Redirect to reOrder action
            window.location.href = `/Candy-Crunch-Website/index.php?controller=OrderDetail&action=reOrder&id=${orderId}`;
            break;
        case 'Write Review':
            showRatingPopup(orderId);
            break;
        default:
            console.warn('Unknown action:', action);
    }
}

/*********************************
 * RATING POPUP LOGIC
 *********************************/
let ratingOverlay, ratingPopup, currentRating = 0;

function initRatingPopup() {
    ratingOverlay = document.getElementById('rating-overlay');
    ratingPopup = document.querySelector('.rating-popup');

    // Star rating functionality
    const stars = document.querySelectorAll('.star-rating .star');
    const starRating = document.querySelector('.star-rating');

    if (stars.length > 0 && starRating) {
        stars.forEach((star) => {
            star.addEventListener('click', () => {
                currentRating = parseInt(star.dataset.value);
                starRating.dataset.rating = currentRating;
                updateStars(currentRating);
            });

            star.addEventListener('mouseenter', () => {
                updateStars(parseInt(star.dataset.value));
            });
        });

        starRating.addEventListener('mouseleave', () => {
            updateStars(currentRating);
        });
    }

    // Close button
    const closeBtn = document.getElementById('closeRatingPopup');
    if (closeBtn) {
        closeBtn.addEventListener('click', hideRatingPopup);
    }

    // Click outside to close
    if (ratingOverlay) {
        ratingOverlay.addEventListener('click', (e) => {
            if (e.target === ratingOverlay) hideRatingPopup();
        });
    }

    // Submit button
    const submitBtn = document.getElementById('submitRating');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitRating);
    }
}

function updateStars(rating) {
    const stars = document.querySelectorAll('.star-rating .star');
    stars.forEach((star) => {
        if (parseInt(star.dataset.value) <= rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function showRatingPopup(orderId) {
    if (!ratingOverlay) initRatingPopup();

    // Find order products to populate dropdown
    const order = ordersData.find(o => o.id === orderId);
    const productSelect = document.getElementById('rating-product-select');

    if (productSelect && order && order.products) {
        productSelect.innerHTML = '';
        order.products.forEach((p, idx) => {
            const skuId = order.productSkuIds ? order.productSkuIds[idx] : '';
            const option = document.createElement('option');
            option.value = skuId;
            option.textContent = `${p.name} - ${p.weight}`;
            productSelect.appendChild(option);
        });
    }

    // Store order ID
    document.getElementById('rating-order-id').value = orderId;

    // Reset form
    currentRating = 0;
    updateStars(0);
    const reviewText = document.getElementById('rating-review-text');
    if (reviewText) reviewText.value = '';

    if (ratingOverlay) {
        ratingOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function hideRatingPopup() {
    if (ratingOverlay) {
        ratingOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function submitRating() {
    const productSelect = document.getElementById('rating-product-select');
    const reviewText = document.getElementById('rating-review-text');
    const skuID = productSelect ? productSelect.value : '';
    const comment = reviewText ? reviewText.value.trim() : '';
    const submitBtn = document.getElementById('submitRating');

    if (currentRating === 0) {
        alert('Please select a rating!');
        return;
    }

    if (!skuID) {
        alert('Please select a product!');
        return;
    }

    // Disable button
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    fetch('/Candy-Crunch-Website/controllers/website/RatingController.php?action=submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `sku_id=${encodeURIComponent(skuID)}&rating=${currentRating}&comment=${encodeURIComponent(comment)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideRatingPopup();
            } else {
                alert(data.message || 'Failed to submit review. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
            }
        });
}

/*********************************
 * CANCEL POPUP LOGIC
 *********************************/
let cancelOverlay, cancelPopupClose, closeContactBtn, dropdownTrigger, dropdownMenu, dropdownText, submitCancelBtn, cancelMessage;

function initCancelPopup() {
    cancelOverlay = document.getElementById("cancel-order-overlay");
    cancelPopupClose = document.getElementById("cancelPopupClose");
    closeContactBtn = document.getElementById("closeContactBtn");
    dropdownTrigger = document.getElementById('dropdownTrigger');
    dropdownMenu = document.getElementById('dropdownMenu');
    dropdownText = dropdownTrigger ? dropdownTrigger.querySelector('.dropdown-text') : null;
    submitCancelBtn = document.getElementById('submitCancelOrder');
    cancelMessage = document.getElementById('cancelMessage');

    if (cancelPopupClose) cancelPopupClose.addEventListener("click", hideCancelPopup);
    if (closeContactBtn) closeContactBtn.addEventListener("click", hideCancelPopup);

    if (cancelOverlay) {
        cancelOverlay.addEventListener("click", (e) => {
            if (e.target === cancelOverlay) hideCancelPopup();
        });
    }

    if (dropdownTrigger && dropdownMenu) {
        dropdownTrigger.addEventListener('click', () => {
            dropdownMenu.classList.toggle('show');
        });

        dropdownMenu.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', () => {
                const value = option.dataset.value;
                if (dropdownText) dropdownText.textContent = value;
                dropdownTrigger.dataset.value = value;
                dropdownMenu.classList.remove('show');
            });
        });
    }

    if (submitCancelBtn) {
        submitCancelBtn.addEventListener('click', submitCancelOrder);
    }
}

function showCancelPopup(orderId) {
    if (!cancelOverlay) initCancelPopup();

    document.getElementById('cancelOrderID').value = orderId;
    if (dropdownText) dropdownText.textContent = 'Select a return reason';
    if (dropdownTrigger) delete dropdownTrigger.dataset.value;
    if (cancelMessage) cancelMessage.textContent = '';
    if (cancelOverlay) cancelOverlay.classList.remove("hidden");
}

function hideCancelPopup() {
    if (cancelOverlay) cancelOverlay.classList.add("hidden");
}

function submitCancelOrder() {
    const orderID = document.getElementById('cancelOrderID').value;
    const reason = dropdownTrigger ? dropdownTrigger.dataset.value : '';

    if (!reason) {
        if (cancelMessage) {
            cancelMessage.style.color = 'red';
            cancelMessage.textContent = 'Please select a reason.';
        }
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/Candy-Crunch-Website/controllers/website/CancelController.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (cancelMessage) {
                    cancelMessage.style.color = res.success ? 'green' : 'red';
                    cancelMessage.textContent = res.message;
                }
                if (res.success) {
                    setTimeout(() => {
                        hideCancelPopup();
                        loadOrders(); // Reload orders to update status
                    }, 1500);
                }
            } catch (e) {
                console.error(e);
            }
        }
    };
    xhr.send('order_id=' + encodeURIComponent(orderID) + '&reason=' + encodeURIComponent(reason));
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
