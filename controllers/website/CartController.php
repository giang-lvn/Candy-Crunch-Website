<?php

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/account_model.php';
require_once __DIR__ . '/../../models/website/CartModel.php';

class CartController
{
    protected $accountModel;
    protected $cartModel;
    protected $isAjax = false;

    public function __construct()
    {
        // Start session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra nếu là AJAX request
        $this->isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Cũng coi là AJAX nếu Content-Type là application/json
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $this->isAjax = true;
        }

        // Load model
        global $db;
        $this->accountModel  = new AccountModel($db);
        $this->cartModel     = new CartModel();

        // 1. Kiểm tra đăng nhập - Login system uses 'AccountID' (uppercase)
        if (!isset($_SESSION['AccountID'])) {
            if ($this->isAjax) {
                // AJAX request → trả về JSON
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Please login to add products to cart',
                    'redirect' => '/Candy-Crunch-Website/views/website/php/login.php'
                ]);
                exit;
            }
            // Chưa đăng nhập → chuyển sang trang login
            header('Location: /Candy-Crunch-Website/views/website/php/login.php');
            exit;
        }

        $accountId = $_SESSION['AccountID'];

        // 2. Kiểm tra account có tồn tại & hợp lệ không
        $account = $this->accountModel->findById($accountId);

        if (!$account || strtolower($account['AccountStatus']) !== 'active') {
            // Account không hợp lệ → logout
            session_destroy();
            if ($this->isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Account is not active',
                    'redirect' => '/Candy-Crunch-Website/views/website/php/login.php'
                ]);
                exit;
            }
            header('Location: /Candy-Crunch-Website/views/website/php/login.php');
            exit;
        }

        // 3. Lấy CustomerID từ AccountID
        $customer = $this->accountModel->getCustomerByAccountId($accountId);
        if (!$customer || empty($customer['CustomerID'])) {
            if ($this->isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
                exit;
            }
            die('Customer not found');
        }

        $_SESSION['customer_id'] = $customer['CustomerID'];

        // 4. Kiểm tra khách đã có cart chưa
        $cart = $this->cartModel->findActiveCartByCustomer($customer['CustomerID']);
        $_SESSION['cart_id'] = $cart
            ? $cart['CartID']
            : $this->cartModel->createCart($customer['CustomerID']);
    }

    // Hiển thị trang giỏ hàng
    public function index()
    {
        $cartId = $_SESSION['cart_id'];

        // Lấy danh sách sản phẩm trong giỏ
        $cartItems = $this->cartModel->getCartItems($cartId);
        
        // Đảm bảo $cartItems luôn là array
        if ($cartItems === null || $cartItems === false) {
            $cartItems = [];
        }

        // Gợi ý sản phẩm upsell
        $upsellProducts = [];
        
        if (!empty($cartItems)) {
            // Nếu CÓ sản phẩm trong giỏ → lấy upsell theo category
            $customerId = $_SESSION['customer_id'];
            
            // Lấy CategoryID từ các sản phẩm trong giỏ
            $categoryIds = $this->cartModel->getCategoryIdsFromCart($customerId);

            // Lấy SKUID đã có trong giỏ (để loại trừ)
            $excludeSkuIds = $this->cartModel->getCartSkuIds($customerId);

            // Lấy danh sách sản phẩm gợi ý
            $upsellProducts = $this->cartModel->getUpsellProducts(
                $categoryIds,
                $excludeSkuIds,
                8
            );
        } else {
            // Nếu KHÔNG có sản phẩm trong giỏ → lấy 8 sản phẩm đầu tiên
            $upsellProducts = $this->cartModel->getFirstProducts(8);
        }

        // Tính tiền
        if (!empty($cartItems)) {
            // Nếu có sản phẩm → tính bình thường
            $amount = $this->cartModel->calculateCartAmount($cartItems);
            $subtotal = $amount['subtotal'];
            $discount = $amount['discount'];
        } else {
            // Nếu giỏ rỗng → tất cả = 0
            $subtotal = 0;
            $discount = 0;
        }

        $promo = 0;

        // LOGIC SHIPPING
        $baseAmount = $subtotal - $discount; // Số tiền sau discount
        $freeShippingThreshold = 200000; // Ngưỡng freeship 200k

        if ($baseAmount >= $freeShippingThreshold) {
            $shipping = 0; // Freeship
            $remainingForFreeShip = 0;
        } else {
            $shipping = 30000; // Phí ship 30k
            $remainingForFreeShip = $freeShippingThreshold - $baseAmount; // Còn thiếu bao nhiêu
        }

        // Tính % cho shipping bar
        $shippingProgress = ($baseAmount / $freeShippingThreshold) * 100;
        $shippingProgress = min($shippingProgress, 100); // Tối đa 100%

        $total = $baseAmount - $promo + $shipping;

        // Truyền dữ liệu sang view
        require 'views/website/php/cart.php';
    }

    // Lấy số lượng sản phẩm trong giỏ
    public function getQuantity(int $cartId, int $skuId): int
    {
        return $this->cartModel->getQuantity($cartId, $skuId);
    }


    // Tính subtotal từ cart items
    private function calculateSubtotal(array $cartItems): float
    {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
            $subtotal += $price * $item['CartQuantity'];
        }
        return $subtotal;
    }

    // Cập nhật số lượng giỏ hàng
    public function updateQuantity()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        $cartId = $_SESSION['cart_id'];
        $skuId  = trim($data['skuid']); // SKUID is VARCHAR(20)
        $action = $data['action'];

        // Lấy quantity hiện tại
        $currentQty = $this->cartModel->getQuantity($cartId, $skuId);

        if ($action === 'increase') {
            $newQty = $currentQty + 1;
        } elseif ($action === 'decrease' && $currentQty > 1) {
            $newQty = $currentQty - 1;
        } else {
            echo json_encode(['success' => false]);
            return;
        }

        $this->cartModel->updateQuantity($cartId, $skuId, $newQty);

        // Lấy lại cart mới
        $cartItems = $this->cartModel->getCartItems($cartId);
        
        // Tính toán đầy đủ dữ liệu
        $cartData = $this->calculateCartData($cartItems);

        echo json_encode([
            'success' => true,
            'items'   => $cartItems,
            'cartEmpty' => empty($cartItems),
            'subtotal' => $cartData['subtotal'],
            'discount' => $cartData['discount'],
            'promo' => $cartData['promo'],
            'shipping' => $cartData['shipping'],
            'remainingForFreeShip' => $cartData['remainingForFreeShip'],
            'total' => $cartData['total']
        ]);
    }

    // Helper tính toán dữ liệu giỏ hàng để hiển thị
    private function calculateCartData($cartItems) {
        $subtotal = 0;
        $discount = 0;
        
        if (!empty($cartItems)) {
            $amount = $this->cartModel->calculateCartAmount($cartItems);
            $subtotal = $amount['subtotal'];
            $discount = $amount['discount'];
        }

        $promo = 0; 
        
        // Apply Voucher from Session
        if (isset($_SESSION['voucher_code']) && !empty($_SESSION['voucher_code'])) {
            $voucher = $this->cartModel->findVoucherByCode($_SESSION['voucher_code']);
            // Validate voucher again (expiration, etc)
            if ($voucher) {
                // Calculate promo based on subtotal - discount (actual price of products)
                $promo = $this->cartModel->calculateVoucherDiscount($voucher, $subtotal - $discount);
            } else {
                // Remove invalid voucher
                unset($_SESSION['voucher_code']);
            }
        }

        // LOGIC SHIPPING
        // Shipping threshold based on amount AFTER product discount but BEFORE voucher (usually)
        // OR After voucher? Let's assume after voucher to encourage higher spending.
        $baseAmount = $subtotal - $discount - $promo; 
        
        $freeShippingThreshold = 200000;
        
        if ($baseAmount >= $freeShippingThreshold) {
            $shipping = 0;
            $remainingForFreeShip = 0;
        } else {
            $shipping = 30000;
            $remainingForFreeShip = max(0, $freeShippingThreshold - $baseAmount);
        }

        $total = max(0, $baseAmount + $shipping);

        return compact('subtotal', 'discount', 'promo', 'shipping', 'remainingForFreeShip', 'total');
    }

    // Xử lý thêm sản phẩm vào giỏ hàng từ request
    public function handleAddToCart()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['customer_id'], $data['skuid'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $customerId = $_SESSION['customer_id'];
        $skuId = trim($data['skuid']); // SKUID is VARCHAR(20)
        $quantity = (int)($data['quantity'] ?? 1);

        // Gọi function addToCart() từ CartModel
        $result = $this->cartModel->addToCart($customerId, $skuId, $quantity);

        if ($result) {
            // Lấy lại cart items sau khi thêm
            $cartId = $_SESSION['cart_id'];
            $cartItems = $this->cartModel->getCartItems($cartId);

            // Tính toán số liệu cho view
            $cartData = $this->calculateCartData($cartItems);
            extract($cartData);

            // Render HTML
            ob_start();
            require __DIR__ . '/../../views/website/php/cart_content.php';
            $html = ob_get_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'items' => $cartItems,
                'cartCount' => count($cartItems),
                'html' => $html
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        }
    }

    // Xóa sản phẩm
    public function removeItem()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['cart_id'], $data['skuid'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $cartId = $_SESSION['cart_id'];
        $skuId  = trim($data['skuid']); // SKUID is VARCHAR(20)

        $this->cartModel->removeItem($cartId, $skuId);

        // Lấy lại cart sau khi xóa
        $cartItems = $this->cartModel->getCartItems($cartId);
        
        // Tính toán đầy đủ dữ liệu
        $cartData = $this->calculateCartData($cartItems);

        echo json_encode([
            'success'   => true,
            'cartEmpty' => empty($cartItems),
            'items'     => $cartItems,
            'subtotal'  => $cartData['subtotal'],
            'discount'  => $cartData['discount'],
            'promo'     => $cartData['promo'],
            'shipping'  => $cartData['shipping'],
            'remainingForFreeShip' => $cartData['remainingForFreeShip'],
            'total'     => $cartData['total']
        ]);
    }

    // Apply vocher
    public function applyVoucher()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $code = trim($data['code'] ?? '');
        $cartId = $_SESSION['cart_id'] ?? null;

        if (!$cartId) {
             echo json_encode(['success' => false, 'message' => 'Cart not found']);
             return;
        }

        // Logic reset voucher (if empty code sent)
        if ($code === '') {
            unset($_SESSION['voucher_code']);
            $cartItems = $this->cartModel->getCartItems($cartId);
            $cartData = $this->calculateCartData($cartItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Voucher removed',
                'subtotal' => $cartData['subtotal'],
                'discount' => $cartData['discount'],
                'promo'    => $cartData['promo'],
                'shipping' => $cartData['shipping'],
                'remainingForFreeShip' => $cartData['remainingForFreeShip'],
                'total'    => $cartData['total']
            ]);
            return;
        }

        $cartItems = $this->cartModel->getCartItems($cartId);
        if (empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            return;
        }

        $voucher = $this->cartModel->findVoucherByCode($code);
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Invalid voucher code']);
            return;
        }

        // Tạm lưu session để tính toán thử
        $_SESSION['voucher_code'] = $code;
        
        // Tính toán lại
        $cartData = $this->calculateCartData($cartItems);
        
        if ($cartData['promo'] > 0) {
            echo json_encode([
                'success'  => true,
                'message'  => 'Voucher applied successfully',
                'subtotal' => $cartData['subtotal'],
                'discount' => $cartData['discount'],
                'promo'    => $cartData['promo'],
                'shipping' => $cartData['shipping'],
                'remainingForFreeShip' => $cartData['remainingForFreeShip'],
                'total'    => $cartData['total']
            ]);
        } else {
            // Voucher valid but not applicable (condition failure)
            unset($_SESSION['voucher_code']); // Revert
            
            // Recalculate without voucher to give clean state data
            $cartData = $this->calculateCartData($cartItems);

            echo json_encode([
                'success' => false, 
                'message' => 'Voucher conditions not met (Min spend or Expiry)',
                'total' => $cartData['total']
            ]);
        }
    }

    // Đổi attribute (SKU)
    public function changeAttribute()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['cart_id'], $data['oldSkuId'], $data['newSkuId'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        $cartId = $_SESSION['cart_id'];
        $oldSkuId = trim($data['oldSkuId']);
        $newSkuId = trim($data['newSkuId']);
        
        $result = $this->cartModel->changeAttribute($cartId, $oldSkuId, $newSkuId);
        
        if ($result) {
            // Lấy lại cart mới
            $cartItems = $this->cartModel->getCartItems($cartId);
            $cartData = $this->calculateCartData($cartItems);
            
            echo json_encode([
                'success' => true,
                'items'   => $cartItems,
                'subtotal' => $cartData['subtotal'],
                'discount' => $cartData['discount'],
                'promo' => $cartData['promo'],
                'shipping' => $cartData['shipping'],
                'remainingForFreeShip' => $cartData['remainingForFreeShip'],
                'total' => $cartData['total']
            ]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Failed to change attribute']);
        }
    }

    // AJAX lấy lại toàn bộ nội dung cart (partial view)
    public function getCartContent()
    {
        $cartId = $_SESSION['cart_id'] ?? null;
        if (!$cartId) {
            echo "Cart empty";
            return;
        }

        $cartItems = $this->cartModel->getCartItems($cartId);
        $cartData = $this->calculateCartData($cartItems);
        
        // Extract data for view ($subtotal, $discount, $promo, $shipping, $remainingForFreeShip, $total)
        extract($cartData);
        
        // Include partial view
        require __DIR__ . '/../../views/website/php/cart_content.php';
    }
}
