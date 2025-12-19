document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPwForm');
    const btnSend = document.getElementById('btnSendCode');

    // Ripple Effect
    if (btnSend) {
        btnSend.addEventListener('click', function(e) {
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

    // Logic Check Email
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('forgot_email').value.trim();

            // 1. Lấy DB từ LocalStorage
            const users = JSON.parse(localStorage.getItem('candy_crunch_users')) || [];

            // 2. Tìm user
            const userExists = users.find(u => u.email === email);

            if (userExists) {
                // Lưu email đang reset để dùng cho trang sau
                localStorage.setItem('reset_email', email);
                alert('Mã xác thực đã được gửi đến email của bạn.');
                window.location.href = 'verify email.html';
            } else {
                alert('Email này chưa được đăng ký trong hệ thống!');
            }
        });
    }
});