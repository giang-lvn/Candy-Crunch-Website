// GSAP ScrollTrigger Animation for Values Section
// Morphs from "CANDY - WE ALL IN LOVE" to "HEALTH - ALSO IN FEAR" to Joy Section

document.addEventListener('DOMContentLoaded', () => {
  // Register ScrollTrigger plugin
  gsap.registerPlugin(ScrollTrigger);

  // Navigation slide down after hero section loads
  // Wait for hero animation to complete (1s) then slide down nav
  setTimeout(() => {
    document.querySelector('nav').classList.add('nav-visible');
    
    // Show scroll indicators after navigation is visible
    const scrollIndicators = document.querySelectorAll('.scroll-indicator');
    scrollIndicators.forEach(indicator => {
      indicator.classList.add('visible');
    });
  }, 1200); // 1s hero animation + 200ms extra delay

  // Smooth scroll for logo link
  document.querySelector('.logo-link').addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  // Fade in images state-1 when scrolling to values section (independent animation)
  gsap.to('.values-img-state-1', {
    opacity: 1,
    duration: 1,
    stagger: 0.1, // Each image appears 0.1s after the previous
    ease: 'power2.out',
    scrollTrigger: {
      trigger: '.values',
      start: 'top 80%', // Start when section top reaches 80% of viewport
      once: true, // Only animate once
      markers: false
    }
  });

  // Create timeline for values section animation
  const valuesTimeline = gsap.timeline({
    scrollTrigger: {
      trigger: '.values',
      start: 'top top', // Start when section top hits viewport top (100vh into section)
      end: '+=400%', // Extended for all 3 transitions
      scrub: 0.1, // Very responsive to scroll speed
      pin: true, // Pin the section during animation
      anticipatePin: 1,
      markers: false // Set to true for debugging
    }
  });

  // Animation sequence - 4 PHASES
  valuesTimeline
    // PHASE 1: Scale up only solid text "WE ALL IN LOVE" (0-3s)
    .to('.values-main-text', {
      scale: 7, // Increased zoom for better balance
      duration: 3,
      ease: 'power4.in' // Strong acceleration - much faster at the end
    }, 0)
    
    // PHASE 2: Transition to HEALTH/ALSO IN FEAR (3-4s)
    .to('.values', {
      backgroundColor: '#017E6A', // green-500
      duration: 1,
      ease: 'power2.inOut' // Smooth transition
    }, 3)
    
    .to('.values-text-state-1', {
      opacity: 0,
      duration: 0.5
    }, 3)
    
    .to('.values-img-state-1', {
      opacity: 0,
      visibility: 'hidden',
      duration: 0.5,
      stagger: 0.03
    }, 3)
    
    // PHASE 3: Zoom in HEALTH + ALSO IN FEAR (3.3-5s) - Start earlier!
    .fromTo('.values-text-state-2', 
      {
        opacity: 0,
        scale: 0.5
      },
      {
        opacity: 1,
        scale: 1, // Normal size first
        duration: 1.5,
        ease: 'power2.out' // Smooth, no bounce
      }, 3.3)
    
    .to('.values-img-state-2', {
      opacity: 1,
      visibility: 'visible',
      duration: 1,
      stagger: 0.05
    }, 4.2)
    
    // PHASE 4: Zoom ONLY "ALSO IN FEAR" (main text), keep "HEALTH" (background) static
    .to('.values-main-text-2', {
      scale: 10, // Only zoom the main text
      opacity: 0, // Fade out
      duration: 2,
      ease: 'power3.in' // Smooth acceleration
    }, 6)
    
    // Fade out HEALTH (background text) at the same time
    .to('.values-bg-text.values-text-state-2', {
      opacity: 0,
      duration: 1.5
    }, 6.5)
    
    .to('.values-img-state-2', {
      opacity: 0,
      duration: 1
    }, 6)
    
    .to('.values', {
      backgroundColor: '#F8F5EE', // Beige - body background
      duration: 1,
      ease: 'power2.inOut'
    }, 7)
    
    // Fade out entire Values section content
    .to('.values', {
      opacity: 0,
      duration: 1,
      ease: 'power2.out'
    }, 7.5)
    
    // Fade in Joy section immediately - appears in same position (fixed)
    .to('.joy', {
      opacity: 1,
      visibility: 'visible',
      duration: 1.5,
      ease: 'power2.out',
      onComplete: () => {
        // Enable interaction after fade in
        const joySection = document.querySelector('.joy');
        if (joySection) {
          joySection.classList.add('active');
        }
      }
    }, 7.5)
    
    // Animate left content from left (starts first)
    .to('.joy-content', {
      opacity: 1,
      x: 0, // Move to original position
      duration: 1.2,
      ease: 'power3.out'
    }, 8)
    
    // Animate right products from right (delayed for smooth effect)
    .to('.joy-products', {
      opacity: 1,
      x: 0, // Move to original position
      duration: 1.2,
      ease: 'power3.out'
    }, 8.3)
    
    // Add counter animations to the timeline (triggered after Joy section is visible)
    .add(() => {
      const counterElements = document.querySelectorAll('.joy-stat-number');
      counterElements.forEach((element, index) => {
        const counter = { value: 100 };
        gsap.to(counter, {
          value: 0,
          duration: 2.5,
          ease: 'power2.out',
          delay: index * 0.2,
          onUpdate: function() {
            element.textContent = Math.round(counter.value) + '%';
          }
        });
      });
    }, 8.8); // Start counters after content and products are visible

  // 3D Tilt effect for values section images
  const images = document.querySelectorAll('.values-img');
  
  images.forEach(img => {
    // Mouse move handler for 3D tilt effect
    img.addEventListener('mouseenter', function() {
      // Enable 3D transform
      gsap.set(img, {
        transformStyle: 'preserve-3d',
        transformPerspective: 1000
      });
    });
    
    img.addEventListener('mousemove', function(e) {
      // Get mouse position relative to image
      const rect = img.getBoundingClientRect();
      const x = e.clientX - rect.left; // x position within the element
      const y = e.clientY - rect.top;  // y position within the element
      
      // Calculate rotation based on mouse position (centered)
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      // Normalize to -1 to 1 range
      const rotateX = ((y - centerY) / centerY) * -25; // Max 25 degrees tilt
      const rotateY = ((x - centerX) / centerX) * 25;
      
      // Apply 3D transformation with GSAP
      gsap.to(img, {
        rotateX: rotateX,
        rotateY: rotateY,
        duration: 0.15,
        ease: 'power2.out',
        transformPerspective: 1000
      });
    });
    
    img.addEventListener('mouseleave', function() {
      // Reset transformation when mouse leaves
      gsap.to(img, {
        rotateX: 0,
        rotateY: 0,
        duration: 0.4,
        ease: 'power2.out'
      });
    });
  });

  console.log('GSAP ScrollTrigger animation loaded');
  
  // ============================================
  // HIDE JOY SECTION WHEN FEATURED PRODUCTS COVERS IT COMPLETELY
  // ============================================
  const featuredProductsSection = document.querySelector('.featured-products');
  const joySection = document.querySelector('.joy');
  
  if (featuredProductsSection && joySection) {
    // Hide Joy only when Featured Products section covers it completely
    // This happens when Featured Products top reaches the top of viewport
    ScrollTrigger.create({
      trigger: featuredProductsSection,
      start: 'top top', // When Featured Products top reaches viewport top (covers Joy completely)
      onEnter: () => {
        // Hide Joy section instantly when Featured Products covers it
        joySection.classList.remove('active');
        joySection.style.opacity = '0';
        joySection.style.visibility = 'hidden';
        joySection.style.pointerEvents = 'none';
        joySection.style.zIndex = '0';
        joySection.style.display = 'none'; // Force hide completely
      },
      onLeaveBack: () => {
        // Show Joy again instantly when scrolling back up (before Featured Products covers it)
        joySection.style.display = 'flex'; // Restore display
        joySection.style.opacity = '1';
        joySection.style.visibility = 'visible';
        joySection.style.pointerEvents = 'auto';
        joySection.style.zIndex = '5';
        joySection.classList.add('active');
      }
    });
  }
  
  // ============================================
  // FEATURED PRODUCTS CAROUSEL
  // ============================================
  
  const carouselTrack = document.querySelector('.featured-carousel-track');
  const prevBtn = document.querySelector('.featured-nav-prev');
  const nextBtn = document.querySelector('.featured-nav-next');
  const cards = document.querySelectorAll('.featured-card');
  
  if (carouselTrack && prevBtn && nextBtn && cards.length > 0) {
    // Ensure track has correct height
    const container = document.querySelector('.featured-carousel-container');
    if (container) {
      carouselTrack.style.height = '100%';
      carouselTrack.style.minHeight = '350px';
    }
    
    const cardWidth = 280;
    const gap = 24;
    const cardTotalWidth = cardWidth + gap;
    const totalCards = cards.length;
    
    // Original set has 6 cards, duplicate set has 4 more = 10 total
    // For seamless infinite scroll, we only move the width of original set (6 cards)
    // then reset to 0 instantly, because duplicate set looks identical
    const originalSetCards = 6;
    const originalSetWidth = (cardWidth * originalSetCards) + (gap * (originalSetCards - 1));
    
    // Create GSAP timeline for seamless infinite scroll
    let carouselTimeline = gsap.timeline({ repeat: -1, paused: false });
    
    // Move from right to left
    carouselTimeline.to(carouselTrack, {
      x: -originalSetWidth, // Move left by width of original set
      duration: originalSetCards * 5, // Slow movement: 5 seconds per card
      ease: 'none', // Linear movement
    });
    
    // Instantly reset to 0 (duration 0) for seamless loop
    carouselTimeline.set(carouselTrack, {
      x: 0 // Reset position instantly
    });
    
    // Store reference for pause/resume
    let carouselAnimation = carouselTimeline;
    let isUserControlling = false; // Flag to know if user is manually controlling
    let resumeTimeout; // Timeout to resume auto-scroll
    
    // Function to get current position
    function getCurrentPosition() {
      return gsap.getProperty(carouselTrack, 'x') || 0;
    }
    
    // Function to move carousel manually
    function moveCarousel(direction) {
      // Clear any pending resume
      if (resumeTimeout) {
        clearTimeout(resumeTimeout);
      }
      
      isUserControlling = true;
      carouselAnimation.pause(); // Pause auto-scroll
      
      const moveDistance = cardTotalWidth * 2; // Move 2 cards at a time
      let currentPosition = getCurrentPosition();
      
      if (direction === 'next') {
        currentPosition -= moveDistance;
      } else {
        currentPosition += moveDistance;
      }
      
      // Wrap around for seamless loop
      if (currentPosition <= -originalSetWidth) {
        currentPosition = 0;
      } else if (currentPosition > 0) {
        currentPosition = -originalSetWidth + moveDistance;
      }
      
      // Animate to new position
      gsap.to(carouselTrack, {
        x: currentPosition,
        duration: 0.5,
        ease: 'power2.out',
        onComplete: () => {
          // Resume auto-scroll after a delay if not paused
          resumeTimeout = setTimeout(() => {
            isUserControlling = false;
            if (!isPaused) {
              // Restart animation from current position
              carouselAnimation.restart();
            }
          }, 3000); // Resume after 3 seconds of no interaction
        }
      });
    }
    
    // Navigation button handlers
    nextBtn.addEventListener('click', () => {
      moveCarousel('next');
    });
    
    prevBtn.addEventListener('click', () => {
      moveCarousel('prev');
    });
    
    // Enable button interactions
    nextBtn.style.pointerEvents = 'auto';
    prevBtn.style.pointerEvents = 'auto';
    nextBtn.style.opacity = '1';
    prevBtn.style.opacity = '1';
    
    let isPaused = false;
    
    // Pause animation when hovering over any card
    cards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        if (!isUserControlling) {
          carouselAnimation.pause();
          isPaused = true;
        }
      });
      
      card.addEventListener('mouseleave', () => {
        if (!isUserControlling) {
          carouselAnimation.resume();
          isPaused = false;
        }
      });
    });
    
    // Also pause when hovering over the track container
    if (container) {
      container.addEventListener('mouseenter', () => {
        if (!isUserControlling) {
          carouselAnimation.pause();
          isPaused = true;
        }
      });
      
      container.addEventListener('mouseleave', () => {
        if (!isUserControlling) {
          carouselAnimation.resume();
          isPaused = false;
        }
      });
    }
    
    // Stagger animation when section comes into view
    // Use clearProps to ensure height is not affected after animation
    gsap.from('.featured-card', {
      opacity: 0,
      y: 50,
      duration: 0.8,
      stagger: 0.15,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.featured-products',
        start: 'top 80%',
        once: true,
        markers: false
      },
      onComplete: function() {
        // Ensure cards maintain their height after animation
        cards.forEach(card => {
          gsap.set(card, { clearProps: 'y' }); // Clear y transform but keep height
        });
      }
    });
    
    // Animate header
    gsap.from('.featured-header', {
      opacity: 0,
      y: -30,
      duration: 0.8,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.featured-products',
        start: 'top 85%',
        once: true,
        markers: false
      }
    });
    
    // Animate navigation buttons
    gsap.from('.featured-nav', {
      opacity: 0,
      y: 30,
      duration: 0.8,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.featured-products',
        start: 'top 75%',
        once: true,
        markers: false
      }
    });
    
    console.log('Featured Products carousel initialized');
  }
  
  // ============================================
  // ARC SECTION ANIMATIONS
  // ============================================
  initArcSectionAnimations();
  
  // ============================================
  // FAQ ACCORDION TOGGLE
  // ============================================
  const faqItems = document.querySelectorAll('.faq-item');
  
  faqItems.forEach((faqItem) => {
    faqItem.addEventListener('click', (e) => {
      // Prevent event bubbling if clicking on toggle button
      // (though it will still work since we're handling it on the item)
      const isActive = faqItem.classList.contains('active');
      
      // Close all other accordions
      faqItems.forEach(item => {
        if (item !== faqItem) {
          item.classList.remove('active');
        }
      });
      
      // Toggle current accordion
      if (isActive) {
        faqItem.classList.remove('active');
      } else {
        faqItem.classList.add('active');
      }
    });
  });
  
  console.log('FAQ accordions initialized');
});


