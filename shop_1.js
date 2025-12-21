// SHOP FILTER & PRODUCT MANAGEMENT SYSTEM
// Client-side filtering with backend data
// ============================================

class ShopManager {
  constructor() {
    this.state = {
      filters: {
        search: '',
        category: [],
        ingredient: [],
        flavour: [],
        rating: null
      },
      currentPage: 1,
      totalPages: 1,
      itemsPerPage: 9,
      sortBy: 'name',
      allProducts: [], // Toàn bộ sản phẩm từ backend
      filteredProducts: [], // Sau khi filter
      displayProducts: [], // Sau khi phân trang
      activeTags: [],
      isLoading: false
    };

    this.API_BASE = '/Candy-Crunch-Website/controllers/website/shop_controller.php';
    this.init();
  }

  // ============================================
  // INITIALIZATION
  // ============================================
  init() {
    this.setupFilterListeners();
    this.setupSearchAndSort();
    this.setupPagination();
    this.setupRatingFilter();
    this.initializeAnimations();
    this.setupKeyboardShortcuts();
    this.loadAllProducts(); // Load tất cả sản phẩm 1 lần
  }

  // ============================================
  // API COMMUNICATION - LOAD ALL PRODUCTS
  // ============================================
  async loadAllProducts() {
    if (this.state.isLoading) return;
    
    this.state.isLoading = true;
    this.showLoadingState();

    try {
      // Load tất cả sản phẩm (không có filter, page lớn)
      const params = new URLSearchParams({
        per_page: 1000, // Lấy max products
        sort: 'name'
      });

      const response = await fetch(`${this.API_BASE}?${params.toString()}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      // Lưu toàn bộ sản phẩm
      this.state.allProducts = data.products || [];
      this.state.filteredProducts = [...this.state.allProducts];
      
      // Apply filters và display
      this.applyFilters();

    } catch (error) {
      console.error('Error loading products:', error);
      this.showNotification('Cannot load products. Please try again.', 'error');
      this.showErrorState();
    } finally {
      this.state.isLoading = false;
    }
  }

  showLoadingState() {
    const container = document.querySelector('.product-listing');
    if (!container) return;

    container.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 80px 20px;">
        <div class="loading-spinner" style="
          width: 50px; 
          height: 50px; 
          border: 4px solid #f3f3f3; 
          border-top: 4px solid #689F38;
          border-radius: 50%;
          animation: spin 1s linear infinite;
          margin: 0 auto 20px;
        "></div>
        <p style="font-size: 16px; color: var(--gray-600);">Loading products...</p>
      </div>
    `;
  }

  showErrorState() {
    const container = document.querySelector('.product-listing');
    if (!container) return;

    container.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 80px 20px;">
        <p style="font-size: 18px; color: #ef4444; margin-bottom: 10px;">⚠️ Error loading products</p>
        <p style="font-size: 14px; color: var(--gray-500);">Please check your connection and try again</p>
        <button onclick="window.shopManager.loadAllProducts()" 
                style="margin-top: 20px; padding: 10px 24px; background: #689F38; color: white; border: none; border-radius: 8px; cursor: pointer;">
          Retry
        </button>
      </div>
    `;
  }

  // ============================================
  // FILTER SYSTEM
  // ============================================
  setupFilterListeners() {
    document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', (e) => this.handleFilterChange(e));
    });

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

    let category = this.getFilterCategory(sectionTitle);

    if (checkbox.checked) {
      if (!this.state.filters[category].includes(filterValue)) {
        this.state.filters[category].push(filterValue);
        this.addFilterTag(filterValue, category);
      }
    } else {
      this.state.filters[category] = this.state.filters[category].filter(
        f => f !== filterValue
      );
      this.removeFilterTagByValue(filterValue);
    }

    this.state.currentPage = 1;
    this.applyFilters(); // Filter ở client-side
  }

  getFilterCategory(sectionTitle) {
    const categoryMap = {
      'Category': 'category',
      'Ingredients': 'ingredient',
      'Flavor': 'flavour',
      'Flavour': 'flavour'
    };
    return categoryMap[sectionTitle] || 'category';
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

    tag.style.transform = 'scale(0.8)';
    tag.style.opacity = '0';

    setTimeout(() => tag.remove(), 300);

    const checkbox = document.querySelector(
      `.filter-checkbox[data-filter="${value}"]`
    );
    if (checkbox) checkbox.checked = false;

    this.state.filters[category] = this.state.filters[category].filter(
      f => f !== value
    );
    this.state.activeTags = this.state.activeTags.filter(
      t => t.value !== value
    );

    this.state.currentPage = 1;
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
  // CLIENT-SIDE FILTERING
  // ============================================
  applyFilters() {
    let filtered = [...this.state.allProducts];

    // Filter by Search
    if (this.state.filters.search) {
      const search = this.state.filters.search.toLowerCase();
      filtered = filtered.filter(p => 
        p.name.toLowerCase().includes(search)
      );
    }

    // Filter by Category
    if (this.state.filters.category.length > 0) {
      filtered = filtered.filter(p => 
        this.state.filters.category.includes(p.category)
      );
    }

    // Filter by Ingredient
    if (this.state.filters.ingredient.length > 0) {
      filtered = filtered.filter(p => 
        this.state.filters.ingredient.some(ing => 
          p.ingredient && p.ingredient.toLowerCase().includes(ing.toLowerCase())
        )
      );
    }

    // Filter by Flavour
    if (this.state.filters.flavour.length > 0) {
      filtered = filtered.filter(p => 
        this.state.filters.flavour.some(flav => 
          p.flavour && p.flavour.toLowerCase().includes(flav.toLowerCase())
        )
      );
    }

    // Filter by Rating
    if (this.state.filters.rating) {
      filtered = filtered.filter(p => 
        p.rating >= this.state.filters.rating
      );
    }

    // Sort products
    filtered = this.sortProducts(filtered);

    this.state.filteredProducts = filtered;
    this.updatePaginationAndDisplay();
  }

  sortProducts(products) {
    const sorted = [...products];

    switch (this.state.sortBy) {
      case 'name':
        return sorted.sort((a, b) => a.name.localeCompare(b.name));
      case 'price_asc':
        return sorted.sort((a, b) => a.basePrice - b.basePrice);
      case 'price_desc':
        return sorted.sort((a, b) => b.basePrice - a.basePrice);
      case 'rating':
        return sorted.sort((a, b) => b.rating - a.rating);
      default:
        return sorted;
    }
  }

  updatePaginationAndDisplay() {
    const total = this.state.filteredProducts.length;
    this.state.totalPages = Math.ceil(total / this.state.itemsPerPage);

    // Get products for current page
    const start = (this.state.currentPage - 1) * this.state.itemsPerPage;
    const end = start + this.state.itemsPerPage;
    this.state.displayProducts = this.state.filteredProducts.slice(start, end);

    this.updateProductDisplay();
    this.updatePagination();
    this.updateResultText();
  }

  // ============================================
  // RATING FILTER
  // ============================================
  setupRatingFilter() {
    const ratingButtons = document.querySelectorAll('.rating-star-btn');
    let selectedRating = null;

    ratingButtons.forEach((btn, index) => {
      const rating = index + 1;

      btn.addEventListener('mouseenter', () => {
        this.highlightStars(rating, ratingButtons);
      });

      btn.addEventListener('click', () => {
        if (selectedRating === rating) {
          selectedRating = null;
          this.state.filters.rating = null;
          this.clearStars(ratingButtons);
        } else {
          selectedRating = rating;
          this.state.filters.rating = rating;
          this.selectStars(rating, ratingButtons);
        }
        this.state.currentPage = 1;
        this.applyFilters();
      });
    });

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
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          this.state.filters.search = e.target.value.trim();
          this.state.currentPage = 1;
          this.applyFilters();
          
          if (e.target.value) {
            this.showSearchFeedback(e.target.value);
          }
        }, 300);
      });
    }

    const sortSelect = document.querySelector('.sort-select');
    const sortWrapper = document.querySelector('.sort-select-wrapper');

    if (sortSelect && sortWrapper) {
      sortSelect.addEventListener('change', (e) => {
        const sortMap = {
          'Newest': 'name',
          'Price: Low to High': 'price_asc',
          'Price: High to Low': 'price_desc',
          'Rating': 'rating'
        };

        this.state.sortBy = sortMap[e.target.value] || 'name';
        this.state.currentPage = 1;
        this.applyFilters();

        const selectedText = e.target.selectedOptions[0].text;
        this.showNotification(`Sorted by: ${selectedText}`, 'success');
        this.pulseProductGrid();
      });

      sortSelect.addEventListener('focus', () => {
        sortWrapper.classList.add('is-open');
      });

      sortSelect.addEventListener('blur', () => {
        sortWrapper.classList.remove('is-open');
      });
    }
  }

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
  // PRODUCT DISPLAY
  // ============================================
  updateProductDisplay() {
    const container = document.querySelector('.product-listing');
    if (!container) return;

    container.style.opacity = '0.3';

    setTimeout(() => {
      container.innerHTML = '';

      if (this.state.displayProducts.length === 0) {
        container.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
            <p style="font-size: 18px; color: var(--gray-600);">No products found</p>
            <p style="font-size: 14px; color: var(--gray-400); margin-top: 8px;">Try adjusting your filters</p>
          </div>
        `;
      } else {
        this.state.displayProducts.forEach((product, index) => {
          const card = this.createProductCard(product);
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
          container.appendChild(card);

          setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, index * 50);
        });
      }

      container.style.transition = 'opacity 0.4s ease';
      container.style.opacity = '1';
    }, 300);
  }

  createProductCard(product) {
    const card = document.createElement('article');
    card.className = 'product-card';
    
    const placeholderImg = '/Candy-Crunch-Website/views/website/img/product1.png';
    const imageUrl = product.image || placeholderImg;
    
    const firstSku = product.skus && product.skus.length > 0 ? product.skus[0] : null;
    const displayPrice = firstSku ? firstSku.salePrice : product.basePrice;
    const originalPrice = firstSku?.originalPrice;
    const hasDiscount = originalPrice && originalPrice > displayPrice;

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
        <div class="product-meta" style="margin: 8px 0; font-size: 13px; color: var(--gray-500);">
          <div>${product.category}</div>
          <div>Stock: ${product.totalStock}</div>
        </div>
        <div class="product-price">
          ${hasDiscount ? `<span class="old-price">${this.formatPrice(originalPrice)}</span>` : ''}
          <span class="new-price">${this.formatPrice(displayPrice)}</span>
        </div>
        <div class="product-actions">
          <button class="btn-primary-small" data-product-id="${product.id}" data-sku-id="${firstSku?.skuId || ''}">
            Add to Cart
          </button>
          <button class="btn-icon-primary-outline-small-square" data-product-id="${product.id}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M12 1.75C12.862 1.75 13.6893 2.09266 14.2988 2.70215C14.9082 3.31162 15.25 4.13816 15.25 5V6.5L15.7354 6.51465C17.0204 6.55359 17.6495 6.69593 18.1074 7.07617V7.0752C18.4229 7.33727 18.6455 7.70404 18.8438 8.32617C19.0462 8.96152 19.205 9.80357 19.4268 10.9863L20.1768 14.9863C20.4881 16.6473 20.7102 17.8404 20.75 18.7549C20.7843 19.5431 20.6791 20.0519 20.4268 20.4385L20.3096 20.5967C19.9729 21.0021 19.4726 21.2418 18.5801 21.3691C17.6738 21.4984 16.4603 21.5 14.7705 21.5H9.23047C7.54006 21.5 6.32608 21.4984 5.41992 21.3691C4.52787 21.2418 4.02806 21.0021 3.69141 20.5967C3.35486 20.1913 3.2115 19.6557 3.25098 18.7549C3.29105 17.8403 3.51339 16.6474 3.82422 14.9863L4.57422 10.9863C4.79656 9.80388 4.95487 8.96178 5.15723 8.32617C5.35528 7.70411 5.57758 7.33712 5.89258 7.0752L5.89355 7.07617C6.35152 6.69593 6.98061 6.55359 8.26562 6.51465L8.75 6.5V5C8.75 4.13816 9.0928 3.31162 9.70215 2.70215C10.3115 2.09277 11.1382 1.75013 12 1.75ZM14.1719 11.1104C13.4859 10.87 12.6984 11.025 12 11.5391C11.3018 11.0253 10.5149 10.87 9.8291 11.1104C9.01314 11.3964 8.5 12.1866 8.5 13.1973C8.5001 13.8742 8.89184 14.4967 9.31445 14.9854C9.75016 15.4891 10.2943 15.9359 10.7471 16.2686L10.748 16.2695C11.1335 16.5522 11.4795 16.828 12 16.8281C12.5219 16.8281 12.8676 16.5521 13.2529 16.2695L13.2539 16.2686C13.7067 15.9359 14.2508 15.4893 14.6865 14.9854C15.1092 14.4964 15.4999 13.8737 15.5 13.1963C15.5 12.1864 14.9876 11.3963 14.1719 11.1104ZM12 2.25C11.2708 2.25013 10.5713 2.54005 10.0557 3.05566C9.54009 3.57137 9.25 4.27077 9.25 5V6.5H14.75V5C14.75 4.27077 14.4609 3.57137 13.9453 3.05566C13.4296 2.53994 12.7293 2.25 12 2.25Z" fill="#017E6A" stroke="#017E6A"/>
            </svg>
          </button>
        </div>
      </div>
    `;

    const addToCartBtn = card.querySelector('.btn-primary-small');
    const wishlistBtn = card.querySelector('.btn-icon-primary-outline-small-square');

    addToCartBtn.addEventListener('click', () => this.addToCart(product, firstSku));
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
  // CART & WISHLIST - REDIRECT TO CART PAGE
  // ============================================
  async addToCart(product, sku) {
    const skuId = sku?.skuId || (product.skus && product.skus[0]?.skuId);
    if (!skuId) {
      this.showNotification('Product variant not found', 'warning');
      return;
    }

    const button = event.currentTarget;
    this.animateAddToCart(button);

    const formData = new FormData();
    formData.append('sku_id', skuId);

    try {
      const response = await fetch('/Candy-Crunch-Website/controllers/website/shop_controller.php?action=add-to-cart', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();

      if (result.success) {
        this.showNotification('Product added! Redirecting to cart...', 'success', 1500);
        
        // Redirect to cart page after 1.5 seconds
        setTimeout(() => {
          window.location.href = '/Candy-Crunch-Website/views/website/php/cart.php';
        }, 1500);
        return;
      } else{
      this.showNotification(result.message || 'Cannot add product to cart', 'warning');}
    } catch (error) {
      console.error('Add to cart error:', error);
      this.showNotification('Cannot add product to cart', 'error');
    }
  }

  toggleWishlist(product) {
    console.log('Toggled wishlist:', product);
    this.showNotification(`${product.name} added to wishlist!`, 'success');

    const target = event.currentTarget;
    target.style.transform = 'scale(1.2)';
    setTimeout(() => {
      target.style.transform = 'scale(1)';
    }, 200);
  }

  animateAddToCart(button) {
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

    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('page-btn')) {
        this.goToPage(parseInt(e.target.textContent));
      }
    });
  }

  goToPage(page) {
    if (page < 1 || page > this.state.totalPages) return;

    this.state.currentPage = page;
    this.updatePaginationAndDisplay();
    this.scrollToTop();
  }

  updatePagination() {
    const pageInfoCurrent = document.querySelector('.page-info-current');
    const pageInfoTotal = document.querySelector('.page-info-total');

    if (pageInfoCurrent) pageInfoCurrent.textContent = this.state.currentPage;
    if (pageInfoTotal) pageInfoTotal.textContent = this.state.totalPages;

    this.renderPaginationButtons();
  }

  renderPaginationButtons() {
    const pageList = document.querySelector('.page-list');
    if (!pageList) return;

    const currentPage = this.state.currentPage;
    const totalPages = this.state.totalPages;

    pageList.innerHTML = '';

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

    const pages = [];
    pages.push(1);

    if (current > 3) {
      pages.push('...');
    }

    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i);
    }

    if (current < total - 2) {
      pages.push('...');
    }

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
      if (e.ctrlKey && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        this.clearAllFilters();
      }

      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.querySelector('.search-input')?.focus();
      }

      if (e.ctrlKey && e.key === 'ArrowRight') {
        e.preventDefault();
        this.goToPage(this.state.currentPage + 1);
      }

      if (e.ctrlKey && e.key === 'ArrowLeft') {
        e.preventDefault();
        this.goToPage(this.state.currentPage - 1);
      }
    });
  }

  clearAllFilters() {
    document.querySelectorAll('.filter-checkbox:checked').forEach(cb => {
      cb.checked = false;
    });

    document.querySelectorAll('.filter-tag').forEach(tag => tag.remove());

    this.state.filters = {
      search: '',
      category: [],
      ingredient: [],
      flavour: [],
      rating: null
    };
    this.state.activeTags = [];

    const searchInput = document.querySelector('.search-input');
    if (searchInput) searchInput.value = '';

    this.clearStars(document.querySelectorAll('.rating-star-btn'));

    this.state.currentPage = 1;
    this.applyFilters();
    this.showNotification('All filters cleared', 'info');
  }
}

// ============================================
// ANIMATIONS CSS
// ============================================
const animationStyles = document.createElement('style');
animationStyles.textContent = `
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

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
    cursor: pointer;
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
    cursor: pointer;
  }

  .rating-star-btn:hover {
    transform: scale(1.15);
  }

  .rating-star-btn.is-active {
    color: #FDBA06;
  }

  .page-btn {
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .page-btn:hover {
    background-color: #f3f4f6;
  }

  .page-btn.is-active {
    background-color: #689F38;
    color: white;
  }

  .sort-select-wrapper.is-open select {
    border-color: #689F38;
  }

  .loading-spinner {
    animation: spin 1s linear infinite;
  }

  .product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
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