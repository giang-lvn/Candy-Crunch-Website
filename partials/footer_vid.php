<?php
$ROOT = '/Candy-Crunch-Website';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
<link rel = "stylesheet" href = "<?php echo $ROOT; ?>/views/website/css/footer_vid.css">
<link rel = "stylesheet" href = "<?php echo $ROOT; ?> /views/website/css/main/css">

<footer class="footer">
    <video class="footer-video" autoplay muted loop playsinline>
        <source src="<?php echo $ROOT; ?>/views/img/footervideo.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <div class="footer-overlay"></div>
    
    <div class="footer-content">
        
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
                    <a href="policy.php" class="link-item">Policy</a>
                </div>

                <div class="link-column">
                    <a href="https://tiktok.com" target="_blank" class="link-item">Tiktok</a>
                    <a href="https://instagram.com" target="_blank" class="link-item">Instagram</a>
                    <a href="https://facebook.com" target="_blank" class="link-item">Facebook</a>
                </div>
                
                <div class = "link-column contact-info">
                    <span class = "link-item column-title"> Contact </span>
                    <p class = "contact-detail"> <strong>Address:</strong> 279 Nguyen Tri Phuong Street, Dien Hong Ward, Ho Chi Minh City </p>
                    <p class = "contact-detail"> <strong>Phone Number:</strong> +84 886258467 </p>
                    <p class = "contact-detail"> <strong>Working Hours</strong>: 8 am - 5 pm, Monday - Friday </p>
            </div>
        </div>
</div>
        <div class="brand-title">
            <h1 class="brand-text">CANDY CRUNCH</h1>
        </div>

    </div>

    <div class="footer-bottom">
        <p class="copyright-text">These statements have not been evaluated by the FDA.</p>
        <p class="copyright-text">©<?php echo date('Y'); ?> Innerwork. All Rights Reserved.</p>
        <p class="copyright-text">Designed by Group H</p>
    </div>
</footer>

<script src="<?php echo $ROOT; ?>/views/website/js/footer.css"></script>