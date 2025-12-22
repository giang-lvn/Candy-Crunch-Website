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

    // CHECKOUT BUTTON (Delegated Event)
    // Using delegation because the button might be replaced by AJAX updates
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.checkout-btn');
        if (btn) {
            e.preventDefault();
            window.location.href = '/Candy-Crunch-Website/views/website/php/checkout.php';
        }
    });

    bindCartEvents();
});

/* =========================
   EVENT BINDING
========================= */
window.bindCartEvents = bindCartEvents;

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

    document.querySelectorAll('.product-item .remove-product').forEach(btn => {
        btn.addEventListener('click', () => {
            removeCartItem(btn.dataset.skuid, btn);
        });
    });

    const promoBtn = document.querySelector('.promo-apply');
    if (promoBtn) {
        promoBtn.addEventListener('click', applyVoucher);
    }

    // Attribute change event
    document.querySelectorAll('.product-attribute-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const oldSkuId = e.target.dataset.oldSku;
            const newSkuId = e.target.value;
            changeAttribute(oldSkuId, newSkuId);
        });
    });
}

/* =========================
   CHANGE ATTRIBUTE
========================= */
function changeAttribute(oldSkuId, newSkuId) {
    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=changeAttribute', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ oldSkuId, newSkuId })
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Failed to change attribute');
                return;
            }
            // Refresh entire cart content via AJAX (partial view)
            refreshCartContent();
        })
        .catch(console.error);
}

// Function to refresh cart content (HTML) without page reload
function refreshCartContent() {
    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=getCartContent')
        .then(res => res.text())
        .then(html => {
            // Check if cart is empty message
            if (html.trim() === 'Cart empty') {
                renderEmptyCart();
                return;
            }

            // Replace content of cart-panel (keeping the overlay wrapper intact)
            // But cart_content.php contains <aside class="cart-panel"> ... </aside>
            // So we need to replace the aside element inside the overlay.

            const oldPanel = document.querySelector('.cart-panel');
            if (oldPanel) {
                oldPanel.outerHTML = html;
            } else {
                // If panel somehow missing, try append to overlay
                const overlay = document.getElementById('cart-overlay');
                if (overlay) overlay.innerHTML = html;
            }

            // Re-bind events for new elements
            bindCartEvents();

            // Re-close button logic (since it's inside the panel)
            const closeCartBtn = document.querySelector('.cart-close');
            const cartOverlay = document.getElementById('cart-overlay');
            if (closeCartBtn && cartOverlay) {
                closeCartBtn.addEventListener('click', () => {
                    cartOverlay.classList.add('hidden');
                });
            }

            // Checkout button is now handled via delegation (see top of file)
        })
        .catch(console.error);
}

/* =========================
   UPDATE QUANTITY
========================= */
function updateQuantity(skuid, action) {
    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=updateQuantity', {
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

    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=removeItem', {
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
/* =========================
   APPLY VOUCHER
========================= */
function applyVoucher(e) {
    const input = document.querySelector('.promo-input-field');
    const btn = e.target.closest('.promo-apply');
    const action = btn.dataset.action;

    let code = input.value.trim();

    if (action === 'remove') {
        code = ''; // Force empty to remove
    } else {
        if (!code) return;
    }

    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=applyVoucher', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code })
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Invalid voucher');

                // If failed, maybe revert UI? No need, backend handles logic.
                // But we might want to clear valid input if failed?
                // For now, keep as is.
                return;
            }
            // Success -> refresh cart content UI
            refreshCartContent();

            if (data.message) {
                // Optionally show toast or alert
                // alert(data.message);
            }
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

        // Update cart count in header
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = `(${data.items.length})`;
        }
        const cartCountEl = document.getElementById('cartCount');
        if (cartCountEl) {
            cartCountEl.innerText = data.items.length;
        }
    }

    // Update payment section
    updatePaymentRow('.payment-row.subtotal .value-payment', data.subtotal);
    updatePaymentRow('.payment-row.discount .value-payment', data.discount, true);
    updatePaymentRow('.payment-row.promo .value-payment', data.promo, true);
    updatePaymentRow('.payment-row.shippingfee .value-payment', data.shipping);
    updatePaymentRow('.payment-total .value-payment', data.total);

    // Update shipping progress
    updateShippingProgress(data.remainingForFreeShip);
}

function updateShippingProgress(remaining) {
    const freeShippingDiv = document.querySelector('.free-shipping');
    if (!freeShippingDiv) return;

    // 200,000 is threshold
    const threshold = 200000;

    if (remaining > 0) {
        freeShippingDiv.innerHTML = `
            <p>Spend <strong>${formatMoney(remaining)}</strong> more for FREE SHIPPING</p>
            <div class="shipping-bar">
                <span class="bar-yellow" style="width: ${100 - (remaining / threshold * 100)}%"></span>
                <span class="bar-green" style="width: 0%"></span>
            </div>
        `;
    } else {
        freeShippingDiv.innerHTML = `
            <p><strong>You've got FREE SHIPPING!</strong></p>
            <div class="shipping-bar">
                <span class="bar-yellow" style="width: 100%"></span>
                <span class="bar-green" style="width: 100%"></span>
            </div>
        `;
    }
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
    // Update cart count in title
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = '(0)';
    }
    const cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) {
        cartCountEl.innerText = '0';
    }

    // Clear cart products container - show empty message only
    const cartProduct = document.querySelector('.cart-product');
    if (cartProduct) {
        cartProduct.innerHTML = `<p class="empty-cart">Your cart is empty.</p>`;
    }

    // Keep payment section but reset all values to 0
    updatePaymentRow('.payment-row.subtotal .value-payment', 0);
    updatePaymentRow('.payment-row.discount .value-payment', 0);
    updatePaymentRow('.payment-row.promo .value-payment', 0);
    updatePaymentRow('.payment-row.shippingfee .value-payment', 0);
    updatePaymentRow('.payment-total .value-payment', 0);

    // Reset promo input
    const promoInput = document.querySelector('.promo-input-field');
    if (promoInput) {
        promoInput.value = '';
    }
    const promoBtn = document.querySelector('.promo-apply');
    if (promoBtn) {
        promoBtn.textContent = 'Apply';
        promoBtn.dataset.action = 'apply';
    }
}

/* =========================
   HELPERS
========================= */
function formatMoney(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
}

function addToCartFromUpsell(skuid) {
    fetch('/Candy-Crunch-Website/index.php?controller=cart&action=handleAddToCart', {
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
