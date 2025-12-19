<?php

require_once 'sign_up_model.php';
// require_once 'config/database.php'; // Đảm bảo bạn đã include kết nối DB

class SignUpController
{
    private SignUpModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new SignUpModel($db);
    }

    public function handleSignUp()
    {
        // Chỉ xử lý khi request là POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // 1. Lấy dữ liệu từ form
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $birth     = $_POST['birth'] ?? null;
        $gender    = $_POST['gender'] ?? 'Other';

        // 2. Validate cơ bản
        $errors = [];

        if (empty($firstName) || empty($lastName)) {
            $errors[] = "Full name is required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($birth)) {
             $errors[] = "Date of birth is required.";
        }

        // 3. Kiểm tra Email đã tồn tại chưa (gọi Model)
        if (empty($errors) && $this->model->isEmailTaken($email)) {
            $errors[] = "Email is already registered.";
        }

        // 4. Nếu có lỗi, trả về view kèm lỗi (hoặc JSON nếu làm API)
        if (!empty($errors)) {
            // Ví dụ trả về view:
            // $error_msg = implode('<br>', $errors);
            // include 'views/signup.php'; 
            echo json_encode(['status' => 'error', 'message' => $errors]);
            return;
        }

        // 5. Hash password (Bắt buộc bảo mật)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 6. Chuẩn bị data gửi sang Model
        $userData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $hashedPassword,
            'birth'      => $birth,
            'gender'     => $gender
        ];

        // 7. Gọi Model để đăng ký
        if ($this->model->registerUser($userData)) {
            // Đăng ký thành công -> Chuyển hướng sang Login
            header("Location: /login.php?status=success");
            exit();
        } else {
            // Lỗi Database
            echo json_encode(['status' => 'error', 'message' => 'System error. Please try again later.']);
        }
    }
}