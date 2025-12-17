<?php

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
        $this->accountModel  = new AccountModel();
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

    //Hiển thị trang giỏ hàng
    public function index()
    {
        $cartId = $_SESSION['cart_id'];

        //Lấy danh sách sản phẩm trong giỏ
        $cartItems = $this->cartModel->getCartItems($cartId);

        //Gợi ý sản phẩm upsell
        $upsellProducts = [];
        if (!empty($cartItems)) {
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
        }

        // Tính tiền
        $amount = $this->cartModel->calculateCartAmount($cartItems);

        $subtotal = $amount['subtotal'];
        $discount = $amount['discount'];
        $promo    = 0;
        $total    = $subtotal - $discount;

        //Tính subtotal
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
            $subtotal += $price * $item['CartQuantity'];
        }

        // 3. Truyền dữ liệu sang view
        require 'views/website/php/cart.php';
    }

    //Cập nhật số lượng giỏ hàng
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


    //Xóa sản phẩm
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

    //Apply vocher
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
