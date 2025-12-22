document.addEventListener('DOMContentLoaded', function () {

    // --- KHAI BÁO BIẾN ---
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const passwordInput = document.getElementById('login_password');
    const togglePasswordBtn = document.getElementById('togglePassword');

    // --- 1. CHỨC NĂNG ẨN/HIỆN MẬT KHẨU ---
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M1 1l22 22"></path><path d="M12.12 7.88a3 3 0 0 1 4.24 4.24"></path><path d="M10.43 4.88c.51-.18 1.04-.28 1.57-.28 7 0 11 8 11 8a18.49 18.49 0 0 1-3.64 5.25"></path></svg>`;
            } else {
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            }
        });
    }

    // --- 2. THÊM AUTOCOMPLETE ATTRIBUTE ĐỂ FIX CONSOLE WARNING ---
    if (passwordInput) {
        passwordInput.setAttribute('autocomplete', 'current-password');
    }

    const emailInput = document.getElementById('login_input');
    if (emailInput) {
        emailInput.setAttribute('autocomplete', 'email');
    }

    // --- 3. XỬ LÝ ĐĂNG NHẬP ---
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            console.log("=== LOGIN PROCESS STARTED ===");

            // Lấy dữ liệu
            const email = document.getElementById('login_input').value.trim();
            const password = document.getElementById('login_password').value;

            console.log("Email entered:", email);
            console.log("Password entered:", password ? "***" : "(empty)");

            // Validate
            if (!email || !password) {
                alert('Please fill in both email and password');
                return;
            }

            // Disable button
            if (btnLogin) {
                btnLogin.disabled = true;
                btnLogin.textContent = 'Logging in...';
            }

            // Prepare data
            const formData = {
                email: email,
                password: password
            };

            // ĐƯỜNG DẪN CONTROLLER - THỬ NHIỀU CÁCH
            // Cách 1: Tương đối từ file hiện tại (views/website/js/)
            const controllerPath1 = '../../controllers/website/MA_LoginController.php';

            // Cách 2: Tuyệt đối từ root
            const controllerPath2 = '/Candy-Crunch-Website/controllers/website/MA_LoginController.php';

            // Cách 3: Full URL
            const controllerPath3 = window.location.origin + '/Candy-Crunch-Website/controllers/website/MA_LoginController.php';

            // Chọn cách 2 trước (tuyệt đối)
            const controllerPath = controllerPath2;
            console.log("Controller path:", controllerPath);

            // Gửi request với timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 giây timeout

            fetch(controllerPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
                signal: controller.signal
            })
                .then(response => {
                    clearTimeout(timeoutId);

                    console.log("Response status:", response.status, response.statusText);
                    console.log("Response URL:", response.url);
                    console.log("Response headers:");
                    for (let pair of response.headers.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }

                    // Kiểm tra content type
                    const contentType = response.headers.get("content-type");
                    console.log("Content-Type:", contentType);

                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error("Raw response (non-JSON):", text);
                            throw new Error("Server returned non-JSON: " + text.substring(0, 100));
                        });
                    }
                })
                .then(data => {
                    console.log("Response data (parsed):", data);

                    if (data && data.success) {
                        // Đăng nhập thành công
                        const fullname = data.data && data.data.fullname ? data.data.fullname : 'User';
                        alert(`🎉 Login successful! Welcome back, ${fullname}.`);

                        // Chuyển hướng
                        setTimeout(() => {
                            window.location.href = 'landing.php';
                        }, 500);
                    } else {
                        // Đăng nhập thất bại
                        const errorMsg = data && data.message ? data.message : 'Unknown error';
                        console.error("Login failed with message:", errorMsg);
                        alert('⛔ ' + errorMsg);

                        // Reset button
                        if (btnLogin) {
                            btnLogin.disabled = false;
                            btnLogin.textContent = 'Login';
                        }
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);

                    if (error.name === 'AbortError') {
                        console.error("Request timeout:", error);
                        alert('⛔ Request timeout. Server may be down or too slow.');
                    } else {
                        console.error("Fetch error details:", error);
                        console.error("Error name:", error.name);
                        console.error("Error message:", error.message);

                        // Kiểm tra nếu lỗi mạng
                        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                            alert('⛔ Network error. Please check your connection and try again.');
                        } else {
                            alert('⛔ Error: ' + error.message);
                        }
                    }

                    // Reset button
                    if (btnLogin) {
                        btnLogin.disabled = false;
                        btnLogin.textContent = 'Login';
                    }
                });
        });
    }

    // --- 4. THÊM NÚT TEST ĐỂ DEBUG ---
    // (Tạm thời thêm nút để test đường dẫn controller)
    const debugButton = document.createElement('button');
    debugButton.textContent = 'Debug: Test Controller Path';
    debugButton.style.position = 'fixed';
    debugButton.style.bottom = '10px';
    debugButton.style.right = '10px';
    debugButton.style.zIndex = '9999';
    debugButton.style.padding = '5px 10px';
    debugButton.style.backgroundColor = '#f0ad4e';
    debugButton.style.color = 'white';
    debugButton.style.border = 'none';
    debugButton.style.borderRadius = '3px';
    debugButton.style.cursor = 'pointer';

    debugButton.addEventListener('click', function () {
        const paths = [
            '../../controllers/website/MA_LoginController.php',
            '/Candy-Crunch-Website/controllers/website/MA_LoginController.php',
            window.location.origin + '/Candy-Crunch-Website/controllers/website/MA_LoginController.php',
            '../controllers/website/MA_LoginController.php',
            'controllers/website/MA_LoginController.php'
        ];

        console.log("=== TESTING CONTROLLER PATHS ===");
        console.log("Current URL:", window.location.href);
        console.log("Current pathname:", window.location.pathname);
        console.log("Current origin:", window.location.origin);

        paths.forEach((path, index) => {
            console.log(`\nPath ${index + 1}: ${path}`);

            // Test với HEAD request để kiểm tra tồn tại
            fetch(path, { method: 'HEAD' })
                .then(response => {
                    console.log(`✓ Path ${index + 1} exists: ${response.status} ${response.statusText}`);
                })
                .catch(error => {
                    console.log(`✗ Path ${index + 1} error: ${error.message}`);
                });
        });
    });

    // Chỉ thêm nút debug khi đang ở localhost
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        document.body.appendChild(debugButton);
    }
});