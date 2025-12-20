<?php
$ROOT = '/Candy-Crunch-Website';
include('../../../partials/header.php');
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$file = basename($path);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Candy Crunch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="../../website/css/main.css">
  <link rel="stylesheet" href="../../website/css/shop.css">
</head>

<body>
  <!-- Banner -->
  <section class="banner" aria-label="Product listing banner">
    <img class="banner-image"
      alt="Candy banner" src="../../website/img/shop-banner.webp"/>
  </section>

  <!-- Main section -->
  <main class="main-section">
    <!-- Sidebar và Product Listing -->
    <div class="top-section">
      <!-- Sidebar -->
      <aside class="sidebar">
        <h2 class="sidebar-title">Filter Options</h2>
        <div class="sidebar-card">

          <!-- Filter by product type -->

          <section class="filter-section" >
            <h3 class="filter-title">Product Type</h3>


            <div class="filter-options">
              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "On Sales"/>
                <span class="filter-text">On Sales</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "New Product"/>
                <span class="filter-text">New Product</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Best Seller"/>
                <span class="filter-text">Best Seller</span>
              </label>
            </div>
          </section>


          <!-- Filter section: Categories -->
          <section class="filter-section" aria-label="Filter by categories">
            <h3 class="filter-title">Category</h3>


            <div class="filter-options">
              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Hard Candy"/>
                <span class="filter-text">Hard Candy</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Filled-Hard Candy"/>
                <span class="filter-text">Filled-Hard Candy</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Gummy"/>
                <span class="filter-text">Gummy</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Chewing Gum"/>
                <span class="filter-text">Chewing Gum</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Marshmallow"/>
                <span class="filter-text">Marshmallow</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Tet Collection"/>
                <span class="filter-text">Tet Collection</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Christmas Collection"/>
                <span class="filter-text">Christmas Collection</span>
              </label>
            </div>
          </section>


          <!-- Filter section: Ingredients -->
          <section class="filter-section" aria-label="Filter by ingredients">
            <h3 class="filter-title">Ingredients</h3>


            <div class="filter-options">
              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Sugar-free"/>
                <span class="filter-text">Sugar-free</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Xylitol"/>
                <span class="filter-text">Xylitol</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Gluten-free"/>
                <span class="filter-text">Gluten-free</span>
              </label>

              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Gelatin-free"/>
                <span class="filter-text">Gelatin-free</span>
              </label>

            </div>
          </section>


          <!-- Filter section: Flavor -->
          <section class="filter-section" aria-label="Filter by flavor">
            <h3 class="filter-title">Flavor</h3>


            <div class="filter-options">
              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Coffee"/>
                <span class="filter-text">Coffee</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Fruit"/>
                <span class="filter-text">Fruit</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Cola"/>
                <span class="filter-text">Cola</span>
              </label>


              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Vanilla"/>
                <span class="filter-text">Vanilla</span>
              </label>

              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Caramel"/>
                <span class="filter-text">Caramel</span>
              </label>

              <label class="filter-option">
                <input class="filter-checkbox" type="checkbox" data-filter = "Chocolate"/>
                <span class="filter-text">Chocolate</span>
              </label>
            </div>
          </section>


          <!-- Filter section: Rating -->
          <section class="filter-section" aria-label="Filter by rating">
            <h3 class="filter-title">Rating</h3>
            <div class="rating-row" aria-label="Filter by rating">
              <button type="button" class="rating-star-btn" data-value="1">
                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <path d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z" fill="currentColor"/>
                </svg>
              </button>
              <button type="button" class="rating-star-btn" data-value="2">
                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <path d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z" fill="currentColor"/>
                </svg>
              </button>
              <button type="button" class="rating-star-btn" data-value="3">
                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <path d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z" fill="currentColor"/>
                </svg>
              </button>
              <button type="button" class="rating-star-btn" data-value="4">
                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <path d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z" fill="currentColor"/>
                </svg>
              </button>
              <button type="button" class="rating-star-btn" data-value="5">
                <svg class="svg-star" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <path d="M13.8688 20.7906L8.57302 23.9551C8.33907 24.1028 8.09448 24.1661 7.83926 24.145C7.58404 24.1239 7.36073 24.0395 7.16931 23.8918C6.9779 23.7441 6.82902 23.5598 6.72268 23.3387C6.61634 23.1176 6.59507 22.8695 6.65888 22.5944L8.06258 16.6135L3.37294 12.5946C3.16026 12.4047 3.02755 12.1883 2.9748 11.9452C2.92206 11.7022 2.9378 11.4651 3.02202 11.2339C3.10624 11.0026 3.23385 10.8128 3.40485 10.6643C3.57584 10.5157 3.80979 10.4208 4.1067 10.3795L10.2957 9.84149L12.6884 4.2087C12.7948 3.95554 12.9598 3.76567 13.1835 3.63909C13.4073 3.51251 13.6357 3.44922 13.8688 3.44922C14.1019 3.44922 14.3303 3.51251 14.5541 3.63909C14.7778 3.76567 14.9428 3.95554 15.0492 4.2087L17.4418 9.84149L23.6309 10.3795C23.9286 10.4216 24.1626 10.5166 24.3327 10.6643C24.5029 10.8119 24.6305 11.0018 24.7156 11.2339C24.8006 11.4659 24.8168 11.7035 24.7641 11.9465C24.7113 12.1895 24.5782 12.4056 24.3646 12.5946L19.675 16.6135L21.0787 22.5944C21.1425 22.8686 21.1212 23.1167 21.0149 23.3387C20.9086 23.5606 20.7597 23.745 20.5683 23.8918C20.3769 24.0387 20.1535 24.123 19.8983 24.145C19.6431 24.1669 19.3985 24.1036 19.1646 23.9551L13.8688 20.7906Z" fill="currentColor"/>
                </svg>
              </button>
          </div>
        </section>
      </div>
     </aside>

     
     <div class="right-container">
      <div class="filter-result">
        <!-- row 1: result text + search + sort -->
        <div class="filter-result-top">
          <p class="filter-result-text">Showing 6 of 32 Products</p>


          <div class="filter-controls">
            <div class="search-box">
              <input class="search-input" type="search" placeholder="Search products..." />
              <svg class="search-icon" width="18" height="18" viewBox="0 0 16 24" fill="none"
                aria-hidden="true">
                <path
                  d="M21 21L16.65 16.65M10.5 18C6.35786 18 3 14.6421 3 10.5C3 6.35786 6.35786 3 10.5 3C14.6421 3 18 6.35786 18 10.5C18 14.6421 14.6421 18 10.5 18Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" />
              </svg>
            </div>


            <div class="sort-box">
              <span class="sort-label">Sort by:</span>
              <div class="sort-select-wrapper">
              <select class="sort-select">
                <option>Waiting payment</option>
                <option>Newest</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
              </select>

              <span class="dropdown-arrow">
                <!-- icon drop-down -->
                <svg class="icon-down" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M13.3145 5.34271L7.99988 10.6573L2.6853 5.34271" stroke="#FAFAFA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              
                <!-- icon drop-up -->
                <svg class="icon-up" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2.71582 10.6421L8.00004 5.35788L13.2843 10.6421" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>

            </div>
          </div>
        </div>


        <!-- row 2: tags -->
        <div class="filter-tags" aria-label="Active filter tags">
          <h3 class="filter-tags-title">Filter:</h3>
          <div class="filter-tags-list">
            
          </div>
        </div>
      </div>

      <div class="product-listing" id = "productContainer">
      </div>




     </div>


    </div>



    <!-- Pagination -->
    <div class="pagination">
      <div class="page-list" aria-label="Pagination">
        <button class="page-item previous-page-btn" aria-label="Previous page">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
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
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M3.48438 1.23438C3.63081 1.08794 3.86918 1.08795 4.01562 1.23438L8.51562 5.73438C8.66207 5.88082 8.66207 6.11918 8.51562 6.26562L4.01562 10.7656C3.86918 10.9121 3.63082 10.9121 3.48438 10.7656C3.33795 10.6192 3.33794 10.3808 3.48438 10.2344L7.71973 6L3.48438 1.76562C3.33795 1.61918 3.33794 1.38081 3.48438 1.23438Z" fill="#017E6A"/>
          </svg>
        </button>
      </div>

      <div class="pagination-controls">
        <div class="page-info" aria-live="polite">
          <span class="page-info-current" data-page="1">1</span>
          <span class="page-info-separator">/</span>
          <span class="page-info-total" data-total="10">10</span>
        </div>
      
        <div class="page-jump">
          <label for="page-jump-input">Go to</label>
          <input
            id="page-jump-input"
            class="page-jump-input"
            type="number"
            min="1"
            value="1"
            inputmode="numeric"
          />
        </div>
      </div>
    </div>

  </main>

    <!-- Load script at end of body -->
    <script src="../js/shop_1.js"></script>


</body>

</html>

<?php
include '../../../partials/footer_kovid.php';

?>
