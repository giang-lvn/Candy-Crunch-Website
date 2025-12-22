document.addEventListener('DOMContentLoaded', () => {
    // MỞ WISHLIST
    const openWishlistBtn = document.getElementById('openWishlistBtn');
    const wishlistOverlay = document.getElementById('wishlist-overlay');

    if (openWishlistBtn && wishlistOverlay) {
        openWishlistBtn.addEventListener('click', (e) => {
            e.preventDefault();
            wishlistOverlay.classList.remove('hidden');
        });
    }

    // ĐÓNG WISHLIST (giả sử có nút close với class .wishlist-close)
    const closeWishlistBtn = document.querySelector('.wishlist-close');
    if (closeWishlistBtn && wishlistOverlay) {
        closeWishlistBtn.addEventListener('click', () => {
            wishlistOverlay.classList.add('hidden');
        });
    }

    // ĐÓNG KHI CLICK NGOÀI
    if (wishlistOverlay) {
        wishlistOverlay.addEventListener('click', (e) => {
            if (e.target === wishlistOverlay) {
                wishlistOverlay.classList.add('hidden');
            }
        });
    }

    initWishlistEvents();
});

/* =========================
   INIT
========================= */
function initWishlistEvents() {
    handleRemoveWishlist();
    handleAddToCartFromWishlist();
}

/* =========================
   REMOVE FROM WISHLIST
========================= */
function handleRemoveWishlist() {
    const overlay = document.getElementById('wishlist-overlay');
    if (!overlay) return;
    const removeButtons = overlay.querySelectorAll('.remove-product');

    removeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const skuid = this.dataset.skuid;

            if (!skuid) return;

            fetch('../../../controllers/website/wishlistcontroller.php?action=remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'skuid=' + encodeURIComponent(skuid)
            })
                .then(response => response.text())
                .then(() => {
                    const productItem = this.closest('.wishlist-product-item');

                    if (productItem) {
                        productItem.remove();
                    }

                    checkEmptyWishlist();
                })
                .catch(error => {
                    console.error('Remove wishlist error:', error);
                });
        });
    });
}

/* =========================
   ADD TO CART FROM WISHLIST
========================= */
function handleAddToCartFromWishlist() {
    const overlay = document.getElementById('wishlist-overlay');
    if (!overlay) return;
    const addButtons = overlay.querySelectorAll('.wishlist-add');

    addButtons.forEach(button => {
        button.addEventListener('click', function () {
            const skuid = this.dataset.skuid;

            if (!skuid) return;

            fetch('../../../controllers/website/cartcontroller.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'skuid=' + encodeURIComponent(skuid) + '&quantity=1'
            })
                .then(response => response.text())
                //.then(() => {
                // Sau khi add vào cart → remove khỏi wishlist luôn
                //removeWishlistItemBySku(skuid);
                //})
                .catch(error => {
                    console.error('Add to cart error:', error);
                });
        });
    });
}

/* =========================
   REMOVE ITEM FROM DOM BY SKUID
========================= */
function removeWishlistItemBySku(skuid) {
    const item = document.querySelector(
        `.wishlist-product-item .remove-product[data-skuid="${skuid}"]`
    );

    if (!item) return;

    const productItem = item.closest('.wishlist-product-item');

    if (productItem) {
        productItem.remove();
    }

    checkEmptyWishlist();
}

/* =========================
   CHECK EMPTY WISHLIST STATE
========================= */
function checkEmptyWishlist() {
    const overlay = document.getElementById('wishlist-overlay');
    if (!overlay) return;

    const productList = overlay.querySelector('.product-list');
    const hasProduct = overlay.querySelector('.wishlist-product-item');

    if (!hasProduct && productList) {
        productList.innerHTML = `
            <div class="empty-cart" style="text-align: left; color: var(--text-black); font: var(--body-medium); align-self: stretch; align-items: flex-start;" >
                <p> Your wishlist is empty.</p>
            </div>
        `;
    }
}

/* =========================
   DYNAMIC UPDATE LOGIC
========================= */
document.addEventListener('wishlist-updated', () => {
    loadWishlistData();
});

