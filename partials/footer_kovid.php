<?php
// footer ko có video
?>

<footer class="footer">
    <div class="footer-top">
        
        <div class="newsletter-section">
            <p class="newsletter-text">
                Subscribe for 15% off your first order and unlock your inner potential with us.
            </p>
            
            <div class="newsletter-form">
                <input 
                    type="email" 
                    placeholder="Your Email" 
                    class="email-input"
                    id="newsletterEmail"
                />
                <button class="submit-btn" id="submitNewsletter">
                    <svg class="submit-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="links-grid">
            
            <div class="link-column">
                <a href="about.php" class="link-item">About us</a>
                <a href="shop.php" class="link-item">Shop</a>
                <a href="contact.php" class="link-item">Contact</a>
                <a href="policy.php" class="link-item">Policy</a>
            </div>

            <div class="link-column">
                <a href="faq.php" class="link-item">FAQ</a>
                <a href="shipping.php" class="link-item">Shipping Policy</a>
                <a href="refund.php" class="link-item">Refund Policy</a>
                <a href="journal.php" class="link-item">Journal</a>
            </div>

            <div class="link-column">
                <a href="https://tiktok.com" target="_blank" class="link-item">Tiktok</a>
                <a href="https://instagram.com" target="_blank" class="link-item">Instagram</a>
                <a href="https://facebook.com" target="_blank" class="link-item">Facebook</a>
            </div>

        </div>
    </div>

    <div class="brand-title">
        <h1 class="brand-text">CANDY CRUNCH</h1>
    </div>

    <div class="footer-bottom">
        <p class="copyright-text">These statements have not been evaluated by the FDA.</p>
        <p class="copyright-text">©<?php echo date('Y'); ?> Innerwork. All Rights Reserved.</p>
        <p class="copyright-text">Designed by Group H</p>
    </div>
</footer>

<script src="views/js/footer.js"></script>