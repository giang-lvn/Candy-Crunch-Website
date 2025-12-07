<?php
// app/controllers/FooterVideoController.php

require_once __DIR__ . '/../models/footermodel_vid.php'; 

class FooterVideoController {
    
    /**
     * Phương thức này hiển thị Footer Component (Có video nền).
     */
    public function renderFooter(): void {
        
        // 1. Lấy tất cả dữ liệu từ Model
        $videoData = FooterVideoModel::getVideoData();
        $newsletterData = FooterVideoModel::getNewsletterData();
        $linkColumns = FooterVideoModel::getLinkColumns();
        $copyrightData = FooterVideoModel::getCopyrightData();
        
        // 2. Tải View và truyền tất cả dữ liệu
        // Các biến sẽ có sẵn trong view
        include __DIR__ . '/../partials/footer_vid.php';
    }
}
?>