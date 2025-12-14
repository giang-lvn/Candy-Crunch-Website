// Hero Badge Image Rotation
document.addEventListener('DOMContentLoaded', () => {
  // Array of hero line images for rotation
  const heroImages = [
    '/views/website/img/hero-line2.webp',
    '/views/website/img/hero-line3.webp',
    '/views/website/img/hero-line4.webp'
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

/* ================================================
   INPUT COMPONENTS HANDLERS
   ================================================ */

document.addEventListener('DOMContentLoaded', () => {
  
  // ========================================
  // TEXT & PASSWORD INPUT HANDLERS
  // ========================================
  
  const inputComponents = document.querySelectorAll('.input[data-type="text"], .input[data-type="password"]');
  
  inputComponents.forEach(component => {
    const inputField = component.querySelector('input');
    
    if (!inputField) return;
    
    // Focus event
    inputField.addEventListener('focus', () => {
      if (component.getAttribute('data-state') !== 'disabled') {
        component.setAttribute('data-state', 'focus');
      }
    });
    
    // Blur event
    inputField.addEventListener('blur', () => {
      if (component.getAttribute('data-state') === 'disabled') return;
      
      if (inputField.value.trim() !== '') {
        component.setAttribute('data-state', 'typed');
      } else {
        component.setAttribute('data-state', 'default');
      }
    });
    
    // Input event (for real-time validation if needed)
    inputField.addEventListener('input', () => {
      // Keep focus state while typing
      if (component.getAttribute('data-state') !== 'disabled' && 
          component.getAttribute('data-state') !== 'error') {
        component.setAttribute('data-state', 'focus');
      }
    });
  });
  
  // ========================================
  // PASSWORD TOGGLE (Eye Icon)
  // ========================================
  
  const passwordComponents = document.querySelectorAll('.input[data-type="password"]');
  
  passwordComponents.forEach(component => {
    const toggleBtn = component.querySelector('.password-toggle');
    const inputField = component.querySelector('input');
    const eyeIcon = component.querySelector('.eye-icon');
    
    if (!toggleBtn || !inputField || !eyeIcon) return;
    
    toggleBtn.addEventListener('click', () => {
      if (inputField.type === 'password') {
        inputField.type = 'text';
        eyeIcon.src = '/views/website/img/eye-open.svg';
      } else {
        inputField.type = 'password';
        eyeIcon.src = '/views/website/img/eye-closed.svg';
      }
    });
  });
  
  // ========================================
  // DROPDOWN HANDLERS
  // ========================================
  
  const dropdownComponents = document.querySelectorAll('.input[data-type="dropdown"]');
  
  dropdownComponents.forEach(component => {
    const trigger = component.querySelector('.dropdown-trigger');
    const menu = component.querySelector('.dropdown-menu');
    const options = component.querySelectorAll('.dropdown-option');
    const triggerText = trigger.querySelector('.dropdown-text');
    
    if (!trigger || !menu) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      
      if (component.getAttribute('data-state') === 'disabled') return;
      
      const isOpen = component.getAttribute('data-state') === 'open';
      
      // Close all other dropdowns
      dropdownComponents.forEach(other => {
        if (other !== component) {
          other.setAttribute('data-state', 'default');
        }
      });
      
      if (isOpen) {
        component.setAttribute('data-state', 'default');
      } else {
        component.setAttribute('data-state', 'open');
      }
    });
    
    // Select option
    options.forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        
        const value = option.getAttribute('data-value');
        const text = option.textContent;
        
        // Update trigger text
        if (triggerText) {
          triggerText.textContent = text;
        }
        
        // Update state
        component.setAttribute('data-state', 'picked');
        component.setAttribute('data-selected', value);
        
        // Trigger custom event
        component.dispatchEvent(new CustomEvent('dropdown-change', {
          detail: { value, text }
        }));
      });
    });
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', () => {
    dropdownComponents.forEach(component => {
      const currentState = component.getAttribute('data-state');
      if (currentState === 'open') {
        const hasPicked = component.hasAttribute('data-selected');
        component.setAttribute('data-state', hasPicked ? 'picked' : 'default');
      }
    });
  });
  
  // ========================================
  // RADIO BUTTON HANDLERS
  // ========================================
  
  const radioComponents = document.querySelectorAll('.radio');
  
  radioComponents.forEach(component => {
    const radioInput = component.querySelector('.radio-input');
    const radioIcon = component.querySelector('.radio-icon');
    
    if (!radioInput || !radioIcon) return;
    
    // Click handler
    component.addEventListener('click', () => {
      if (component.getAttribute('data-disabled') === 'true') return;
      
      const groupName = radioInput.name;
      const isChecked = component.getAttribute('data-checked') === 'true';
      
      if (isChecked) return; // Already checked
      
      // Uncheck all radios in the same group
      if (groupName) {
        document.querySelectorAll(`.radio input[name="${groupName}"]`).forEach(input => {
          const parentComponent = input.closest('.radio');
          if (parentComponent) {
            parentComponent.setAttribute('data-checked', 'false');
            const icon = parentComponent.querySelector('.radio-icon');
            if (icon) {
              icon.src = '/views/website/img/radio-unchecked.svg';
            }
            input.checked = false;
          }
        });
      }
      
      // Check this radio
      component.setAttribute('data-checked', 'true');
      radioIcon.src = '/views/website/img/radio-checked.svg';
      radioInput.checked = true;
      
      // Trigger custom event
      component.dispatchEvent(new CustomEvent('radio-change', {
        detail: { value: radioInput.value, name: groupName }
      }));
    });
  });
  
  console.log('Input components handlers initialized');
});

