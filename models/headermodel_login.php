<?php
// header với login/signup pages

class HeaderModel {
    
    /**
     * Lấy dữ liệu cho Menu điều hướng chính (Primary Links).
     * @return array
     */
    public static function getPrimaryLinks(): array {
        return [
            'Homepage' => ['url' => 'index.php', 'active' => false],
            'About us' => ['url' => 'about.php', 'active' => false],
            'Shop' => ['url' => 'shop.php', 'active' => false, 'dropdown' => true],
            'Checkout' => ['url' => 'checkout.php', 'active' => false],
            'Contact' => ['url' => 'contact.php', 'active' => false],
            'Policy' => ['url' => 'policy.php', 'active' => false],
        ];
    }
    
    /**
     * Lấy dữ liệu cho các nút xác thực (Login/Signup).
     * @return array
     */
    public static function getAuthButtonsData(): array {
        return [
            'login' => ['url' => 'login.php', 'text' => 'Log in', 'class' => 'btn-login'],
            'signup' => ['url' => 'signup.php', 'text' => 'Sign up', 'class' => 'btn-signup'],
        ];
    }

    /**
     * Lấy dữ liệu cho menu drop-down (các danh mục và sản phẩm nổi bật).
     * @return array
     */
    public static function getShopDropdownData(): array {
        return [
            'categories' => [
                'Hard Candy' => [
                    ['title' => 'Milk Coffee Candy', 'url' => 'product.php?id=milk-coffee-candy', 'image' => 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?w=400', 'desc' => 'Rich and creamy coffee-flavored hard candy with a smooth finish'],
                    ['title' => 'Fruit Candy', 'url' => 'product.php?id=fruit-candy', 'image' => 'https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?w=400', 'desc' => 'Assorted tropical fruit flavors bursting with natural sweetness'],
                ],
                'Filled-Hard Candy' => [
                    ['title' => 'Caramel-Filled Coffee Candy', 'url' => 'product.php?id=caramel-coffee', 'image' => 'https://images.unsplash.com/photo-1568471173238-64ed8e7e9815?w=400', 'desc' => 'Coffee candy with gooey caramel center for double indulgence'],
                    ['title' => 'Milk-filled Coffee Candy', 'url' => 'product.php?id=milk-filled-coffee', 'image' => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=400', 'desc' => 'Smooth milk filling wrapped in coffee-flavored shell'],
                ],
                'Gummy' => [
                    ['title' => 'Wiggly Worm Gummies', 'url' => 'product.php?id=worm-gummies', 'image' => 'https://images.unsplash.com/photo-1582058091505-be6f8b6c1c88?w=400', 'desc' => 'Fun worm-shaped gummies in fruity flavors kids love'],
                    ['title' => 'Tiny Bear Gummies', 'url' => 'product.php?id=bear-gummies', 'image' => 'https://images.unsplash.com/photo-1625869016774-3a92be2ae2cd?w=400', 'desc' => 'Adorable bear-shaped gummies packed with fruit flavors'],
                ],
                'Chewing Gum' => [
                    ['title' => 'Blueberry Crisp Chewy', 'url' => 'product.php?id=blueberry-chewy', 'image' => 'https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?w=400', 'desc' => 'Crispy shell with chewy center, sweet blueberry taste'],
                    ['title' => 'Mint Crisp Chewy', 'url' => 'product.php?id=mint-chewy', 'image' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=400', 'desc' => 'Refreshing mint flavor for lasting fresh breath'],
                    ['title' => 'Cola Crisp Chewy', 'url' => 'product.php?id=cola-chewy', 'image' => 'https://images.unsplash.com/photo-1629203851122-3726ecdf080e?w=400', 'desc' => 'Classic cola taste in a fun chewing gum format'],
                    ['title' => 'Strawberry Soft Chewy', 'url' => 'product.php?id=strawberry-chewy', 'image' => 'https://images.unsplash.com/photo-1588548961454-2b8e051686bf?w=400', 'desc' => 'Soft and sweet strawberry chewing gum'],
                ],
                'Marshmallow' => [
                    ['title' => 'Vanilla Cotton Whirl', 'url' => 'product.php?id=vanilla-whirl', 'image' => 'https://images.unsplash.com/photo-1606312619070-d48b4a0a4f06?w=400', 'desc' => 'Cloud-like vanilla marshmallows that melt in your mouth'],
                    ['title' => 'Chocolate Cotton Whirl', 'url' => 'product.php?id=chocolate-whirl', 'image' => 'https://images.unsplash.com/photo-1612203985729-70726954388c?w=400', 'desc' => 'Rich chocolate marshmallows with fluffy texture'],
                    ['title' => 'Strawberry Cotton Whirl', 'url' => 'product.php?id=strawberry-whirl', 'image' => 'https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?w=400', 'desc' => 'Pink and fluffy strawberry marshmallow delights'],
                    ['title' => 'Blueberry Fluffy Cloud', 'url' => 'product.php?id=blueberry-cloud', 'image' => 'https://images.unsplash.com/photo-1559156596-d0fdb07da244?w=400', 'desc' => 'Light blueberry marshmallows with fruity burst'],
                ],
                'Collection' => [
                    ['title' => 'Tet Collection', 'url' => 'collection.php?id=tet', 'image' => 'https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?w=400', 'desc' => 'Special edition candy boxes for Lunar New Year celebrations'],
                    ['title' => 'Christmas Collection', 'url' => 'collection.php?id=christmas', 'image' => 'https://images.unsplash.com/photo-1512909006721-3d6018887383?w=400', 'desc' => 'Festive candy assortments for holiday season'],
                ],
            ],
            'featured' => [
                'image' => 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?w=400',
                'title' => 'Milk Coffee Candy',
                'desc' => 'Rich and creamy coffee-flavored hard candy with a smooth finish',
            ],
            'see_all_url' => 'shop.php',
        ];
    }
}
?>