<?php
// app/controllers/FooterController.php

require_once __DIR__ . '/../models/FooterModel.php'; 

class FooterController {
    
    /**
     * Phương thức này hiển thị Footer Component (Không có video).
     */
    public function renderFooter(): void {
        
        // 1. Lấy tất cả dữ liệu từ Model
        $newsletterData = FooterModel::getNewsletterData();
        $linkColumns = FooterModel::getLinkColumns();
        $copyrightData = FooterModel::getCopyrightData();
        
        // 2. Tải View và truyền tất cả dữ liệu
        // Các biến ($newsletterData, $linkColumns, $copyrightData) sẽ có sẵn trong view
        include __DIR__ . '/../partials/footer_kovid.php';
    }
}
?>