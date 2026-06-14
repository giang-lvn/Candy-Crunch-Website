<?php
// ============================================================
//  MOMO CONTROLLER
//  File: /Candy-Crunch-Website/controllers/website/MomoController.php
//  Handles MoMo Sandbox payment flow:
//    ?action=create  → AJAX: call MoMo API, return payUrl
//    ?action=return  → Browser redirect back from MoMo (user-facing)
//    ?action=ipn     → Server-to-server callback from MoMo (no session)
//    ?action=cancel  → User cancelled on MoMo page
// ============================================================

require_once __DIR__ . '/../../config/momo_config.php';
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/CartModel.php';
require_once __DIR__ . '/../../services/OrderMailService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create': handleCreate(); break;
    case 'return': handleReturn(); break;
    case 'ipn':    handleIpn();    break;
    case 'cancel': handleCancel(); break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// ============================================================
//  1. TẠO MOMO ORDER → trả payUrl để JS redirect
// ============================================================
function handleCreate()
{
    header('Content-Type: application/json');

    if (!isset($_SESSION['AccountID'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $input          = json_decode(file_get_contents('php://input'), true);
    $totalVND       = (int)round(floatval($input['total'] ?? 0));
    $addressId      = substr(trim($input['addressId'] ?? ''), 0, 20);
    $shippingMethod = $input['shippingMethod'] ?? 'Standard';
    $shippingFee    = (int)($input['shippingFee'] ?? 0);
    $voucherCode    = substr(trim($input['voucherCode'] ?? ''), 0, 50);

    if ($totalVND <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }

    $customerId = $_SESSION['customer_id'] ?? $_SESSION['CustomerID'] ?? null;
    $cartId     = $_SESSION['cart_id'] ?? null;

    if (!$customerId || !$cartId) {
        echo json_encode(['success' => false, 'message' => 'Customer or cart not found']);
        exit;
    }

    // Encode pending data into extraData — MoMo echoes it back in IPN (no session available there)
    $extraData = base64_encode(json_encode([
        'customer_id'     => $customerId,
        'cart_id'         => $cartId,
        'address_id'      => $addressId,
        'shipping_method' => $shippingMethod,
        'shipping_fee'    => $shippingFee,
        'voucher_code'    => $voucherCode,
        'total_vnd'       => $totalVND,
    ]));

    $momoOrderId = 'CC' . time() . rand(1000, 9999);
    $requestId   = $momoOrderId;
    $orderInfo   = 'Thanh toan don hang Candy Crunch';

    // Signature: keys must be in strict alphabetical order
    $rawSignature = 'accessKey='   . MOMO_ACCESS_KEY
        . '&amount='       . $totalVND
        . '&extraData='    . $extraData
        . '&ipnUrl='       . MOMO_IPN_URL
        . '&orderId='      . $momoOrderId
        . '&orderInfo='    . $orderInfo
        . '&partnerCode='  . MOMO_PARTNER_CODE
        . '&redirectUrl='  . MOMO_REDIRECT_URL
        . '&requestId='    . $requestId
        . '&requestType='  . MOMO_REQUEST_TYPE;

    $signature = hash_hmac('sha256', $rawSignature, MOMO_SECRET_KEY);

    $body = [
        'partnerCode' => MOMO_PARTNER_CODE,
        'accessKey'   => MOMO_ACCESS_KEY,
        'requestId'   => $requestId,
        'amount'      => $totalVND,
        'orderId'     => $momoOrderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => MOMO_REDIRECT_URL,
        'ipnUrl'      => MOMO_IPN_URL,
        'extraData'   => $extraData,
        'requestType' => MOMO_REQUEST_TYPE,
        'signature'   => $signature,
        'lang'        => 'vi',
    ];

    $ch = curl_init(MOMO_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $result   = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        error_log('MoMo cURL error: ' . $curlErr);
        echo json_encode(['success' => false, 'message' => 'Không thể kết nối đến MoMo']);
        exit;
    }

    $response = json_decode($result, true);

    if (($response['resultCode'] ?? -1) !== 0) {
        error_log('MoMo create failed: ' . json_encode($response));
        echo json_encode([
            'success' => false,
            'message' => $response['message'] ?? 'Lỗi từ MoMo',
        ]);
        exit;
    }

    // Store pending in session — used in return action (same browser session)
    $_SESSION['momo_pending'] = [
        'momo_order_id'   => $momoOrderId,
        'total_vnd'       => $totalVND,
        'address_id'      => $addressId,
        'shipping_method' => $shippingMethod,
        'shipping_fee'    => $shippingFee,
        'voucher_code'    => $voucherCode,
        'extra_data'      => $extraData,
    ];

    echo json_encode(['success' => true, 'payUrl' => $response['payUrl']]);
    exit;
}

// ============================================================
//  2. RETURN — browser redirect back from MoMo (has user session)
//     This is the primary order-creation handler (also works when
//     IPN cannot reach localhost in sandbox environments).
// ============================================================
function handleReturn()
{
    $ROOT = '/Candy-Crunch-Website';

    $resultCode  = (int)($_GET['resultCode'] ?? -1);
    $momoOrderId = $_GET['orderId']  ?? '';
    $transId     = (string)($_GET['transId'] ?? '');

    // Payment failed or cancelled
    if ($resultCode !== 0) {
        $msg = htmlspecialchars($_GET['message'] ?? 'Thanh toán thất bại hoặc bị hủy.');
        unset($_SESSION['momo_pending']);
        $_SESSION['flash_error'] = $msg;
        header('Location: ' . $ROOT . '/views/website/php/checkout.php');
        exit;
    }

    if (!isset($_SESSION['momo_pending'])) {
        $_SESSION['flash_error'] = 'Phiên đã hết hạn. Vui lòng kiểm tra lịch sử đơn hàng.';
        header('Location: ' . $ROOT . '/views/website/php/checkout.php');
        exit;
    }

    $pending = $_SESSION['momo_pending'];

    // Idempotency: IPN may have already created the order
    global $db;
    $orderId = null;

    if ($transId !== '') {
        $stmt = $db->prepare("
            SELECT o.OrderID FROM transaction t
            JOIN orders o ON t.OrderID = o.OrderID
            WHERE t.ProviderTransactionID = ?
            LIMIT 1
        ");
        $stmt->execute([$transId]);
        $row     = $stmt->fetch(PDO::FETCH_ASSOC);
        $orderId = $row['OrderID'] ?? null;
    }

    // Create order if IPN hasn't done it yet
    if (!$orderId) {
        $pendingData = [
            'customer_id'     => $_SESSION['customer_id'] ?? null,
            'cart_id'         => $_SESSION['cart_id'] ?? null,
            'address_id'      => $pending['address_id'],
            'shipping_method' => $pending['shipping_method'],
            'shipping_fee'    => $pending['shipping_fee'],
            'voucher_code'    => $pending['voucher_code'],
            'total_vnd'       => $pending['total_vnd'],
        ];

        try {
            $orderId = createMomoOrder($pendingData, $transId ?: $momoOrderId);
        } catch (Exception $e) {
            error_log('MoMo return - order creation error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Thanh toán thành công nhưng tạo đơn hàng thất bại. Liên hệ hỗ trợ với mã: ' . $transId;
            header('Location: ' . $ROOT . '/views/website/php/checkout.php');
            exit;
        }
    }

    // Send confirmation email (return action has user session — safe to send here)
    $customerId = $_SESSION['customer_id'] ?? null;
    if ($customerId && $orderId) {
        OrderMailService::sendOrderConfirmation($db, $orderId, $customerId);
    }

    // Set session vars for ordersuccess page
    $_SESSION['last_order_id']       = $orderId;
    $_SESSION['last_order_date']     = date('Y-m-d H:i:s');
    $_SESSION['last_payment_method'] = 'MoMo';
    $_SESSION['last_order_total']    = $pending['total_vnd'];
    $_SESSION['last_order_shipping'] = $pending['shipping_fee'];

    unset($_SESSION['momo_pending'], $_SESSION['voucher_code']);

    header('Location: ' . $ROOT . '/views/website/php/ordersuccess.php?order_id=' . urlencode($orderId) . '&method=momo');
    exit;
}

// ============================================================
//  3. IPN — server-to-server callback from MoMo (NO user session)
//     Creates order if return action hasn't done so yet.
//     Must always respond HTTP 200 after processing.
// ============================================================
function handleIpn()
{
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['message' => 'invalid body']);
        exit;
    }

    // Verify HMAC-SHA256 signature before any processing
    if (!verifyMomoSignature($data)) {
        error_log('MoMo IPN - invalid signature. raw=' . $raw);
        http_response_code(400);
        echo json_encode(['message' => 'invalid signature']);
        exit;
    }

    $resultCode = (int)($data['resultCode'] ?? -1);
    $transId    = (string)($data['transId'] ?? '');

    if ($resultCode !== 0) {
        error_log('MoMo IPN - payment failed: resultCode=' . $resultCode . ' msg=' . ($data['message'] ?? ''));
        http_response_code(200);
        echo json_encode(['message' => 'payment failed']);
        exit;
    }

    // Idempotency guard (MoMo may retry IPN multiple times)
    global $db;
    $stmt = $db->prepare("SELECT TransactionID FROM transaction WHERE ProviderTransactionID = ? LIMIT 1");
    $stmt->execute([$transId]);
    if ($stmt->fetch()) {
        http_response_code(200);
        echo json_encode(['message' => 'already processed']);
        exit;
    }

    // Decode pending data from extraData (session is unavailable here)
    $pendingData = json_decode(base64_decode($data['extraData'] ?? ''), true);
    if (!$pendingData) {
        error_log('MoMo IPN - cannot decode extraData: ' . ($data['extraData'] ?? ''));
        http_response_code(400);
        echo json_encode(['message' => 'invalid extraData']);
        exit;
    }

    try {
        createMomoOrder($pendingData, $transId);
    } catch (Exception $e) {
        error_log('MoMo IPN - DB error: ' . $e->getMessage() . ' | raw=' . $raw);
        http_response_code(500);
        echo json_encode(['message' => 'db error']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['message' => 'success']);
    exit;
}

// ============================================================
//  4. CANCEL — user clicked "cancel" on MoMo page
// ============================================================
function handleCancel()
{
    $ROOT = '/Candy-Crunch-Website';
    unset($_SESSION['momo_pending']);
    $_SESSION['flash_info'] = 'Đã hủy thanh toán MoMo.';
    header('Location: ' . $ROOT . '/views/website/php/checkout.php');
    exit;
}

// ============================================================
//  HELPER: Create order + transaction in DB (used by both
//  return and ipn actions — includes idempotency at DB level)
// ============================================================
function createMomoOrder(array $pending, string $transId): string
{
    global $db;

    $customerId     = $pending['customer_id'] ?? null;
    $cartId         = $pending['cart_id'] ?? null;
    $shippingMethod = $pending['shipping_method'] ?? 'Standard';
    $shippingFee    = (int)($pending['shipping_fee'] ?? 0);
    $voucherCode    = $pending['voucher_code'] ?? '';
    $totalVND       = (int)($pending['total_vnd'] ?? 0);

    if (!$customerId) throw new Exception('customer_id missing in pending data');
    if (!$cartId)     throw new Exception('cart_id missing in pending data');

    $db->beginTransaction();

    try {
        // Generate OrderID
        $stmt    = $db->query("SELECT MAX(CAST(SUBSTRING(OrderID, 4) AS UNSIGNED)) AS maxNum FROM orders");
        $row     = $stmt->fetch(PDO::FETCH_ASSOC);
        $orderId = 'ORD' . str_pad(($row['maxNum'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        // Resolve VoucherID
        $voucherId = null;
        if (!empty($voucherCode)) {
            $vs = $db->prepare("SELECT VoucherID FROM voucher WHERE Code = ? AND VoucherStatus = 'Active' LIMIT 1");
            $vs->execute([$voucherCode]);
            $voucherId = $vs->fetchColumn() ?: null;
        }

        // Insert ORDERS
        $db->prepare("
            INSERT INTO orders (OrderID, CustomerID, VoucherID, OrderDate, ShippingMethod, ShippingFee, OrderStatus)
            VALUES (?, ?, ?, NOW(), ?, ?, 'Pending Confirmation')
        ")->execute([$orderId, $customerId, $voucherId, $shippingMethod, $shippingFee]);

        // Get cart items
        $cartModel = new CartModel();
        $cartItems = $cartModel->getCartItems($cartId);

        if (empty($cartItems)) {
            throw new Exception('Cart is empty — may have already been processed for cartId=' . $cartId);
        }

        // Stock validation before inserting order details
        foreach ($cartItems as $item) {
            $stockStmt = $db->prepare("
                SELECT i.Stock, p.ProductName, s.Attribute
                FROM SKU s
                JOIN INVENTORY i ON s.InventoryID = i.InventoryID
                JOIN PRODUCT p  ON s.ProductID   = p.ProductID
                WHERE s.SKUID = ?
            ");
            $stockStmt->execute([$item['SKUID']]);
            $stockInfo = $stockStmt->fetch(PDO::FETCH_ASSOC);

            if (!$stockInfo) {
                throw new Exception('SKU not found: ' . $item['SKUID']);
            }

            if ((int)$stockInfo['Stock'] < (int)$item['CartQuantity']) {
                $name = $stockInfo['ProductName'] . ' (' . $stockInfo['Attribute'] . 'g)';
                throw new Exception("Không đủ hàng cho '{$name}'");
            }
        }

        // Insert ORDER_DETAIL and update INVENTORY
        $detailStmt = $db->prepare("
            INSERT INTO order_detail (OrderID, SKUID, OrderQuantity) VALUES (?, ?, ?)
        ");
        $invStmt = $db->prepare("
            UPDATE INVENTORY i
            JOIN SKU s ON i.InventoryID = s.InventoryID
            SET i.Stock = i.Stock - ?,
                i.InventoryStatus = CASE
                    WHEN (i.Stock - ?) >= 20 THEN 'Available'
                    WHEN (i.Stock - ?) > 0 AND (i.Stock - ?) < 20 THEN 'Low in stock'
                    ELSE 'Out of stock'
                END,
                i.LastestUpdate = NOW()
            WHERE s.SKUID = ?
        ");

        foreach ($cartItems as $item) {
            $qty = (int)$item['CartQuantity'];
            $detailStmt->execute([$orderId, $item['SKUID'], $qty]);
            $invStmt->execute([$qty, $qty, $qty, $qty, $item['SKUID']]);
        }

        // Generate TransactionID
        $txStmt = $db->query("SELECT MAX(CAST(SUBSTRING(TransactionID, 3) AS UNSIGNED)) AS maxNum FROM transaction");
        $txRow  = $txStmt->fetch(PDO::FETCH_ASSOC);
        $txId   = 'TX' . str_pad(($txRow['maxNum'] ?? 0) + 1, 6, '0', STR_PAD_LEFT);

        // Insert TRANSACTION
        $db->prepare("
            INSERT INTO transaction
                (TransactionID, OrderID, TransactionType, PaymentMethod, PaymentStatus, Amount, ProviderTransactionID, Note, CreatedAt)
            VALUES (?, ?, 'Payment', 'MoMo', 'Completed', ?, ?, 'MoMo Sandbox Payment', NOW())
        ")->execute([$txId, $orderId, $totalVND, $transId]);

        // Clear cart
        $db->prepare("DELETE FROM cart_detail WHERE CartID = ?")->execute([$cartId]);

        $db->commit();
        return $orderId;

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// ============================================================
//  HELPER: Verify MoMo HMAC-SHA256 signature
//  Works for both IPN (POST JSON) and return (GET params)
//  by passing the data array as argument.
// ============================================================
function verifyMomoSignature(array $data): bool
{
    $rawSignature = 'accessKey='    . MOMO_ACCESS_KEY
        . '&amount='       . ($data['amount']       ?? '')
        . '&extraData='    . ($data['extraData']    ?? '')
        . '&message='      . ($data['message']      ?? '')
        . '&orderId='      . ($data['orderId']      ?? '')
        . '&orderInfo='    . ($data['orderInfo']    ?? '')
        . '&orderType='    . ($data['orderType']    ?? '')
        . '&partnerCode='  . ($data['partnerCode']  ?? '')
        . '&payType='      . ($data['payType']      ?? '')
        . '&requestId='    . ($data['requestId']    ?? '')
        . '&responseTime=' . ($data['responseTime'] ?? '')
        . '&resultCode='   . ($data['resultCode']   ?? '')
        . '&transId='      . ($data['transId']      ?? '');

    $expected = hash_hmac('sha256', $rawSignature, MOMO_SECRET_KEY);
    return hash_equals($expected, $data['signature'] ?? '');
}
