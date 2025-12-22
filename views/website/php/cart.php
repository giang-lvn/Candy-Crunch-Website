<?php
// Cart Partial Component
// This file should be included in header.php, not used as a standalone page
?>

<!-- Cart CSS - loaded via header.php -->
<link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/cart.css">

<!-- OVERLAY -->
<div class="cart-overlay hidden" id="cart-overlay">
    <!-- GIỎ HÀNG -->
    <?php include __DIR__ . '/cart_content.php'; ?>
</div>
<!-- End Cart Partial -->