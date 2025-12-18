

// BANKING ACCOUNT======================
// ======================================
// Toggle bank accounts container when Banking Account is selected
document.addEventListener('DOMContentLoaded', () => {
    const bankAccountsContainer = document.getElementById('bankAccountsContainer');
    if (!bankAccountsContainer) return;
  
    // Chỉ lấy các radio trong phần Payment Method
    const paymentRadios = document.querySelectorAll('.payment-method .radio');
  
    paymentRadios.forEach(component => {
      component.addEventListener('radio-change', (e) => {
        const value = e.detail.value; // chính là value của input (cod / bank)
  
        if (value === 'bank') {
          bankAccountsContainer.classList.add('active');
        } else {
          bankAccountsContainer.classList.remove('active');
        }
      });
    });
  });