function loadWishlistData() {
    fetch(`/Candy-Crunch-Website/controllers/website/WishlistController.php?action=get_json&t=${new Date().getTime()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderWishlistItems(data.items);
            }
        })
        .catch(error => console.error('Error loading wishlist:', error));
}

function renderWishlistItems(items) {
    const overlay = document.getElementById('wishlist-overlay');
    const container = overlay ? overlay.querySelector('.cart-product') : null;
    if (!container) return;

    if (!items || items.length === 0) {
        container.innerHTML = '<p class="empty-cart">Your wishlist is empty.</p>';
        return;
    }

    let html = '<div class="cart-has-product"><div class="product-list">';

    items.forEach(item => {
        // Format price
        const originalPrice = new Intl.NumberFormat('vi-VN').format(item.OriginalPrice);
        const promotionPrice = item.PromotionPrice ? new Intl.NumberFormat('vi-VN').format(item.PromotionPrice) : null;
        const displayPrice = promotionPrice || originalPrice;

        html += `
            <div class="wishlist-product-item">
                <div class="wishlist-product-left">
                    <img class="product-image" src="${item.Image}" alt="${item.ProductName}" />
                    <div class="product-info">
                        <h4 class="product-name">${item.ProductName}</h4>
                        <div class="product-meta">
                            <button class="product-attribute" disabled>
                                ${item.Attribute}
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="8" viewBox="0 0 13 8" fill="none">
                                    <path d="M12.8 0.8C12.8 1.01667 12.7208 1.20417 12.5625 1.3625L6.9625 6.9625C6.80417 7.12083 6.61667 7.2 6.4 7.2C6.18333 7.2 5.99583 7.12083 5.8375 6.9625L0.2375 1.3625C0.0791667 1.20417 0 1.01667 0 0.8C0 0.583333 0.0791667 0.395833 0.2375 0.2375C0.395833 0.0791667 0.583333 0 0.8 0H12C12.2167 0 12.4042 0.0791667 12.5625 0.2375C12.7208 0.395833 12.8 0.583333 12.8 0.8Z" fill="#9E9E9E" />
                                </svg>
                            </button>
                        </div>
                        <div class="upsell-price">
                             ${promotionPrice ? `<span class="price-old">${originalPrice} VND</span>` : ''}
                            <span class="price-new">${displayPrice} VND</span>
                        </div>
                    </div>
                </div>
                <div class="wishlist-product-right">
                    <button class="remove-product" data-skuid="${item.SKUID}" aria-label="Remove product">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button class="wishlist-add" data-skuid="${item.SKUID}" aria-label="Wishlist-Add to cart">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <mask id="path-1-inside-1_1663_5327_${item.SKUID}" fill="white">
                                <path d="M10.3085 13.0568C10.1402 13.0568 9.97883 13.1236 9.85984 13.2426C9.74085 13.3616 9.67401 13.523 9.67401 13.6912C9.67401 13.8595 9.74085 14.0209 9.85984 14.1399C9.97883 14.2589 10.1402 14.3257 10.3085 14.3257H13.6923C13.8606 14.3257 14.0219 14.2589 14.1409 14.1399C14.2599 14.0209 14.3268 13.8595 14.3268 13.6912C14.3268 13.523 14.2599 13.3616 14.1409 13.2426C14.0219 13.1236 13.8606 13.0568 13.6923 13.0568H10.3085Z" />
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.2549 3.81893C14.4053 3.74371 14.5795 3.73131 14.739 3.78445C14.8986 3.83759 15.0306 3.95193 15.1059 4.10232L16.6396 7.16976C17.0011 7.18724 17.331 7.21713 17.6294 7.25943C18.5227 7.38717 19.2621 7.66295 19.7874 8.31265C20.3128 8.96234 20.4278 9.74316 20.366 10.6433C20.3068 11.5154 20.07 12.616 19.7756 13.9907L19.394 15.7731C19.1952 16.7011 19.0337 17.4532 18.8306 18.0403C18.6191 18.6536 18.34 19.157 17.8646 19.5419C17.3891 19.9268 16.8376 20.0934 16.1946 20.1721C15.5771 20.2474 14.8073 20.2474 13.8598 20.2474H10.141C9.19181 20.2474 8.42284 20.2474 7.80529 20.1721C7.16237 20.0934 6.6108 19.9268 6.13538 19.5419C5.65995 19.157 5.38078 18.6536 5.16929 18.0411C4.96626 17.4532 4.80553 16.7011 4.60589 15.774L4.22436 13.9915C3.92997 12.616 3.69395 11.5154 3.63388 10.6433C3.57213 9.74316 3.68718 8.96319 4.21252 8.31265C4.73701 7.66295 5.47638 7.38717 6.36971 7.25943C6.66861 7.2177 6.99853 7.18781 7.35947 7.16976L8.89573 4.10232C8.97167 3.95312 9.10351 3.83998 9.26251 3.78757C9.42151 3.73515 9.59478 3.7477 9.74457 3.82248C9.89435 3.89726 10.0085 4.02821 10.0622 4.1868C10.1158 4.34538 10.1046 4.51875 10.031 4.66911L8.79591 7.13677C9.10383 7.13507 9.42784 7.13451 9.76791 7.13508H14.2329C14.5729 7.13508 14.8969 7.13564 15.2049 7.13677L13.9706 4.66911C13.8954 4.51867 13.883 4.34452 13.9361 4.18493C13.9893 4.02535 14.1036 3.8934 14.254 3.81808M6.69794 8.49537L6.35702 9.17721C6.31903 9.25184 6.2962 9.33324 6.28982 9.41673C6.28345 9.50022 6.29367 9.58415 6.31988 9.66368C6.3461 9.7432 6.3878 9.81675 6.44258 9.88008C6.49736 9.94341 6.56413 9.99527 6.63905 10.0327C6.71396 10.0701 6.79554 10.0923 6.87908 10.098C6.96262 10.1037 7.04646 10.0929 7.12578 10.066C7.2051 10.0392 7.27832 9.99692 7.34123 9.94165C7.40413 9.88638 7.45547 9.81921 7.49229 9.744L8.15806 8.41247C8.64025 8.40401 9.19012 8.40316 9.81951 8.40316H14.1813C14.8107 8.40316 15.3605 8.40316 15.8427 8.41162L16.5085 9.744C16.5844 9.8932 16.7163 10.0063 16.8753 10.0588C17.0343 10.1112 17.2075 10.0986 17.3573 10.0238C17.5071 9.94907 17.6213 9.81811 17.6749 9.65953C17.7286 9.50094 17.7174 9.32758 17.6438 9.17721L17.3028 8.49537L17.45 8.51483C18.1979 8.62227 18.5642 8.81768 18.801 9.10954C19.0337 9.39716 19.1479 9.78884 19.1022 10.5181H4.89859C4.85291 9.78884 4.96711 9.39716 5.19975 9.10954C5.43662 8.81768 5.80291 8.62227 6.55074 8.51483L6.69794 8.49537ZM5.47638 13.7758C5.33094 13.1152 5.19529 12.4525 5.06947 11.7878H18.9313C18.805 12.4524 18.669 13.1152 18.5236 13.7758L18.1615 15.4677C17.9517 16.4448 17.8062 17.1207 17.6311 17.6266C17.4619 18.1173 17.2893 18.3761 17.0668 18.5555C16.8452 18.7348 16.555 18.8499 16.0415 18.9125C15.5094 18.9776 14.8174 18.9785 13.8183 18.9785H10.1816C9.18335 18.9785 8.49136 18.9776 7.95926 18.9125C7.44492 18.8499 7.1556 18.7348 6.93396 18.5555C6.71147 18.3761 6.53805 18.1164 6.36971 17.6266C6.19459 17.1207 6.04824 16.4448 5.83929 15.4677L5.47638 13.7758Z" fill="#017E6A" mask="url(#path-1-inside-1_1663_5327_${item.SKUID})" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
    });

    html += '</div></div>';

    container.innerHTML = html;

    // Re-initialize events for new elements
    initWishlistEvents();
}
