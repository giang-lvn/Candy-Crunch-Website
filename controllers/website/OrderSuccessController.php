<?php
/**
 * OrderSuccessController.php
 * Handles order creation from checkout page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$ROOT = '/Candy-Crunch-Website';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order']);
    exit;
}

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/CartModel.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'place_order':
        placeOrder();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Place a new order
 */
function placeOrder()
{
    global $db, $ROOT;

    $customerId = $_SESSION['customer_id'] ?? null;
    $cartId = $_SESSION['cart_id'] ?? null;

    if (!$customerId || !$cartId) {
        echo json_encode(['success' => false, 'message' => 'Customer or Cart not found']);
        return;
    }

    // Get order data from POST
    $addressId = $_POST['address_id'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $deliveryMethod = $_POST['delivery_method'] ?? 'standard';
    $bankingId = $_POST['banking_id'] ?? null;

    // Validate required fields
    if (empty($addressId)) {
        echo json_encode(['success' => false, 'message' => 'Please select a shipping address']);
        return;
    }

    // Get cart model
    $cartModel = new CartModel();

    // Get cart items
    $cartItems = $cartModel->getCartItems($cartId);

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
        return;
    }

    // Debug: Log cart items data
    error_log("OrderSuccessController - Cart Items Count: " . count($cartItems));
    foreach ($cartItems as $idx => $item) {
        error_log("Item $idx: " . json_encode([
            'ProductName' => $item['ProductName'] ?? 'N/A',
            'OriginalPrice' => $item['OriginalPrice'] ?? 0,
            'PromotionPrice' => $item['PromotionPrice'] ?? 0,
            'CartQuantity' => $item['CartQuantity'] ?? 0
        ]));
    }

    // Get payment values from POST (sent from checkout UI - already calculated and displayed)
    $subtotal = (int)($_POST['subtotal'] ?? 0);
    $discount = (int)($_POST['discount'] ?? 0);
    $promo = (int)($_POST['promo'] ?? 0);
    $shippingFee = (int)($_POST['shipping'] ?? 30000);
    $total = (int)($_POST['total'] ?? 0);
    
    // Get voucher ID from session (for saving to database)
    $voucherId = null;
    $voucherCode = $_SESSION['voucher_code'] ?? '';
    if (!empty($voucherCode)) {
        $voucher = $cartModel->findVoucherByCode($voucherCode);
        if ($voucher) {
            $voucherId = $voucher['VoucherID'];
        }
    }

    // Debug: Log received payment data
    error_log("OrderSuccessController - Received from POST: subtotal=$subtotal, discount=$discount, promo=$promo, shipping=$shippingFee, total=$total");

    try {
        $db->beginTransaction();

        // Generate OrderID (format: ORD001, ORD002, ...)
        $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(OrderID, 4) AS UNSIGNED)) FROM ORDERS");
        $next = ((int) $stmt->fetchColumn()) + 1;
        $orderId = 'ORD' . str_pad($next, 3, '0', STR_PAD_LEFT);

        $orderDate = date('Y-m-d H:i:s');

        // Insert into ORDERS table
        $shippingMethod = ($deliveryMethod === 'fast') ? 'Express' : 'Standard';

        $stmt = $db->prepare("
            INSERT INTO ORDERS (
                OrderID, CustomerID, VoucherID, OrderDate, 
                PaymentMethod, ShippingMethod, ShippingFee, OrderStatus
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, 'Pending Confirmation'
            )
        ");

        $stmt->execute([
            $orderId,
            $customerId,
            $voucherId, // Will be NULL if no voucher applied
            $orderDate,
            $paymentMethod,
            $shippingMethod,
            $shippingFee
        ]);

        // Kiểm tra tồn kho trước khi đặt hàng
        foreach ($cartItems as $item) {
            $skuId = $item['SKUID'];
            $requestedQty = (int)$item['CartQuantity'];
            
            // Lấy stock hiện tại
            $stockStmt = $db->prepare("
                SELECT i.Stock, p.ProductName, s.Attribute
                FROM SKU s
                JOIN INVENTORY i ON s.InventoryID = i.InventoryID
                JOIN PRODUCT p ON s.ProductID = p.ProductID
                WHERE s.SKUID = ?
            ");
            $stockStmt->execute([$skuId]);
            $stockInfo = $stockStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stockInfo) {
                throw new PDOException("Product not found: " . $skuId);
            }
            
            $currentStock = (int)$stockInfo['Stock'];
            
            if ($currentStock < $requestedQty) {
                $productName = $stockInfo['ProductName'] . ' (' . $stockInfo['Attribute'] . 'g)';
                throw new PDOException("Sorry, '{$productName}' only has {$currentStock} items in stock. Please adjust your quantity.");
            }
        }
        
        // Insert order details và cập nhật tồn kho
        foreach ($cartItems as $item) {
            $itemPrice = !empty($item['PromotionPrice']) ? $item['PromotionPrice'] : $item['OriginalPrice'];

            $stmt = $db->prepare("
                INSERT INTO ORDER_DETAIL (
                    OrderID, SKUID, OrderQuantity
                ) VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $orderId,
                $item['SKUID'],
                $item['CartQuantity']
            ]);

            // Update stock quantity when order is placed
            // Constraint: Stock >= 20 = 'Available', Stock < 20 & > 0 = 'Low in stock', Stock = 0 = 'Out of stock'
            $db->prepare("
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
            ")->execute([$item['CartQuantity'], $item['CartQuantity'], $item['CartQuantity'], $item['CartQuantity'], $item['SKUID']]);
        }



        // Get shipping address details from session
        $shippingAddress = null;
        $userAddresses = $_SESSION['user_addresses'] ?? [];
        foreach ($userAddresses as $addr) {
            if ($addr['AddressID'] == $addressId) {
                $shippingAddress = $addr;
                break;
            }
        }

        $db->commit();

        // Set session variables for order success page BEFORE any session manipulation
        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['last_order_date'] = $orderDate;
        $_SESSION['last_payment_method'] = $paymentMethod;
        $_SESSION['last_order_items'] = $cartItems;
        $_SESSION['last_order_subtotal'] = $subtotal;
        $_SESSION['last_order_discount'] = $discount;
        $_SESSION['last_order_shipping'] = $shippingFee;
        $_SESSION['last_order_promo'] = $promo;
        $_SESSION['last_order_total'] = $total;
        $_SESSION['last_order_address'] = $shippingAddress;
        
        // Clear voucher from session (already used in this order)
        unset($_SESSION['voucher_code']);

        // Debug log
        error_log("Order Success - Session Data Set:");
        error_log("Subtotal: " . $subtotal);
        error_log("Discount: " . $discount);
        error_log("Shipping: " . $shippingFee);
        error_log("Promo: " . $promo);
        error_log("Total: " . $total);
        error_log("Cart Items: " . count($cartItems));

        // Clear the cart from database
        $db->prepare("DELETE FROM CART_DETAIL WHERE CartID = ?")->execute([$cartId]);

        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'redirect' => $ROOT . '/views/website/php/ordersuccess.php?order_id=' . urlencode($orderId)
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log('OrderSuccessController Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>