<?php
// ============================================================
//  PAYPAL CONTROLLER
//  Đặt file này tại: /Candy-Crunch-Website/controllers/website/paypal_controller.php
// ============================================================

require_once __DIR__ . '/../../config/paypal_config.php';
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/orders_model.php';
require_once __DIR__ . '/../../models/website/CartModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        handleCreateOrder();
        break;
    case 'capture':
        handleCaptureOrder();
        break;
    case 'cancel':
        handleCancel();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// ============================================================
//  1. TẠO PAYPAL ORDER → trả về approvalUrl để redirect
// ============================================================
function handleCreateOrder()
{
    header('Content-Type: application/json');

    // Kiểm tra đăng nhập
    if (!isset($_SESSION['AccountID'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Nhận dữ liệu POST từ checkout.js
    $input         = json_decode(file_get_contents('php://input'), true);
    $totalVND      = floatval($input['total']        ?? 0);
    $addressId     = $input['addressId']             ?? null;
    $shippingMethod = $input['shippingMethod']       ?? 'Standard';
    $shippingFee   = floatval($input['shippingFee']  ?? 0);
    $voucherCode   = $input['voucherCode']           ?? '';

    if ($totalVND <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid total amount']);
        exit;
    }

    // Quy đổi VND → USD (làm tròn 2 chữ số thập phân)
    $totalUSD = round($totalVND / VND_TO_USD_RATE, 2);
    if ($totalUSD < 0.01) $totalUSD = 0.01; // PayPal tối thiểu $0.01

    // Lấy access token
    $accessToken = getPayPalAccessToken();
    if (!$accessToken) {
        echo json_encode(['success' => false, 'message' => 'Failed to get PayPal access token']);
        exit;
    }

    // Tạo PayPal Order
    $orderPayload = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => 'candy_crunch_' . time(),
            'description'  => 'Candy Crunch Order',
            'amount'       => [
                'currency_code' => PAYPAL_CURRENCY,
                'value'         => number_format($totalUSD, 2, '.', '')
            ]
        ]],
        'application_context' => [
            'return_url' => PAYPAL_RETURN_URL,
            'cancel_url' => PAYPAL_CANCEL_URL,
            'brand_name' => 'Candy Crunch',
            'user_action' => 'PAY_NOW'
        ]
    ];

    $ch = curl_init(PAYPAL_BASE_URL . '/v2/checkout/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($orderPayload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        echo json_encode(['success' => false, 'message' => 'PayPal request failed', 'details' => $curlErr]);
        exit;
    }

    $response = json_decode($body, true);

    if ($httpCode !== 201 || empty($response['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'PayPal create order failed',
            'details' => $response
        ]);
        exit;
    }

    // Lưu dữ liệu vào session để dùng khi capture
    $_SESSION['paypal_pending'] = [
        'paypal_order_id'  => $response['id'],
        'total_vnd'        => $totalVND,
        'total_usd'        => $totalUSD,
        'address_id'       => $addressId,
        'shipping_method'  => $shippingMethod,
        'shipping_fee'     => $shippingFee,
        'voucher_code'     => $voucherCode,
    ];

    // Lấy approval URL để redirect user
    $approvalUrl = '';
    foreach ($response['links'] as $link) {
        if ($link['rel'] === 'approve') {
            $approvalUrl = $link['href'];
            break;
        }
    }

    echo json_encode([
        'success'     => true,
        'approvalUrl' => $approvalUrl,
        'paypalOrderId' => $response['id']
    ]);
    exit;
}

// ============================================================
//  2. CAPTURE PAYMENT → sau khi user approve trên PayPal
// ============================================================
function handleCaptureOrder()
{
    $ROOT = '/Candy-Crunch-Website';

    // PayPal redirect về với ?token=ORDER_ID (PayerID optional on newer flows)
    $paypalOrderId = $_GET['token'] ?? '';

    if (empty($paypalOrderId)) {
        redirectWithMessage($ROOT, 'error', 'Invalid PayPal callback parameters.');
        exit;
    }

    // Kiểm tra session
    if (!isset($_SESSION['paypal_pending'])) {
        redirectWithMessage($ROOT, 'error', 'Session expired. Please try again.');
        exit;
    }

    $pending = $_SESSION['paypal_pending'];

    // Xác minh PayPal order ID khớp
    if ($pending['paypal_order_id'] !== $paypalOrderId) {
        redirectWithMessage($ROOT, 'error', 'PayPal order mismatch.');
        exit;
    }

    // Lấy access token
    $accessToken = getPayPalAccessToken();
    if (!$accessToken) {
        redirectWithMessage($ROOT, 'error', 'Cannot connect to PayPal.');
        exit;
    }

    // Capture payment
    $ch = curl_init(PAYPAL_BASE_URL . '/v2/checkout/orders/' . $paypalOrderId . '/capture');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '{}',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        redirectWithMessage($ROOT, 'error', 'Cannot connect to PayPal.');
        exit;
    }

    $response = json_decode($body, true);

    if (($httpCode !== 201 && $httpCode !== 200) || ($response['status'] ?? '') !== 'COMPLETED') {
        error_log('PayPal capture failed: HTTP ' . $httpCode . ' - ' . json_encode($response));
        redirectWithMessage($ROOT, 'error', 'PayPal capture failed. Please contact support.');
        exit;
    }

    // Lấy capture ID (ProviderTransactionID)
    $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? '';

    // Tạo đơn hàng trong DB
    try {
        $orderId = createOrderInDatabase($pending, $captureId);
    } catch (Exception $e) {
        // Thanh toán đã thành công nhưng lưu DB lỗi → ghi log
        error_log('PayPal capture DB error: ' . $e->getMessage());
        redirectWithMessage($ROOT, 'error', 'Payment received but order creation failed. Contact support with PayPal ID: ' . $captureId);
        exit;
    }

    // Lưu session cho trang order success
    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_date'] = date('Y-m-d H:i:s');
    $_SESSION['last_payment_method'] = 'PayPal';
    $_SESSION['last_order_total'] = $pending['total_vnd'];
    $_SESSION['last_order_shipping'] = $pending['shipping_fee'];

    // Xóa session pending
    unset($_SESSION['paypal_pending']);
    unset($_SESSION['voucher_code']);

    // Redirect đến trang success
    header('Location: ' . $ROOT . '/views/website/php/ordersuccess.php?order_id=' . urlencode($orderId) . '&method=paypal');
    exit;
}

