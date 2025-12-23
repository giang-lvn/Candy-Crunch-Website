<?php
// controllers/website/MA_SignUpController.php

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/MA_AccountModel.php';
require_once __DIR__ . '/../../models/website/MA_CustomerModel.php';

class MA_SignUpController
{
    private $db;
    private $accountModel;
    private $customerModel;

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->accountModel = new MA_AccountModel($this->db);
        $this->customerModel = new MA_CustomerModel($this->db);
    }

    /**
     * Hiển thị trang đăng ký
     */
    public function showRegisterForm()
    {
        include __DIR__ . '/../../views/website/php/sign_up.php';
    }

    /**
     * Xử lý đăng ký tài khoản
     */
    public function register()
    {
        // Set header cho JSON response
        header('Content-Type: application/json');

        try {
            // Lấy dữ liệu từ POST request
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate dữ liệu đầu vào
            $validation = $this->validateInput($data);
            if (!$validation['success']) {
                echo json_encode($validation);
                return;
            }

            $email = trim($data['email']);
            $password = $data['password'];
            $firstName = trim($data['firstname']);
            $lastName = trim($data['lastname']);

            // Kiểm tra email đã tồn tại chưa
            if ($this->accountModel->checkEmailExists($email)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email already exists!'
                ]);
                return;
            }

            // Bắt đầu transaction
            $this->db->beginTransaction();

            try {
                // 1. Tạo AccountID
                $accountID = $this->accountModel->generateAccountID();
                if (!$accountID) {
                    throw new Exception('Failed to generate AccountID');
                }

                // 2. Tạo tài khoản
                $accountCreated = $this->accountModel->createAccount($accountID, $email, $password);
                if (!$accountCreated) {
                    throw new Exception('Failed to create account');
                }

                // 3. Tạo CustomerID
                $customerID = $this->customerModel->generateCustomerID();
                if (!$customerID) {
                    throw new Exception('Failed to generate CustomerID');
                }

                // 4. Tạo thông tin customer
                $customerCreated = $this->customerModel->createCustomer($customerID, $accountID, $firstName, $lastName);
                if (!$customerCreated) {
                    throw new Exception('Failed to create customer');
                }

                // Commit transaction
                $this->db->commit();

                // Đăng ký thành công
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful!',
                    'data' => [
                        'accountID' => $accountID,
                        'customerID' => $customerID
                    ]
                ]);

            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            error_log("Registration error trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Validate dữ liệu đầu vào
     */
    private function validateInput($data)
    {
        $errors = [];

        // Kiểm tra các trường bắt buộc
        if (empty($data['firstname'])) {
            $errors[] = 'First name is required';
        }

        if (empty($data['lastname'])) {
            $errors[] = 'Last name is required';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if (empty($data['confirm_password'])) {
            $errors[] = 'Confirm password is required';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode(', ', $errors)
            ];
        }

        return ['success' => true];
    }
}

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new MA_SignUpController();
    $controller->register();
} else {
    $controller = new MA_SignUpController();
    $controller->showRegisterForm();
}
?>