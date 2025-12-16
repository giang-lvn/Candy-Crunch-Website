<?php

class CartController
{
    protected $accountModel;
    protected $customerModel;
    protected $cartModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->accountModel  = new AccountModel();
        $this->customerModel = new CustomerModel();
        $this->cartModel     = new CartModel();

        if (!isset($_SESSION['account_id'])) {
            header('Location: /login');
            exit;
        }

        $accountId = $_SESSION['account_id'];
        $account = $this->accountModel->findById($accountId);

        if (!$account || $account['AccountStatus'] !== 'active') {
            session_destroy();
            header('Location: /login');
            exit;
        }

        $customer = $this->customerModel->findByAccountId($accountId);
        if (!$customer) {
            die('Customer not found');
        }

        $_SESSION['customer_id'] = $customer['CustomerID'];

        $cart = $this->cartModel->findActiveCartByCustomer($customer['CustomerID']);
        $_SESSION['cart_id'] = $cart
            ? $cart['CartID']
            : $this->cartModel->createCart($customer['CustomerID']);
    }

    public function index()
    {
        $cartId = $_SESSION['cart_id'];

        $cartItems = $this->cartModel->getCartItems($cartId);

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item['PromotionPrice'] ?? $item['OriginalPrice'];
            $subtotal += $price * $item['CartQuantity'];
        }

        require 'views/website/cart.php';
    }
}
