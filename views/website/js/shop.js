
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

// ========================================