/**
 * Arc Section: Rotating wheel with 3 arc phrases
 * The wheel rotates counter-clockwise as user scrolls to show each phrase at the top
 */
function initArcSectionAnimations() {
  const arcSection = document.querySelector('.arc-section');
  const arcWheel = document.querySelector('.arc-wheel');
  const arcPhrases = document.querySelectorAll('.arc-phrase');
  const arcButton = document.querySelector('.arc-button');
  
  // Check if elements exist
  if (!arcSection || !arcWheel) {
    console.warn('Arc section elements not found');
    return;
  }
  
  // Main timeline: Pin the section and rotate the wheel
  const mainTimeline = gsap.timeline({
    scrollTrigger: {
      trigger: arcSection,
      start: 'top top',
      end: '+=400%', // Pin for 4x viewport height - slower rotation
      pin: true,
      scrub: 0.5,
      invalidateOnRefresh: true,
      pinSpacing: true
    }
  });
  
  // Rotate the wheel -240° counter-clockwise to show all 3 phrases
  // Start: phrase 1 at top (0°)
  // After -120° rotation: phrase 2 at top (was at 120°)
  // After -240° rotation: phrase 3 at top (was at 240°)
  mainTimeline.fromTo(arcWheel, 
    {
      rotation: 0
    },
    {
      rotation: -240,
      ease: 'none'
    }
  );
  
  // Gallery images parallax effect
  gsap.to('.gallery-left .gallery-item', {
    y: -300,
    stagger: 0.1,
    scrollTrigger: {
      trigger: arcSection,
      start: 'top top',
      end: '+=400%',
      scrub: 1.5
    }
  });
  
  gsap.to('.gallery-center .gallery-item', {
    y: -500,
    stagger: 0.2,
    scrollTrigger: {
      trigger: arcSection,
      start: 'top top',
      end: '+=400%',
      scrub: 1.8
    }
  });
  
  gsap.to('.gallery-right .gallery-item', {
    y: -400,
    stagger: 0.15,
    scrollTrigger: {
      trigger: arcSection,
      start: 'top top',
      end: '+=400%',
      scrub: 2
    }
  });
  
  // Animate the center button to appear in the last portion of the scroll
  if (arcButton) {
    gsap.fromTo(arcButton,
      { opacity: 0, y: 30 },
      {
        opacity: 1,
        y: 0,
        scrollTrigger: {
          trigger: arcSection,
          start: 'top top',
          end: '+=400%',
          scrub: 1,
          onUpdate: (self) => {
            // Button appears after 75% of scroll progress
            if (self.progress > 0.75) {
              gsap.to(arcButton, { opacity: 1, y: 0, duration: 0.3 });
            } else {
              gsap.to(arcButton, { opacity: 0, y: 30, duration: 0.3 });
            }
          }
        }
      }
    );
  }
  
  console.log('Arc section animations initialized');
}

