<?php
// views/website/php/productdetail-new.php
$ROOT = '/Candy-Crunch-Website';
include(__DIR__ . '/../../../partials/header.php');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title><?php echo htmlspecialchars($product['ProductName'] ?? 'Product Detail'); ?> - Candy Crunch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/main.css">
    <link rel="stylesheet" href="<?php echo $ROOT; ?>/views/website/css/productdetail.css">
</head>

<body>

    <div class="product-detail">
        <div class="product-detail-container">
            <!-- Image -->
            <div class="thumbnail">

                <div class="main-thumb-image">
                    <?php
                    // Sử dụng thumbnail image từ controller (đã parse từ JSON)
                    $mainImage = !empty($thumbnailImage) ? $thumbnailImage : '/Candy-Crunch-Website/views/website/img/product-img/main-thumb-example.png';
                    ?>
                    <img src="<?php echo htmlspecialchars($mainImage); ?>"
                        alt="<?php echo htmlspecialchars($product['ProductName'] ?? 'Product'); ?>" id="main-image">
                    <?php if (!empty($ingredients)): ?>
                        <section class="tag">
                            <?php foreach ($ingredients as $ingredient): ?>
                                <span class="product-tag"><?php echo htmlspecialchars($ingredient); ?></span>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                </div>

                <div class="gallery">
                    <?php if (!empty($productImages)): ?>
                        <?php foreach ($productImages as $image): ?>
                            <?php if (!empty($image['path'])): ?>
                                <img class="preview-image" src="<?php echo htmlspecialchars($image['path']); ?>"
                                    alt="<?php echo htmlspecialchars($product['ProductName'] ?? ''); ?>"
                                    onclick="changeImage('<?php echo htmlspecialchars($image['path']); ?>')">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- end Image -->

            <!-- Product Description -->
            <div class="product-description">

                <!-- Product Info -->
                <div class="product-info">

                    <!-- Product Info top-->
                    <div class="product-info-top">

                        <section class="title-rating-price">
                            <!-- Title and Rating -->
                            <section class="title-rating">
                                <h3 class="product-name">
                                    <?php echo htmlspecialchars($product['ProductName'] ?? 'Unknown Product'); ?>
                                </h3>
                                <div class="review-rating">
                                    <div class="rating-container">
                                        <span class="rating-number">4.9</span>
                                        <span class="rating-star">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M12.0601 18.0795L7.45505 20.8312C7.25162 20.9596 7.03893 21.0147 6.817 20.9963C6.59508 20.978 6.40089 20.9046 6.23444 20.7762C6.06799 20.6478 5.93854 20.4874 5.84607 20.2952C5.7536 20.1029 5.7351 19.8872 5.79058 19.648L7.01119 14.4472L2.93325 10.9525C2.74831 10.7874 2.63291 10.5992 2.58704 10.3878C2.54118 10.1765 2.55486 9.97032 2.6281 9.76926C2.70133 9.5682 2.8123 9.40309 2.96099 9.27395C3.10968 9.1448 3.31312 9.06225 3.57129 9.02629L8.95307 8.5585L11.0337 3.66042C11.1261 3.44028 11.2696 3.27517 11.4642 3.1651C11.6588 3.05503 11.8574 3 12.0601 3C12.2628 3 12.4614 3.05503 12.656 3.1651C12.8505 3.27517 12.994 3.44028 13.0865 3.66042L15.1671 8.5585L20.5489 9.02629C20.8078 9.06298 21.0112 9.14553 21.1592 9.27395C21.3071 9.40236 21.4181 9.56746 21.4921 9.76926C21.566 9.97105 21.5801 10.1776 21.5342 10.3889C21.4884 10.6003 21.3726 10.7881 21.1869 10.9525L17.109 14.4472L18.3296 19.648C18.385 19.8865 18.3666 20.1022 18.2741 20.2952C18.1816 20.4882 18.0522 20.6485 17.8857 20.7762C17.7193 20.9039 17.5251 20.9772 17.3031 20.9963C17.0812 21.0154 16.8685 20.9604 16.6651 20.8312L12.0601 18.0795Z"
                                                    fill="#FDBA06" />
                                            </svg>
                                        </span>
                                    </div>
                                    <span class="review">230 reviews</span>
                                </div>

                            </section>

                            <!-- Price -->
                            <section class="product-price">
                                <?php
                                $originalPrice = $product['OriginalPrice'] ?? 0;
                                $promotionPrice = $product['PromotionPrice'] ?? $originalPrice;
                                $displayPrice = $promotionPrice > 0 ? $promotionPrice : $originalPrice;
                                ?>
                                <span class="new-price" id="price-new">
                                    <?php echo number_format($displayPrice, 0, ',', '.'); ?> VND
                                </span>
                                <?php if ($promotionPrice > 0 && $promotionPrice < $originalPrice): ?>
                                    <span class="old-price" id="price-old">
                                        <?php echo number_format($originalPrice, 0, ',', '.'); ?> VND
                                    </span>
                                <?php endif; ?>
                            </section>
                        </section>

                        <p class="product-description-container">
                            <?php echo htmlspecialchars($product['Description'] ?? 'No description available'); ?>
                        </p>

                    </div>
                    <!-- end Product Info top-->


                    <!-- adjust quantity and attributes -->
                    <div class="adjust">
                        <div class="attributes">
                            <span class="attribute-title">Unit</span>

                            <div class="attribute-select-wrapper">

                                <select class="attribute-select" id="sku-select" onchange="updateSkuInfo(this.value)">
                                    <?php if (empty($skuList)): ?>
                                        <option value="">No units available</option>
                                    <?php else: ?>
                                        <?php foreach ($skuList as $sku): ?>
                                            <option value="<?php echo $sku['SKUID']; ?>"
                                                data-price="<?php echo $sku['PromotionPrice'] ?? $sku['OriginalPrice']; ?>"
                                                data-original="<?php echo $sku['OriginalPrice']; ?>"
                                                data-stock="<?php echo $sku['Stock'] ?? 0; ?>"
                                                data-image="<?php echo $sku['Image'] ?? ''; ?>">
                                                <?php echo htmlspecialchars($sku['Attribute'] ?? 'Default'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <span class="dropdown-arrow">
                                    <!-- icon drop-down -->
                                    <svg class="icon-down" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path d="M13.3145 5.34271L7.99988 10.6573L2.6853 5.34271"
                                            stroke="var(--text-black)" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                    <!-- icon drop-up -->
                                    <svg class="icon-up" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <path d="M2.71582 10.6421L8.00004 5.35788L13.2843 10.6421"
                                            stroke="var(--text-black)" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>

                            </div>

                        </div>


                        <!-- quantity adjust -->
                        <div class="quantity-adjust">
                            <div class="quantity">
                                <button class="quantity-btn" onclick="decreaseQuantity()">-</button>

                                <span class="quantity-number" id="quantity-display">1</span>

                                <button class="quantity-btn" onclick="increaseQuantity()">+</button>

                            </div>
                            <span class="quantity-stock" id="stock-display">
                                <?php echo (int) ($defaultSku['Stock'] ?? $product['Stock'] ?? 0); ?> in stock
                            </span>
                        </div>

                    </div>
                    <!-- end adjust quantity and attributes -->


                    <!-- product actions -->
                    <div class="product-action">

                        <button class="btn-primary-medium" id="btn-buy-now">Buy now</button>
                        <button class="btn-primary-outline-medium" id="btn-add-to-cart">Add to Cart</button>
                        <button class="btn-icon-primary-outline-small-square" id="btn-wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M12 1.75C12.862 1.75 13.6893 2.09266 14.2988 2.70215C14.9082 3.31162 15.25 4.13816 15.25 5V6.5L15.7354 6.51465C17.0204 6.55359 17.6495 6.69593 18.1074 7.07617V7.0752C18.4229 7.33727 18.6455 7.70404 18.8438 8.32617C19.0462 8.96152 19.205 9.80357 19.4268 10.9863L20.1768 14.9863C20.4881 16.6473 20.7102 17.8404 20.75 18.7549C20.7843 19.5431 20.6791 20.0519 20.4268 20.4385L20.3096 20.5967C19.9729 21.0021 19.4726 21.2418 18.5801 21.3691C17.6738 21.4984 16.4603 21.5 14.7705 21.5H9.23047C7.54006 21.5 6.32608 21.4984 5.41992 21.3691C4.52787 21.2418 4.02806 21.0021 3.69141 20.5967C3.35486 20.1913 3.2115 19.6557 3.25098 18.7549C3.29105 17.8403 3.51339 16.6474 3.82422 14.9863L4.57422 10.9863C4.79656 9.80388 4.95487 8.96178 5.15723 8.32617C5.35528 7.70411 5.57758 7.33712 5.89258 7.0752L5.89355 7.07617C6.35152 6.69593 6.98061 6.55359 8.26562 6.51465L8.75 6.5V5C8.75 4.13816 9.0928 3.31162 9.70215 2.70215C10.3115 2.09277 11.1382 1.75013 12 1.75ZM14.1719 11.1104C13.4859 10.87 12.6984 11.025 12 11.5391C11.3018 11.0253 10.5149 10.87 9.8291 11.1104C9.01314 11.3964 8.5 12.1866 8.5 13.1973C8.5001 13.8742 8.89184 14.4967 9.31445 14.9854C9.75016 15.4891 10.2943 15.9359 10.7471 16.2686L10.748 16.2695C11.1335 16.5522 11.4795 16.828 12 16.8281C12.5219 16.8281 12.8676 16.5521 13.2529 16.2695L13.2539 16.2686C13.7067 15.9359 14.2508 15.4893 14.6865 14.9854C15.1092 14.4964 15.4999 13.8737 15.5 13.1963C15.5 12.1864 14.9876 11.3963 14.1719 11.1104ZM12 2.25C11.2708 2.25013 10.5713 2.54005 10.0557 3.05566C9.54009 3.57137 9.25 4.27077 9.25 5V6.5H14.75V5C14.75 4.27077 14.4609 3.57137 13.9453 3.05566C13.4296 2.53994 12.7293 2.25 12 2.25Z"
                                    fill="#017E6A" stroke="#017E6A" />
                            </svg>
                        </button>
                    </div>
                    <!-- end product actions -->
                </div>
                <!-- end product info -->

                <div class="social">
                    <span class="icon-earth">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                            <path
                                d="M16.7959 22.8445C18.0309 20.517 22.1472 20.517 22.1472 20.517C26.4372 20.472 27.0172 17.867 27.4047 16.5508C27.0629 19.2772 25.8328 21.8152 23.9045 23.7727C21.9762 25.7302 19.4569 26.9982 16.7359 27.3808C16.3322 26.5308 15.8547 24.6183 16.7959 22.8445Z"
                                fill="#017E6A" />
                            <path
                                d="M6.25751 7.29305L5.74251 6.85305C5.7004 6.8161 5.65955 6.77774 5.62001 6.73805C3.60524 9.01839 2.49535 11.9576 2.50001 15.0005C2.50001 21.8255 7.97126 27.3755 14.7675 27.498C14.3263 26.1793 14.0375 24.043 15.14 21.9668C16.1525 20.0593 18.19 19.308 19.4875 18.988C20.3328 18.7841 21.1971 18.6689 22.0663 18.6443H22.1325C23.8588 18.6243 24.5288 18.1155 24.8375 17.7668C25.22 17.3368 25.37 16.8268 25.5788 16.1155L25.6063 16.023C25.7249 15.6195 25.9758 15.2677 26.3186 15.0239C26.6614 14.7802 27.0762 14.6588 27.4963 14.6793C27.4235 11.6803 26.2703 8.80833 24.2488 6.5918C24.2088 6.81305 24.1613 7.02055 24.115 7.20305C23.9025 8.0293 23.5475 8.9218 23.11 9.5843C22.6838 10.2343 21.9238 10.813 21.42 11.1755C21.0388 11.4493 20.6488 11.6755 20.3288 11.8618L20.2138 11.928C19.9238 12.0943 19.6938 12.228 19.4738 12.3718C19.0288 12.6655 18.7613 12.9268 18.5813 13.2805C18.6913 13.683 18.7688 14.1468 18.7713 14.6305C18.7738 15.783 18.1838 16.693 17.48 17.2605C16.7709 17.8277 15.888 18.1331 14.98 18.1255C11.2925 18.0855 9.13001 15.0768 8.85126 11.978C8.77001 11.0768 8.36501 10.1055 7.79876 9.20055C7.36522 8.50389 6.84758 7.86324 6.25751 7.29305Z"
                                fill="#017E6A" />
                            <path
                                d="M10.7191 11.8088C10.4854 9.20376 8.47664 6.77626 7.50039 5.88876L6.96289 5.42626C9.21239 3.53223 12.0597 2.49559 15.0004 2.50001C17.7679 2.50001 20.3254 3.40001 22.3954 4.92001C22.6879 5.80876 22.1304 7.66501 21.5454 8.55251C21.3329 8.87376 20.8529 9.27376 20.3254 9.65251C19.1379 10.5063 17.6379 10.9275 16.8754 12.5C16.6848 12.8959 16.6478 13.3484 16.7716 13.77C16.8466 14.045 16.8966 14.3438 16.8966 14.635C16.8991 15.5788 15.9441 16.26 15.0004 16.25C12.5454 16.2238 10.9379 14.245 10.7191 11.8088Z"
                                fill="#017E6A" />
                        </svg>
                    </span>

                    <div class="social-content">
                        <h4 class="content-title">Sustainable Development and Nutrition Access</h4>
                        <p class="content-description">At no additional cost, for every purchase you make, we will
                            allocate 1% toward donations to UNICEF's child support funds.</p>
                    </div>
                </div>

            </div>
        </div>


        <!-- description section -->
        <div class="description-section">
            <div class="description-content">
                <h4 class="description-title">Description</h4>
                <p class="description-text collapsed">
                    This product is thoughtfully crafted to bring a satisfying and enjoyable experience with every
                    piece. Using carefully selected ingredients and consistent production standards, it delivers a
                    balanced flavor and pleasant texture that candy lovers can appreciate. Whether you’re looking for a
                    small treat during the day or something to share with others, this product fits effortlessly into
                    any moment.

                    Designed with convenience in mind, the product is easy to store, easy to enjoy, and simple to order
                    online. Each package is prepared to maintain freshness and quality from the first bite to the last.
                    Suitable for personal enjoyment, gifting, or adding a touch of sweetness to your routine, it offers
                    a reliable choice for everyday indulgence.

                    With its versatile appeal and dependable quality, this product supports a comfortable and enjoyable
                    snacking experience. It’s perfect for those who value taste, consistency, and ease of ordering,
                    making it a great addition to any candy collection.
                </p>
            </div>
            <button class="btn-secondary-outline-small">See more</button>
        </div>
        <!-- end description section -->


        <!-- customer feedback section -->
        <div class="customer-feedback-section">
            <span class="customer-feedback-title">CUSTOMER FEEDBACK</span>
            <div class="customer-feedback-content">
                <div class="rating-summary">
                    <!-- Average rating -->
                    <div class="rating-summary__header">
                        <div class="rating-summary__average">4.0</div>
                        <div class="rating-summary__stars">
                            <span class="rating-star-btn">
                                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 28 28" fill="none">
                                    <path
                                        d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z"
                                        fill="var(--yellow-500)" />
                                </svg>
                            </span>
                            <span class="rating-star-btn">
                                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 28 28" fill="none">
                                    <path
                                        d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z"
                                        fill="var(--yellow-500)" />
                                </svg>
                            </span>
                            <span class="rating-star-btn">
                                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 28 28" fill="none">
                                    <path
                                        d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z"
                                        fill="var(--yellow-500)" />
                                </svg>
                            </span>
                            <span class="rating-star-btn">
                                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 28 28" fill="none">
                                    <path
                                        d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z"
                                        fill="var(--yellow-500)" />
                                </svg>
                            </span>
                            <span class="rating-star-btn">
                                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 28 28" fill="none">
                                    <path
                                        d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z"
                                        fill="var(--gray-500)" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Progress bars -->
                    <div class="rating-summary__list">
                        <!-- 5 sao -->
                        <div class="rating-row">
                            <span class="rating-row__label">5.0</span>
                            <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="5 star"
                                class="rating-row__icon" />
                            <div class="rating-row__bar">
                                <div class="rating-row__bar-fill" style="width: 80%;"></div>
                            </div>
                        </div>

                        <!-- 4 sao -->
                        <div class="rating-row">
                            <span class="rating-row__label">4.0</span>
                            <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="4 star"
                                class="rating-row__icon" />
                            <div class="rating-row__bar">
                                <div class="rating-row__bar-fill" style="width: 70%;"></div>
                            </div>
                        </div>

                        <!-- 3 sao -->
                        <div class="rating-row">
                            <span class="rating-row__label">3.0</span>
                            <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="3 star"
                                class="rating-row__icon" />
                            <div class="rating-row__bar">
                                <div class="rating-row__bar-fill" style="width: 60%;"></div>
                            </div>
                        </div>

                        <!-- 2 sao -->
                        <div class="rating-row">
                            <span class="rating-row__label">2.0</span>
                            <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="2 star"
                                class="rating-row__icon" />
                            <div class="rating-row__bar">
                                <div class="rating-row__bar-fill" style="width: 50%;"></div>
                            </div>
                        </div>

                        <!-- 1 sao -->
                        <div class="rating-row">
                            <span class="rating-row__label">1.0</span>
                            <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="1 star"
                                class="rating-row__icon" />
                            <div class="rating-row__bar">
                                <div class="rating-row__bar-fill" style="width: 40%;"></div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="customer-feedback-card">

                    <!-- Card 1 -->
                    <div class="card-comment">
                        <div class="card-top">
                            <div class="user">
                                <span class="username">Thomas Shelby</span>
                                <div class="rating-comment">
                                    <span class="rating-number">4.0</span>
                                    <span class="rating-star">
                                        <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="star" />
                                    </span>

                                </div>
                            </div>
                            <span class="created-time">Last 20 days</span>
                        </div>

                        <span class="comment-text">I have to admit, KitKat really puts their heart into making
                            chocolate!<br> The wafer is perfectly crispy, the chocolate is rich but not too sweet,
                            and once you have one bar, you immediately want to break another 😆<br> Whenever I’m
                            stressed, snapping a KitKat bar instantly makes me feel lighter. Truly a classic,
                            well-deserving 10/10!
                        </span>
                    </div>

                    <!-- Card 2 -->
                    <div class="card-comment">
                        <div class="card-top">
                            <div class="user">
                                <span class="username">Thomas Shelby</span>
                                <div class="rating-comment">
                                    <span class="rating-number">4.0</span>
                                    <span class="rating-star">
                                        <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="star" />
                                    </span>

                                </div>
                            </div>
                            <span class="created-time">Last 10 days</span>
                        </div>

                        <span class="comment-text">I have to admit, KitKat really puts their heart into making
                            chocolate!<br> The wafer is perfectly crispy, the chocolate is rich but not too sweet,
                            and once you have one bar, you immediately want to break another 😆<br> Whenever I’m
                            stressed, snapping a KitKat bar instantly makes me feel lighter. Truly a classic,
                            well-deserving 10/10!
                        </span>
                    </div>

                    <?php 
                    // Dynamic Customer Reviews from Database
                    if (!empty($customerReviews)): 
                        foreach ($customerReviews as $review):
                            // Calculate relative time
                            $reviewDate = new DateTime($review['CreateDate']);
                            $now = new DateTime();
                            $diff = $now->diff($reviewDate);
                            
                            if ($diff->days == 0) {
                                $timeAgo = 'Today';
                            } elseif ($diff->days == 1) {
                                $timeAgo = 'Yesterday';
                            } elseif ($diff->days < 30) {
                                $timeAgo = 'Last ' . $diff->days . ' days';
                            } elseif ($diff->m < 12) {
                                $timeAgo = $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
                            } else {
                                $timeAgo = $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
                            }
                    ?>
                    <!-- Customer Review Card -->
                    <div class="card-comment">
                        <div class="card-top">
                            <div class="user">
                                <span class="username"><?php echo htmlspecialchars($review['CustomerName']); ?></span>
                                <div class="rating-comment">
                                    <span class="rating-number"><?php echo number_format($review['Rating'], 1); ?></span>
                                    <span class="rating-star">
                                        <img src="<?php echo $ROOT; ?>/views/website/img/Icon _ Star.svg" alt="star" />
                                    </span>
                                </div>
                            </div>
                            <span class="created-time"><?php echo $timeAgo; ?></span>
                        </div>

                        <?php if (!empty($review['Comment'])): ?>
                        <span class="comment-text"><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></span>
                        <?php else: ?>
                        <span class="comment-text"><em>No comment provided.</em></span>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>

                    <!-- Pagination -->
                    <div class="pagination">
                        <div class="page-list" aria-label="Pagination">
                            <button class="page-item previous-page-btn" aria-label="Previous page">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"
                                    fill="none">
                                    <path
                                        d="M7.98438 1.23438C8.13068 1.08808 8.36817 1.08837 8.51465 1.23438C8.66109 1.38082 8.66109 1.6182 8.51465 1.76465L4.28027 6L8.51465 10.2344C8.66109 10.3808 8.66109 10.6182 8.51465 10.7646C8.3682 10.9111 8.13082 10.9111 7.98438 10.7646L3.48438 6.26465C3.33836 6.11817 3.33807 5.88068 3.48438 5.73438L7.98438 1.23438Z"
                                        fill="#017E6A" />
                                </svg>
                            </button>

                            <button class="page-item page-btn">1</button>
                            <button class="page-item page-btn is-active">2</button>
                            <button class="page-item page-btn">3</button>
                            <span class="page-item page-ellipsis">…</span>
                            <button class="page-item page-btn">10</button>

                            <button class="page-item next-page-btn" aria-label="Next page">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"
                                    fill="none">
                                    <path
                                        d="M3.48438 1.23438C3.63081 1.08794 3.86918 1.08795 4.01562 1.23438L8.51562 5.73438C8.66207 5.88082 8.66207 6.11918 8.51562 6.26562L4.01562 10.7656C3.86918 10.9121 3.63082 10.9121 3.48438 10.7656C3.33795 10.6192 3.33794 10.3808 3.48438 10.2344L7.71973 6L3.48438 1.76562C3.33795 1.61918 3.33794 1.38081 3.48438 1.23438Z"
                                        fill="#017E6A" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end customer feedback section -->




    <!-- Related product -->
    <div class="related-product-section">
        <h2 class="related-product-title">YOU MIGHT ALSO LIKE</h2>
        <div class="related-product-list">
            <span class="slide-nav">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="#212121" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </span>
            <section class="product-grid">
                <?php if (!empty($relatedProducts)): ?>
                    <?php foreach ($relatedProducts as $item): ?>
                        <article class="product-card">
                            <img class="product-image" src="<?php echo htmlspecialchars($item['Thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($item['ProductName']); ?>" style="cursor: pointer;"
                                onclick="window.location.href='<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=<?php echo $item['ProductID']; ?>'" />

                            <div class="product-info">
                                <div class="product-top">
                                    <h4 class="product-name" style="cursor: pointer;"
                                        onclick="window.location.href='<?php echo $ROOT; ?>/controllers/website/ProductDetailNewController.php?productId=<?php echo $item['ProductID']; ?>'">
                                        <?php echo htmlspecialchars($item['ProductName']); ?>
                                    </h4>

                                    <div class="product-rating">
                                        <span class="rating-number">4.9</span>
                                        <span class="rating-star">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none">
                                                <path
                                                    d="M12.0601 18.0795L7.45505 20.8312C7.25162 20.9596 7.03893 21.0147 6.817 20.9963C6.59508 20.978 6.40089 20.9046 6.23444 20.7762C6.06799 20.6478 5.93854 20.4874 5.84607 20.2952C5.7536 20.1029 5.7351 19.8872 5.79058 19.648L7.01119 14.4472L2.93325 10.9525C2.74831 10.7874 2.63291 10.5992 2.58704 10.3878C2.54118 10.1765 2.55486 9.97032 2.6281 9.76926C2.70133 9.5682 2.8123 9.40309 2.96099 9.27395C3.10968 9.1448 3.31312 9.06225 3.57129 9.02629L8.95307 8.5585L11.0337 3.66042C11.1261 3.44028 11.2696 3.27517 11.4642 3.1651C11.6588 3.05503 11.8574 3 12.0601 3C12.2628 3 12.4614 3.05503 12.656 3.1651C12.8505 3.27517 12.994 3.44028 13.0865 3.66042L15.1671 8.5585L20.5489 9.02629C20.8078 9.06298 21.0112 9.14553 21.1592 9.27395C21.3071 9.40236 21.4181 9.56746 21.4921 9.76926C21.566 9.97105 21.5801 10.1776 21.5342 10.3889C21.4884 10.6003 21.3726 10.7881 21.1869 10.9525L17.109 14.4472L18.3296 19.648C18.385 19.8865 18.3666 20.1022 18.2741 20.2952C18.1816 20.4882 18.0522 20.6485 17.8857 20.7762C17.7193 20.9039 17.5251 20.9772 17.3031 20.9963C17.0812 21.0154 16.8685 20.9604 16.6651 20.8312L12.0601 18.0795Z"
                                                    fill="#FDBA06" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <div class="product-price">
                                    <?php
                                    $rOriginal = $item['OriginalPrice'] ?? 0;
                                    $rPromo = $item['PromotionPrice'] ?? 0;
                                    // Assuming logic: if Promo > 0 and < Original, show both. Else show Original (or Promo if Original is 0/lower?)
                                    // Standard logic: 
                                    // Display = Promo if > 0, else Original
                                    $rDisplay = ($rPromo > 0) ? $rPromo : $rOriginal;
                                    ?>

                                    <?php if ($rPromo > 0 && $rPromo < $rOriginal): ?>
                                        <span class="old-price"><?php echo number_format($rOriginal, 0, ',', '.'); ?> VND</span>
                                    <?php endif; ?>
                                    <span class="new-price"><?php echo number_format($rDisplay, 0, ',', '.'); ?> VND</span>
                                </div>

                                <div class="product-actions">
                                    <button class="btn-primary-small"
                                        onclick="addRelatedToCart('<?php echo $item['SKUID']; ?>', this)">
                                        Add to Cart
                                    </button>
                                    <button class="btn-icon-primary-outline-small-square"
                                        onclick="toggleRelatedWishlist('<?php echo $item['ProductID']; ?>', this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none">
                                            <path
                                                d="M12 1.75C12.862 1.75 13.6893 2.09266 14.2988 2.70215C14.9082 3.31162 15.25 4.13816 15.25 5V6.5L15.7354 6.51465C17.0204 6.55359 17.6495 6.69593 18.1074 7.07617V7.0752C18.4229 7.33727 18.6455 7.70404 18.8438 8.32617C19.0462 8.96152 19.205 9.80357 19.4268 10.9863L20.1768 14.9863C20.4881 16.6473 20.7102 17.8404 20.75 18.7549C20.7843 19.5431 20.6791 20.0519 20.4268 20.4385L20.3096 20.5967C19.9729 21.0021 19.4726 21.2418 18.5801 21.3691C17.6738 21.4984 16.4603 21.5 14.7705 21.5H9.23047C7.54006 21.5 6.32608 21.4984 5.41992 21.3691C4.52787 21.2418 4.02806 21.0021 3.69141 20.5967C3.35486 20.1913 3.2115 19.6557 3.25098 18.7549C3.29105 17.8403 3.51339 16.6474 3.82422 14.9863L4.57422 10.9863C4.79656 9.80388 4.95487 8.96178 5.15723 8.32617C5.35528 7.70411 5.57758 7.33712 5.89258 7.0752L5.89355 7.07617C6.35152 6.69593 6.98061 6.55359 8.26562 6.51465L8.75 6.5V5C8.75 4.13816 9.0928 3.31162 9.70215 2.70215C10.3115 2.09277 11.1382 1.75013 12 1.75ZM14.1719 11.1104C13.4859 10.87 12.6984 11.025 12 11.5391C11.3018 11.0253 10.5149 10.87 9.8291 11.1104C9.01314 11.3964 8.5 12.1866 8.5 13.1973C8.5001 13.8742 8.89184 14.4967 9.31445 14.9854C9.75016 15.4891 10.2943 15.9359 10.7471 16.2686L10.748 16.2695C11.1335 16.5522 11.4795 16.828 12 16.8281C12.5219 16.8281 12.8676 16.5521 13.2529 16.2695L13.2539 16.2686C13.7067 15.9359 14.2508 15.4893 14.6865 14.9854C15.1092 14.4964 15.4999 13.8737 15.5 13.1963C15.5 12.1864 14.9876 11.3963 14.1719 11.1104ZM12 2.25C11.2708 2.25013 10.5713 2.54005 10.0557 3.05566C9.54009 3.57137 9.25 4.27077 9.25 5V6.5H14.75V5C14.75 4.27077 14.4609 3.57137 13.9453 3.05566C13.4296 2.53994 12.7293 2.25 12 2.25Z"
                                                fill="#017E6A" stroke="#017E6A" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No related products found.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
    </div>

    <script>
        // Truyền ROOT sang JavaScript
        const ROOT = '<?php echo $ROOT; ?>';
        const productId = '<?php echo $product['ProductID']; ?>';
    </script>
    <script src="<?php echo $ROOT; ?>/views/website/js/productdetail.js"></script>
    <script>
        // Khởi tạo maxStock từ PHP
        setMaxStock(<?php echo (int) ($defaultSku['Stock'] ?? $product['Stock'] ?? 0); ?>);
    </script>

</body>

</html>

<? include('footer_kovid.php'); ?>