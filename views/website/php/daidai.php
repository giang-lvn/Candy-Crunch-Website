<?php
// views/website/php/productdetail.php
// File này GỌI Controller trước, rồi Controller sẽ load View

require_once __DIR__ . '/../../../controllers/website/productdetail-controller.php';

$controller = new ProductDetailController();

// Kiểm tra action (cho AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'getSkuInfo') {
    $controller->getSkuInfo();
} else {
    $controller->index();
}