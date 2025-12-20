<?php
// controllers/website/MA_LoginController.php

// Include các file cần thiết
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/MA_LoginModel.php';

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
        
        // Xác thực đăng nhập
        $account = $loginModel->authenticate($email, $password);
        
        if ($account) {
            // Đăng nhập thành công
            
            // Lấy thông tin customer
            $customer = $loginModel->getCustomerByAccountID($account['AccountID']);
            
            if ($customer) {
                // Bắt đầu session (nếu chưa có)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Lưu thông tin user vào session
                $_SESSION['user_id'] = $account['AccountID'];
                $_SESSION['customer_id'] = $customer['CustomerID'];
                $_SESSION['email'] = $account['Email'];
                $_SESSION['firstname'] = $customer['FirstName'];
                $_SESSION['lastname'] = $customer['LastName'];
                $_SESSION['fullname'] = $customer['FirstName'] . ' ' . $customer['LastName'];
                $_SESSION['logged_in'] = true;
                
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
            
        } else {
            // Đăng nhập thất bại
            // Kiểm tra xem email có tồn tại không để thông báo chi tiết
            
            // Query kiểm tra email có tồn tại không
            $checkEmailSql = "SELECT AccountID FROM ACCOUNT WHERE Email = :email";
            $stmt = $db->prepare($checkEmailSql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $emailExists = $stmt->fetch();
            
            if ($emailExists) {
                // Email tồn tại nhưng password sai
                echo json_encode([
                    'success' => false,
                    'message' => 'Incorrect password'
                ]);
            } else {
                // Email không tồn tại
                echo json_encode([
                    'success' => false,
                    'message' => 'Email not found'
                ]);
            }
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