<?php
$ROOT = '/Candy-Crunch-Website';
include(__DIR__ . '/../../../partials/header.php');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candy Crunch</title>
    <!-- Preload Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap"
        rel="stylesheet">
    <!-- Preload critical hero images for faster loading -->
    <link rel="preload" href="<?php echo $ROOT; ?>/views/website/img/hero-thumbnail.svg" as="image"
        fetchpriority="high">
    <link rel="preload" href="<?php echo $ROOT; ?>/views/website/img/hero-line1.webp" as="image">
    <link rel="preload" href="<?php echo $ROOT; ?>/views/website/img/hero-line2.webp" as="image">
    <link rel="preload" href="<?php echo $ROOT; ?>/views/website/img/hero-line3.webp" as="image">
    <link rel="preload" href="<?php echo $ROOT; ?>/views/website/img/hero-line4.webp" as="image">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/index.css">
</head>

<body id="top">

    <!-- Hero Section -->


    <section class="hero">
        <!-- Snowflakes Container -->
        <div class="snowflakes-container" aria-hidden="true"></div>

        <div class="hero-content">
            <div class="hero-main">
                <div class="hero-heading">
                    <div class="hero-line">
                        <h1 class="hero-text hero-text-pink">CHRISTMAS</h1>
                        <img src="<?php echo $ROOT; ?>/views/website/img/hero-line1.webp" alt="badge" class="hero-badge"
                            loading="eager" width="48" height="48">
                    </div>
                    <div class="hero-line">
                        <img src="<?php echo $ROOT; ?>/views/website/img/hero-line2.webp" alt="badge" class="hero-badge"
                            loading="eager" width="48" height="48">
                        <h1 class="hero-text hero-text-teal">IS</h1>
                        <img src="<?php echo $ROOT; ?>/views/website/img/hero-line2.webp" alt="badge" class="hero-badge"
                            loading="eager" width="48" height="48">
                        <h1 class="hero-text hero-text-teal">COMING</h1>
                        <img src="<?php echo $ROOT; ?>/views/website/img/hero-line3.webp" alt="badge" class="hero-badge"
                            loading="eager" width="48" height="48">
                    </div>
                    <div class="hero-line">
                        <h1 class="hero-text hero-text-orange">All THE WAYS</h1>
                        <img src="<?php echo $ROOT; ?>/views/website/img/hero-line4.webp" alt="badge" class="hero-badge"
                            loading="eager" width="48" height="48">
                    </div>
                </div>

                <button onclick="window.location.href='<?php echo $ROOT; ?>/views/website/php/shop.php'"
                    class="btn-primary-large">Check it
                    out now</button>
            </div>

            <p class="hero-tagline">
                Candy Crunch - an unparalleled candy experience.<br>
                Always sustainable, always premium.<br> Follow us on social media to learn more!
            </p>
        </div>

        <div class="hero-image">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-1.webp" alt="Ice cream bowls" loading="eager"
                fetchpriority="high" width="527" height="527">
        </div>
    </section>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator" id="scrollIndicator">
        <div class="scroll-text">Scroll</div>
        <div class="scroll-arrow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </div>
    <!-- End Scroll Indicator -->

    <!-- End Hero Section -->

    <!-- Values Section -->
    <section class="values">
        <!-- State 1: CANDY + WE ALL IN LOVE -->
        <h2 class="values-bg-text values-text-state-1">CANDY</h2>
        <h3 class="values-main-text values-text-state-1">WE ALL IN LOVE</h3>

        <!-- State 2: HEALTH + ALSO IN FEAR -->
        <h2 class="values-bg-text values-text-state-2">HEALTH</h2>
        <h3 class="values-main-text-2 values-text-state-2">ALSO IN FEAR</h3>

        <!-- Intro Images State 1 (intro1-6) -->
        <img src="<?php echo $ROOT; ?>/views/website/img/intro1.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-1">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro2.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-2">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro3.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-3">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro4.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-4">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro5.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-5">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro6.svg" alt="Candy lover"
            class="values-img values-img-state-1 values-img-6">

        <!-- Intro Images State 2 (intro7-12) -->
        <img src="<?php echo $ROOT; ?>/views/website/img/intro7.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-1">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro8.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-2">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro9.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-3">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro10.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-4">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro11.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-5">
        <img src="<?php echo $ROOT; ?>/views/website/img/intro12.svg" alt="Health concern"
            class="values-img values-img-state-2 values-img-6">
    </section>
    <!-- End Values Section -->

    <!-- Scroll Indicator -->
    <div class="scroll-indicator scroll-indicator-keep" id="keepScrollingIndicator">
        <div class="scroll-text">Scroll</div>
        <div class="scroll-arrow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </div>
    <!-- End Scroll Indicator -->

    <!-- Joy Section -->
    <section class="joy">
        <div class="joy-content">
            <div class="joy-heading-group">
                <p class="joy-label">For Candy Crunch</p>
                <div class="joy-heading">
                    <h2 class="joy-heading-line joy-heading-pink">Joy</h2>
                    <h2 class="joy-heading-line joy-heading-red-light">shouldn't come</h2>
                    <h2 class="joy-heading-line joy-heading-red">with fear</h2>
                </div>
            </div>

            <div class="joy-stats">
                <div class="joy-stat">
                    <span class="joy-stat-number">100%</span>
                    <span class="joy-stat-label">Sugar</span>
                </div>
                <div class="joy-stat">
                    <span class="joy-stat-number">100%</span>
                    <span class="joy-stat-label">Gluten</span>
                </div>
                <div class="joy-stat">
                    <span class="joy-stat-number">100%</span>
                    <span class="joy-stat-label">Artifactual</span>
                </div>
            </div>

            <button class="btn-primary-large" onclick="window.location.href = 'shop.php';">Go to shop</button>
        </div>

        <div class="joy-products">
            <div class="joy-products-col">
                <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-CB001-royal-cocoa-bomb.webp" alt="Product"
                    class="joy-product-img">

            </div>
            <div class="joy-products-col">
                <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-NOU001-dried-fruit.webp" alt="Product"
                    class="joy-product-img">

            </div>
        </div>
    </section>

    <!-- End Joy Section -->

    <!-- Featured Products Section -->
    <section class="featured-products">
        <div class="featured-header">
            <h2 class="featured-title">
                <span class="featured-title-teal">BEST CHOICES FROM</span>
                <span class="featured-title-yellow">CUSTOMERS</span>
            </h2>
            <p class="featured-subtitle">We proud of making everyone satisfied with our products</p>
        </div>

        <div class="featured-carousel-container">
            <div class="featured-carousel-track">
                <!-- Products will be loaded dynamically by JavaScript -->
                <div class="featured-loading"
                    style="grid-column: 1/-1; text-align: center; padding: 60px 20px; width: 100%;">
                    <p style="font-size: 16px; color: var(--teal-500);">Loading products...</p>
                </div>
            </div>
        </div>

        <div class="featured-nav">
            <button class="featured-nav-btn featured-nav-prev" aria-label="Previous">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <button class="featured-nav-btn featured-nav-next" aria-label="Next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </section>
    <!-- End Featured Products Section -->

    <!-- Arc Section: Rotating curved text on scroll -->
    <section class="arc-section">


        <!-- Gallery Images - Scroll with parallax -->
        <div class="gallery-scroll">
            <div class="gallery-column gallery-left">
                <div class="crew-member crew-member-1">
                    <img src="<?php echo $ROOT; ?>/views/website/img/ot-thanhgiang.png" alt="Thanh Giang"
                        class="gallery-item">
                    <div class="crew-name">THANH GIANG</div>
                </div>
                <div class="crew-member crew-member-2">
                    <img src="<?php echo $ROOT; ?>/views/website/img/ot-giang.png" alt="Ngoc Giang"
                        class="gallery-item">
                    <div class="crew-name">NGOC GIANG</div>
                </div>
            </div>
            <div class="gallery-column gallery-center">
                <img src="<?php echo $ROOT; ?>/views/website/img/logo.svg" alt="Candy Crunch Logo" class="crew-logo">
            </div>
            <div class="gallery-column gallery-right">
                <div class="crew-member crew-member-3">
                    <img src="<?php echo $ROOT; ?>/views/website/img/ot-mananh.png" alt="Team Member"
                        class="gallery-item">
                    <div class="crew-name">MAN ANH</div>
                </div>
                <div class="crew-member crew-member-4">
                    <img src="<?php echo $ROOT; ?>/views/website/img/ot-longvo.png" alt="Team Member"
                        class="gallery-item">
                    <div class="crew-name">LONG VO</div>
                </div>
            </div>
        </div>

        <!-- Community Images - Appear during "A COMMUNITY" phase -->
        <div class="community-gallery">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-CG001-blueberry.webp" alt="Community"
                class="community-img community-img-1">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-CG002-mint.webp" alt="Community"
                class="community-img community-img-2">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-CG003-cola.webp" alt="Community"
                class="community-img community-img-3">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-CG004-strawbery.webp" alt="Community"
                class="community-img community-img-4">
            <img src="<?php echo $ROOT; ?>/views/website/img/Commercial-GUM001- Worm.webp" alt="Community"
                class="community-img community-img-5">
        </div>

        <!-- Delight Images - Flip in during "A DELIGHT" phase -->
        <div class="delight-gallery">
            <img src="<?php echo $ROOT; ?>/views/website/img/community_3.png" alt="Delight"
                class="delight-img delight-img-1">
            <img src="<?php echo $ROOT; ?>/views/website/img/community_5.png" alt="Delight"
                class="delight-img delight-img-2">
            <img src="<?php echo $ROOT; ?>/views/website/img/community_2.png" alt="Delight"
                class="delight-img delight-img-3">
            <img src="<?php echo $ROOT; ?>/views/website/img/community_1.png" alt="Delight"
                class="delight-img delight-img-4">
            <img src="<?php echo $ROOT; ?>/views/website/img/community_4.png" alt="Delight"
                class="delight-img delight-img-5">
        </div>

        <!-- Arc Wheel: Rotating container with 3 arc phrases positioned at 0°, 120°, 240° -->
        <div class="arc-wheel-container">
            <div class="arc-wheel">
                <!-- Arc 1: Top position (0°) -->
                <div class="arc-phrase arc-phrase-1">
                    <svg viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <path id="arc1-line1" d="M 100,400 A 600,600 0 0 1 1100,400" fill="none" />
                        </defs>
                        <text>
                            <textPath href="#arc1-line1" startOffset="50%" text-anchor="middle">
                                MEET OUR CREW
                            </textPath>
                        </text>
                    </svg>
                </div>

                <!-- Arc 2: Right position (120°) -->
                <div class="arc-phrase arc-phrase-2">
                    <svg viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <path id="arc2-line1" d="M 100,400 A 600,600 0 0 1 1100,400" fill="none" />
                            <path id="arc2-line2" d="M 100,432 A 600,600 0 0 1 1100,432" fill="none" />
                        </defs>
                        <text>
                            <textPath href="#arc2-line1" startOffset="50%" text-anchor="middle">
                                A COMMUNITY
                            </textPath>
                        </text>
                        <text>
                            <textPath href="#arc2-line2" startOffset="50%" text-anchor="middle">
                                THAT CHOOSES
                            </textPath>
                        </text>
                    </svg>
                </div>

                <!-- Arc 3: Bottom position (240°) -->
                <div class="arc-phrase arc-phrase-3">
                    <svg viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <path id="arc3-line1" d="M 100,400 A 600,600 0 0 1 1100,400" fill="none" />
                            <path id="arc3-line2" d="M 100,432 A 600,600 0 0 1 1100,432" fill="none" />
                        </defs>
                        <text>
                            <textPath href="#arc3-line1" startOffset="50%" text-anchor="middle">
                                A DELIGHT FOR
                            </textPath>
                        </text>
                        <text>
                            <textPath href="#arc3-line2" startOffset="50%" text-anchor="middle">
                                EVERYONE
                            </textPath>
                        </text>
                    </svg>
                </div>
            </div>

            <!-- Call-to-Action Button -->
            <button class="arc-button btn-primary-large">Join the Community</button>
        </div>
    </section>
    <!-- End Arc Section -->

    <!-- FAQs Section -->
    <section class="faqs">
        <div class="faqs-left">
            <h2 class="faqs-title">YOU HAVE<br>QUESTIONS?</h2>
            <p class="faqs-subtitle">We wish we can answer all</p>
        </div>

        <div class="faqs-right">
            <div class="faqs-accordions">
                <!-- FAQ Item 1 - Active by default -->
                <div class="faq-item active">
                    <div class="faq-header">
                        <h3 class="faq-question">Is your candy really sugar-free?</h3>
                        <button class="faq-toggle" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Yes. Our candy contains no sucrose, glucose, or fructose — only safe, natural sweeteners.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item">
                    <div class="faq-header">
                        <h3 class="faq-question">What natural sweeteners do you use?</h3>
                        <button class="faq-toggle" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>We use stevia, erythritol, and monk fruit extract — all natural, zero-calorie sweeteners that
                            are safe for daily consumption.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item">
                    <div class="faq-header">
                        <h3 class="faq-question">Are your products suitable for diabetics?</h3>
                        <button class="faq-toggle" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, our products are suitable for diabetics as they contain no sugar and have a glycemic
                            index of zero. However, we always recommend consulting with your healthcare provider.</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="faq-item">
                    <div class="faq-header">
                        <h3 class="faq-question">How long do your candies stay fresh?</h3>
                        <button class="faq-toggle" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Our candies have a shelf life of 12 months when stored in a cool, dry place. Once opened, we
                            recommend consuming within 3 months for optimal freshness.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="faq-item">
                    <div class="faq-header">
                        <h3 class="faq-question">Do you ship internationally?</h3>
                        <button class="faq-toggle" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we ship to over 50 countries worldwide. Shipping times and costs vary by location. Check
                            our shipping page for details.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End FAQs Section -->

    <!-- Testimonials Section -->
    <section class="testimonials">
        <!-- Decorative Stars -->
        <img src="<?php echo $ROOT; ?>/views/website/img/star1.svg" alt="Star decoration"
            class="testimonial-star testimonial-star-1">
        <img src="<?php echo $ROOT; ?>/views/website/img/star2.svg" alt="Star decoration"
            class="testimonial-star testimonial-star-2">

        <!-- Section Header -->
        <div class="testimonials-header">
            <h2 class="testimonials-title">TESTIMONIALS</h2>
            <p class="testimonials-subtitle">We proud of making everyone satisfied with our products</p>
        </div>

        <!-- Testimonial Cards Container -->
        <div class="testimonials-cards">
            <!-- Card 1 -->
            <div class="testimonial-card testimonial-card-1">
                <img src="<?php echo $ROOT; ?>/views/website/img/testimonial1.png" alt="Customer testimonial"
                    class="testimonial-image">
                <div class="testimonial-text-container">
                    <p class="testimonial-comment">“For me, it’s all about energy and vibe. Candy Crunch hits just right
                        — bold flavor, not too heavy, and keeps me sharp. Whether I’m in the studio or working late, one
                        pack is enough to keep my flow going. Once you try it, it’s hard to stop.”</p>
                    <p class="testimonial-tag">Hoang Long - Rapper</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="testimonial-card testimonial-card-2">
                <img src="<?php echo $ROOT; ?>/views/website/img/testimonial2.png" alt="Customer testimonial"
                    class="testimonial-image">
                <div class="testimonial-text-container">
                    <p class="testimonial-comment">“I’m pretty picky when it comes to sweets, but Candy Crunch is
                        different~ The flavors are light, colorful, and fun — just like me! Every bite feels cheerful
                        and gives me a little boost of happy energy.”</p>
                    <p class="testimonial-tag">7 Colors Rabbit - Animal</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="testimonial-card testimonial-card-3">
                <img src="<?php echo $ROOT; ?>/views/website/img/testimonial3.png" alt="Customer testimonial"
                    class="testimonial-image">
                <div class="testimonial-text-container">
                    <p class="testimonial-comment">“I’m pretty picky when it comes to sweets, but Candy Crunch is
                        different~ The flavors are light, colorful, and fun — just like me! Every bite feels cheerful
                        and gives me a little boost of happy energy.”</p>
                    <p class="testimonial-tag">C.Ronaldo - GOAT</p>
                </div>
            </div>
        </div>
    </section>
    <!-- End Testimonials Section -->


    <!-- Footer -->
    <footer class="footer">
        <video class="footer-video" autoplay loop muted playsinline>
            <source src="<?php echo $ROOT; ?>/views/website/img/footervideo.mp4" type="video/mp4">
        </video>
        <div class="footer-overlay"></div>
        <div class="upper">
            <div class="line">
                <div class="newsletter">
                    <p class="text-wrapper">Subscribe for 15% off your first order and unlock your inner potential with
                        us.</p>
                    <div class="div-wrapper">
                        <div class="div-wrapper">
                            <div class="field">
                                <div class="text-title">
                                    <div class="text"><input class="div" placeholder="Your Email" type="email" /></div>
                                </div>
                                <div class="button"><img class="solar-arrow-right"
                                        src="<?php echo $ROOT; ?>/views/website/img/solar-arrow-right-linear.svg"
                                        alt="Subscribe" /></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="menus">
                    <div class="menu-footer">
                        <div class="menu">
                            <div class="text-wrapper-2">About us</div>
                        </div>
                        <div class="menu-2">
                            <div class="text-wrapper-2">Shop</div>
                        </div>
                        <div class="menu-2">
                            <div class="text-wrapper-2">Contact</div>
                        </div>
                        <div class="menu-2">
                            <div class="text-wrapper-2">Policy</div>
                        </div>
                    </div>
                    <div class="menu-footer">
                        <div class="menu-3">
                            <div class="text-wrapper-2">FAQ</div>
                        </div>
                        <div class="menu-3">
                            <div class="text-wrapper-2">Shipping Policy</div>
                        </div>
                        <div class="menu-3">
                            <div class="text-wrapper-2">Refund Policy</div>
                        </div>
                        <div class="menu-3">
                            <div class="text-wrapper-2">Journal</div>
                        </div>
                    </div>
                    <div class="menu-footer">
                        <div class="menu-3">
                            <div class="text-wrapper-2">Tiktok</div>
                        </div>
                        <div class="menu-3">
                            <div class="text-wrapper-2">Instagram</div>
                        </div>
                        <div class="menu-3">
                            <div class="text-wrapper-2">Facebook</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="typo">
                <div class="text-2">CANDY CRUNCH</div>
            </div>
        </div>
        <div class="tags">
            <div class="tag">
                <p class="text-wrapper-3">These statements have not been evaluated by the FDA.</p>
            </div>
            <div class="tag">
                <p class="text-wrapper-3">©2025 Innerwork. All Rights Reserved.</p>
            </div>
            <div class="tag">
                <div class="text-wrapper-3">Designed by Group H</div>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Scripts -->
    <!-- GSAP Core + ScrollTrigger Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <script src="<?php echo $ROOT; ?>/views/website/js/main.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/featured_products.js"></script>
    <script src="<?php echo $ROOT; ?>/views/website/js/index.js"></script>
</body>

</html>