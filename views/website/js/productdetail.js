// ATTRIBUTE SELECT
// ==============================

// Xoay ngược icon drop-down 180 độ khi select được focus

const attributeSelectWrapper = document.querySelector('.attribute-select-wrapper');
const attributeSelect = document.querySelector('.attribute-select');

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
// ========================================