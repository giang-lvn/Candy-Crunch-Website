<?php
// app/controllers/HeaderController.php

require_once __DIR__ . '/../models/headermodel_login.php'; 

class HeaderController {
    
    /**
     * Phương thức này hiển thị Header Component.
     * @param array $data Dữ liệu tùy chọn (ví dụ: để xác định trang active, hoặc nếu bạn muốn truyền is_logged_in ở đây)
     */
    public function renderHeader(array $data = []): void {
        
        // 1. Lấy dữ liệu từ Model
        $primaryLinks = HeaderModel::getPrimaryLinks();
        $authButtons = HeaderModel::getAuthButtonsData();
        $shopDropdownData = HeaderModel::getShopDropdownData();
        
        // 2. Tải View và truyền tất cả dữ liệu
        // Các biến ($primaryLinks, $authButtons, $shopDropdownData) sẽ có sẵn trong view
        extract($data); // Cho phép sử dụng các biến trong $data
        include __DIR__ . '/../partials/header_login.php';
    }
}
?>