document.addEventListener('DOMContentLoaded', () => {
    // MỞ CART
    const openCartBtn = document.getElementById('openCartBtn');
    const cartOverlay = document.getElementById('cart-overlay');
    
    if (openCartBtn && cartOverlay) {
        openCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            cartOverlay.classList.remove('hidden');
        });
    }

    // ĐÓNG CART
    const closeCartBtn = document.querySelector('.cart-close');
    if (closeCartBtn && cartOverlay) {
        closeCartBtn.addEventListener('click', () => {
            cartOverlay.classList.add('hidden');
        });
    }

    // ĐÓNG KHI CLICK OVERLAY
    if (cartOverlay) {
        cartOverlay.addEventListener('click', (e) => {
            if (e.target === cartOverlay) {
                cartOverlay.classList.add('hidden');
            }
        });
    }

    bindCartEvents();
});

/* =========================
   EVENT BINDING
========================= */
function bindCartEvents() {
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', () => {
            updateQuantity(btn.dataset.skuid, 'increase');
        });
    });

    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', () => {
            updateQuantity(btn.dataset.skuid, 'decrease');
        });
    });

    document.querySelectorAll('.remove-product').forEach(btn => {
        btn.addEventListener('click', () => {
            removeCartItem(btn.dataset.skuid, btn);
        });
    });

    const promoBtn = document.querySelector('.promo-apply');
    if (promoBtn) {
        promoBtn.addEventListener('click', applyVoucher);
    }
}

/* =========================
   UPDATE QUANTITY
========================= */
function updateQuantity(skuid, action) {
    fetch('/index.php?controller=cart&action=updateQuantity', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skuid, action })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        if (data.cartEmpty) {
            renderEmptyCart();
        } else {
            updateCartUI(data);
        }
    });
}


/* =========================
   REMOVE ITEM
========================= */
function removeCartItem(skuid, btn) {
    if (!confirm('Remove this product from cart?')) return;

    fetch('/cart/remove-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skuid })
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            // Remove item DOM
            const productItem = btn.closest('.product-item');
            if (productItem) productItem.remove();

            if (data.cartEmpty) {
                renderEmptyCart();
            } else {
                updateCartUI(data);
            }
        })
        .catch(console.error);
}

/* =========================
   APPLY VOUCHER
========================= */
function applyVoucher() {
    const input = document.querySelector('.promo-input-field');
    const code = input.value.trim();
    if (!code) return;

    fetch('/cart/apply-voucher', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code })
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Invalid voucher');
                return;
            }
            updateCartUI(data);
        })
        .catch(console.error);
}

/* =========================
   UPDATE UI (TOTALS + QTY)
========================= */
function updateCartUI(data) {
    // Update quantity
    if (data.items) {
        data.items.forEach(item => {
            const qtySpan = document.querySelector(
                `.qty-plus[data-skuid="${item.SKUID}"]`
            )?.previousElementSibling;

            if (qtySpan) qtySpan.innerText = item.CartQuantity;
        });
    }

    // Update payment section
    updatePaymentRow('.payment-row.subtotal .value', data.subtotal);
    updatePaymentRow('.payment-row.discount .value', data.discount, true);
    updatePaymentRow('.payment-row.promo .value', data.promo, true);
    updatePaymentRow('.payment-total .value', data.total);
}

function updatePaymentRow(selector, value, isMinus = false) {
    const el = document.querySelector(selector);
    if (!el) return;

    const formatted = formatMoney(value);
    el.innerText = isMinus && value > 0 ? `-${formatted}` : formatted;
}

/* =========================
   EMPTY CART RENDER
========================= */
function renderEmptyCart() {
    const cartProduct = document.querySelector('.cart-product');
    if (!cartProduct) return;

    cartProduct.innerHTML = `
        <p class="empty-cart">Your cart is empty.</p>
    `;

    const paymentSection = document.querySelector('.payment-section');
    if (paymentSection) paymentSection.remove();
}

/* =========================
   HELPERS
========================= */
function formatMoney(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
}

function addToCartFromUpsell(skuid) {
    fetch('/cart/handle-add-to-cart', {  
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skuid, quantity: 1 })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart!');
            location.reload();
        } else {
            alert(data.message || 'Failed to add product');
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred');
    });
}
