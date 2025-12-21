<?php

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/website/shop_model.php';

class ShopController
{
    private ShopModel $model;

    public function __construct()
    {
        global $db;

        if (!$db instanceof PDO) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB connection failed']);
            exit;
        }

        $this->model = new ShopModel($db);
    }

    /* ===============================
       GET PRODUCTS
    =============================== */
    public function getProducts(): void
    {
        header('Content-Type: application/json');

        try {
            $params = [
                'search' => $_GET['search'] ?? null,
                'category' => $_GET['category'] ?? null,
                'ingredient' => $_GET['ingredient'] ?? null,
                'flavour' => $_GET['flavour'] ?? null,
            ];

            echo json_encode(
                $this->model->getProducts($params),
                JSON_UNESCAPED_UNICODE
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể tải danh sách sản phẩm'
            ]);
        }
    }

    /* ===============================
       ADD TO CART
    =============================== */
    public function addToCart(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        $customerId = $_SESSION['customer_id'] ?? null;
        $skuId = $_POST['sku_id'] ?? null;
        $qty = (int) ($_POST['qty'] ?? 1);

        // ❌ Không phân biệt login nữa
        if (!$customerId || !$skuId) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot add to cart'
            ]);
            return;
        }

        try {
            $this->model->addToCart($customerId, $skuId, $qty);

            echo json_encode([
                'success' => true,
                'message' => 'Add product to cart!'
            ]);
        } catch (Exception $e) {
            // Hết hàng / không đủ tồn
            echo json_encode([
                'success' => false,
                'message' => 'Product is out of stock'
            ]);
        }
    }
}

/* ====== ROUTER MINI ====== */
$controller = new ShopController();

$action = $_GET['action'] ?? 'list';

match ($action) {
    'add-to-cart' => $controller->addToCart(),
    default => $controller->getProducts(),
};
