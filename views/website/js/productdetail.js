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
// ========================================

function changeImage(src) {
  document.getElementById('main-image').src = src;
}

function updateSkuInfo() {
  const skuId = document.getElementById('sku-select').value;

  const formData = new FormData();
  formData.append('skuid', skuId);

  // Sửa lại đường dẫn fetch
  fetch('/Candy-Crunch-Website/views/website/php/productdetail.php?action=getSkuInfo', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error(data.error);
        return;
      }

      // Cập nhật hình ảnh chính
      if (data.image) {
        const mainImage = document.getElementById('main-image');
        if (mainImage) {
          mainImage.src = (typeof ROOT !== 'undefined' ? ROOT : '') + data.image;
        }
      }

      // Cập nhật giá
      if (data.price) {
        const newPrice = new Intl.NumberFormat('vi-VN').format(data.price.PromotionPrice);
        const oldPrice = new Intl.NumberFormat('vi-VN').format(data.price.OriginalPrice);

        const priceNewEl = document.getElementById('price-new');
        if (priceNewEl) priceNewEl.innerText = newPrice + ' VND';

        let oldPriceEl = document.getElementById('price-old');

        // Nếu có giảm giá
        if (parseInt(data.price.OriginalPrice) > parseInt(data.price.PromotionPrice)) {
          if (!oldPriceEl) {
            // Tạo element nếu chưa có
            oldPriceEl = document.createElement('span');
            oldPriceEl.className = 'old-price';
            oldPriceEl.id = 'price-old';
            priceNewEl.parentNode.insertBefore(oldPriceEl, priceNewEl.nextSibling);
          }
          oldPriceEl.innerText = oldPrice + ' VND';
          oldPriceEl.style.display = 'inline-block';
        } else {
          // Không giảm giá thì ẩn
          if (oldPriceEl) {
            oldPriceEl.style.display = 'none';
          }
        }
      }

      // Cập nhật tồn kho
      if (data.stock) {
        const stockEl = document.getElementById('stock-display');
        if (stockEl) stockEl.innerText = data.stock.Stock + ' in stock';
      }
    })
    .catch(error => console.error('Error:', error));
}