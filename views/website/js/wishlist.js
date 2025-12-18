document.addEventListener('DOMContentLoaded', () => {
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
    const removeButtons = document.querySelectorAll('.remove-product');

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
    const addButtons = document.querySelectorAll('.wishlist-add');

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
    const productList = document.querySelector('.product-list');
    const hasProduct = document.querySelector('.wishlist-product-item');

    if (!hasProduct && productList) {
        productList.innerHTML = `
            <div class="cart-empty">
                Your wishlist is empty.
            </div>
        `;
    }
}
