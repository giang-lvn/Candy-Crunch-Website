<?php
// controllers/website/MA_LoginController.php

// Include các file cần thiết
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/MA_LoginModel.php';
require_once __DIR__ . '/../../models/website/account_model.php';

// Chỉ xử lý POST request cho login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Set header cho JSON response
    header('Content-Type: application/json');
    
    try {
        // Lấy dữ liệu từ POST request (JSON format từ login.js)
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        // Kiểm tra dữ liệu có hợp lệ không
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request data'
            ]);
            exit();
        }
        
        $email = trim($data['email']);
        $password = $data['password'];
        
        // Validate cơ bản
        if (empty($email) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
            exit();
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email format'
            ]);
            exit();
        }
        
        // Kết nối database và xác thực
        global $db;
        $loginModel = new MA_LoginModel($db);
        
        // Xác thực đăng nhập - Nhận response với status
        $authResult = $loginModel->authenticate($email, $password);
        
        // Xử lý theo status
        switch ($authResult['status']) {
            case 'success':
                // Đăng nhập thành công
                $account = $authResult['account'];
                $customer = $loginModel->getCustomerByAccountID($account['AccountID']);
                
                if ($customer) {
                    // Bắt đầu session (nếu chưa có)
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Lưu thông tin user vào session
                    $_SESSION['user_id'] = $account['AccountID'];
                    $_SESSION['AccountID'] = $account['AccountID']; // Thêm để khớp với account_controller
                    $_SESSION['customer_id'] = $customer['CustomerID'];
                    $_SESSION['CustomerID'] = $customer['CustomerID']; // Thêm để dễ truy cập
                    $_SESSION['email'] = $account['Email'];
                    $_SESSION['firstname'] = $customer['FirstName'];
                    $_SESSION['lastname'] = $customer['LastName'];
                    $_SESSION['fullname'] = $customer['FirstName'] . ' ' . $customer['LastName'];
                    $_SESSION['logged_in'] = true;
                    
                    // ✅ Load đầy đủ thông tin customer và lưu vào session (để my_account.php hiển thị đúng)
                    $accountModel = new AccountModel($db);
                    $fullCustomerData = $accountModel->getCustomerByAccountId($account['AccountID']);
                    if ($fullCustomerData) {
                        $_SESSION['user_data'] = $fullCustomerData;
                        $_SESSION['user_addresses'] = $accountModel->getAddresses($customer['CustomerID']);
                        $_SESSION['user_banking'] = $accountModel->getBankingInfo($customer['CustomerID']);
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful!',
                        'data' => [
                            'accountID' => $account['AccountID'],
                            'customerID' => $customer['CustomerID'],
                            'fullname' => $customer['FirstName'] . ' ' . $customer['LastName']
                        ]
                    ]);
                } else {
                    // Tìm thấy account nhưng không tìm thấy customer (trường hợp hiếm)
                    echo json_encode([
                        'success' => false,
                        'message' => 'Account error. Please contact support.'
                    ]);
                }
                break;
                
            case 'inactive':
                // Tài khoản không hoạt động
                echo json_encode([
                    'success' => false,
                    'status' => 'inactive',
                    'message' => 'Your account is currently inactive. Please contact support to reactivate your account.'
                ]);
                break;
                
            case 'banned':
                // Tài khoản bị cấm
                echo json_encode([
                    'success' => false,
                    'status' => 'banned',
                    'message' => 'Your account is banned. For more information, access this link: https://youtu.be/dQw4w9WgXcQ?si=7XVIiSLCkvk3Ap7u'
                ]);
                break;
                
            case 'not_found':
                // Email không tồn tại
                echo json_encode([
                    'success' => false,
                    'message' => 'Email not found'
                ]);
                break;
                
            case 'wrong_password':
                // Password sai
                echo json_encode([
                    'success' => false,
                    'message' => 'Incorrect password'
                ]);
                break;
                
            case 'error':
            default:
                // Lỗi hệ thống
                echo json_encode([
                    'success' => false,
                    'message' => 'An error occurred. Please try again.'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        // Xử lý lỗi
        error_log("Login controller error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ]);
    }
    
} else {
    // Nếu không phải POST request, trả về lỗi
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>