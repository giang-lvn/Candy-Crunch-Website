// SHOP FILTER & PRODUCT MANAGEMENT SYSTEM
// Advanced JavaScript for e-commerce filtering and pagination
// ============================================

class ShopManager {
  constructor() {
    // State management
    this.state = {
      filters: {
        productType: [],
        category: [],
        ingredients: [],
        flavor: [],
        rating: null
      },
      currentPage: 1,
      totalPages: 10,
      itemsPerPage: 9,
      sortBy: 'default',
      searchQuery: '',
      products: [],
      filteredProducts: [],
      activeTags: []
    };

    this.init();
  }

  // ============================================
  // INITIALIZATION
  // ============================================
  init() {
    this.loadProducts();
    this.setupFilterListeners();
    this.setupSearchAndSort();
    this.setupPagination();
    this.setupRatingFilter();
    this.initializeAnimations();
    this.setupKeyboardShortcuts();
    this.updateUI();
  }

  // ============================================
  // FILTER SYSTEM
  // ============================================
  setupFilterListeners() {
    // All filter checkboxes
    document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', (e) => this.handleFilterChange(e));
    });

    // Tag remove buttons
    document.addEventListener('click', (e) => {
      if (e.target.closest('.filter-tag-remove')) {
        this.removeFilterTag(e);
      }
    });
  }

  handleFilterChange(e) {
    const checkbox = e.target;
    const filterValue = checkbox.dataset.filter;
    const filterSection = checkbox.closest('.filter-section');
    const sectionTitle = filterSection.querySelector('.filter-title').textContent.trim();

    // Determine filter category
    let category = this.getFilterCategory(sectionTitle);

    if (checkbox.checked) {
      // Add filter
      if (!this.state.filters[category].includes(filterValue)) {
        this.state.filters[category].push(filterValue);
        this.addFilterTag(filterValue, category);
      }
    } else {
      // Remove filter
      this.state.filters[category] = this.state.filters[category].filter(
        f => f !== filterValue
      );
      this.removeFilterTagByValue(filterValue);
    }

    this.applyFilters();
    this.animateProductGrid();
  }

  getFilterCategory(sectionTitle) {
    const categoryMap = {
      'Product Type': 'productType',
      'Category': 'category',
      'Ingredients': 'ingredients',
      'Flavor': 'flavor'
    };
    return categoryMap[sectionTitle] || 'productType';
  }

  addFilterTag(value, category) {
    const tagsList = document.querySelector('.filter-tags-list');
    if (!tagsList) return;

    const tag = document.createElement('div');
    tag.className = 'filter-tag';
    tag.dataset.filterValue = value;
    tag.dataset.filterCategory = category;
    tag.innerHTML = `
      <span class="filter-tag-title">${value}</span>
      <button class="filter-tag-remove" aria-label="Remove ${value} filter">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M12 4L4 12M4 4L12 12" stroke="#689F38" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    `;

    tag.style.opacity = '0';
    tag.style.transform = 'scale(0.8)';
    tagsList.appendChild(tag);

    // Animate in
    requestAnimationFrame(() => {
      tag.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
      tag.style.opacity = '1';
      tag.style.transform = 'scale(1)';
    });

    this.state.activeTags.push({ value, category });
  }

  removeFilterTag(e) {
    e.preventDefault();
    const tag = e.target.closest('.filter-tag');
    const value = tag.dataset.filterValue;
    const category = tag.dataset.filterCategory;

    // Animate out
    tag.style.transform = 'scale(0.8)';
    tag.style.opacity = '0';

    setTimeout(() => {
      tag.remove();
    }, 300);

    // Uncheck corresponding checkbox
    const checkbox = document.querySelector(
      `.filter-checkbox[data-filter="${value}"]`
    );
    if (checkbox) {
      checkbox.checked = false;
    }

    // Remove from state
    this.state.filters[category] = this.state.filters[category].filter(
      f => f !== value
    );
    this.state.activeTags = this.state.activeTags.filter(
      t => t.value !== value
    );

    this.applyFilters();
  }

  removeFilterTagByValue(value) {
    const tag = document.querySelector(
      `.filter-tag[data-filter-value="${value}"]`
    );
    if (tag) {
      tag.style.transform = 'scale(0.8)';
      tag.style.opacity = '0';
      setTimeout(() => tag.remove(), 300);
    }

    this.state.activeTags = this.state.activeTags.filter(
      t => t.value !== value
    );
  }

  // ============================================
  // RATING FILTER
  // ============================================
  setupRatingFilter() {
    const ratingButtons = document.querySelectorAll('.rating-star-btn');
    let selectedRating = null;

    ratingButtons.forEach((btn, index) => {
      const rating = index + 1;

      // Hover effect
      btn.addEventListener('mouseenter', () => {
        this.highlightStars(rating, ratingButtons);
      });

      // Click to select
      btn.addEventListener('click', () => {
        if (selectedRating === rating) {
          // Deselect
          selectedRating = null;
          this.state.filters.rating = null;
          this.clearStars(ratingButtons);
        } else {
          // Select new rating
          selectedRating = rating;
          this.state.filters.rating = rating;
          this.selectStars(rating, ratingButtons);
        }
        this.applyFilters();
      });
    });

    // Reset on mouse leave
    const ratingRow = document.querySelector('.rating-row');
    if (ratingRow) {
      ratingRow.addEventListener('mouseleave', () => {
        if (selectedRating) {
          this.selectStars(selectedRating, ratingButtons);
        } else {
          this.clearStars(ratingButtons);
        }
      });
    }
  }

  highlightStars(rating, buttons) {
    buttons.forEach((btn, i) => {
      if (i < rating) {
        btn.classList.add('is-active');
      } else {
        btn.classList.remove('is-active');
      }
    });
  }

  selectStars(rating, buttons) {
    buttons.forEach((btn, i) => {
      if (i < rating) {
        btn.classList.add('is-active');
      } else {
        btn.classList.remove('is-active');
      }
    });
  }

  clearStars(buttons) {
    buttons.forEach(btn => btn.classList.remove('is-active'));
  }

  // ============================================
  // SEARCH & SORT
  // ============================================
  setupSearchAndSort() {
    // Search input
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          this.state.searchQuery = e.target.value.toLowerCase();
          this.applyFilters();
          this.showSearchFeedback(e.target.value);
        }, 300);
      });
    }

    // Sort dropdown - UPDATED TO AUTO FILTER
    const sortSelect = document.querySelector('.sort-select');
    const sortWrapper = document.querySelector('.sort-select-wrapper');

    if (sortSelect && sortWrapper) {
      // Auto-apply sort on change
      sortSelect.addEventListener('change', (e) => {
        this.state.sortBy = e.target.value;
        this.applyFilters(); // Auto apply filters

        // Show visual feedback
        const selectedText = e.target.selectedOptions[0].text;
        this.showNotification(`Sorted by: ${selectedText}`, 'success');

        // Add pulse animation to grid
        this.pulseProductGrid();
      });

      // Toggle dropdown icon animation
      sortSelect.addEventListener('focus', () => {
        sortWrapper.classList.add('is-open');
      });

      sortSelect.addEventListener('blur', () => {
        sortWrapper.classList.remove('is-open');
      });

      // Also add click handler for immediate visual feedback
      sortSelect.addEventListener('click', () => {
        sortWrapper.classList.toggle('is-open');
      });
    }
  }

  // Add pulse animation for product grid
  pulseProductGrid() {
    const grid = document.querySelector('.product-listing');
    if (grid) {
      grid.style.transform = 'scale(0.98)';
      grid.style.opacity = '0.7';

      setTimeout(() => {
        grid.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        grid.style.transform = 'scale(1)';
        grid.style.opacity = '1';
      }, 100);
    }
  }

  showSearchFeedback(query) {
    if (query.length > 0) {
      this.showNotification(`Searching for "${query}"...`, 'info', 2000);
    }
  }

  // ============================================
  // FILTER APPLICATION
  // ============================================
  applyFilters() {
    let filtered = [...this.state.products];

    // Lọc theo Search
    if (this.state.searchQuery) {
      filtered = filtered.filter(p => p.name.toLowerCase().includes(this.state.searchQuery));
    }

    // Lọc theo Category
    if (this.state.filters.category.length > 0) {
      filtered = filtered.filter(p => this.state.filters.category.includes(p.category));
    }

    // Lọc theo Ingredients (Khớp với field 'ingredient' từ Model)
    if (this.state.filters.ingredients.length > 0) {
      filtered = filtered.filter(p => this.state.filters.ingredients.includes(p.ingredient));
    }

    // Lọc theo Flavor (Khớp với field 'flavour' từ Model)
    if (this.state.filters.flavor.length > 0) {
      filtered = filtered.filter(p => this.state.filters.flavor.includes(p.flavour));
    }

    // Lọc theo Rating
    if (this.state.filters.rating) {
      filtered = filtered.filter(p => p.rating >= this.state.filters.rating);
    }

    this.state.filteredProducts = filtered;
    this.state.currentPage = 1;
    this.updateProductDisplay();
    this.updateResultText();
  }

  sortProducts(products) {
    const sorted = [...products];

    switch (this.state.sortBy) {
      case 'Newest':
        return sorted.sort((a, b) => b.id - a.id);
      case 'Price: Low to High':
        return sorted.sort((a, b) => a.price - b.price);
      case 'Price: High to Low':
        return sorted.sort((a, b) => b.price - a.price);
      default:
        return sorted;
    }
  }

  updateResultText() {
    const resultText = document.querySelector('.filter-result-text');
    if (resultText) {
      const total = this.state.filteredProducts.length;
      const start = (this.state.currentPage - 1) * this.state.itemsPerPage + 1;
      const end = Math.min(
        this.state.currentPage * this.state.itemsPerPage,
        total
      );
      resultText.textContent = `Showing ${start}-${end} of ${total} Products`;
    }
  }

  // ============================================
  // PRODUCT DISPLAY
  // ============================================
  updateProductDisplay() {
    const container = document.querySelector('.product-listing');
    if (!container) return;

    const start = (this.state.currentPage - 1) * this.state.itemsPerPage;
    const end = start + this.state.itemsPerPage;
    const pageProducts = this.state.filteredProducts.slice(start, end);

    // Fade out
    container.style.opacity = '0.3';

    setTimeout(() => {
      container.innerHTML = '';

      if (pageProducts.length === 0) {
        container.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
            <p style="font-size: 18px; color: var(--gray-600);">No products found</p>
            <p style="font-size: 14px; color: var(--gray-400); margin-top: 8px;">Try adjusting your filters</p>
          </div>
        `;
      } else {
        pageProducts.forEach((product, index) => {
          const card = this.createProductCard(product);
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
          container.appendChild(card);

          // Stagger animation
          setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, index * 50);
        });
      }

      // Fade in
      container.style.transition = 'opacity 0.4s ease';
      container.style.opacity = '1';
    }, 300);

    this.updatePagination();
  }

  createProductCard(product) {
    const card = document.createElement('article');
    card.className = 'product-card';
    // Handle null/empty image with a fallback - use a product image or empty placeholder
    const placeholderImg = '/Candy-Crunch-Website/views/website/img/product1.png';
    const imageUrl = product.image || placeholderImg;
    card.innerHTML = `
      <img class="product-image" src="${imageUrl}" alt="${product.name}" onerror="this.src='${placeholderImg}'" />
      <div class="product-info">
        <div class="product-top">
          <h4 class="product-name">${product.name}</h4>
          <div class="product-rating">
            <span class="rating-number">${product.rating}</span>
            <span class="rating-star">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12.0601 18.0795L7.45505 20.8312C7.25162 20.9596 7.03893 21.0147 6.817 20.9963C6.59508 20.978 6.40089 20.9046 6.23444 20.7762C6.06799 20.6478 5.93854 20.4874 5.84607 20.2952C5.7536 20.1029 5.7351 19.8872 5.79058 19.648L7.01119 14.4472L2.93325 10.9525C2.74831 10.7874 2.63291 10.5992 2.58704 10.3878C2.54118 10.1765 2.55486 9.97032 2.6281 9.76926C2.70133 9.5682 2.8123 9.40309 2.96099 9.27395C3.10968 9.1448 3.31312 9.06225 3.57129 9.02629L8.95307 8.5585L11.0337 3.66042C11.1261 3.44028 11.2696 3.27517 11.4642 3.1651C11.6588 3.05503 11.8574 3 12.0601 3C12.2628 3 12.4614 3.05503 12.656 3.1651C12.8505 3.27517 12.994 3.44028 13.0865 3.66042L15.1671 8.5585L20.5489 9.02629C20.8078 9.06298 21.0112 9.14553 21.1592 9.27395C21.3071 9.40236 21.4181 9.56746 21.4921 9.76926C21.566 9.97105 21.5801 10.1776 21.5342 10.3889C21.4884 10.6003 21.3726 10.7881 21.1869 10.9525L17.109 14.4472L18.3296 19.648C18.385 19.8865 18.3666 20.1022 18.2741 20.2952C18.1816 20.4882 18.0522 20.6485 17.8857 20.7762C17.7193 20.9039 17.5251 20.9772 17.3031 20.9963C17.0812 21.0154 16.8685 20.9604 16.6651 20.8312L12.0601 18.0795Z" fill="#FDBA06"/>
              </svg>
            </span>
          </div>
        </div>
        <div class="product-price">
          ${product.oldPrice ? `<span class="old-price">${this.formatPrice(product.oldPrice)}</span>` : ''}
          <span class="new-price">${this.formatPrice(product.price)}</span>
        </div>
        <div class="product-actions">
          <button class="btn-primary-small" data-product-id="${product.id}">Add to Cart</button>
          <button class="btn-icon-primary-outline-small-square" data-product-id="${product.id}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M12 1.75C12.862 1.75 13.6893 2.09266 14.2988 2.70215C14.9082 3.31162 15.25 4.13816 15.25 5V6.5L15.7354 6.51465C17.0204 6.55359 17.6495 6.69593 18.1074 7.07617V7.0752C18.4229 7.33727 18.6455 7.70404 18.8438 8.32617C19.0462 8.96152 19.205 9.80357 19.4268 10.9863L20.1768 14.9863C20.4881 16.6473 20.7102 17.8404 20.75 18.7549C20.7843 19.5431 20.6791 20.0519 20.4268 20.4385L20.3096 20.5967C19.9729 21.0021 19.4726 21.2418 18.5801 21.3691C17.6738 21.4984 16.4603 21.5 14.7705 21.5H9.23047C7.54006 21.5 6.32608 21.4984 5.41992 21.3691C4.52787 21.2418 4.02806 21.0021 3.69141 20.5967C3.35486 20.1913 3.2115 19.6557 3.25098 18.7549C3.29105 17.8403 3.51339 16.6474 3.82422 14.9863L4.57422 10.9863C4.79656 9.80388 4.95487 8.96178 5.15723 8.32617C5.35528 7.70411 5.57758 7.33712 5.89258 7.0752L5.89355 7.07617C6.35152 6.69593 6.98061 6.55359 8.26562 6.51465L8.75 6.5V5C8.75 4.13816 9.0928 3.31162 9.70215 2.70215C10.3115 2.09277 11.1382 1.75013 12 1.75Z" fill="#017E6A" stroke="#017E6A"/>
            </svg>
          </button>
        </div>
      </div>
    `;

    // Add event listeners
    const addToCartBtn = card.querySelector('.btn-primary-small');
    const wishlistBtn = card.querySelector('.btn-icon-primary-outline-small-square');

    addToCartBtn.addEventListener('click', () => this.addToCart(product));
    wishlistBtn.addEventListener('click', () => this.toggleWishlist(product));

    return card;
  }

  formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  }

  // ============================================
  // CART & WISHLIST
  // ============================================
  // ============================================
  // CART & WISHLIST (Đã kết nối Database)
  // ============================================

  async addToCart(product) {
    const button = event.currentTarget;
    this.animateAddToCart(button);

    const formData = new FormData();
    formData.append('sku_id', product.id);

    try {
      const response = await fetch('/Candy-Crunch-Website/controllers/website/shop_controller.php?action=add-to-cart', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Thông báo thành công từ ShopModel (Ví dụ: "Đã thêm vào giỏ hàng")
        this.showNotification('Add product to Cart!');
        return;
      }
      this.showNotification(result.message || 'Product is out of stock', 'warning');
    } catch (error) {
      // Lỗi hệ thống 
      this.showNotification('Cannot add product to cart', 'error');
    }
  }

  toggleWishlist(product) {
    // Giữ nguyên hiệu ứng UI cho wishlist
    console.log('Toggled wishlist:', product);
    this.showNotification(`${product.name} added to wishlist!`, 'success');

    const target = event.currentTarget;
    target.style.transform = 'scale(1.2)';
    setTimeout(() => {
      target.style.transform = 'scale(1)';
    }, 200);
  }

  animateAddToCart(button) {
    // Giữ nguyên hiệu ứng thu phóng nút khi nhấn
    if (!button) return;
    button.style.transform = 'scale(0.95)';
    button.style.opacity = '0.7';
    setTimeout(() => {
      button.style.transform = 'scale(1)';
      button.style.opacity = '1';
    }, 200);
  }

  // ============================================
  // PAGINATION
  // ============================================
  setupPagination() {
    const prevBtn = document.querySelector('.previous-page-btn');
    const nextBtn = document.querySelector('.next-page-btn');
    const jumpInput = document.querySelector('.page-jump-input');

    if (prevBtn) {
      prevBtn.addEventListener('click', () => this.goToPage(this.state.currentPage - 1));
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => this.goToPage(this.state.currentPage + 1));
    }

    if (jumpInput) {
      jumpInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          const page = parseInt(e.target.value);
          if (page >= 1 && page <= this.state.totalPages) {
            this.goToPage(page);
          }
        }
      });
    }

    // Page number buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('page-btn')) {
        this.goToPage(parseInt(e.target.textContent));
      }
    });
  }

  goToPage(page) {
    const totalPages = Math.ceil(
      this.state.filteredProducts.length / this.state.itemsPerPage
    );

    if (page < 1 || page > totalPages) return;

    this.state.currentPage = page;
    this.updateProductDisplay();
    this.scrollToTop();
  }

  updatePagination() {
    const totalPages = Math.ceil(
      this.state.filteredProducts.length / this.state.itemsPerPage
    );
    this.state.totalPages = totalPages;

    // Update page info
    const pageInfoCurrent = document.querySelector('.page-info-current');
    const pageInfoTotal = document.querySelector('.page-info-total');

    if (pageInfoCurrent) pageInfoCurrent.textContent = this.state.currentPage;
    if (pageInfoTotal) pageInfoTotal.textContent = totalPages;

    // Update page buttons
    this.renderPaginationButtons();
  }

  renderPaginationButtons() {
    const pageList = document.querySelector('.page-list');
    if (!pageList) return;

    const currentPage = this.state.currentPage;
    const totalPages = this.state.totalPages;

    // Clear all buttons
    pageList.innerHTML = '';

    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-item previous-page-btn';
    prevBtn.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
        <path d="M7.98438 1.23438C8.13068 1.08808 8.36817 1.08837 8.51465 1.23438C8.66109 1.38082 8.66109 1.6182 8.51465 1.76465L4.28027 6L8.51465 10.2344C8.66109 10.3808 8.66109 10.6182 8.51465 10.7646C8.3682 10.9111 8.13082 10.9111 7.98438 10.7646L3.48438 6.26465C3.33836 6.11817 3.33807 5.88068 3.48438 5.73438L7.98438 1.23438Z" fill="currentColor"/>
      </svg>
    `;
    prevBtn.disabled = currentPage === 1;
    if (currentPage === 1) prevBtn.style.opacity = '0.4';
    prevBtn.addEventListener('click', () => this.goToPage(currentPage - 1));
    pageList.appendChild(prevBtn);

    // Generate page numbers with smart logic
    const pages = this.getPageNumbers(currentPage, totalPages);

    pages.forEach((page) => {
      if (page === '...') {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'page-item page-ellipsis';
        ellipsis.textContent = '...';
        pageList.appendChild(ellipsis);
      } else {
        const btn = document.createElement('button');
        btn.className = 'page-item page-btn';
        if (page === currentPage) btn.classList.add('is-active');
        btn.textContent = page;
        btn.addEventListener('click', () => this.goToPage(page));
        pageList.appendChild(btn);
      }
    });

    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-item next-page-btn';
    nextBtn.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
        <path d="M3.48438 1.23438C3.63081 1.08794 3.86918 1.08795 4.01562 1.23438L8.51562 5.73438C8.66207 5.88082 8.66207 6.11918 8.51562 6.26562L4.01562 10.7656C3.86918 10.9121 3.63082 10.9121 3.48438 10.7656C3.33795 10.6192 3.33794 10.3808 3.48438 10.2344L7.71973 6L3.48438 1.76562C3.33795 1.61918 3.33794 1.38081 3.48438 1.23438Z" fill="currentColor"/>
      </svg>
    `;
    nextBtn.disabled = currentPage === totalPages;
    if (currentPage === totalPages) nextBtn.style.opacity = '0.4';
    nextBtn.addEventListener('click', () => this.goToPage(currentPage + 1));
    pageList.appendChild(nextBtn);
  }

  getPageNumbers(current, total) {
    if (total <= 1) return [1];
    if (total <= 5) {
      return Array.from({ length: total }, (_, i) => i + 1);
    }

    // Show first, last, current and neighbors
    const pages = [];

    // Always show first page
    pages.push(1);

    if (current > 3) {
      pages.push('...');
    }

    // Show pages around current
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i);
    }

    if (current < total - 2) {
      pages.push('...');
    }

    // Always show last page
    if (total > 1) {
      pages.push(total);
    }

    return pages;
  }

  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // ============================================
  // ANIMATIONS
  // ============================================
  initializeAnimations() {
    this.animateOnScroll();
    this.addHoverEffects();
  }

  animateOnScroll() {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      },
      { threshold: 0.1 }
    );

    document.querySelectorAll('.product-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.5s ease';
      observer.observe(card);
    });
  }

  addHoverEffects() {
    document.addEventListener('mouseover', (e) => {
      if (e.target.closest('.product-card')) {
        const card = e.target.closest('.product-card');
        card.style.transform = 'translateY(-4px)';
        card.style.boxShadow = '0 8px 24px rgba(0,0,0,0.12)';
      }
    });

    document.addEventListener('mouseout', (e) => {
      if (e.target.closest('.product-card')) {
        const card = e.target.closest('.product-card');
        card.style.transform = 'translateY(0)';
        card.style.boxShadow = '';
      }
    });
  }

  animateProductGrid() {
    const grid = document.querySelector('.product-listing');
    if (grid) {
      grid.style.animation = 'none';
      setTimeout(() => {
        grid.style.animation = 'fadeIn 0.4s ease';
      }, 10);
    }
  }

  // ============================================
  // NOTIFICATIONS
  // ============================================
  showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `shop-notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span class="notification-icon">${this.getNotificationIcon(type)}</span>
        <span class="notification-message">${message}</span>
      </div>
    `;

    Object.assign(notification.style, {
      position: 'fixed',
      top: '100px',
      right: '20px',
      padding: '16px 24px',
      borderRadius: '12px',
      backgroundColor: this.getNotificationColor(type),
      color: 'white',
      boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
      zIndex: '10000',
      animation: 'slideInRight 0.3s ease',
      fontFamily: 'Poppins, sans-serif',
      fontSize: '14px'
    });

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, duration);
  }

  getNotificationIcon(type) {
    const icons = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ'
    };
    return icons[type] || icons.info;
  }

  getNotificationColor(type) {
    const colors = {
      success: '#10b981',
      error: '#ef4444',
      warning: '#f59e0b',
      info: '#3b82f6'
    };
    return colors[type] || colors.info;
  }

  // ============================================
  // KEYBOARD SHORTCUTS
  // ============================================
  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Clear all filters: Ctrl+Shift+C
      if (e.ctrlKey && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        this.clearAllFilters();
      }

      // Focus search: Ctrl+K or Cmd+K
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.querySelector('.search-input')?.focus();
      }

      // Next page: Ctrl+→
      if (e.ctrlKey && e.key === 'ArrowRight') {
        e.preventDefault();
        this.goToPage(this.state.currentPage + 1);
      }

      // Previous page: Ctrl+←
      if (e.ctrlKey && e.key === 'ArrowLeft') {
        e.preventDefault();
        this.goToPage(this.state.currentPage - 1);
      }
    });
  }

  clearAllFilters() {
    // Uncheck all checkboxes
    document.querySelectorAll('.filter-checkbox:checked').forEach(cb => {
      cb.checked = false;
    });

    // Clear all tags
    document.querySelectorAll('.filter-tag').forEach(tag => tag.remove());

    // Reset state
    this.state.filters = {
      productType: [],
      category: [],
      ingredients: [],
      flavor: [],
      rating: null
    };
    this.state.activeTags = [];
    this.state.searchQuery = '';

    // Clear search input
    const searchInput = document.querySelector('.search-input');
    if (searchInput) searchInput.value = '';

    // Clear rating
    this.clearStars(document.querySelectorAll('.rating-star-btn'));

    this.applyFilters();
    this.showNotification('All filters cleared', 'info');
  }

  // ============================================
  // DATA MANAGEMENT
  // ============================================
  async loadProducts() {
    try {
      // Gọi đến file chứa ShopController của bạn
      const response = await fetch('/Candy-Crunch-Website/controllers/website/shop_controller.php');
      const data = await response.json();

      this.state.products = data;
      this.state.filteredProducts = [...this.state.products];
      this.applyFilters();
      this.updateUI();
    } catch (error) {
      console.error('Lỗi tải sản phẩm:', error);
      this.showNotification('Không thể tải dữ liệu sản phẩm', 'error');
    }
  }
  updateUI() {
    const container = document.getElementById('productContainer');
    if (!container) {
      console.error("Không tìm thấy container có id 'productContainer'");
      return;
    }
    container.innerHTML = '';

    // Nếu filteredProducts rỗng, hãy hiện thông báo
    if (this.state.filteredProducts.length === 0) {
      container.innerHTML = '<p>No products found</p>';
      return;
    }

    this.state.filteredProducts.forEach(product => {
      const card = this.createProductCard(product);
      container.appendChild(card);
    });
  }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// ============================================
// ADD ANIMATIONS CSS
// ============================================
const animationStyles = document.createElement('style');
animationStyles.textContent = `
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
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
      transform: translateX(100%);
      opacity: 0;
    }
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .notification-content {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .notification-icon {
    font-size: 20px;
    font-weight: bold;
  }

  .product-card {
    transition: all 0.3s ease;
  }

  .filter-tag {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .btn-primary-small,
  .btn-icon-primary-outline-small-square {
    transition: all 0.2s ease;
  }

  .btn-primary-small:active {
    transform: scale(0.95);
  }

  .btn-icon-primary-outline-small-square:hover {
    transform: scale(1.1);
  }

  .rating-star-btn {
    transition: all 0.2s ease;
  }

  .rating-star-btn:hover {
    transform: scale(1.15);
  }
`;
document.head.appendChild(animationStyles);

// ============================================
// INITIALIZE ON DOM LOAD
// ============================================
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.shopManager = new ShopManager();
    console.log('🛍️ Shop Manager initialized successfully!');
  });
} else {
  window.shopManager = new ShopManager();
  console.log('🛍️ Shop Manager initialized successfully!');
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ShopManager;
}