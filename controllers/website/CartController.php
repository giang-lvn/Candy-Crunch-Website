<?php

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/account_model.php';

class CartController
{
    protected $accountModel;
    protected $customerModel;
    protected $cartModel;

    public function __construct()
    {
        // Start session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load model
        global $db;
        $this->accountModel  = new AccountModel($db);
        $this->customerModel = new CustomerModel();
        $this->cartModel     = new CartModel();

        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['account_id'])) {
            // Chưa đăng nhập → chuyển sang trang login
            header('Location: /login');
            exit;
        }

        $accountId = $_SESSION['account_id'];

        // 2. Kiểm tra account có tồn tại & hợp lệ không
        $account = $this->accountModel->findById($accountId);

        if (!$account || $account['AccountStatus'] !== 'active') {
            // Account không hợp lệ → logout
            session_destroy();
            header('Location: /login');
            exit;
        }

        // 3. Lấy CustomerID từ AccountID
        $customer = $this->customerModel->findByAccountId($accountId);
        if (!$customer) {
            // Không tồn tại customer → lỗi dữ liệu
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
        $skuId  = (int)$data['skuid'];
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

        echo json_encode([
            'success' => true,
            'items'   => $cartItems,
            'subtotal'=> $this->calculateSubtotal($cartItems)
        ]);
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
        $skuId  = (int)$data['skuid'];

        $this->cartModel->removeItem($cartId, $skuId);

        // Lấy lại cart sau khi xóa
        $cartItems = $this->cartModel->getCartItems($cartId);

        echo json_encode([
            'success'   => true,
            'cartEmpty'=> empty($cartItems),
            'items'    => $cartItems
        ]);
    }

    // Apply vocher
    public function applyVoucher()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $code = trim($data['code'] ?? '');

        if ($code === '') {
            echo json_encode(['success' => false, 'message' => 'Voucher code required']);
            return;
        }

        $cartId = $_SESSION['cart_id'];

        $cartItems = $this->cartModel->getCartItems($cartId);
        if (empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            return;
        }

        $amount = $this->cartModel->calculateCartAmount($cartItems);
        $subtotal = $amount['subtotal'];
        $discount = $amount['discount'];

        $voucher = $this->cartModel->findVoucherByCode($code);
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Invalid voucher']);
            return;
        }

        $promo = $this->cartModel->calculateVoucherDiscount($voucher, $subtotal);
        //$baseAmount = $subtotal - $discount;
        //$promo = $this->cartModel->calculateVoucherDiscount($voucher, $baseAmount);


        if ($promo <= 0) {
            echo json_encode(['success' => false, 'message' => 'Voucher not applicable']);
            return;
        }

        $total = max(0, $subtotal - $discount - $promo);

        echo json_encode([
            'success'  => true,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo'    => $promo,
            'total'    => $total
        ]);
    }

}
