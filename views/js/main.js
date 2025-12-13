// Hero Badge Image Rotation
document.addEventListener('DOMContentLoaded', () => {
  // Array of hero line images for rotation
  const heroImages = [
    '/views/img/hero-line2.webp',
    '/views/img/hero-line3.webp',
    '/views/img/hero-line4.webp'
  ];
  
  let currentImageIndex = 0;
  
  // Get all hero badge elements
  const allBadges = Array.from(document.querySelectorAll('.hero-badge'));
  
  // Filter: only badges in the 2nd hero-line (IS COMING line)
  // Skip hero-line1 (CHRISTMAS line) and hero-line4 (All THE WAYS line)
  const badges = allBadges.filter((badge, index) => {
    const src = badge.getAttribute('src');
    // Get parent to identify which line it belongs to
    const parentLine = badge.closest('.hero-line');
    const lineIndex = Array.from(parentLine.parentElement.children).indexOf(parentLine);
    
    // Only allow badges from line index 1 (the 2nd line: IS COMING)
    return lineIndex === 1;
  });
  
  // Function to rotate all badges at once
  function rotateAllBadges() {
    // Move to next image
    currentImageIndex = (currentImageIndex + 1) % heroImages.length;
    
    // Change all badges instantly to the same new image
    badges.forEach(badge => {
      badge.setAttribute('src', heroImages[currentImageIndex]);
    });
  }
  
  // Rotate all badges together every 3 seconds
  setInterval(rotateAllBadges, 3000);
  
  console.log('Hero badge rotation initialized - only IS COMING line badges');
});

