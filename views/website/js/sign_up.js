// views/website/js/MA_signup.js

document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Lấy dữ liệu từ form
            const formData = {
                firstname: document.getElementById('firstname').value.trim(),
                lastname: document.getElementById('lastname').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value
            };

            // Validate trước khi gửi
            if (!validateForm(formData)) {
                return;
            }

            // Disable nút submit để tránh double click
            const btnSubmit = document.getElementById('btnSignup');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Processing...';

            // ĐƯỜNG DẪN TUYỆT ĐỐI - Sửa lại cho đúng với cấu trúc thư mục của bạn
            // Nếu website của bạn ở: http://localhost/Candy-Crunch-Website/
            // Thì dùng đường dẫn này:
            const controllerPath = '/Candy-Crunch-Website/controllers/website/MA_SignUpController.php';
            
            // Gửi request đến server
            fetch(controllerPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                // Kiểm tra response có phải JSON không
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new TypeError("Response is not JSON");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Đăng ký thành công
                    alert('Registration successful! Please login.');
                    // Chuyển hướng đến trang đăng nhập
                    window.location.href = 'login.php';
                } else {
                    // Đăng ký thất bại
                    alert('Error: ' + data.message);
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Sign up';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please check console for details.');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Sign up';
            });
        });
    }
});

/**
 * Validate form trước khi submit
 */
function validateForm(data) {
    // Kiểm tra first name
    if (!data.firstname) {
        alert('Please enter your first name');
        document.getElementById('firstname').focus();
        return false;
    }

    // Kiểm tra last name
    if (!data.lastname) {
        alert('Please enter your last name');
        document.getElementById('lastname').focus();
        return false;
    }

    // Kiểm tra email
    if (!data.email) {
        alert('Please enter your email');
        document.getElementById('email').focus();
        return false;
    }

    if (!isValidEmail(data.email)) {
        alert('Please enter a valid email address');
        document.getElementById('email').focus();
        return false;
    }

    // Kiểm tra password
    if (!data.password) {
        alert('Please enter your password');
        document.getElementById('password').focus();
        return false;
    }

    if (data.password.length < 6) {
        alert('Password must be at least 6 characters');
        document.getElementById('password').focus();
        return false;
    }

    // Kiểm tra confirm password
    if (!data.confirm_password) {
        alert('Please confirm your password');
        document.getElementById('confirm_password').focus();
        return false;
    }

    if (data.password !== data.confirm_password) {
        alert('Passwords do not match');
        document.getElementById('confirm_password').focus();
        return false;
    }

    return true;
}

/**
 * Kiểm tra email hợp lệ
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Toggle hiển thị password
 */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}