// ============================================
// SCROLL INDICATORS
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  const scrollIndicator = document.getElementById('scrollIndicator');
  const keepScrollingIndicator = document.getElementById('keepScrollingIndicator');
  
  if (!scrollIndicator || !keepScrollingIndicator) return;
  
  // Handle scroll indicators visibility
  function handleScrollIndicators() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const heroSection = document.querySelector('.hero');
    const valuesSection = document.querySelector('.values');
    
    if (heroSection && valuesSection) {
      // Calculate hero section height
      const heroHeight = heroSection.offsetHeight;
      
      // Hero Scroll Indicator - only show when in hero section
      if (scrollTop < heroHeight - 100) {
        scrollIndicator.classList.add('visible');
      } else {
        scrollIndicator.classList.remove('visible');
      }
      
      // Keep Scrolling Indicator - show ONLY during CANDY section (state 1)
      // Values section is pinned from heroHeight to heroHeight + 400vh (400% pin duration)
      // Timeline: Phase 1 (CANDY) is 0-3s out of 8s total = 0-37.5% of scroll progress
      const valuesPinStart = heroHeight;
      const valuesPinDuration = window.innerHeight * 4; // 400% of viewport height
      
      if (scrollTop >= valuesPinStart) {
        // Calculate scroll progress through pinned Values section (0 = start, 1 = end)
        const scrollProgress = (scrollTop - valuesPinStart) / valuesPinDuration;
        
        // Show only during first 40% (CANDY phase), fade out at 25-40%
        if (scrollProgress < 0.4) {
          // Start fading at 25% progress, fully hidden at 40%
          let opacity = 1;
          if (scrollProgress > 0.25) {
            // Fade from 1 to 0 between 25% and 40% with easing
            const fadeProgress = (scrollProgress - 0.25) / 0.15; // 0 to 1
            // Use cubic easing for smoother fade out
            const easedProgress = fadeProgress * fadeProgress * (3 - 2 * fadeProgress); // smoothstep
            opacity = 1 - easedProgress;
          }
          
          keepScrollingIndicator.classList.add('visible');
          keepScrollingIndicator.style.opacity = opacity;
        } else {
          keepScrollingIndicator.classList.remove('visible');
          keepScrollingIndicator.style.opacity = '1';
        }
      } else {
        keepScrollingIndicator.classList.remove('visible');
        keepScrollingIndicator.style.opacity = '1';
      }
    }
  }
  
  // Smooth scroll to Values section when clicking hero indicator
  scrollIndicator.addEventListener('click', () => {
    // Scroll on click has been disabled intentionally
    return;
  });
  
  // Smooth scroll to Joy/Featured section when clicking keep scrolling indicator
  keepScrollingIndicator.addEventListener('click', () => {
    // Scroll on click has been disabled intentionally
    return;
  });
  
  // Listen to scroll events
  window.addEventListener('scroll', handleScrollIndicators, { passive: true });
  
  console.log('Scroll indicators initialized');
});

