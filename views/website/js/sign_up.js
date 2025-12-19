document.addEventListener('DOMContentLoaded', function() {
    
    // 1. KHAI BÁO CÁC ELEMENT
    const form = document.getElementById('signupForm');
    const btnSignup = document.getElementById('btnSignup');
    const closeBtn = document.querySelector('.close-btn');

    // 2. HIỆU ỨNG RIPPLE (SÓNG NƯỚC) CHO NÚT SIGN UP
    // Tự động thêm thẻ span hiệu ứng vào vị trí click chuột
    if (btnSignup) {
        btnSignup.addEventListener('click', function(e) {
            // Tạo phần tử sóng
            let ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            // Tính toán vị trí con trỏ chuột trong nút
            let rect = btnSignup.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;
            
            // Đặt vị trí cho sóng
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            // Thêm vào nút
            this.appendChild(ripple);

            // Xóa sóng sau khi animation kết thúc (600ms khớp với CSS)
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }

    // 3. XỬ LÝ NÚT ĐÓNG (X)
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            // Hành động khi bấm nút X. 
            // Ví dụ: Quay về trang chủ hoặc ẩn form
            if(confirm("Bạn có muốn thoát trang đăng ký không?")) {
                window.location.href = "index.html"; // Chuyển hướng về trang chủ (tùy chỉnh link này)
            }
        });
    }

    // 4. XỬ LÝ SUBMIT FORM VÀ LƯU LOCAL STORAGE
    if (form) {
        form.addEventListener('submit', function(e) {
            // Ngăn chặn hành vi load lại trang mặc định của form
            e.preventDefault();

            // Lấy giá trị từ các ô input
            const firstname = document.getElementById('firstname').value.trim();
            const lastname = document.getElementById('lastname').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            // --- VALIDATION (KIỂM TRA DỮ LIỆU) ---

            // Kiểm tra độ dài mật khẩu
            if (password.length < 6) {
                alert('Mật khẩu quá ngắn! Vui lòng nhập ít nhất 6 ký tự.');
                return; // Dừng lại, không lưu
            }

            // Kiểm tra mật khẩu nhập lại
            if (password !== confirmPass) {
                alert('Mật khẩu xác nhận không khớp! Vui lòng kiểm tra lại.');
                return; // Dừng lại
            }

            // --- LƯU DỮ LIỆU (LOCAL STORAGE) ---

            // Tạo đối tượng User mới
            const newUser = {
                id: Date.now(), // Tạo ID ngẫu nhiên dựa trên thời gian
                firstname: firstname,
                lastname: lastname,
                email: email,
                username: username,
                password: password, // Lưu ý: Thực tế nên mã hóa password trước khi lưu
                createdAt: new Date().toLocaleString() // Ngày tạo
            };

            // Lấy danh sách user cũ từ Local Storage (nếu có)
            let usersList = JSON.parse(localStorage.getItem('candy_crunch_users')) || [];

            // Kiểm tra xem Username hoặc Email đã tồn tại chưa (Giả lập check trùng)
            const exists = usersList.some(u => u.username === username || u.email === email);
            if (exists) {
                alert('Tên đăng nhập hoặc Email này đã được sử dụng!');
                return;
            }

            // Thêm user mới vào danh sách
            usersList.push(newUser);

            // Lưu danh sách mới ngược lại vào Local Storage
            localStorage.setItem('candy_crunch_users', JSON.stringify(usersList));

            // --- THÔNG BÁO VÀ RESET FORM ---
            alert('🎉 Đăng ký thành công! Chào mừng ' + firstname + ' đến với Candy Crunch.');
            
            // Xóa trắng form để nhập người tiếp theo
            form.reset();
            // Chuyển hướng đến trang login để đăng nhập lại;
            window.location.href = 'login.html'; 
        });
    }
});