<?php
// admin/login.php

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config và db trước
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Load auth.php để có các hàm auth
require_once __DIR__ . '/includes/auth.php';

// Kiểm tra nếu đã đăng nhập thì chuyển hướng
if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$error = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE Email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['Password'])) {
                $_SESSION['admin_id'] = $admin['AccountID'];
                $_SESSION['admin_email'] = $admin['Email'];
                $_SESSION['admin_name'] = $admin['Email']; // Có thể thay bằng tên nếu có
                
                // Cập nhật thời gian đăng nhập nếu có trường đó
                if (isset($admin['LastLogin'])) {
                    $updateStmt = $pdo->prepare("UPDATE ACCOUNT SET LastLogin = NOW() WHERE AccountID = ?");
                    $updateStmt->execute([$admin['AccountID']]);
                }
                
                header('Location: ' . BASE_URL . 'index.php');
                exit();
            } else {
                $error = 'Email hoặc mật khẩu không đúng';
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.';
            if (DEBUG) {
                $error .= ' ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .login-subtitle {
            opacity: 0.9;
            font-size: 15px;
        }
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 15px;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        .brand-logo {
            font-size: 48px;
            margin-bottom: 15px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="brand-logo">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h2 class="login-title">Admin Panel</h2>
                    <p class="login-subtitle mb-0">Đăng nhập để quản lý hệ thống</p>
                </div>
                
                <div class="login-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="mb-4">
                            <label class="form-label fw-medium">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="admin@example.com" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-medium">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="••••••••" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <button type="submit" class="btn btn-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                            </button>
                        </div>
                        
                        <div class="login-footer">
                            <p class="mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Tài khoản mặc định: <strong>admin@example.com</strong> / <strong>admin123</strong>
                            </p>
                            <p class="mb-0 text-muted">
                                © <?php echo date('Y'); ?> - Hệ thống quản trị
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.querySelector('input[name="password"]');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
    
    // Form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = this.querySelector('input[name="email"]').value.trim();
        const password = this.querySelector('input[name="password"]').value.trim();
        
        if (!email || !password) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ email và mật khẩu!');
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Email không hợp lệ!');
            return false;
        }
        
        return true;
    });
    
    // Auto focus on email field
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('input[name="email"]').focus();
    });
    </script>
</body>
</html>