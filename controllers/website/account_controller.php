<?php
// Ngăn chặn việc in lỗi ra màn hình làm hỏng chuỗi JSON khi gọi AJAX
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/account_model.php';

class AccountController
{
    private AccountModel $model;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['AccountID'] = $_SESSION['AccountID'] ?? 'ACC001';
        $this->model = new AccountModel($db);
    }

    public function index()
    {
        if (!isset($_SESSION['AccountID'])) {
            header('Location: ../../views/website/php/login.html');
            exit;
        }

        $accountId = $_SESSION['AccountID'];
        $customer = $this->model->getCustomerByAccountId($accountId);
        if (!$customer) die('Customer not found');

        $customerId = $customer['CustomerID'];
        $_SESSION['user_data']      = $customer;
        $_SESSION['user_addresses'] = $this->model->getAddresses($customerId);
        $_SESSION['user_banking']   = $this->model->getBankingInfo($customerId);

        if (ob_get_length()) ob_end_clean();
        header('Location: /Candy-Crunch-Website/views/website/php/my_account.php');
        exit;
    }

    private function sendJSON($data) {
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // ✅ FIX: Thêm xử lý email
    public function updateProfile()
    {
        // 🔍 DEBUG
        error_log("🔵 updateProfile() called");
        error_log("📦 POST data: " . print_r($_POST, true));
        error_log("🔑 Session AccountID: " . ($_SESSION['AccountID'] ?? 'NOT SET'));
        
        if (!isset($_SESSION['AccountID'])) {
            error_log("❌ No AccountID in session");
            $this->sendJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
        error_log("👤 Customer found: " . print_r($customer, true));
        
        if (!$customer) {
            error_log("❌ Customer not found");
            $this->sendJSON(['success'=>false,'message'=>'Customer not found']);
        }
        
        // ✅ Thêm email vào data
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'birth'      => $_POST['birth'] ?? null,
            'gender'     => $_POST['gender'] ?? 'Other',
            'email'      => $_POST['email'] ?? ''
        ];

        error_log("📝 Data to update: " . print_r($data, true));

        // ✅ Gọi hàm update đã fix
        $success = $this->model->updateCustomerProfile(
            $_SESSION['AccountID'], 
            $customer['CustomerID'], 
            $data
        );

        error_log("✅ Update result: " . ($success ? 'SUCCESS' : 'FAILED'));

        if ($success) {
            // ✅ Lấy lại customer data mới từ DB
            $updatedCustomer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
            
            $_SESSION['user_data'] = $updatedCustomer;
            error_log("✅ Session updated with fresh data");
            
            // ✅ Trả về data mới cho JavaScript
            $this->sendJSON([
                'success' => true,
                'data' => [
                    'firstName' => $updatedCustomer['FirstName'],
                    'lastName' => $updatedCustomer['LastName'],
                    'email' => $updatedCustomer['Email'],
                    'dob' => $updatedCustomer['CustomerBirth'],
                    'gender' => $updatedCustomer['CustomerGender']
                ]
            ]);
        }
        
        $this->sendJSON(['success'=>false, 'message'=>'Update failed']);
    }

    public function addBanking() { $this->saveBanking('add'); }
    public function editBanking() { $this->saveBanking('edit'); }

    private function saveBanking(string $mode)
    {
        if (!isset($_SESSION['AccountID'])) {
            $this->sendJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
        $data = [
            'account_number' => $_POST['account_number'] ?? '',
            'holder_name'    => $_POST['holder_name'] ?? '',
            'bank_name'      => $_POST['bank_name'] ?? '',
            'branch_name'    => $_POST['bank_branch'] ?? '',
            'id_number'      => $_POST['id_number'] ?? '',
            'is_default'     => $_POST['is_default'] ?? 'No'
        ];

        if ($mode === 'edit') {
            $data['banking_id'] = $_POST['banking_id'] ?? '';
            if (empty($data['banking_id'])) {
                $this->sendJSON(['success' => false, 'message' => 'ID required']);
            }
            $success = $this->model->updateBanking($customer['CustomerID'], $data);
        } else {
            $success = $this->model->addBanking($customer['CustomerID'], $data);
        }
        if ($success){
            $newBankingList = $this->model->getBankingInfo($customer['CustomerID']);
            $_SESSION['user_banking'] = $newBankingList; // Cập nhật Session
            $this->sendJSON([
                'success' => true,
                'banking' => $newBankingList
            ]);
        }

        $this->sendJSON(['success' => false, 'message' => 'Database error']);
    }

    public function deleteBanking()
    {
        $bankingId = $_POST['banking_id'] ?? '';
        if (!$bankingId) {
            $this->sendJSON(['success' => false, 'message' => 'ID required']);
        }

        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
        $success  = $this->model->deleteBanking($customer['CustomerID'], $bankingId);
        if($success){
            $newBankingList = $this->model->getBankingInfo($customer['CustomerID']);
            $_SESSION['user_banking'] = $newBankingList;
            $this->sendJSON([
                'success' => true,
                'banking' => $newBankingList
            ]);
        }
        $this->sendJSON(['success' => false, 'message' => 'Delete failed']);
    }

    public function addAddress() {
        $this->saveAddress('add');
    }
    
    public function updateAddress() {
        $this->saveAddress('edit');
    }
    
    public function deleteAddress() {
        if (!isset($_SESSION['AccountID'])) {
            $this->sendJSON(['success'=>false,'message'=>'Unauthorized']);
        }
        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
        $addressId = $_POST['address_id'] ?? '';
        if (!$addressId) $this->sendJSON(['success'=>false,'message'=>'ID required']);
        $success = $this->model->deleteAddress($customer['CustomerID'], $addressId);
        if($success){
            $newAddresses = $this->model->getAddresses($customer['CustomerID']);
            $_SESSION['user_addresses'] = $newAddresses;
            $this->sendJSON([
                'success' => true,
                'addresses' => $newAddresses
            ]);
        }
        $this->sendJSON(['success'=>false]);
    }

    /**
     * Đăng xuất - Hủy session phía server
     * Không xóa dữ liệu trong database
     */
    public function logout()
    {
        // Xóa tất cả session data
        $_SESSION = [];
        
        // Xóa session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Hủy session
        session_destroy();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    
    private function saveAddress($mode) {
        if (!isset($_SESSION['AccountID'])) $this->sendJSON(['success'=>false,'message'=>'Unauthorized']);
        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);
        
        $data = [
            'fullname' => $_POST['fullname'] ?? '',
            'phone'    => $_POST['phone'] ?? '',
            'address'  => $_POST['address'] ?? '',
            'city'     => $_POST['city'] ?? '',
            'country'  => $_POST['country'] ?? ''
        ];
    
        if ($mode === 'edit') {
            $data['address_id'] = $_POST['address_id'] ?? '';
            if (!$data['address_id']) $this->sendJSON(['success'=>false,'message'=>'ID required']);
            $success = $this->model->updateAddress($customer['CustomerID'], $data);
        } else {
            // ✅ Model sẽ tự tạo address_id
            $success = $this->model->addAddress($customer['CustomerID'], $data);
        }
        if($success) {
            $newAddresses = $this->model->getAddresses($customer['CustomerID']);
            $_SESSION['user_addresses'] = $newAddresses;
            $this->sendJSON([
                'success' => true,
                'addresses' => $newAddresses
            ]);
        }
        $this->sendJSON(['success'=>false]);
    }
}

// ROUTER
$controller = new AccountController($db);
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'updateProfile': $controller->updateProfile(); break;
        case 'addBanking':    $controller->addBanking(); break;
        case 'updateBanking': $controller->editBanking(); break;
        case 'editBanking':   $controller->editBanking(); break;
        case 'deleteBanking': $controller->deleteBanking(); break;
        case 'addAddress':     $controller->addAddress(); break;
        case 'updateAddress':  $controller->updateAddress(); break;
        case 'deleteAddress':  $controller->deleteAddress(); break;
        case 'logout':         $controller->logout(); break;
        default:
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
} else {
    $controller->index();
}