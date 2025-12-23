/**
 * Featured Products Manager
 * Xử lý hiển thị sản phẩm nổi bật và các chức năng Add to Cart, Wishlist
 */

class FeaturedProductsManager {
    constructor() {
        this.API_BASE = '/Candy-Crunch-Website/index.php?controller=featured';
        this.products = [];
        this.colorClasses = ['green', 'red', 'yellow', 'pink'];
        this.init();
    }

    init() {
        this.loadFeaturedProducts();
    }

    /**
     * Tải danh sách sản phẩm nổi bật từ API
     */
    async loadFeaturedProducts() {
        try {
            const response = await fetch(`${this.API_BASE}&limit=10`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.products) {
                this.products = data.products;
                this.renderProducts();
            }
        } catch (error) {
            console.error('Error loading featured products:', error);
        }
    }

    /**
     * Render sản phẩm vào carousel
     */
    renderProducts() {
        const track = document.querySelector('.featured-carousel-track');
        if (!track) return;

        // Xóa nội dung cũ
        track.innerHTML = '';

        // Render sản phẩm gốc
        this.products.forEach((product, index) => {
            const card = this.createProductCard(product, index);
            track.appendChild(card);
        });

        // Duplicate products cho infinite scroll
        this.products.forEach((product, index) => {
            const card = this.createProductCard(product, index + this.products.length, true);
            track.appendChild(card);
        });

        // Initialize carousel animation after products are rendered
        // Small delay to ensure DOM is fully updated
        setTimeout(() => {
            if (typeof window.initFeaturedCarousel === 'function') {
                console.log('Featured products loaded, initializing carousel...');
                window.initFeaturedCarousel();
            } else {
                console.warn('initFeaturedCarousel not available');
            }
        }, 100);
    }

    /**
     * Tạo card sản phẩm
     */
    createProductCard(product, index, isDuplicate = false) {
        const colorClass = this.colorClasses[index % this.colorClasses.length];
        const patternId = `scallop-${colorClass}-${isDuplicate ? 'dup-' : ''}${index}`;

        const card = document.createElement('div');
        card.className = `featured-card featured-card-${colorClass}`;
        card.dataset.productId = product.id;
        card.dataset.skuId = product.skuId || '';

        // Truncate description if too long
        const description = product.description
            ? (product.description.length > 60 ? product.description.substring(0, 60) + '...' : product.description)
            : 'Delicious candy for everyone';

        card.innerHTML = `
            <div class="featured-card-slideup">
                <svg viewBox="0 0 280 30" preserveAspectRatio="none" class="scalloped-edge">
                    <defs>
                        <pattern id="${patternId}" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                            <circle cx="15" cy="30" r="25" fill="currentColor" />
                        </pattern>
                    </defs>
                    <rect width="280" height="30" fill="url(#${patternId})" />
                </svg>
                <h3 class="featured-card-title">${this.escapeHtml(product.name)}</h3>
                <div class="featured-card-content">
                    <p class="featured-card-description">${this.escapeHtml(description)}</p>
                    <div class="featured-card-buttons">
                        <button class="btn-add-cart" data-product-id="${product.id}" data-sku-id="${product.skuId || ''}">Add to cart</button>
                        <button class="btn-wishlist" data-product-id="${product.id}">Wishlist</button>
                    </div>
                </div>
            </div>
        `;

        // Set background image if available
        if (product.image) {
            card.style.backgroundImage = `url('${product.image}')`;
            card.style.backgroundSize = 'cover';
            card.style.backgroundPosition = 'center';
        }

        // Setup event listeners
        this.setupCardEventListeners(card, product);

        return card;
    }

    /**
     * Setup event listeners cho card
     */
    setupCardEventListeners(card, product) {
        // Add to Cart button
        const addToCartBtn = card.querySelector('.btn-add-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.addToCart(product, addToCartBtn);
            });
        }

        // Wishlist button
        const wishlistBtn = card.querySelector('.btn-wishlist');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleWishlist(product, wishlistBtn);
            });
        }

        // Click on card navigates to product detail
        card.addEventListener('click', (e) => {
            if (e.target.closest('.btn-add-cart') || e.target.closest('.btn-wishlist')) {
                return;
            }
            const productDetailUrl = `/Candy-Crunch-Website/index.php?controller=productdetail&productId=${product.id}`;
            window.location.href = productDetailUrl;
        });
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    async addToCart(product, button) {
        const skuId = product.skuId;

        if (!skuId) {
            this.showNotification('Product variant not found', 'warning');
            return;
        }

        // Animation
        this.animateButton(button);

        // Loading state
        const originalText = button.innerHTML;
        button.innerHTML = '<span>...</span>';
        button.disabled = true;

        try {
            const response = await fetch('/Candy-Crunch-Website/index.php?controller=cart&action=handleAddToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ skuid: skuId, quantity: 1 })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Product added to cart!', 'success');

                // Update cart count in header
                const cartCountEl = document.getElementById('cartCount');
                if (cartCountEl) {
                    cartCountEl.innerText = result.cartCount;
                }

                // Update cart panel
                const cartPanel = document.querySelector('.cart-panel');
                if (cartPanel && result.html) {
                    cartPanel.outerHTML = result.html;

                    // Re-bind cart events
                    if (window.bindCartEvents) {
                        window.bindCartEvents();
                    }
                }
            } else {
                if (result.redirect) {
                    this.showNotification(result.message || 'Please login to continue', 'warning');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    this.showNotification(result.message || 'Cannot add product to cart', 'warning');
                }
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            this.showNotification('Cannot add product to cart', 'error');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }

    /**
     * Toggle wishlist
     */
    async toggleWishlist(product, button) {
        this.animateButton(button);

        try {
            const response = await fetch('/Candy-Crunch-Website/controllers/website/wishlistcontroller.php?action=toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: product.id })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');

                // Dispatch event for wishlist update
                document.dispatchEvent(new CustomEvent('wishlist-updated'));
            } else {
                if (result.redirect) {
                    this.showNotification('Please login to use wishlist', 'warning');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    this.showNotification(result.message || 'Error updating wishlist', 'warning');
                }
            }
        } catch (error) {
            console.error('Wishlist error:', error);
            this.showNotification('Cannot update wishlist', 'error');
        }
    }

    /**
     * Animate button
     */
    animateButton(button) {
        if (!button) return;
        button.style.transform = 'scale(0.95)';
        button.style.opacity = '0.7';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
            button.style.opacity = '1';
        }, 200);
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'success', duration = 3000) {
        // Remove existing notifications
        const existingNotification = document.querySelector('.featured-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `featured-notification featured-notification-${type}`;
        notification.innerHTML = `
            <div class="featured-notification-content">
                <span class="featured-notification-icon">${type === 'success' ? '✓' : type === 'warning' ? '⚠' : '✕'}</span>
                <span class="featured-notification-message">${message}</span>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 10000;
            padding: 16px 24px;
            border-radius: 12px;
            background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#ef4444'};
            color: white;
            font-family: var(--font-primary, 'Poppins', sans-serif);
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Auto remove
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Chỉ khởi tạo nếu đang ở trang landing
    if (document.querySelector('.featured-carousel-track')) {
        window.featuredProductsManager = new FeaturedProductsManager();
    }
});
