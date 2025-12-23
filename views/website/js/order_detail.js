/**
 * OrderDetail.js - Xử lý các tương tác trên trang Order Detail
 */

// Khởi tạo khi DOM load xong
document.addEventListener('DOMContentLoaded', function () {
    initOrderDetail();
});

/**
 * Khởi tạo các event listeners
 */
function initOrderDetail() {
    // Button Cancel Order
    const btnCancel = document.querySelector('.btn-cancel-order');
    if (btnCancel) {
        btnCancel.addEventListener('click', handleCancelOrder);
    }

    // Button Confirm Received
    const btnConfirm = document.querySelector('.btn-confirm-received');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', handleConfirmReceived);
    }

    // Button Contact
    const btnContact = document.querySelector('.btn-contact');
    if (btnContact) {
        btnContact.addEventListener('click', handleContact);
    }

    // Button Buy Again
    const btnBuyAgain = document.querySelector('.btn-buy-again');
    if (btnBuyAgain) {
        btnBuyAgain.addEventListener('click', handleBuyAgain);
    }

    // Button Pay Now
    const btnPayNow = document.querySelector('.btn-pay-now');
    if (btnPayNow) {
        btnPayNow.addEventListener('click', handlePayNow);
    }

    // Button Change Method
    const btnChangeMethod = document.querySelector('.btn-change-method');
    if (btnChangeMethod) {
        btnChangeMethod.addEventListener('click', handleChangeMethod);
    }

    // Button Return
    const btnReturn = document.querySelector('.btn-return');
    if (btnReturn) {
        btnReturn.addEventListener('click', handleReturn);
    }

    // Button Write Review
    const btnReview = document.querySelector('.btn-write-review');
    if (btnReview) {
        btnReview.addEventListener('click', handleWriteReview);
    }

    // Close Rating Popup
    const closeRatingBtn = document.getElementById('closeRatingPopup');
    if (closeRatingBtn) {
        closeRatingBtn.addEventListener('click', function () {
            const overlay = document.getElementById('rating-overlay');
            if (overlay) overlay.classList.add('hidden');
        });
    }

    // Submit Rating
    const submitRatingBtn = document.getElementById('submitRating');
    if (submitRatingBtn) {
        submitRatingBtn.addEventListener('click', handleSubmitRating);
    }

    // Setup Cancel Popup Events
    setupCancelPopupEvents();
}

/**
 * Xử lý hủy đơn hàng - Hiển thị popup
 */
let cancelSelectedReason = '';

function handleCancelOrder(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Hiển thị cancel popup
    showCancelPopup();
}

/**
 * Hiển thị cancel popup
 */
