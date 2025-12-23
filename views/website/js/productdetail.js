// views/website/js/productdetail.js

// ATTRIBUTE SELECT
// ==============================

// Xoay ngược icon drop-down 180 độ khi select được focus

const attributeSelectWrapper = document.querySelector('.attribute-select-wrapper');
const attributeSelect = document.querySelector('.attribute-select');

if (attributeSelect) {
  /* khi click để mở */
  attributeSelect.addEventListener('mousedown', () => {
    attributeSelectWrapper.classList.add('is-open');
  });

  /* khi chọn xong hoặc click ra ngoài */
  attributeSelect.addEventListener('change', () => {
    attributeSelectWrapper.classList.remove('is-open');
  });

  attributeSelect.addEventListener('blur', () => {
    attributeSelectWrapper.classList.remove('is-open');
  });
}

//======DESCRIPTION======
//========================

// mở phần description khi click see more
const seeMoreBtn = document.querySelector('.description-section .btn-secondary-outline-small');
const descriptionText = document.querySelector('.description-section .description-text');

if (seeMoreBtn && descriptionText) {
  seeMoreBtn.addEventListener('click', () => {
    descriptionText.classList.toggle('collapsed');

    if (descriptionText.classList.contains('collapsed')) {
      seeMoreBtn.textContent = 'See more';
    } else {
      seeMoreBtn.textContent = 'See less';
    }
  });
}


//======PRODUCT DETAIL FUNCTIONS======
//====================================

// Biến toàn cục cho quantity (dùng var để tránh temporal dead zone)
var currentQuantity = 1;
var maxStock = 0;

// Khởi tạo maxStock từ DOM khi trang load
document.addEventListener('DOMContentLoaded', function () {
  const stockDisplay = document.getElementById('stock-display');
  if (stockDisplay) {
    const stockText = stockDisplay.textContent.trim();
    const stockMatch = stockText.match(/(\d+)/);
    if (stockMatch) {
      maxStock = parseInt(stockMatch[1]) || 0;
    }
  }
});

// Hàm thay đổi hình ảnh chính
function changeImage(src) {
  const mainImage = document.getElementById('main-image');
  if (mainImage) {
    mainImage.src = src;
  }
}

// Hàm cập nhật thông tin khi đổi SKU
function updateSkuInfo(skuId) {
  const select = document.getElementById('sku-select');
  if (!select) return;

  const selectedOption = select.options[select.selectedIndex];

  if (!selectedOption || !skuId) return;

  // Lấy data từ option attributes
  const price = selectedOption.dataset.price;
  const originalPrice = selectedOption.dataset.original;
  const stock = selectedOption.dataset.stock;
  const image = selectedOption.dataset.image;

  // Cập nhật giá
  const priceNew = document.getElementById('price-new');
  if (priceNew) {
    priceNew.textContent = formatPrice(price) + ' VND';
  }

  const priceOld = document.getElementById('price-old');
  if (priceOld && parseFloat(originalPrice) > parseFloat(price)) {
    priceOld.textContent = formatPrice(originalPrice) + ' VND';
    priceOld.style.display = 'inline';
  } else if (priceOld) {
    priceOld.style.display = 'none';
  }

  // Cập nhật tồn kho
  maxStock = parseInt(stock) || 0;
  const stockDisplay = document.getElementById('stock-display');
  if (stockDisplay) {
    stockDisplay.textContent = maxStock + ' in stock';
  }

  // Reset quantity nếu vượt quá stock
  if (currentQuantity > maxStock) {
    currentQuantity = maxStock > 0 ? maxStock : 1;
    const quantityDisplay = document.getElementById('quantity-display');
    if (quantityDisplay) {
      quantityDisplay.textContent = currentQuantity;
    }
  }

  // Cập nhật hình ảnh - DISABLED as per user request (Step 199)
  /*
  if (image && typeof ROOT !== 'undefined') {
    const mainImage = document.getElementById('main-image');
    if (mainImage) {
      mainImage.src = ROOT + '/views/website/img/product-img/' + image;
    }
  }
  */
}

// Format giá tiền
function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN').format(price);
}

// Tăng số lượng
function increaseQuantity() {
  if (currentQuantity < maxStock) {
    currentQuantity++;
    const quantityDisplay = document.getElementById('quantity-display');
    if (quantityDisplay) {
      quantityDisplay.textContent = currentQuantity;
    }
  }
}

