document.addEventListener('DOMContentLoaded', () => {

    /* =======================
       ELEMENTS
    ======================= */
    const cartOverlay = document.querySelector('.cart-overlay');
    const cartCloseBtn = document.querySelector('.cart-close');
    const cartCount = document.querySelector('.cart-count');

    const productList = document.querySelector('.product-list');
    const emptyCart = document.querySelector('.cart-empty');

    const subtotalEl = document.querySelector('.payment-row.subtotal .value');
    const discountEl = document.querySelector('.payment-row.discount .value');
    const promoEl = document.querySelector('.payment-row.promo .value');
    const totalEl = document.querySelector('.payment-total .value');

    const promoWrapper = document.querySelector('.promo-input');
    const promoInput = promoWrapper?.querySelector('input');
    const promoApplyBtn = promoWrapper?.querySelector('.promo-apply');

    /* =======================
       DATA (mock)
    ======================= */
    let cart = [
        {
            id: 1,
            name: 'Fruit-Filled Candy',
            price: 150000,
            salePrice: 120000,
            qty: 1
        }
    ];

    let promoDiscount = 0;

    /* =======================
       CART OPEN / CLOSE
    ======================= */
    cartCloseBtn?.addEventListener('click', () => {
        cartOverlay.classList.remove('active');
    });

    /* =======================
       UPDATE CART COUNT
    ======================= */
    function updateCartCount() {
        const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
        cartCount.textContent = `(${totalQty})`;
    }

    /* =======================
       CALCULATE TOTAL
    ======================= */
    function calculateTotals() {
        const subtotal = cart.reduce(
            (sum, item) => sum + item.salePrice * item.qty,
            0
        );

        const discount = promoDiscount;
        const total = Math.max(subtotal - discount, 0);

        subtotalEl.textContent = formatPrice(subtotal);
        discountEl.textContent = discount ? `-${formatPrice(discount)}` : '0 VND';
        promoEl.textContent = discount ? `-${formatPrice(discount)}` : '0 VND';
        totalEl.textContent = formatPrice(total);
    }

    /* =======================
       FORMAT PRICE
    ======================= */
    function formatPrice(value) {
        return value.toLocaleString('vi-VN') + ' VND';
    }

    /* =======================
       EMPTY CART STATE
    ======================= */
    function checkEmptyCart() {
        if (cart.length === 0) {
            productList.style.display = 'none';
            emptyCart.style.display = 'block';
        } else {
            productList.style.display = 'block';
            emptyCart.style.display = 'none';
        }
    }

    /* =======================
       QUANTITY CONTROL
    ======================= */
    productList?.addEventListener('click', (e) => {

        const itemEl = e.target.closest('.product-item');
        if (!itemEl) return;

        const id = Number(itemEl.dataset.id);
        const item = cart.find(p => p.id === id);

        if (e.target.matches('.quantity-control button')) {
            if (e.target.textContent === '+') {
                item.qty++;
            } else if (e.target.textContent === '-') {
                item.qty = Math.max(1, item.qty - 1);
            }

            itemEl.querySelector('.quantity-control span').textContent = item.qty;
            updateCartCount();
            calculateTotals();
        }

        if (e.target.closest('.remove-product')) {
            cart = cart.filter(p => p.id !== id);
            itemEl.remove();

            updateCartCount();
            calculateTotals();
            checkEmptyCart();
        }
    });

    /* =======================
       PROMO INPUT STATE
    ======================= */
    promoInput?.addEventListener('input', () => {
        promoWrapper.classList.toggle(
            'has-value',
            promoInput.value.trim() !== ''
        );
    });

    /* =======================
       APPLY PROMO CODE (DEMO)
    ======================= */
    promoApplyBtn?.addEventListener('click', () => {
        const code = promoInput.value.trim().toUpperCase();

        if (code === 'CANDY10') {
            promoDiscount = 10000;
            alert('Promo code applied!');
        } else if (code === 'FREESHIP') {
            promoDiscount = 20000;
            alert('Free shipping applied!');
        } else {
            promoDiscount = 0;
            alert('Invalid promo code');
        }

        calculateTotals();
    });

    /* =======================
       UPSELL ADD TO CART
    ======================= */
    document.querySelectorAll('.upsell-add').forEach(btn => {
        btn.addEventListener('click', () => {

            const upsellItem = {
                id: Date.now(),
                name: 'Upsell Candy',
                price: 100000,
                salePrice: 85000,
                qty: 1
            };

            cart.push(upsellItem);
            updateCartCount();
            calculateTotals();
            checkEmptyCart();

            alert('Product added to cart');
        });
    });

    /* =======================
       INIT
    ======================= */
    updateCartCount();
    calculateTotals();
    checkEmptyCart();

});
