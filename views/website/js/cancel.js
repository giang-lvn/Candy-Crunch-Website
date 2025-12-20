document.addEventListener('DOMContentLoaded', () => {
    // Mở popup
    const cancelOrderBtn = document.getElementById("cancelOrderBtn");
    const cancelOverlay = document.getElementById("cancel-order-overlay");
    if (cancelOrderBtn) {
        cancelOrderBtn.addEventListener("click", () => {
            cancelOverlay.classList.remove("hidden");
        });
    }

    // Đóng popup
    const closeBtn = document.getElementById("cancelPopupClose");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            cancelOverlay.classList.add("hidden");
        });
    }

    // Đóng popup khi click ngoài popup
    cancelOverlay.addEventListener("click", (e) => {
        if (e.target === cancelOverlay) {
            cancelOverlay.classList.add("hidden");
        }
    });

    // Dropdown lý do hủy
    const dropdownTrigger = document.getElementById('dropdownTrigger');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const dropdownText = dropdownTrigger ? dropdownTrigger.querySelector('.dropdown-text') : null;
    let selectedReason = '';

    if (dropdownTrigger && dropdownMenu) {
        dropdownTrigger.addEventListener('click', () => {
            dropdownMenu.classList.toggle('show');
        });

        dropdownMenu.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', () => {
                selectedReason = option.dataset.value;
                dropdownText.textContent = selectedReason;
                dropdownMenu.classList.remove('show');
            });
        });
    }

    // Gửi yêu cầu hủy đơn qua AJAX
    const submitBtn = document.getElementById('submitCancelOrder');
    const messageElem = document.getElementById('cancelMessage');
    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            const orderIDElem = document.getElementById('cancelOrderID');
            const orderID = orderIDElem ? orderIDElem.value : 0;

            if (!selectedReason) {
                messageElem.style.color = 'red';
                messageElem.textContent = 'Please select a reason to cancel your order.';
                return;
            }

            // AJAX POST
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/controllers/website/CancelController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        messageElem.style.color = res.success ? 'green' : 'red';
                        messageElem.textContent = res.message;

                        if (res.success) {
                            setTimeout(() => {
                                cancelOverlay.classList.add('hidden');
                                location.reload(); // reload page nếu muốn
                            }, 1500);
                        }
                    } catch (e) {
                        messageElem.style.color = 'red';
                        messageElem.textContent = 'Unexpected server response.';
                        console.error(e);
                    }
                }
            };
            xhr.send('order_id=' + encodeURIComponent(orderID) + '&reason=' + encodeURIComponent(selectedReason));
        });
    }
});
