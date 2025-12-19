document.addEventListener('DOMContentLoaded', function() {
    
    // ========== XỬ LÝ DROPDOWN ==========
    const dropdowns = document.querySelectorAll('[data-type="dropdown"]');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        const text = dropdown.querySelector('.dropdown-text');
        const options = dropdown.querySelectorAll('.dropdown-option');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        
        // Toggle dropdown
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Đóng tất cả dropdown khác
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            
            menu.classList.toggle('active');
        });
        
        // Chọn option
        options.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-value');
                const displayText = this.textContent;
                
                text.textContent = displayText;
                if (hiddenInput) {
                    hiddenInput.value = value;
                }
                
                menu.classList.remove('active');
            });
        });
    });
    
    // Đóng dropdown khi click ngoài
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('active');
        });
    });
    
    
    // ========== VALIDATE FORM TRƯỚC KHI SUBMIT ==========
    const form = document.querySelector('.return-form');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        /* 1. Kiểm tra có chọn sản phẩm không
        const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
        if (selectedProducts.length === 0) {
            isValid = false;
            errorMessage += 'Please select at least one product.\n';
        }*/
        
        // Kiểm tra lý do hoàn trả
        const refundReason = document.getElementById('refundReasonInput').value;
        if (!refundReason) {
            isValid = false;
            errorMessage += 'Please select a return reason.\n';
        }
        
        // Kiểm tra ảnh
        const fileInput = document.querySelector('input[name="refund_image"]');
        if (fileInput.files.length > 0) {
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
        }
    });
    
    
    // ========== PREVIEW ẢNH TRƯỚC KHI UPLOAD ==========
    const fileInput = document.querySelector('input[name="refund_image"]');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Tạo preview (có thể tùy chỉnh UI)
                    console.log('Image selected:', file.name);
                    
                    // Hiển thị preview:
                    // const preview = document.createElement('img');
                    // preview.src = e.target.result;
                    // document.body.appendChild(preview);
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
});