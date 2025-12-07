<?php
//footer ko vid
class FooterModel {
    
    /**
     * Lấy dữ liệu cho phần Newsletter.
     * @return array
     */
    public static function getNewsletterData(): array {
        return [
            'text' => 'Subscribe for 15% off your first order and unlock your inner potential with us.',
            'placeholder' => 'Your Email',
            'submit_icon_svg' => [
                '<svg class="submit-icon" viewBox="0 0 24 24" fill="none">',
                '<path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
                '<path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
                '</svg>'
            ],
        ];
    }
    
    /**
     * Lấy dữ liệu cho các cột liên kết (Links Grid).
     * @return array
     */
    public static function getLinkColumns(): array {
        return [
            // Cột 1: Navigation
            'navigation' => [
                ['text' => 'About us', 'url' => 'about.php'],
                ['text' => 'Shop', 'url' => 'shop.php'],
                ['text' => 'Contact', 'url' => 'contact.php'],
                ['text' => 'Policy', 'url' => 'policy.php'],
            ],
            // Cột 2: Policies
            'policies' => [
                ['text' => 'FAQ', 'url' => 'faq.php'],
                ['text' => 'Shipping Policy', 'url' => 'shipping.php'],
                ['text' => 'Refund Policy', 'url' => 'refund.php'],
                ['text' => 'Journal', 'url' => 'journal.php'],
            ],
            // Cột 3: Social Media
            'social' => [
                ['text' => 'Tiktok', 'url' => 'https://tiktok.com', 'external' => true],
                ['text' => 'Instagram', 'url' => 'https://instagram.com', 'external' => true],
                ['text' => 'Facebook', 'url' => 'https://facebook.com', 'external' => true],
            ],
        ];
    }

    /**
     * Lấy dữ liệu cho phần Copyright.
     * @return array
     */
    public static function getCopyrightData(): array {
        return [
            'brand_title' => 'CANDY CRUNCH',
            'fda_note' => 'These statements have not been evaluated by the FDA.',
            'copyright_owner' => 'Innerwork',
            'designer' => 'Group H',
        ];
    }
}
?>