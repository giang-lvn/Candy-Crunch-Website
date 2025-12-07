document.addEventListener("DOMContentLoaded", function () {
  
  // ==============================
  // DROPDOWN SHOP
  // ==============================
  const shopDropdownBtn = document.getElementById("shopDropdownBtn");
  const dropdownContent = document.getElementById("shopDropdown");
  const featuredCard = document.getElementById("featuredCard");
  const featuredImage = document.getElementById("featuredImage");
  const featuredTitle = document.getElementById("featuredTitle");
  const featuredDesc = document.getElementById("featuredDesc");
  const menuItems = document.querySelectorAll(".menu-item");

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
  // NAV ACTIVE STATE
  // ==============================
  const navItems = document.querySelectorAll('.nav-item');
  
  navItems.forEach(item => {
    if (item.dataset.dropdown === "false") {
      item.addEventListener("click", function (e) {
        e.preventDefault();
        
        // Update active state
        navItems.forEach(nav => nav.setAttribute("data-active", "No"));
        this.setAttribute("data-active", "Yes");
        
        // Close dropdown if open
        if (dropdownContent && shopDropdownBtn) {
          dropdownContent.classList.remove("show");
          shopDropdownBtn.classList.remove("open");
          if (featuredCard) {
            featuredCard.classList.remove("show");
          }
        }
      });
    }
  });

  // ==============================
  // USER ACTIONS HOVER EFFECT
  // ==============================
  const actionItems = document.querySelectorAll('.action-item');
  
  actionItems.forEach(item => {
    item.addEventListener("click", function(e) {
      // Nếu không phải link thật, prevent default
      if (this.getAttribute('href') === '#') {
        e.preventDefault();
      }
    });
  });

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
      dropdownContent.classList.remove("show");
      shopDropdownBtn.classList.remove("open");
      if (featuredCard) {
        featuredCard.classList.remove("show");
      }
    }
  });

  // ==============================
  // DEBUG LOG
  // ==============================
  console.log("✅ Candy Shop Header Loaded!");
  console.log("📦 Nav items:", navItems.length);
  console.log("📦 Menu items:", menuItems.length);
  console.log("📦 Action items:", actionItems.length);
});