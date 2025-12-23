document.addEventListener('DOMContentLoaded', () => {
    // ================================================
    // MENU NAVIGATION - CONSISTENT WITH MY ACCOUNT ✅
    // ================================================
    initMenuNavigation();
    initChangePassword();
});

function initMenuNavigation() {
    const menus = document.querySelectorAll('.account-menu');

    // Click handler
    menus.forEach(menu => {
        menu.addEventListener('click', e => {
            e.preventDefault();
            const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();
            handleMenuAction(text);
        });
    });

    // Set active menu based on current URL
    const currentPage = window.location.pathname;

    // Clear all active first
    menus.forEach(m => m.classList.remove('active'));

    // Find and set the correct active menu
    menus.forEach(menu => {
        const text = menu.querySelector('.my-orders, .my-orders2')?.textContent.trim();

        if (text === 'Change Password' && currentPage.includes('changepass')) {
            menu.classList.add('active');
        } else if (text === 'My Orders' && currentPage.includes('orders')) {
            menu.classList.add('active');
        } else if (text === 'My Vouchers' && currentPage.includes('vouchers')) {
            menu.classList.add('active');
        } else if (text === 'My Account' && (currentPage.includes('account') || currentPage.includes('my_account'))) {
            menu.classList.add('active');
        }
    });
}

function handleMenuAction(action) {
    switch (action) {
        case 'My Account':
            window.location.href = '../php/my_account.php';
            break;
        case 'Change Password':
            // Already on this page, just scroll to top or do nothing
            window.scrollTo({ top: 0, behavior: 'smooth' });
            break;
        case 'My Orders':
            window.location.href = '../php/my_orders.php';
            break;
        case 'My Vouchers':
            window.location.href = '../php/my_vouchers.php';
            break;
        case 'Log out':
            if (confirm('Are you sure you want to log out?')) window.location.href = '../php/login.php';
            break;
    }
}

// ================================================
// CHANGE PASSWORD FUNCTIONALITY
// ================================================
function initChangePassword() {
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const savePassBtn = document.getElementById('savePassBtn');

    if (!savePassBtn) return; // Exit if button not found

    savePassBtn.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent form submission if inside a form

        const current = currentPassword?.value.trim() || '';
        const newPass = newPassword?.value.trim() || '';
        const confirmPass = confirmPassword?.value.trim() || '';

        // Validation
        if (!current || !newPass || !confirmPass) {
            alert('Please fill in all fields.');
            return;
        }

        if (newPass.length < 6) {
            alert('New password must be at least 6 characters long.');
            return;
        }

        if (newPass !== confirmPass) {
            alert('New password and confirm password do not match.');
            return;
        }

        if (current === newPass) {
            alert('New password must be different from current password.');
            return;
        }

        // Confirm and save
        if (confirm('Are you sure you want to change your password?')) {
            const formData = new FormData();
            formData.append('currentPassword', current);
            formData.append('newPassword', newPass);

            fetch('/Candy-Crunch-Website/controllers/website/changepass_controller.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP status ' + response.status);
                    }
                    return response.json();
                })

                .then(data => {
                    if (data.status === 'success') {
                        alert('Password changed successfully!');
                        currentPassword.value = '';
                        newPassword.value = '';
                        confirmPassword.value = '';
                    } else {
                        alert(data.message || 'Change password failed.');
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Server error. Please try again.');
                })
        }
    });

    // Optional: Add show/hide password toggle
    addPasswordToggle();
}

// ================================================
// PASSWORD VISIBILITY TOGGLE (OPTIONAL)
// ================================================
function addPasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');

    passwordFields.forEach(field => {
        const toggleBtn = field.parentElement.querySelector('.icon-toggle');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                if (field.type === 'password') {
                    field.type = 'text';
                    toggleBtn.classList.add('showing');
                } else {
                    field.type = 'password';
                    toggleBtn.classList.remove('showing');
                }
            });
        }
    });
}

console.log('✅ Change Password JS loaded – FIXED VERSION');
