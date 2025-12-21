document.addEventListener('DOMContentLoaded', function () {

    // --- 1. XỬ LÝ COLLAPSE SIDEBAR (ĐÓNG/MỞ) ---
    // Lấy tất cả các header của sidebar (vùng chứa tên và mũi tên)
    const sidebarHeaders = document.querySelectorAll('.sidebar-header');

    sidebarHeaders.forEach(header => {
        header.addEventListener('click', function () {
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
        link.addEventListener('click', function (e) {
            // Xóa class active ở tất cả các link khác
            submenuLinks.forEach(l => l.classList.remove('active-link'));

            // Thêm class active cho link vừa bấm
            this.classList.add('active-link');

            // (Tùy chọn) Smooth scroll đã được xử lý bởi CSS scroll-behavior: smooth 
            // nếu bạn đặt nó trong thẻ html.
        });
    });

    // --- 3. XỬ LÝ SIDEBAR STICKY BẰNG JAVASCRIPT ---
    // CSS sticky không hoạt động vì body có overflow-x: hidden
    // Nên dùng JS để tính toán và set position cho sidebar
    const sidebar = document.querySelector('.policy-sidebar');
    const policyLayout = document.querySelector('.policy-layout');

    if (sidebar && policyLayout) {
        const topOffset = 40; // Khoảng cách từ top khi fixed
        let sidebarWidth = sidebar.offsetWidth;
        let sidebarLeft = sidebar.getBoundingClientRect().left + window.scrollX;
        let layoutTop = policyLayout.getBoundingClientRect().top + window.scrollY;
        let layoutBottom = policyLayout.getBoundingClientRect().bottom + window.scrollY;

        // Tạo placeholder để giữ chỗ cho sidebar khi nó fixed
        const placeholder = document.createElement('div');
        placeholder.style.width = sidebarWidth + 'px';
        placeholder.style.flexShrink = '0';
        placeholder.style.display = 'none';
        sidebar.parentNode.insertBefore(placeholder, sidebar);

        function updateSidebarPosition() {
            const scrollY = window.scrollY;
            const sidebarHeight = sidebar.offsetHeight;

            // Cập nhật lại các giá trị khi resize
            sidebarWidth = placeholder.style.display === 'none' ? sidebar.offsetWidth : parseInt(placeholder.style.width);
            layoutTop = policyLayout.getBoundingClientRect().top + window.scrollY;
            layoutBottom = policyLayout.getBoundingClientRect().bottom + window.scrollY;

            // Điểm bắt đầu fix sidebar
            const startFix = layoutTop - topOffset;
            // Điểm dừng fix (khi sidebar chạm đáy của layout)
            const stopFix = layoutBottom - sidebarHeight - topOffset;

            if (scrollY > startFix && scrollY < stopFix) {
                // Sidebar đang ở giữa - fixed
                sidebar.style.position = 'fixed';
                sidebar.style.top = topOffset + 'px';
                sidebar.style.left = sidebarLeft + 'px';
                sidebar.style.width = sidebarWidth + 'px';
                placeholder.style.display = 'block';
            } else if (scrollY >= stopFix) {
                // Sidebar đã chạm đáy - absolute ở cuối
                sidebar.style.position = 'absolute';
                sidebar.style.top = (stopFix - layoutTop + topOffset) + 'px';
                sidebar.style.left = '0';
                sidebar.style.width = sidebarWidth + 'px';
                placeholder.style.display = 'block';
            } else {
                // Sidebar ở vị trí bình thường
                sidebar.style.position = 'relative';
                sidebar.style.top = '';
                sidebar.style.left = '';
                sidebar.style.width = '';
                placeholder.style.display = 'none';
            }
        }

        // Cập nhật khi resize để tính lại vị trí
        function handleResize() {
            // Reset về trạng thái bình thường để tính toán lại
            sidebar.style.position = 'relative';
            sidebar.style.top = '';
            sidebar.style.left = '';
            sidebar.style.width = '';
            placeholder.style.display = 'none';

            // Tính lại giá trị
            sidebarWidth = sidebar.offsetWidth;
            sidebarLeft = sidebar.getBoundingClientRect().left + window.scrollX;
            placeholder.style.width = sidebarWidth + 'px';

            // Cập nhật lại position
            updateSidebarPosition();
        }

        // Kiểm tra nếu màn hình đủ rộng mới áp dụng sticky
        function checkScreenWidth() {
            if (window.innerWidth > 900) {
                window.addEventListener('scroll', updateSidebarPosition);
                updateSidebarPosition();
            } else {
                window.removeEventListener('scroll', updateSidebarPosition);
                sidebar.style.position = '';
                sidebar.style.top = '';
                sidebar.style.left = '';
                sidebar.style.width = '';
                placeholder.style.display = 'none';
            }
        }

        window.addEventListener('resize', () => {
            handleResize();
            checkScreenWidth();
        });

        checkScreenWidth();
    }
});