document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newPwForm');
    const btnReset = document.getElementById('btnReset');

    // Ripple
    if (btnReset) {
        btnReset.addEventListener('click', function(e) {
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

    // Logic Đổi Mật Khẩu
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_new_password').value;

            // 1. Validate
            if (newPass !== confirmPass) {
                alert('Mật khẩu nhập lại không khớp!');
                return;
            }
            if (newPass.length < 6) {
                alert('Mật khẩu phải dài hơn 6 ký tự!');
                return;
            }

            // 2. Lấy email user đang reset (được lưu từ trang Forgot Password)
            const resetEmail = localStorage.getItem('reset_email');
            if (!resetEmail) {
                alert('Lỗi phiên làm việc! Vui lòng quay lại trang Quên mật khẩu.');
                window.location.href = 'forgot password.html';
                return;
            }

            // 3. Cập nhật trong Local Storage
            let users = JSON.parse(localStorage.getItem('candy_crunch_users')) || [];
            // Tìm index của user đó
            const userIndex = users.findIndex(u => u.email === resetEmail);

            if (userIndex !== -1) {
                // Kiểm tra trùng mật khẩu cũ
                if (users[userIndex].password === newPass) {
                    alert('Mật khẩu mới không được trùng với mật khẩu cũ!');
                    return;
                }

                // Cập nhật mật khẩu mới
                users[userIndex].password = newPass;
                localStorage.setItem('candy_crunch_users', JSON.stringify(users));
                
                // Xóa email tạm
                localStorage.removeItem('reset_email');

                alert('Đổi mật khẩu thành công! Vui lòng đăng nhập lại.');
                window.location.href = 'login.html';
            } else {
                alert('Có lỗi xảy ra, không tìm thấy tài khoản!');
            }
        });
    }
});