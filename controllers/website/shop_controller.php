<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/shop_model.php';

class ShopController
{
    private ShopModel $model;

    public function __construct()
    {
        global $db;
        $this->model = new ShopModel($db);
    }

    /**
     * Hiển thị trang shop
     */
    public function index(): void
    {
        require_once __DIR__ . '/../../views/website/php/shop.php';
    }

    /**
     * API: Lấy danh sách sản phẩm (AJAX)
     */
    public function getProducts(): void
    {
        header('Content-Type: application/json');

        $params = [
            'search' => $_GET['search'] ?? null,
            'category' => $_GET['category'] ?? null,
            'ingredient' => $_GET['ingredient'] ?? null,
            'flavour' => $_GET['flavour'] ?? null,
            'rating' => $_GET['rating'] ?? null,
            'sort' => $_GET['sort'] ?? null,
            'page' => (int) ($_GET['page'] ?? 1),
            'per_page' => (int) ($_GET['per_page'] ?? 20),
        ];

        echo json_encode(
            $this->model->getProducts($params),
            JSON_UNESCAPED_UNICODE
        );
    }
}