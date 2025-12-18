document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. XỬ LÝ COLLAPSE (ĐÓNG/MỞ SIDEBAR) ---
    const sidebarHeaders = document.querySelectorAll('.sidebar-header');

    sidebarHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const parentItem = this.parentElement;
            
            // Toggle class 'open' để kích hoạt CSS animation
            if (parentItem.classList.contains('open')) {
                parentItem.classList.remove('open');
            } else {
                // (Tùy chọn) Nếu muốn bấm cái này đóng cái kia thì bỏ comment dòng dưới:
                // document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('open'));
                
                parentItem.classList.add('open');
            }
        });
    });

    // --- 2. XỬ LÝ ACTIVE STATE KHI CLICK LINK CON ---
    const submenuLinks = document.querySelectorAll('.sidebar-submenu a');
    
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Xóa active cũ
            submenuLinks.forEach(l => l.classList.remove('active-link'));
            // Thêm active cho cái vừa bấm
            this.classList.add('active-link');
        });
    });
});