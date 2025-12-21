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
    public function handleRequest(): void
    {
        header('Content-Type: application/json');
        $action = $_GET['action'] ?? 'list';

        switch ($action) {
            case 'add-to-cart':
                $this->addToCart();
                break;
            case 'list':
            default:
                $this->getProducts();
                break;
        }
    }
    private function getProducts(): void
    {

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
    private function addToCart(): void
    {
        $skuId = $_POST['sku_id'] ?? null;
        echo json_encode(['success' => true, 'message' => 'Done']);
    }
}

// ROUTER
$controller = new ShopController();
$controller->handleRequest();