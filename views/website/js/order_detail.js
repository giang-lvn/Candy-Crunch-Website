/**
 * OrderDetail.js - Xử lý các tương tác trên trang Order Detail
 */

// Khởi tạo khi DOM load xong
document.addEventListener('DOMContentLoaded', function() {
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
}

/**
 * Xử lý hủy đơn hàng
 */
function handleCancelOrder(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Hiển thị confirm dialog
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }

    // Gửi AJAX request
    fetch('/index.php?controller=OrderDetail&action=cancel', {
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
    fetch('/index.php?controller=OrderDetail&action=confirmReceived', {
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

    // Mở Gmail/Email client
    window.location.href = `mailto:${supportEmail}?subject=${subject}&body=${body}`;
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
    window.location.href = `/index.php?controller=OrderDetail&action=reOrder&id=${orderId}`;
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
    window.location.href = `/index.php?controller=OrderDetail&action=payNow&id=${orderId}`;
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
    window.location.href = `/index.php?controller=OrderDetail&action=changeMethod&id=${orderId}`;
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
    window.location.href = `/index.php?controller=return&action=index&order_id=${orderId}`;
}

/**
 * Xử lý button Write Review
 */
function handleWriteReview(e) {
    e.preventDefault();

    const orderId = getOrderIdFromPage();

    if (!orderId) {
        showNotification('Error: Order ID not found', 'error');
        return;
    }

    // Chuyển hướng đến trang Review
    window.location.href = `/index.php?controller=review&action=write&order_id=${orderId}`;
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
