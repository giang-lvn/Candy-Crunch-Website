document.addEventListener('DOMContentLoaded', function () {

    // ========== XỬ LÝ DROPDOWN - RETURN REASON ==========
    const reasonTrigger = document.getElementById('returnReasonTrigger');
    const reasonMenu = document.getElementById('returnReasonMenu');
    const reasonInput = document.getElementById('refundReasonInput');

    if (reasonTrigger && reasonMenu) {
        const reasonText = reasonTrigger.querySelector('.dropdown-text');

        reasonTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== reasonMenu) m.classList.remove('active');
            });
            reasonMenu.classList.toggle('active');
        });

        reasonMenu.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                reasonText.textContent = value;
                if (reasonInput) {
                    reasonInput.value = value;
                }
                reasonMenu.classList.remove('active');
            });
        });
    }

    // ========== XỬ LÝ DROPDOWN - REFUND METHOD ==========
    const methodTrigger = document.getElementById('refundMethodTrigger');
    const methodMenu = document.getElementById('refundMethodMenu');
    const methodInput = document.getElementById('refundMethodInput');

    if (methodTrigger && methodMenu) {
        const methodText = methodTrigger.querySelector('.dropdown-text');

        methodTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== methodMenu) m.classList.remove('active');
            });
            methodMenu.classList.toggle('active');
        });

        methodMenu.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                methodText.textContent = value;
                if (methodInput) {
                    methodInput.value = value;
                }
                methodMenu.classList.remove('active');
            });
        });
    }

    // Đóng dropdown khi click ngoài
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('active');
        });
    });


    // ========== VALIDATE FORM TRƯỚC KHI SUBMIT ==========
    const form = document.getElementById('returnForm');

    if (form) {
        form.addEventListener('submit', function (e) {
            let isValid = true;
            let errorMessage = '';

            // Kiểm tra lý do hoàn trả
            const refundReason = document.getElementById('refundReasonInput').value;
            if (!refundReason) {
                isValid = false;
                errorMessage += 'Please select a return reason.\n';
            }

            // Kiểm tra phương thức hoàn tiền
            const refundMethod = document.getElementById('refundMethodInput').value;
            if (!refundMethod) {
                isValid = false;
                errorMessage += 'Please select a refund method.\n';
            }

            // Kiểm tra ảnh
            const fileInput = document.querySelector('input[name="refund_image"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (file.size > maxSize) {
                    isValid = false;
                    errorMessage += 'Image size must be less than 5MB.\n';
                }

                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    errorMessage += 'Only JPEG, PNG, WEBP images are allowed.\n';
                }
            }

            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            } else {
                // Debug log - xem giá trị đang được submit
                console.log('Submitting form with:');
                console.log('Order ID:', document.querySelector('input[name="order_id"]')?.value);
                console.log('Reason:', document.getElementById('refundReasonInput')?.value);
                console.log('Method:', document.getElementById('refundMethodInput')?.value);
            }
        });
    }


    // ========== PREVIEW ẢNH TRƯỚC KHI UPLOAD ==========
    const fileInput = document.querySelector('input[name="refund_image"]');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                console.log('Image selected:', file.name);
            }
        });
    }
});