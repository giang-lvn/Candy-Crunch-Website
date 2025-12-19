document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. XỬ LÝ COLLAPSE SIDEBAR (ĐÓNG/MỞ) ---
    // Lấy tất cả các header của sidebar (vùng chứa tên và mũi tên)
    const sidebarHeaders = document.querySelectorAll('.sidebar-header');

    sidebarHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Tìm thẻ li cha (sidebar-item)
            const parentItem = this.parentElement;
            
            // Toggle class 'open'. 
            // CSS sẽ lo việc xoay mũi tên và hiện menu con nhờ class này.
            parentItem.classList.toggle('open');
        });
    });

    // --- 2. XỬ LÝ ACTIVE STATE KHI CLICK LINK CON ---
    const submenuLinks = document.querySelectorAll('.sidebar-submenu a');
    
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Xóa class active ở tất cả các link khác
            submenuLinks.forEach(l => l.classList.remove('active-link'));
            
            // Thêm class active cho link vừa bấm
            this.classList.add('active-link');
            
            // (Tùy chọn) Smooth scroll đã được xử lý bởi CSS scroll-behavior: smooth 
            // nếu bạn đặt nó trong thẻ html.
        });
    });
});