// ============================================
// SNOWFLAKES EFFECT (Hero Section Only)
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  const snowflakesContainer = document.querySelector('.snowflakes-container');
  
  if (!snowflakesContainer) return;
  
  // Snowflake characters (various styles)
  const snowflakeChars = ['❄', '❅', '❆'];
  
  // Number of snowflakes
  const snowflakeCount = 50;
  
  // Create snowflakes
  function createSnowflakes() {
    for (let i = 0; i < snowflakeCount; i++) {
      const snowflake = document.createElement('div');
      snowflake.classList.add('snowflake');
      
      // Random snowflake character
      snowflake.textContent = snowflakeChars[Math.floor(Math.random() * snowflakeChars.length)];
      
      // Random horizontal position
      snowflake.style.left = Math.random() * 100 + '%';
      
      // Random animation delay (stagger the falling)
      snowflake.style.animationDelay = Math.random() * 10 + 's';
      
      // Random animation duration (between 10-15s)
      const duration = 10 + Math.random() * 5;
      snowflake.style.animationDuration = duration + 's';
      
      snowflakesContainer.appendChild(snowflake);
    }
  }
  
  // Initialize snowflakes after a short delay (let page load first)
  setTimeout(createSnowflakes, 500);
  
  console.log('Snowflakes effect initialized for hero section');
});

