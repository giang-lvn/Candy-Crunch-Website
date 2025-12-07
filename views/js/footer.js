document.addEventListener("DOMContentLoaded", function () {
  
  // ==============================
  // NEWSLETTER SUBSCRIPTION
  // ==============================
  const newsletterEmail = document.getElementById("newsletterEmail");
  const submitBtn = document.getElementById("submitNewsletter");

  if (submitBtn && newsletterEmail) {
    submitBtn.addEventListener("click", function (e) {
      e.preventDefault();
      
      const email = newsletterEmail.value.trim();
      
      // Validate email
      if (!email) {
        showMessage("Please enter your email address", "error");
        return;
      }

      if (!isValidEmail(email)) {
        showMessage("Please enter a valid email address", "error");
        return;
      }

      // Success - Here you would typically send to your backend
      console.log("Newsletter subscription:", email);
      showMessage("Thank you for subscribing! Check your email for 15% off.", "success");
      
      // Clear input
      newsletterEmail.value = "";
    });

    // Submit on Enter key
    newsletterEmail.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        submitBtn.click();
      }
    });
  }

  // ==============================
  // EMAIL VALIDATION
  // ==============================
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // ==============================
  // SHOW MESSAGE (SIMPLE ALERT)
  // ==============================
  function showMessage(message, type) {
    // Simple alert for now - you can replace with a toast notification
    if (type === "success") {
      alert("✅ " + message);
    } else {
      alert("⚠️ " + message);
    }
  }

  // ==============================
  // LINK HOVER ANIMATIONS
  // ==============================
  const linkItems = document.querySelectorAll(".link-item");

  linkItems.forEach(link => {
    link.addEventListener("mouseenter", function () {
      this.style.transform = "translateX(5px)";
    });

    link.addEventListener("mouseleave", function () {
      this.style.transform = "translateX(0)";
    });
  });

  // ==============================
  // SUBMIT BUTTON ANIMATION
  // ==============================
  if (submitBtn) {
    submitBtn.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.1)";
    });

    submitBtn.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)";
    });

    submitBtn.addEventListener("mousedown", function () {
      this.style.transform = "scale(0.95)";
    });

    submitBtn.addEventListener("mouseup", function () {
      this.style.transform = "scale(1.1)";
    });
  }

  // ==============================
  // INPUT FOCUS EFFECT
  // ==============================
  if (newsletterEmail) {
    newsletterEmail.addEventListener("focus", function () {
      this.parentElement.style.boxShadow = "0 0 0 3px rgba(1, 126, 106, 0.2)";
    });

    newsletterEmail.addEventListener("blur", function () {
      this.parentElement.style.boxShadow = "none";
    });
  }

  // ==============================
  // SMOOTH SCROLL FOR FOOTER LINKS
  // ==============================
  document.documentElement.style.scrollBehavior = "smooth";

  // ==============================
  // ANIMATE BRAND TITLE ON SCROLL
  // ==============================
  const brandText = document.querySelector(".brand-text");
  
  if (brandText) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            brandText.style.opacity = "0";
            brandText.style.transform = "scale(0.9)";
            
            setTimeout(() => {
              brandText.style.transition = "all 0.8s ease";
              brandText.style.opacity = "1";
              brandText.style.transform = "scale(1)";
            }, 100);
          }
        });
      },
      {
        threshold: 0.3
      }
    );

    observer.observe(brandText);
  }

  // ==============================
  // CURRENT YEAR FOR COPYRIGHT
  // ==============================
  const currentYear = new Date().getFullYear();
  const copyrightTexts = document.querySelectorAll(".copyright-text");
  
  copyrightTexts.forEach(text => {
    if (text.textContent.includes("©2025")) {
      text.textContent = text.textContent.replace("©2025", `©${currentYear}`);
    }
  });

  // ==============================
  // DEBUG LOG
  // ==============================
  console.log("✅ Candy Shop Footer Loaded!");
  console.log("📧 Newsletter form ready");
  console.log("🔗 Footer links:", linkItems.length);
});