// ============================================================
//  3. USER HỦY → quay về checkout
// ============================================================
function handleCancel()
{
    $ROOT = '/Candy-Crunch-Website';
    unset($_SESSION['paypal_pending']);
    redirectWithMessage($ROOT, 'info', 'PayPal payment was cancelled.');
}

// ============================================================
//  HELPER: Lấy PayPal Access Token
// ============================================================
function getPayPalAccessToken(): string
{
    if (!function_exists('curl_init')) {
        error_log('PayPal: PHP cURL extension is not enabled');
        return '';
    }

    $ch = curl_init(PAYPAL_BASE_URL . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($body === false || $httpCode !== 200) {
        error_log('PayPal token failed: HTTP ' . $httpCode . ' curl=' . $curlErr . ' body=' . substr((string) $body, 0, 300));
        return '';
    }

    $response = json_decode($body, true);
    return $response['access_token'] ?? '';
}

// ============================================================
//  HELPER: Tạo Order + Transaction trong DB
// ============================================================
function createOrderInDatabase(array $pending, string $captureId): string
{
    global $db; // PDO từ db.php

    // Generate OrderID mới
    $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(OrderID, 4) AS UNSIGNED)) AS maxNum FROM orders");
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextNum = ($row['maxNum'] ?? 0) + 1;
    $orderId = 'ORD' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    // Lấy CustomerID từ session
    $customerId = $_SESSION['user_data']['CustomerID']
        ?? $_SESSION['customer_id']
        ?? $_SESSION['CustomerID']
        ?? null;

    if (!$customerId) {
        throw new Exception('Customer ID not found in session');
    }

    // Lấy VoucherID nếu có voucher code
    $voucherId = null;
    if (!empty($pending['voucher_code'])) {
        $vs = $db->prepare("SELECT VoucherID FROM voucher WHERE Code = ? AND VoucherStatus = 'Active' LIMIT 1");
        $vs->execute([$pending['voucher_code']]);
        $vRow = $vs->fetch(PDO::FETCH_ASSOC);
        $voucherId = $vRow['VoucherID'] ?? null;
    }

    // Insert ORDERS
    $db->prepare("
        INSERT INTO orders (OrderID, CustomerID, VoucherID, OrderDate, ShippingMethod, ShippingFee, OrderStatus)
        VALUES (?, ?, ?, NOW(), ?, ?, 'Pending Confirmation')
    ")->execute([
        $orderId,
        $customerId,
        $voucherId,
        $pending['shipping_method'],
        $pending['shipping_fee']
    ]);

    // Insert ORDER_DETAIL từ cart
    $cartModel = new CartModel();
    $cartId    = $_SESSION['cart_id'] ?? null;
    if ($cartId) {
        $cartItems = $cartModel->getCartItems($cartId);
        $stmtDetail = $db->prepare("
            INSERT INTO order_detail (OrderID, SKUID, OrderQuantity) VALUES (?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $stmtDetail->execute([$orderId, $item['SKUID'], $item['CartQuantity']]);
        }

        // Xóa cart items sau khi tạo order
        $db->prepare("DELETE FROM cart_detail WHERE CartID = ?")->execute([$cartId]);
    }

    // Generate TransactionID
    $txStmt = $db->query("SELECT MAX(CAST(SUBSTRING(TransactionID, 3) AS UNSIGNED)) AS maxNum FROM transaction");
    $txRow  = $txStmt->fetch(PDO::FETCH_ASSOC);
    $txNum  = ($txRow['maxNum'] ?? 0) + 1;
    $transactionId = 'TX' . str_pad($txNum, 6, '0', STR_PAD_LEFT);

    // Insert TRANSACTION
    $db->prepare("
        INSERT INTO transaction
            (TransactionID, OrderID, TransactionType, PaymentMethod, PaymentStatus, Amount, ProviderTransactionID, Note, CreatedAt)
        VALUES (?, ?, 'Payment', 'PayPal', 'Completed', ?, ?, 'PayPal Sandbox Payment', NOW())
    ")->execute([
        $transactionId,
        $orderId,
        $pending['total_vnd'],
        $captureId
    ]);

    return $orderId;
}

// ============================================================
//  HELPER: Redirect với flash message
// ============================================================
function redirectWithMessage(string $root, string $type, string $msg): void
{
    $_SESSION['flash_' . $type] = $msg;
    header('Location: ' . $root . '/views/website/php/checkout.php');
    exit;
}