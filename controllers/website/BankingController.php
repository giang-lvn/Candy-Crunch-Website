<?php
/**
 * BankingController.php
 * Handles AJAX requests for banking account management in checkout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage banking accounts']);
    exit;
}

require_once __DIR__ . '/../../models/db.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addBanking();
        break;

    case 'select':
        selectBanking();
        break;

    case 'get_all':
        getAllBanking();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Save selected banking to session
 */
function selectBanking()
{
    $bankingId = $_POST['banking_id'] ?? '';

    if (empty($bankingId)) {
        echo json_encode(['success' => false, 'message' => 'Banking ID is required']);
        return;
    }

    $_SESSION['selected_banking'] = $bankingId;

    echo json_encode(['success' => true, 'message' => 'Banking selected successfully']);
}

/**
 * Add new banking account
 */
function addBanking()
{
    global $db;

    $customerId = $_SESSION['customer_id'] ?? null;

    if (!$customerId) {
        echo json_encode(['success' => false, 'message' => 'Customer ID not found. Please log in again.']);
        return;
    }

    $accountNumber = trim($_POST['account_number'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $bankBranch = trim($_POST['bank_branch'] ?? '');
    $holderName = trim($_POST['holder_name'] ?? '');
    $idNumber = trim($_POST['id_number'] ?? '');

    // Validation - required fields
    if (empty($accountNumber) || empty($bankName) || empty($holderName)) {
        echo json_encode(['success' => false, 'message' => 'Account Number, Bank Name, and Account Holder Name are required']);
        return;
    }

    // Validate Account Number - must be numeric
    if (!preg_match('/^[0-9]+$/', $accountNumber)) {
        echo json_encode(['success' => false, 'message' => 'Account Number must contain only numbers']);
        return;
    }

    // Validate ID Number - must be numeric if provided
    if (!empty($idNumber) && !preg_match('/^[0-9]+$/', $idNumber)) {
        echo json_encode(['success' => false, 'message' => 'ID Number must contain only numbers']);
        return;
    }

    try {
        // Generate BankingID (format: BAN001, BAN002, ...)
        $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(BankingID, 4) AS UNSIGNED)) FROM BANKING");
        $next = ((int) $stmt->fetchColumn()) + 1;
        $bankingId = 'BAN' . str_pad($next, 3, '0', STR_PAD_LEFT);

        // Check if this is the first banking account for the customer
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM BANKING WHERE CustomerID = ?");
        $checkStmt->execute([$customerId]);
        $isDefault = $checkStmt->fetchColumn() == 0 ? 'Yes' : 'No';

        // Insert new banking account
        $stmt = $db->prepare("
            INSERT INTO BANKING (
                BankingID, CustomerID, IDNumber,
                AccountNumber, AccountHolderName,
                BankName, BankBranchName, BankDefault
            ) VALUES (
                ?, ?, ?,
                ?, ?,
                ?, ?, ?
            )
        ");

        $stmt->execute([
            $bankingId,
            $customerId,
            $idNumber,
            $accountNumber,
            $holderName,
            $bankName,
            $bankBranch,
            $isDefault
        ]);

        // Prepare response data
        $newBanking = [
            'BankingID' => $bankingId,
            'CustomerID' => $customerId,
            'AccountNumber' => $accountNumber,
            'BankName' => $bankName,
            'BankBranchName' => $bankBranch,
            'AccountHolderName' => $holderName,
            'IDNumber' => $idNumber,
            'IsDefault' => $isDefault
        ];

        // Add to session
        if (!isset($_SESSION['user_banking'])) {
            $_SESSION['user_banking'] = [];
        }
        $_SESSION['user_banking'][] = $newBanking;

        echo json_encode([
            'success' => true,
            'message' => 'Banking account added successfully',
            'banking' => $newBanking
        ]);

    } catch (PDOException $e) {
        error_log('BankingController Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get all banking accounts for current user
 */
function getAllBanking()
{
    global $db;

    $customerId = $_SESSION['customer_id'] ?? null;

    if (!$customerId) {
        echo json_encode(['success' => false, 'message' => 'Customer ID not found']);
        return;
    }

    try {
        $stmt = $db->prepare("
            SELECT 
                BankingID,
                IDNumber,
                AccountNumber,
                AccountHolderName,
                BankName,
                BankBranchName,
                BankDefault AS IsDefault
            FROM BANKING 
            WHERE CustomerID = ? 
            ORDER BY BankDefault DESC, BankingID DESC
        ");
        $stmt->execute([$customerId]);
        $banking = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update session
        $_SESSION['user_banking'] = $banking;

        echo json_encode([
            'success' => true,
            'banking' => $banking
        ]);

    } catch (PDOException $e) {
        error_log('BankingController Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?>