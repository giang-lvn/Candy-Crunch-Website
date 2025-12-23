<?php
/**
 * Featured Products Controller
 * Xử lý API request để lấy danh sách sản phẩm nổi bật cho landing page
 */
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/featured_products_model.php';

class FeaturedProductsController
{
    private FeaturedProductsModel $model;

    public function __construct()
    {
        global $db;
        $this->model = new FeaturedProductsModel($db);
    }

    /**
     * API: Lấy danh sách featured products
     */
    public function getProducts(): void
    {
        header('Content-Type: application/json');

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

        // Giới hạn tối đa 20 sản phẩm
        $limit = min($limit, 20);

        $products = $this->model->getFeaturedProducts($limit);

        echo json_encode([
            'success' => true,
            'products' => $products
        ], JSON_UNESCAPED_UNICODE);
    }
}

