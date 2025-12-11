document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verifyForm');
    const btnVerify = document.getElementById('btnVerify');

    // Ripple
    if (btnVerify) {
        btnVerify.addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            let rect = this.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            this.appendChild(ripple);
            setTimeout(() => { ripple.remove(); }, 600);
        });
    }

    // Logic Check Mã
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const code = document.getElementById('verify_code').value.trim();

            // Mã cứng theo yêu cầu của bạn
            if (code === "ma xac thuc vi du") {
                alert('Xác thực thành công!');
                window.location.href = 'new password.html';
            } else {
                alert('Mã xác thực không đúng! Vui lòng thử lại (Gợi ý: ma xac thuc vi du)');
            }
        });
    }
});