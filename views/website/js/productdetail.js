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

  // Cập nhật hình ảnh
  if (image && typeof ROOT !== 'undefined') {
    const mainImage = document.getElementById('main-image');
    if (mainImage) {
      mainImage.src = ROOT + '/views/website/img/product-img/' + image;
    }
  }
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

