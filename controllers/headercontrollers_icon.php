<?php
// app/controllers/HeaderController.php

// Đường dẫn tương đối từ vị trí Controller đến Model
require_once __DIR__ . '/../models/headermodel_icon.php'; 

class HeaderController {
    
    /**
     * Phương thức này hiển thị Header Component.
     * @param array $data Dữ liệu tùy chọn (ví dụ: số lượng giỏ hàng thực tế)
     */
    public function renderHeader(array $data = []): void {
        
        // 1. Lấy dữ liệu tĩnh từ Model
        $primaryLinks = HeaderModel::getPrimaryLinks();
        $shopDropdownData = HeaderModel::getShopDropdownData();
        
        // 2. Lấy dữ liệu động User Actions, sử dụng cartCount được truyền vào
        $userActions = HeaderModel::getUserActionsData($data['cartCount'] ?? 0); 
        
        // 3. Tải View và truyền tất cả dữ liệu
        // Các biến ($primaryLinks, $shopDropdownData, $userActions) sẽ có sẵn trong view
        extract($data); // Cho phép sử dụng các biến trong $data (như $cartCount)
        include __DIR__ . '/../partials/header_icon.php';
    }
}
?>