
// SORT SELECT
// ==============================

// Xoay ngược icon drop-down 180 độ khi select được focus

const sortWrapper = document.querySelector('.sort-select-wrapper');
const sortSelect = document.querySelector('.sort-select');

/* khi click để mở */
sortSelect.addEventListener('mousedown', () => {
  sortWrapper.classList.add('is-open');
});

/* khi chọn xong hoặc click ra ngoài */
sortSelect.addEventListener('change', () => {
  sortWrapper.classList.remove('is-open');
});

sortSelect.addEventListener('blur', () => {
  sortWrapper.classList.remove('is-open');
});
// ========================================



// RATING STAR
// ==============================


const stars = document.querySelectorAll('.rating-star-btn');
const ratingRow = document.querySelector('.rating-row');

let selectedRating = 0;

/* render sao */
function renderStars(rating) {
  stars.forEach(star => {
    const value = Number(star.dataset.value);
    star.classList.toggle('is-active', value <= rating);
  });
}

/* hover preview */
stars.forEach(star => {
  star.addEventListener('mouseenter', () => {
    renderStars(Number(star.dataset.value));
  });

  /* click toggle */
  star.addEventListener('click', () => {
    const value = Number(star.dataset.value);

    // nếu click lại đúng rating đang chọn → reset
    if (selectedRating === value) {
      selectedRating = 0;
    } else {
      selectedRating = value;
    }

    renderStars(selectedRating);
  });
});

/* rời chuột → quay về trạng thái đã chọn */
ratingRow.addEventListener('mouseleave', () => {
  renderStars(selectedRating);
});


// ========================================




// FILTER CHECKBOX và FILTER TAGS
// ==============================
const checkboxes = document.querySelectorAll('.filter-checkbox');
const tagContainer = document.querySelector('.filter-tags-list');

/* tạo tag */
function createFilterTag(value) {
  // tránh tạo trùng
  if (tagContainer.querySelector(`[data-tag="${value}"]`)) return;

  const tag = document.createElement('section');
  tag.className = 'filter-tag';
  tag.dataset.tag = value;

  tag.innerHTML = `
    <h4 class="filter-tag-title">${value}</h4>
    <button class="filter-tag-remove" type="button" aria-label="Remove ${value}">
      ×
    </button>
  `;

  // click ❌ → remove tag + uncheck checkbox
  tag.querySelector('.filter-tag-remove').addEventListener('click', () => {
    tag.remove();

    const checkbox = document.querySelector(
      `.filter-checkbox[data-filter="${value}"]`
    );
    if (checkbox) checkbox.checked = false;
  });

  tagContainer.appendChild(tag);
}

/* lắng nghe checkbox */
checkboxes.forEach((checkbox) => {
  checkbox.addEventListener('change', () => {
    const value = checkbox.dataset.filter;

    if (checkbox.checked) {
      createFilterTag(value);
    } else {
      const tag = tagContainer.querySelector(`[data-tag="${value}"]`);
      if (tag) tag.remove();
    }
  });
});

/* ========================================
  WISHLIST + CART
======================================== */
// ========================================
// ADD TO CART FROM SHOP PAGE
// ========================================
function addToCartFromShop(skuid) {
  if (!skuid) {
      showNotification('Thiếu thông tin sản phẩm', 'error');
      return;
  }

  fetch('/index.php?controller=cart&action=handleAddToCart', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
          skuid: skuid, 
          quantity: 1 
      })
  })
  .then(res => res.json())
  .then(data => {
      if (data.success) {
          showNotification('✓ Đã thêm vào giỏ hàng!', 'success');
          
          // Cập nhật số lượng cart badge nếu có
          if (data.cartCount) {
              updateCartBadge(data.cartCount);
          }
      } else {
          showNotification(data.message || 'Không thể thêm sản phẩm', 'error');
      }
  })
  .catch(err => {
      console.error('Error adding to cart:', err);
      showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
  });
}

// ========================================
// TOGGLE WISHLIST (ADD/REMOVE)
// ========================================
function toggleWishlist(productId, buttonElement) {
  if (!productId) {
      showNotification('Thiếu thông tin sản phẩm', 'error');
      return;
  }

  fetch('/index.php?controller=wishlist&action=toggle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
          product_id: productId 
      })
  })
  .then(res => res.json())
  .then(data => {
      if (data.success) {
          // Toggle class active
          if (data.action === 'added') {
              buttonElement.classList.add('active');
              showNotification('♥ Đã thêm vào wishlist!', 'success');
          } else if (data.action === 'removed') {
              buttonElement.classList.remove('active');
              showNotification('Đã xóa khỏi wishlist', 'success');
          }
      } else {
          showNotification(data.message || 'Có lỗi xảy ra', 'error');
      }
  })
  .catch(err => {
      console.error('Error toggling wishlist:', err);
      showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
  });
}

// ========================================
// HIỂN THỊ NOTIFICATION (TOAST)
// ========================================
function showNotification(message, type = 'success') {
  // Xóa toast cũ nếu có
  const existingToast = document.querySelector('.custom-toast');
  if (existingToast) {
      existingToast.remove();
  }

  // Tạo toast mới
  const toast = document.createElement('div');
  toast.className = `custom-toast toast-${type}`;
  toast.textContent = message;
  
  // Style cho toast
  const bgColor = type === 'success' ? '#10b981' : '#ef4444';
  toast.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${bgColor};
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      font-weight: 500;
      animation: slideInRight 0.3s ease;
  `;
  
  document.body.appendChild(toast);
  
  // Tự động ẩn sau 3 giây
  setTimeout(() => {
      toast.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ========================================
// CẬP NHẬT CART BADGE
// ========================================
function updateCartBadge(count) {
  const badge = document.querySelector('.cart-badge');
  if (badge) {
      badge.textContent = count;
      
      // Animation nhấp nháy
      badge.style.transform = 'scale(1.4)';
      setTimeout(() => {
          badge.style.transform = 'scale(1)';
      }, 200);
  }
}

// ========================================
// CSS ANIMATIONS
// ========================================
if (!document.getElementById('shop-toast-styles')) {
  const style = document.createElement('style');
  style.id = 'shop-toast-styles';
  style.textContent = `
      @keyframes slideInRight {
          from {
              transform: translateX(400px);
              opacity: 0;
          }
          to {
              transform: translateX(0);
              opacity: 1;
          }
      }
      
      @keyframes slideOutRight {
          from {
              transform: translateX(0);
              opacity: 1;
          }
          to {
              transform: translateX(400px);
              opacity: 0;
          }
      }
      
      .cart-badge {
          transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }
  `;
  document.head.appendChild(style);
}