function showCancelPopup() {
    const overlay = document.getElementById('cancel-order-overlay');
    const dropdownText = document.querySelector('#cancelDropdownTrigger .dropdown-text');

    cancelSelectedReason = '';
    if (dropdownText) {
        dropdownText.textContent = 'Select a cancel reason';
    }

    if (overlay) {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Đóng cancel popup
 */
function closeCancelPopup() {
    const overlay = document.getElementById('cancel-order-overlay');
    if (overlay) {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Setup cancel popup events
 */
function setupCancelPopupEvents() {
    const overlay = document.getElementById('cancel-order-overlay');
    const closeBtn = document.getElementById('cancelPopupClose');
    const dropdownTrigger = document.getElementById('cancelDropdownTrigger');
    const dropdownMenu = document.getElementById('cancelDropdownMenu');
    const dropdownText = dropdownTrigger ? dropdownTrigger.querySelector('.dropdown-text') : null;
    const submitBtn = document.getElementById('submitCancelOrder');
    const contactBtn = document.getElementById('cancelContactBtn');

    // Đóng popup
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCancelPopup);
    }

    // Đóng popup khi click ngoài popup
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeCancelPopup();
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

    // Contact button
    if (contactBtn) {
        contactBtn.addEventListener('click', () => {
            window.location.href = '/Candy-Crunch-Website/views/website/policy.php';
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        if (dropdownMenu) {
            dropdownMenu.classList.remove('show');
        }
    });
}

/**
 * Submit cancel request
 */
function submitCancelRequest() {
    const orderId = getOrderIdFromPage();
    const submitBtn = document.getElementById('submitCancelOrder');

    if (!cancelSelectedReason) {
        showNotification('Please select a reason to cancel your order.', 'error');
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
                showNotification(data.message, 'success');
                closeCancelPopup();

                // Reload trang sau 1.5s
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Request';
            }
        })
        .catch(err => {
            console.error('Cancel request failed:', err);
            showNotification('An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Request';
        });
}

/**
 * Xử lý xác nhận đã nhận hàng
 */
function handleConfirmReceived(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Hiển thị confirm dialog
    if (!confirm('Have you received this order?')) {
        return;
    }

    // Gửi AJAX request
    fetch('/Candy-Crunch-Website/index.php?controller=OrderDetail&action=confirmReceived', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');

                // Reload trang sau 1.5s
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
}

/**
 * Xử lý button Contact - Mở Gmail với nội dung sẵn
 */
function handleContact(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();
    const supportEmail = 'support@candycrunch.com'; // Email hỗ trợ

    // Tạo subject và body cho email
    const subject = encodeURIComponent(`Support Request - Order ID: ${orderId}`);
    const body = encodeURIComponent(
        `Hi Support Team,\n\n` +
        `I need help with my order (Order ID: ${orderId}).\n\n` +
        `Issue: [Please describe your issue here]\n\n` +
        `Thank you!`
    );

    // Mở Gmail/Email client (Direct Link)
    const gmailLink = `https://mail.google.com/mail/?view=cm&fs=1&to=${supportEmail}&su=${subject}&body=${body}`;
    window.open(gmailLink, '_blank');
}

/**
 * Xử lý button Buy Again
 */
function handleBuyAgain(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Hiển thị loading
    showNotification('Adding products to cart...', 'info');

    // Chuyển hướng đến controller để thêm vào giỏ hàng
    window.location.href = `/Candy-Crunch-Website/index.php?controller=OrderDetail&action=reOrder&id=${orderId}`;
}

/**
 * Xử lý button Pay Now
 */
function handlePayNow(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Chuyển hướng đến trang thanh toán
    window.location.href = `/Candy-Crunch-Website/index.php?controller=OrderDetail&action=payNow&id=${orderId}`;
}

/**
 * Xử lý button Change Method
 */
function handleChangeMethod(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Chuyển hướng đến trang đổi phương thức thanh toán
    window.location.href = `/Candy-Crunch-Website/index.php?controller=OrderDetail&action=changeMethod&id=${orderId}`;
}

/**
 * Xử lý button Return
 */
function handleReturn(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Chuyển hướng đến trang Return
    window.location.href = `/Candy-Crunch-Website/views/website/php/return.php?order_id=${orderId}`;
}

/**
 * Xử lý button Write Review - Hiển thị popup rating
 */
function handleWriteReview(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Show rating popup embedded in order_detail.php
    const ratingOverlay = document.getElementById('rating-overlay');
    if (ratingOverlay) {
        ratingOverlay.classList.remove('hidden');
        // Set order ID for the rating form
        const orderIdInput = document.getElementById('rating-order-id');
        if (orderIdInput) {
            orderIdInput.value = orderId;
        }
    } else {
        // Fallback: redirect to rating page if popup not found
        window.location.href = `/Candy-Crunch-Website/views/website/php/rating.php?order_id=${orderId}`;
    }
}

/**
 * Xử lý submit rating
 */
function handleSubmitRating() {
    const skuId = document.getElementById('rating-product-select')?.value;
    const starRating = document.querySelector('.star-rating')?.dataset.rating || 0;
    const reviewText = document.getElementById('rating-review-text')?.value || '';
    const orderId = document.getElementById('rating-order-id')?.value;

    if (!skuId || starRating == 0) {
        showNotification('Please select a product and provide a rating.', 'error');
        return;
    }

    fetch('/Candy-Crunch-Website/controllers/website/RatingController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `sku_id=${skuId}&rating=${starRating}&review=${encodeURIComponent(reviewText)}&order_id=${orderId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Review submitted successfully!', 'success');
                // Close popup
                const overlay = document.getElementById('rating-overlay');
                if (overlay) overlay.classList.add('hidden');
            } else {
                showNotification(data.message || 'Failed to submit review.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while submitting your review.', 'error');
        });
}

/**
 * Lấy OrderID từ trang (từ element có class 'order-id')
 * @returns {string|null}
 */
function getOrderIdFromPage() {
    const orderIdElement = document.querySelector('.order-id');
    return orderIdElement ? orderIdElement.textContent.trim() : null;
}

/**
 * Hiển thị thông báo cho user
 * @param {string} message - Nội dung thông báo
 * @param {string} type - Loại: 'success', 'error', 'info'
 */
function showNotification(message, type = 'info') {
    // Kiểm tra xem đã có notification container chưa
    let container = document.querySelector('.notification-container');

    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }

    // Tạo notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close">&times;</button>
    `;

    // Thêm vào container
    container.appendChild(notification);

    // Hiển thị animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Xử lý button close
    const btnClose = notification.querySelector('.notification-close');
    btnClose.addEventListener('click', () => {
        closeNotification(notification);
    });

    // Tự động đóng sau 5s
    setTimeout(() => {
        closeNotification(notification);
    }, 5000);
}

/**
 * Đóng notification
 * @param {HTMLElement} notification
 */
function closeNotification(notification) {
    notification.classList.remove('show');

    setTimeout(() => {
        notification.remove();
    }, 300);
}

/**
 * Format số tiền (thêm dấu phẩy ngăn cách)
 * @param {number} amount
 * @returns {string}
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

/**
 * Format ngày tháng
 * @param {string} dateString
 * @returns {string}
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
}

/**
 * Format thời gian
 * @param {string} dateString
 * @returns {string}
 */
function formatTime(dateString) {
    const date = new Date(dateString);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
}
