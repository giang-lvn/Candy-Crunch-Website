<?php
require_once __DIR__ . '/../../models/website/wishlistmodel.php';

class WishlistController {
    private $model;

    public function __construct() {
        $this->model = new WishlistModel();
    }

    public function index() {
        session_start();

        $customerId = $_SESSION['customer_id'] ?? 1;

        $wishlistItems = $this->model->getWishlistByCustomer($customerId);

        require_once __DIR__ . '/../../views/website/php/wishlist.php';
    }

    public function remove() {
        session_start();

        $customerId = $_SESSION['customer_id'];
        $productId = $_POST['product_id'];

        $this->model->removeFromWishlist($customerId, $productId);

        header("Location: wishlist.php");
        exit;
    }
}
