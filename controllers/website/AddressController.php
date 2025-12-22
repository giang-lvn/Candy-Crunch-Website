<?php
/**
 * AddressController.php
 * Handles AJAX requests for shipping address management in checkout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage addresses']);
    exit;
}

require_once __DIR__ . '/../../models/db.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'select':
        selectAddress();
        break;

    case 'add':
        addAddress();
        break;

    case 'get_all':
        getAllAddresses();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Save selected address to session
 */
function selectAddress()
{
    $addressId = $_POST['address_id'] ?? '';

    if (empty($addressId)) {
        echo json_encode(['success' => false, 'message' => 'Address ID is required']);
        return;
    }

    $_SESSION['selected_shipping_address'] = $addressId;

    echo json_encode(['success' => true, 'message' => 'Address selected successfully']);
}

/**
 * Add new shipping address
 */
function addAddress()
{
    global $db;

    $customerId = $_SESSION['customer_id'] ?? null;

    if (!$customerId) {
        echo json_encode(['success' => false, 'message' => 'Customer ID not found. Please log in again.']);
        return;
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $isDefault = ($_POST['is_default'] ?? '') === 'Yes' ? 'Yes' : 'No';

    // Validation - only require essential fields
    if (empty($fullname) || empty($phone) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Full name, phone, and address are required']);
        return;
    }

    try {
        // Generate AddressID (format: ADD001, ADD002, ...)
        $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(AddressID, 4) AS UNSIGNED)) FROM ADDRESS");
        $next = ((int) $stmt->fetchColumn()) + 1;
        $addressId = 'ADD' . str_pad($next, 3, '0', STR_PAD_LEFT);

        // If setting as default, update all existing addresses to non-default
        if ($isDefault === 'Yes') {
            $updateStmt = $db->prepare("UPDATE ADDRESS SET AddressDefault = 'No' WHERE CustomerID = ?");
            $updateStmt->execute([$customerId]);
        }

        // Insert new address into ADDRESS table
        $stmt = $db->prepare("
            INSERT INTO ADDRESS (AddressID, CustomerID, Fullname, Phone, Alias, Address, CityState, Country, PostalCode, AddressDefault) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$addressId, $customerId, $fullname, $phone, '', $address, $city, $country, $postalCode, $isDefault]);

        // Prepare response data
        $newAddress = [
            'AddressID' => $addressId,
            'CustomerID' => $customerId,
            'Fullname' => $fullname,
            'Phone' => $phone,
            'Address' => $address,
            'City' => $city,
            'Country' => $country,
            'Postal' => $postalCode,
            'IsDefault' => $isDefault
        ];

        // Add to session
        if (!isset($_SESSION['user_addresses'])) {
            $_SESSION['user_addresses'] = [];
        }

        // If this is default, update other addresses in session
        if ($isDefault === 'Yes') {
            foreach ($_SESSION['user_addresses'] as &$addr) {
                $addr['IsDefault'] = 'No';
            }
        }

        $_SESSION['user_addresses'][] = $newAddress;

        // Auto-select new address if it's the first one or default
        if (count($_SESSION['user_addresses']) === 1 || $isDefault === 'Yes') {
            $_SESSION['selected_shipping_address'] = $addressId;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $newAddress
        ]);

    } catch (PDOException $e) {
        error_log('AddressController Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get all addresses for current user
 */
function getAllAddresses()
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
                AddressID,
                Fullname,
                Phone,
                Address,
                CityState AS City,
                Country,
                PostalCode AS Postal,
                AddressDefault AS IsDefault
            FROM ADDRESS 
            WHERE CustomerID = ? 
            ORDER BY AddressDefault DESC, AddressID DESC
        ");
        $stmt->execute([$customerId]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update session
        $_SESSION['user_addresses'] = $addresses;

        echo json_encode([
            'success' => true,
            'addresses' => $addresses
        ]);

    } catch (PDOException $e) {
        error_log('AddressController Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?>