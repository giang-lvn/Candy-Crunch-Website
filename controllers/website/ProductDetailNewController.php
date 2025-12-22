<?php
// controllers/website/ProductDetailNewController.php

require_once __DIR__ . '/../../models/website/CartModel.php';
require_once __DIR__ . '/../../models/website/ProductDetailNewModel.php';
require_once __DIR__ . '/../../models/db.php';

class ProductDetailNewController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProductDetailNewModel();
    }

    /**
     * Hiển thị trang chi tiết sản phẩm
     */
    public function index()
    {
        // 1. Kiểm tra ProductID
        if (!isset($_GET['productId'])) {
            die("Product ID is required");
        }

        $productId = $_GET['productId'];

        // 2. Lấy thông tin sản phẩm
        $product = $this->model->getProductDetail($productId);

        if (!$product) {
            die("Product not found");
        }

        // 3. Lấy danh sách SKU
        $skuList = $this->model->getAllSkuWithStock($productId);

        // 4. SKU mặc định
        $defaultSku = !empty($skuList) ? $skuList[0] : null;

        // 5. Lấy danh sách Ingredient
        $ingredients = $this->model->getProductIngredients($productId);

        // 6. Parse product images từ JSON
        $productImages = $this->model->parseProductImages($product['Image'] ?? '');
        $thumbnailImage = $this->model->getProductThumbnail($product['Image'] ?? '');

        // 7. Lấy danh sách sản phẩm liên quan
        $relatedProducts = $this->model->getRelatedProducts($product['CategoryID'], $productId, 4);

        // Process related product images
        foreach ($relatedProducts as &$relatedProduct) {
            $relatedProduct['Thumbnail'] = $this->model->getProductThumbnail($relatedProduct['Image'] ?? '');
        }
        unset($relatedProduct); // Break reference

        // 8. Truyền dữ liệu sang View
        require_once __DIR__ . '/../../views/website/php/productdetail-new.php';
    }

    /**
     * AJAX: Lấy thông tin SKU khi thay đổi unit
     */
    public function getSkuInfo()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['skuId']) && !isset($_POST['skuId'])) {
            echo json_encode(['error' => 'SKUID is required']);
            return;
        }

        $skuId = $_GET['skuId'] ?? $_POST['skuId'];
        $skuInfo = $this->model->getSkuById($skuId);

        if (!$skuInfo) {
            echo json_encode(['error' => 'SKU not found']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'SKUID' => $skuInfo['SKUID'],
                'OriginalPrice' => $skuInfo['OriginalPrice'],
                'PromotionPrice' => $skuInfo['PromotionPrice'],
                'Stock' => $skuInfo['Stock'] ?? 0,
                'Image' => $skuInfo['Image'] ?? ''
            ]
        ]);
    }

    /**
     * AJAX: Xử lý Buy Now
     */
    public function buyNow()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            header('Content-Type: application/json');

            // Check login
            if (!isset($_SESSION['AccountID']) || !isset($_SESSION['CustomerID'])) {
                echo json_encode([
                    'success' => false,
                    'redirect' => '/Candy-Crunch-Website/views/website/php/login.php',
                    'message' => 'Please login to continue'
                ]);
                return;
            }

            // Get input
            $input = json_decode(file_get_contents('php://input'), true);
            $skuId = $input['skuid'] ?? null;
            $quantity = $input['quantity'] ?? 1;

            if (!$skuId) {
                echo json_encode(['success' => false, 'message' => 'Missing SKU ID']);
                return;
            }

            // Validate SKU and Stock
            $skuInfo = $this->model->getSkuById($skuId);
            if (!$skuInfo) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                return;
            }

            if (($skuInfo['Stock'] ?? 0) < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock']);
                return;
            }

            // Add to Database Cart
            $cartModel = new CartModel();
            $result = $cartModel->addToCart($_SESSION['CustomerID'], $skuId, $quantity);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'redirect' => '/Candy-Crunch-Website/views/website/php/checkout.php' // Standard checkout link
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}

// Router - xử lý action
$action = $_GET['action'] ?? 'index';
$controller = new ProductDetailNewController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    die("Action not found");
}
