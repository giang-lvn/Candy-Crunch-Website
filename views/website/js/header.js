document.addEventListener("DOMContentLoaded", function () {
    
  // ==============================
  // KHAI BÁO CÁC BIẾN CHÍNH
  // ==============================
  const shopDropdownBtn = document.getElementById("shopDropdownBtn");
  const dropdownContent = document.getElementById("shopDropdown");
  const featuredCard = document.getElementById("featuredCard");
  const featuredImage = document.getElementById("featuredImage");
  const featuredTitle = document.getElementById("featuredTitle");
  const featuredDesc = document.getElementById("featuredDesc");
  const menuItems = document.querySelectorAll(".menu-item");
  const navItems = document.querySelectorAll(".nav-item"); // ← FIX: Thêm dòng này
  const actionItems = document.querySelectorAll('.action-item');
  const authButtons = document.querySelectorAll('.auth-buttons .btn');
  
  // Lấy current page từ URL
  const currentPage = window.location.pathname.split('/').pop() || 'index.php';

  // ==============================
  // DROPDOWN SHOP
  // ==============================
  if (shopDropdownBtn && dropdownContent) {
    // Click để toggle dropdown
    shopDropdownBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const isOpen = dropdownContent.classList.contains("show");

      if (isOpen) {
        closeDropdown();
      } else {
        openDropdown();
      }
    });

    // Click ngoài để đóng
    document.addEventListener("click", function (e) {
      if (!shopDropdownBtn.contains(e.target) && !dropdownContent.contains(e.target)) {
        closeDropdown();
      }
    });

    // Mở dropdown
    function openDropdown() {
      dropdownContent.classList.add("show");
      shopDropdownBtn.classList.add("open");
      
      // Show featured card với delay nhẹ
      setTimeout(() => {
        if (featuredCard) {
          featuredCard.classList.add("show");
        }
      }, 150);
    }

    // Đóng dropdown
    function closeDropdown() {
      dropdownContent.classList.remove("show");
      shopDropdownBtn.classList.remove("open");
      if (featuredCard) {
        featuredCard.classList.remove("show");
      }
    }
  }

  // Helper function để đóng dropdown (dùng chung)
  function closeDropdown() {
    if (dropdownContent && shopDropdownBtn && featuredCard) {
      dropdownContent.classList.remove("show");
      shopDropdownBtn.classList.remove("open");
      featuredCard.classList.remove("show");
    }
  }

  // ==============================
  // INTERACTIVE MENU ITEMS
  // ==============================
  if (menuItems.length > 0) {
    menuItems.forEach(item => {
      item.addEventListener("mouseenter", function () {
        // Update featured card
        const image = this.getAttribute("data-image");
        const title = this.getAttribute("data-title");
        const desc = this.getAttribute("data-desc");

        if (image && title && desc && featuredImage && featuredTitle && featuredDesc) {
          // Smooth image transition
          featuredImage.style.opacity = "0.7";
          
          setTimeout(() => {
            featuredImage.src = image;
            featuredTitle.textContent = title;
            featuredDesc.textContent = desc;
            featuredImage.style.opacity = "1";
          }, 150);

          // Remove active from all
          menuItems.forEach(mi => mi.classList.remove("active"));
          // Add active to current
          this.classList.add("active");
        }
      });
    });
  }

  // ==============================
  // NAV ITEMS CLICK HANDLER - FIX ACTIVE STATE
  // ==============================
  navItems.forEach(item => {
    // Bỏ qua dropdown button
    if (item.dataset.dropdown === "true") {
      return;
    }

    item.addEventListener("click", function (e) {
      const href = this.getAttribute('href');
      
      // Nếu là link thật (không phải # hoặc javascript:void(0))
      if (href && href !== '#' && !href.startsWith('javascript:')) {
        // Prevent default để có thời gian animation
        e.preventDefault();
        
        // Close dropdown nếu đang mở
        closeDropdown();
        
        // Remove active from all items
        navItems.forEach(nav => {
          nav.setAttribute("data-active", "No");
          nav.classList.remove('active-transition');
        });
        
        // Add active to clicked item với animation
        this.setAttribute("data-active", "Yes");
        this.classList.add('active-transition');
        
        // Chờ animation xong rồi chuyển trang (300ms)
        setTimeout(() => {
          window.location.href = href;
        }, 300);
      }
    });
  });

  // ==============================
  // USER ACTIONS HOVER EFFECT
  // ==============================
  actionItems.forEach(item => {
    item.addEventListener("click", function(e) {
      // Chỉ prevent default nếu href là '#'
      if (this.getAttribute('href') === '#') {
        e.preventDefault();
        console.log('Action item clicked:', this.className);
      }
    });
  });

  // ==============================
  // AUTH BUTTONS CLICK (cho header login)
  // ==============================
  authButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      // Thêm animation khi click
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = '';
      }, 150);
    });
  });

  // ==============================
  // CART COUNT UPDATE (nếu cần update động)
  // ==============================
  const cartCountElement = document.getElementById('cartCount');
  
  // Function để update cart count từ session/API
  function updateCartCount(count) {
    if (cartCountElement) {
      cartCountElement.textContent = count;
      
      // Animation khi update
      cartCountElement.style.transform = 'scale(1.3)';
      setTimeout(() => {
        cartCountElement.style.transform = 'scale(1)';
      }, 200);
    }
  }

  // Export function để dùng ở các trang khác
  window.updateCartCount = updateCartCount;

  // ==============================
  // SEE ALL LINK HOVER
  // ==============================
  const seeAllLink = document.querySelector(".see-all-link");
  
  if (seeAllLink) {
    seeAllLink.addEventListener("mouseenter", function () {
      this.style.transform = "translateX(3px)";
    });

    seeAllLink.addEventListener("mouseleave", function () {
      this.style.transform = "translateX(0)";
    });
  }

  // ==============================
  // FEATURED CARD HOVER EFFECT
  // ==============================
  if (featuredCard && featuredImage) {
    featuredCard.addEventListener("mouseenter", function () {
      featuredImage.style.transform = "scale(1.05)";
    });

    featuredCard.addEventListener("mouseleave", function () {
      featuredImage.style.transform = "scale(1)";
    });
  }

  // ==============================
  // SMOOTH SCROLL
  // ==============================
  document.documentElement.style.scrollBehavior = "smooth";

  // ==============================
  // ESC KEY TO CLOSE DROPDOWN
  // ==============================
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && dropdownContent && dropdownContent.classList.contains("show")) {
      closeDropdown();
    }
  });

  // ==============================
  // DROPDOWN AUTO-CLOSE ON SCROLL
  // ==============================
  let scrollTimeout;
  window.addEventListener('scroll', function() {
    if (dropdownContent && dropdownContent.classList.contains("show")) {
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        closeDropdown();
      }, 100);
    }
  });

  // ==============================
  // PREVENT DROPDOWN CLOSE WHEN CLICKING INSIDE
  // ==============================
  if (dropdownContent) {
    dropdownContent.addEventListener('click', function(e) {
      // Chỉ close nếu click vào link, không close nếu click vào khoảng trống
      if (e.target.classList.contains('menu-item') || e.target.closest('.menu-item')) {
        // Để link hoạt động bình thường
      } else {
        // Click vào background thì không làm gì
        e.stopPropagation();
      }
    });
  }

  // ==============================
  // MOBILE MENU TOGGLE (nếu cần responsive)
  // ==============================
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const navPills = document.querySelector('.nav-pills');
  
  if (mobileMenuBtn && navPills) {
    mobileMenuBtn.addEventListener('click', function() {
      navPills.classList.toggle('mobile-open');
      this.classList.toggle('active');
    });
  }

  // ==============================
  // HANDLE WINDOW RESIZE
  // ==============================
  let resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      // Close dropdown khi resize
      if (window.innerWidth < 768 && dropdownContent && dropdownContent.classList.contains("show")) {
        closeDropdown();
      }
    }, 200);
  });

  // ==============================
  // ACCESSIBILITY: Focus Management
  // ==============================
  const headerNav = document.querySelector('.nav-pills');
  window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;
    if (currentScroll > 20) {
      headerNav.classList.add('scrolled');
    } else {
      headerNav.classList.remove('scrolled');
    }
});

  // ==============================
  // DEBUG LOG
  // ==============================
  console.log("✅ Candy Shop Header Loaded!");
  console.log("📦 Nav items:", navItems.length);
  console.log("📦 Menu items:", menuItems.length);
  console.log("📦 Action items:", actionItems.length);
  console.log("📍 Current page:", currentPage);
  console.log("🔐 Auth buttons:", authButtons.length);
});