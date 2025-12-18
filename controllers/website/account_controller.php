<?php
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

        // TEST – khi làm login xong thì XÓA dòng này
        $_SESSION['AccountID'] = $_SESSION['AccountID'] ?? 'ACC001';

        $this->model = new AccountModel($db);
    }

    /* ===============================
       RENDER MY ACCOUNT PAGE
       =============================== */
    public function index()
    {
        if (!isset($_SESSION['AccountID'])) {
            header('Location: ../../views/website/php/login.html');
            exit;
        }

        $accountId = $_SESSION['AccountID'];

        $customer = $this->model->getCustomerByAccountId($accountId);
        if (!$customer) {
            die('Customer not found');
        }

        $customerId = $customer['CustomerID'];

        $addresses   = $this->model->getAddresses($customerId);
        $bankingList = $this->model->getBankingInfo($customerId);
        $banking     = !empty($bankingList) ? $bankingList[0] : [];
        $_SESSION['user_data']      = $customer;
        $_SESSION['user_addresses'] = $addresses;
        $_SESSION['user_banking']   = $banking;

        // ⚠️ QUAN TRỌNG: include view TẠI ĐÂY
        header('Location: ../../views/website/php/my_account.php');
        exit;
    }

    /* ===============================
       API: UPDATE PROFILE
       =============================== */
    public function updateProfile()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['AccountID'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $customer = $this->model->getCustomerByAccountId($_SESSION['AccountID']);

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'birth'      => $_POST['birth'] ?? '',
            'gender'     => $_POST['gender'] ?? 'Other'
        ];

        $success = $this->model->updateCustomerProfile(
            $customer['CustomerID'],
            $data
        );

        echo json_encode(['success' => $success]);
        exit;
    }

    /* ===============================
       API: ADDRESS
       =============================== */
    public function addAddress()
    {
        header('Content-Type: application/json');

        $data = [
            'address_id' => uniqid('ADDR'),
            'fullname'   => $_POST['fullname'],
            'phone'      => $_POST['phone'],
            'alias'      => $_POST['alias'],
            'address'    => $_POST['address'],
            'city'       => $_POST['city'],
            'country'    => $_POST['country'],
            'postal'     => $_POST['postal'],
            'is_default' => $_POST['is_default'] ?? 'No'
        ];

        $success = $this->model->addAddress($_POST['customer_id'], $data);
        echo json_encode(['success' => $success]);
        exit;
    }
}

/* ===============================
   ROUTER NHỎ TRONG FILE
   =============================== */
$controller = new AccountController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'updateProfile':
            $controller->updateProfile();
            break;
        case 'addAddress':
            $controller->addAddress();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// ⬅️ CHỈ CHỖ NÀY render view
$controller = new AccountController($db);
$controller->index();