// Giảm số lượng
function decreaseQuantity() {
  if (currentQuantity > 1) {
    currentQuantity--;
    const quantityDisplay = document.getElementById('quantity-display');
    if (quantityDisplay) {
      quantityDisplay.textContent = currentQuantity;
    }
  }
}

// Hàm set maxStock (gọi từ PHP inline script)
function setMaxStock(stock) {
  maxStock = parseInt(stock) || 0;
}


// ====== ADD TO CART & WISHLIST ======
// ====================================

// Show notification helper
function showNotification(message, type = 'info', duration = 3000) {
  // Create container if not exists
  let container = document.getElementById('notification-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
    container.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
    `;
    document.body.appendChild(container);
  }

  // Create toast
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  // Style based on type
  let bgColor = '#333';
  let icon = '';

  if (type === 'success') {
    bgColor = '#017E6A'; // Green
    icon = '✓';
  } else if (type === 'warning') {
    bgColor = '#FDBA06'; // Yellow
    icon = '!';
  } else if (type === 'error') {
    bgColor = '#e74c3c'; // Red
    icon = '✕';
  }

  toast.style.cssText = `
    background-color: ${bgColor};
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    min-width: 250px;
  `;

  toast.innerHTML = `
    <span style="font-weight: bold;">${icon}</span>
    <span>${message}</span>
  `;

  // Add to container
  container.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(0)';
  }, 10);

  // Remove after duration
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => {
      container.removeChild(toast);
    }, 300);
  }, duration);
}

// Add to Cart Function
async function addToCart() {
  const select = document.getElementById('sku-select');
  const skuId = select ? select.value : null;

  if (!skuId) {
    showNotification('Please select a product option/SKU', 'warning');
    return;
  }

  const btn = document.getElementById('btn-add-to-cart');
  const originalText = btn ? btn.innerText : 'Add to Cart';
  if (btn) {
    btn.innerText = 'Adding...';
    btn.disabled = true;
  }

  try {
    const response = await fetch('/Candy-Crunch-Website/index.php?controller=cart&action=handleAddToCart', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ skuid: skuId, quantity: currentQuantity })
    });

    const result = await response.json();

    if (result.success) {
      showNotification('Product added to cart!', 'success');

      // Update Header Cart Count
      const cartCountEl = document.getElementById('cartCount');
      if (cartCountEl) {
        cartCountEl.innerText = result.cartCount;
      }

      // Update Cart Panel HTML (Popup)
      const cartPanel = document.querySelector('.cart-panel');
      if (cartPanel && result.html) {
        cartPanel.outerHTML = result.html;

        // Re-bind events for new cart content
        if (window.bindCartEvents) {
          window.bindCartEvents();
        }

        // Re-attach close event listeners manually if needed
        const closeCartBtn = document.querySelector('.cart-close');
        const cartOverlay = document.getElementById('cart-overlay');
        if (closeCartBtn && cartOverlay) {
          closeCartBtn.addEventListener('click', () => {
            cartOverlay.classList.add('hidden');
          });
        }
      }

      // Reset quantity back to 1 if desired, or keep it
      // currentQuantity = 1; 
      // document.getElementById('quantity-display').innerText = currentQuantity;

    } else {
      if (result.redirect) {
        showNotification(result.message || 'Please login to continue', 'warning');
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        showNotification(result.message || 'Cannot add product to cart', 'warning');
      }
    }
  } catch (error) {
    console.error('Add to cart error:', error);
    showNotification('Cannot add product to cart', 'error');
  } finally {
    if (btn) {
      btn.innerText = originalText;
      btn.disabled = false;
    }
  }
}

// Add to Wishlist Function
async function addToWishlist() {
  if (typeof productId === 'undefined' || !productId) {
    console.error('Product ID not found');
    return;
  }

  const btn = document.getElementById('btn-wishlist');
  if (btn) {
    btn.style.transform = 'scale(0.9)';
    setTimeout(() => btn.style.transform = 'scale(1)', 200);
  }

  try {
    const response = await fetch('/Candy-Crunch-Website/controllers/website/wishlistcontroller.php?action=toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ product_id: productId })
    });

    const result = await response.json();

    if (result.success) {
      showNotification(result.message, 'success');

      // Dispatch event to update header wishlist (if applicable)
      document.dispatchEvent(new CustomEvent('wishlist-updated'));

    } else {
      if (result.redirect) {
        showNotification('Please login to use wishlist', 'warning');
        setTimeout(() => window.location.href = result.redirect, 1500);
      } else {
        showNotification(result.message || 'Error updating wishlist', 'warning');
      }
    }
  } catch (error) {
    console.error('Wishlist error:', error);
    showNotification('Cannot update wishlist', 'error');
  }
}

// Bind Events on Load
document.addEventListener('DOMContentLoaded', function () {
  const btnAddToCart = document.getElementById('btn-add-to-cart');
  if (btnAddToCart) {
    btnAddToCart.addEventListener('click', addToCart);
  }

  const btnWishlist = document.getElementById('btn-wishlist');
  if (btnWishlist) {
    btnWishlist.addEventListener('click', addToWishlist);
  }

  const btnBuyNow = document.getElementById('btn-buy-now');
  if (btnBuyNow) {
    btnBuyNow.addEventListener('click', buyNow);
  }
});

// Buy Now Function
async function buyNow() {
  const select = document.getElementById('sku-select');
  const skuId = select ? select.value : null;

  if (!skuId) {
    showNotification('Please select a product option/SKU', 'warning');
    return;
  }

  const btn = document.getElementById('btn-buy-now');
  const originalText = btn ? btn.innerText : 'Buy now';
  if (btn) {
    btn.innerText = 'Processing...';
    btn.disabled = true;
  }

  try {
    const response = await fetch('/Candy-Crunch-Website/index.php?controller=productdetail&action=buyNow', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ skuid: skuId, quantity: currentQuantity })
    });

    const result = await response.json();

    if (result.success) {
      window.location.href = result.redirect;
    } else {
      if (result.redirect) {
        showNotification(result.message || 'Please login to continue', 'warning');
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        showNotification(result.message || 'Cannot proceed to checkout', 'warning');
      }
    }
  } catch (error) {
    console.error('Buy now error:', error);
    showNotification('Error processing buy now request', 'error');
  } finally {
    if (btn) {
      btn.innerText = originalText;
      btn.disabled = false;
    }
  }
}

// Add Related Product to Cart
async function addRelatedToCart(skuId, btnElement) {
  if (!skuId) return;

  const originalText = btnElement ? btnElement.innerText : 'Add to Cart';
  if (btnElement) {
    btnElement.innerText = '...';
    btnElement.disabled = true;
  }

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
      showNotification('Product added to cart!', 'success');

      if (document.getElementById('cartCount')) {
        document.getElementById('cartCount').innerText = result.cartCount;
      }

      // Update Cart Panel HTML if returned
      const cartPanel = document.querySelector('.cart-panel');
      if (cartPanel && result.html) {
        cartPanel.outerHTML = result.html;
        if (window.bindCartEvents) window.bindCartEvents();

        const closeCartBtn = document.querySelector('.cart-close');
        const cartOverlay = document.getElementById('cart-overlay');
        if (closeCartBtn && cartOverlay) {
          closeCartBtn.addEventListener('click', () => {
            cartOverlay.classList.add('hidden');
          });
        }
      }

    } else {
      if (result.redirect) {
        showNotification(result.message || 'Please login to continue', 'warning');
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        showNotification(result.message || 'Cannot add product to cart', 'warning');
      }
    }
  } catch (error) {
    console.error(error);
    showNotification('Error adding to cart', 'error');
  } finally {
    if (btnElement) {
      btnElement.innerText = originalText;
      btnElement.disabled = false;
    }
  }
}

// Toggle Related Product Wishlist
async function toggleRelatedWishlist(prodId, btnElement) {
  if (!prodId) return;

  if (btnElement) {
    btnElement.style.transform = 'scale(0.9)';
    setTimeout(() => btnElement.style.transform = 'scale(1)', 200);
    // Optimistic UI update could be done here, but we'll wait for response
  }

  try {
    const response = await fetch('/Candy-Crunch-Website/controllers/website/wishlistcontroller.php?action=toggle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: prodId })
    });

    const result = await response.json();

    if (result.success) {
      showNotification(result.message, 'success');
      document.dispatchEvent(new CustomEvent('wishlist-updated'));

      // Update icon fill if needed (optional, simplistic toggle for now)
      // const path = btnElement.querySelector('path');
      // if(path) path.style.fill = result.isAdded ? '#017E6A' : 'none';

    } else {
      if (result.redirect) {
        showNotification('Please login to use wishlist', 'warning');
        setTimeout(() => window.location.href = result.redirect, 1500);
      } else {
        showNotification(result.message || 'Error updating wishlist', 'warning');
      }
    }
  } catch (error) {
    console.error(error);
    showNotification('Cannot update wishlist', 'error');
  }
}

