document.addEventListener('DOMContentLoaded', function() {
    
    // --- KHAI BÁO BIẾN ---
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const passwordInput = document.getElementById('login_password');
    const togglePasswordBtn = document.getElementById('togglePassword');

    // --- 1. CHỨC NĂNG ẨN/HIỆN MẬT KHẨU (EYE TOGGLE) ---
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            // Kiểm tra trạng thái hiện tại
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Đổi icon (Mắt mở <-> Mắt gạch chéo)
            if (type === 'text') {
                // Đổi sang icon Mắt gạch chéo (Eye Off)
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M1 1l22 22"></path><path d="M12.12 7.88a3 3 0 0 1 4.24 4.24"></path><path d="M10.43 4.88c.51-.18 1.04-.28 1.57-.28 7 0 11 8 11 8a18.49 18.49 0 0 1-3.64 5.25"></path></svg>`;
            } else {
                // Đổi về icon Mắt mở (Eye)
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            }
        });
    }

    // --- 2. HIỆU ỨNG RIPPLE (Sóng nước) ---
    if (btnLogin) {
        btnLogin.addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            let rect = this.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            this.appendChild(ripple);
            setTimeout(() => { ripple.remove(); }, 600);
        });
    }

    // --- 3. XỬ LÝ ĐĂNG NHẬP (CHECK LOCAL STORAGE) ---
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Ngăn load lại trang

            // Lấy dữ liệu người dùng nhập
            const emailInput = document.getElementById('login_input').value.trim();
            const passwordInputVal = passwordInput.value;

            // Lấy dữ liệu từ Local Storage (Database giả lập)
            // Lưu ý: Key phải giống hệt lúc bạn lưu ở trang Sign up ('candy_crunch_users')
            const usersDB = JSON.parse(localStorage.getItem('candy_crunch_users')) || [];

            // Tìm kiếm user khớp cả Email và Password
            // (Ở đây giả sử login_input là Email, nếu bạn muốn login bằng Username thì sửa điều kiện u.email thành u.username)
            const user = usersDB.find(u => u.email === emailInput && u.password === passwordInputVal);

            if (user) {
                // --- THÀNH CÔNG ---
                // (Tùy chọn) Lưu thông tin phiên đăng nhập
                localStorage.setItem('currentUser', JSON.stringify(user));

                alert(`🎉 Đăng nhập thành công! Chào mừng trở lại, ${user.firstname} ${user.lastname}.`);
                
                // Chuyển hướng về trang chủ
                window.location.href = 'index.html'; 
            } else {
                // --- THẤT BẠI ---
                alert('⛔ Email hoặc mật khẩu không chính xác. Vui lòng thử lại!');
            }
        });
    }
});