
// SORT SELECT
// ==============================

// Xoay ngược icon drop-down 180 độ khi select được focus

document.addEventListener('DOMContentLoaded', function () {
    const sortWrapper = document.querySelector('.sort-select-wrapper');
    const sortSelect  = document.querySelector('.sort-select');
  
    if (!sortWrapper || !sortSelect) return;
  
    // Khi select được focus (user click mở dropdown) -> mũi tên xoay lên
    sortSelect.addEventListener('focus', () => {
      sortWrapper.classList.add('is-open');
    });
  
    // Khi select mất focus (dropdown đóng) -> mũi tên quay lại
    sortSelect.addEventListener('blur', () => {
      sortWrapper.classList.remove('is-open');
    });
  });