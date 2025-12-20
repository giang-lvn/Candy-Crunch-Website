<?php
// controllers/website/productdetail-controller.php

require_once __DIR__ . '/../../models/website/product-detail.php';
require_once __DIR__ . '/../../models/db.php';

class ProductDetailController
{
    private $model;

    public function __construct()
    {
        global $db;
        $this->model = new ProductDetailModel();
    }

    /**
     * Hiển thị trang chi tiết sản phẩm
     */
    public function index()
    {
        // 1️⃣ Kiểm tra ProductID
        if (!isset($_GET['productId'])) {
            die("Product ID is required");
        }

        $productId = $_GET['productId'];

        // 2️⃣ Lấy dữ liệu sản phẩm
        $product = $this->model->getProductById($productId);

        if (!$product) {
            die("Product not found");
        }

        // 3️⃣ Lấy mô tả + filter
        $description = $this->model->getProductDescription($productId);
        $filters = $this->model->getProductFilter($productId);

        // 4️⃣ Lấy danh sách SKU (unit)
        $skuList = $this->model->getSkuByProductId($productId);

        // Kiểm tra nếu không có SKU nào
        if (empty($skuList)) {
            die("No SKU found for this product");
        }

        // 5️⃣ SKU mặc định (SKU đầu tiên)
        $defaultSku = $skuList[0];

        // 6️⃣ Lấy giá và tồn kho cho SKU mặc định
        $price = $this->model->getProductPriceBySku($defaultSku['SKUID']);
        $stock = $this->model->getProductStockBySku($defaultSku['SKUID']);

        // Đảm bảo price và stock luôn có giá trị
        if (!$price) {
            $price = ['OriginalPrice' => 0, 'PromotionPrice' => 0];
        }
        if (!$stock) {
            $stock = ['Stock' => 0, 'InventoryStatus' => 'Out of Stock'];
        }

        // Đảm bảo $description không null
        if (!$description) {
            $description = 'No description available';
        }

        // Đảm bảo $filters là array
        if (!is_array($filters)) {
            $filters = [];
        }

        // 7️⃣ Truyền dữ liệu sang View
        require_once __DIR__ . '/../../views/website/php/productdetail.php';
    }

    /**
     * AJAX: lấy giá + tồn kho khi đổi unit
     */
    public function getSkuInfo()
    {
        header('Content-Type: application/json');

        if (!isset($_POST['skuid'])) {
            echo json_encode(['error' => 'SKUID is required']);
            return;
        }

        $skuId = $_POST['skuid'];

        $price = $this->model->getProductPriceBySku($skuId);
        $stock = $this->model->getProductStockBySku($skuId);

        // Lấy thông tin SKU để có Image
        $sql = "SELECT Image FROM SKU WHERE SKUID = :skuId";
        global $db;
        $stmt = $db->prepare($sql);
        $stmt->execute(['skuId' => $skuId]);
        $skuInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'price' => $price ?: ['OriginalPrice' => 0, 'PromotionPrice' => 0],
            'stock' => $stock ?: ['Stock' => 0, 'InventoryStatus' => 'Out of Stock'],
            'image' => $skuInfo ? $skuInfo['Image'] : ''
        ]);
    }
}