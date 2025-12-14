<?php


// Bắt đầu Session trước khi gửi bất kỳ output nào
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$ROOT = '/Candy-Crunch-Website'; 

$BASE_PATH = realpath(__DIR__ . '/../../../');
// ----------------------------------------------------

// *** PHẦN GIẢ LẬP TRẠNG THÁI *** (Giữ nguyên)
// ...
?>

<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candy Crunch - Taste the Potential!</title>
    
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    
</head>
<body>
    
    <?php 

    include $BASE_PATH . "/partials/header.php"; 
    ?>
    
    <main class="main-content">
        
        <section id="hero" class="hero-section container">
            <div class="hero-content">
                <h1 class="text-heading-1">Taste the Crunch, Unlock Your Inner Potential!</h1>
                <p class="text-body-xlarge-regular">We offer the finest artisanal candies designed not just for flavor, but for a boost of natural energy and focus.</p>
                <a href="<?php echo $ROOT; ?>/shop.php" class="btn btn-primary">Shop Now</a>
            </div>
            <div class="hero-image">
                                </div>
        </section>
        
        <section id="showcase" class="product-showcase-section container">
            <h2 class="text-heading-2">Featured Crunchies</h2>
            <div class="product-grid">
                <div class="product-card">Product 1 - Boost Berry</div>
                <div class="product-card">Product 2 - Focus Fusion</div>
                <div class="product-card">Product 3 - Calm Cocoa</div>
            </div>
            <a href="<?php echo $ROOT; ?>/shop.php" class="btn btn-secondary">View All Products</a>
        </section>
        
        <section id="mission" class="mission-section container">
            <h2 class="text-heading-3">Our Sweet Mission</h2>
            <p class="text-body-large-regular">Candy Crunch believes in combining pure joy with functional nutrition. Every bite is a step toward a brighter, more focused you. We use natural ingredients and sustainable practices.</p>
        </section>
        
    </main>
    
    <?php 
    include $BASE_PATH . "/partials/footer_kovid.php"; 
    ?>
    
</body>
</html>