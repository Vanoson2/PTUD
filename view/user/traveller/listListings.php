<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Get search parameters
$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? 1;

// Calculate number of nights
$nights = 0;
if ($checkin && $checkout) {
  $checkinDate = strtotime($checkin);
  $checkoutDate = strtotime($checkout);
  $nights = round(($checkoutDate - $checkinDate) / (60 * 60 * 24));
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cType&Amenties.php');
include_once(__DIR__ . '/../../../controller/cListing.php');

// Get place types from database
$cType = new cTypeAndAmenties();
$placeTypesResult = $cType->cGetAllTypes();
$placeTypes = [];
if ($placeTypesResult) {
  while ($row = $placeTypesResult->fetch_assoc()) {
    $placeTypes[] = $row;
  }
}

// Get amenities from database
$amenitiesResult = $cType->cGetAllAmenities();
$amenities = [];
if ($amenitiesResult) {
  while ($row = $amenitiesResult->fetch_assoc()) {
    $amenities[] = $row;
  }
}

// Fetch listings from database based on search location
$listings = [];
$cListing = new cListing();
if (!empty($location)) {
  // Sử dụng search với filters: location, checkin, checkout, guests
  $listingsResult = $cListing->cSearchListingsWithFilters($location, $checkin, $checkout, $guests);
  if ($listingsResult) {
    while ($row = $listingsResult->fetch_assoc()) {
      $listings[] = $row;
    }
  }
}

$totalResults = count($listings);
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<!-- Page-specific CSS and JavaScript -->
<link rel="stylesheet" href="../../../view/css/search-form.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../../view/css/listListing.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../../view/css/listing-search.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10ns/vn.js"></script>
<script defer src="../../../public/js/autocomplete.js"></script>
<script defer src="../../../public/js/guestscounter.js"></script>
<script defer src="../../../public/js/date-picker.js?v=<?php echo time(); ?>"></script>

<!-- Search Form at Top -->
<div class="search-results-container">
  <div class="search-results-header">
    <h1 class="search-results-title">
      <?php echo $totalResults; ?>+ chỗ ở tại <?php echo htmlspecialchars($location); ?>
    </h1>
    <div class="search-results-info">
      <span><?php echo date('d/m', strtotime($checkin)); ?> - <?php echo date('d/m', strtotime($checkout)); ?></span>
      <span class="separator">•</span>
      <span><?php echo $guests; ?> khách</span>
    </div>
    <?php 
    $formAction = ''; // Submit to same page
    $formWrapperClass = 'search-form-wrapper-inline';
    include __DIR__ . '/../../partials/search-form.php'; 
    ?>
  </div>
</div>

<div class="list-container">
  <!-- Sidebar Filters -->
  <aside class="sidebar">
    <!-- Loại chỗ ở -->
    <div class="filter-section">
      <h3 class="filter-title">Loại chỗ ở</h3>
      <div class="filter-options">
        <?php 
        if (!empty($placeTypes)) {
          foreach ($placeTypes as $type) {
            echo '<label class="checkbox-label">';
            echo '<input type="checkbox" name="type[]" value="' . $type['place_type_id'] . '">';
            echo '<span>' . htmlspecialchars($type['name']) . '</span>';
            echo '</label>';
          }
        } else {
          echo '<p class="filter-empty-message">Không có loại chỗ ở</p>';
        }
        ?>
      </div>
    </div>

    <!-- Khoảng giá -->
    <div class="filter-section">
      <h3 class="filter-title">Khoảng giá</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="radio" name="price" value="">
          <span>Tất cả</span>
        </label>
        <label class="checkbox-label">
          <input type="radio" name="price" value="0-500000">
          <span>Dưới 500.000</span>
        </label>
        <label class="checkbox-label">
          <input type="radio" name="price" value="500000-1000000">
          <span>500.000 - 1.000.000</span>
        </label>
        <label class="checkbox-label">
          <input type="radio" name="price" value="1000000-1500000">
          <span>1.000.000 - 1.500.000</span>
        </label>
        <label class="checkbox-label">
          <input type="radio" name="price" value="1500000+">
          <span>Trên 1.500.000</span>
        </label>
      </div>
    </div>

    <!-- Đánh giá -->
    <div class="filter-section">
      <h3 class="filter-title">Đánh giá</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="1">
          <span>1 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="2">
          <span>2 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="3">
          <span>3 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="4">
          <span>4 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="5">
          <span>5 sao</span>
        </label>
      </div>
    </div>

    <!-- Tiện nghi -->
    <div class="filter-section">
      <h3 class="filter-title">Tiện nghi</h3>
      <div class="filter-options">
        <?php 
        if (!empty($amenities)) {
          foreach ($amenities as $amenity) {
            echo '<label class="checkbox-label">';
            echo '<input type="checkbox" name="amenities[]" value="' . $amenity['amenity_id'] . '">';
            echo '<span>' . htmlspecialchars($amenity['name']) . '</span>';
            echo '</label>';
          }
        } else {
          echo '<p class="filter-empty-message">Không có tiện nghi</p>';
        }
        ?>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Listings Grid -->
    <div class="listings-grid">
      <?php if (!empty($listings)): ?>
        <?php foreach ($listings as $listing): ?>
          <?php
          // Lấy amenities của listing
          $amenities = $cListing->cGetListingAmenities($listing['listing_id']);
          $amenitiesStr = implode(',', $amenities);
          ?>
          <a href="./detailListing.php?id=<?php echo $listing['listing_id']; ?>&checkin=<?php echo urlencode($checkin); ?>&checkout=<?php echo urlencode($checkout); ?>&guests=<?php echo urlencode($guests); ?>" 
             class="listing-card-link">
            <article class="listing-card" 
                     data-place-type-id="<?php echo $listing['place_type_id'] ?? ''; ?>"
                     data-price="<?php echo $listing['price']; ?>"
                     data-rating="<?php echo $listing['avg_rating']; ?>"
                     data-amenities="<?php echo $amenitiesStr; ?>">
              <div class="listing-image">
              <?php 
              if (!empty($listing['file_url'])) {
                if (strpos($listing['file_url'], 'http') === 0) {
                  $imageUrl = $listing['file_url'];
                } else {
                  $imageUrl = '../../../' . $listing['file_url'];
                }
              } else {
                $imageUrl = '../../../public/img/placeholder_listing/demo.png';
              }
              ?>
              <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
            </div>
            
            <div class="listing-content">
              <div class="listing-header">
                <span class="listing-type">
                  <?php echo htmlspecialchars($listing['place_type_name'] ?? 'Chỗ ở'); ?> 
                  tại <?php echo htmlspecialchars($listing['province_name'] ?? $listing['ward_name']); ?>
                </span>
              </div>
              
              <h2 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h2>
              
              <div class="listing-details">
                <span><?php echo htmlspecialchars($listing['address']); ?></span>
              </div>
              
              <!-- Capacity -->
              <div class="listing-capacity">
                <i class="fa-solid fa-users"></i>
                <span>Tối đa <?php echo $listing['capacity']; ?> khách</span>
              </div>
              
              <?php if (!empty($listing['description'])): ?>
                <div class="listing-description">
                  <?php echo htmlspecialchars(substr($listing['description'], 0, 100)) . '...'; ?>
                </div>
              <?php endif; ?>
              
              <!-- Rating and Review Count -->
              <?php if ($listing['review_count'] > 0): ?>
                <div class="listing-rating">
                  <div class="rating-wrapper">
                    <i class="fa-solid fa-star rating-star"></i>
                    <span class="rating-value"><?php echo number_format($listing['avg_rating'], 1); ?></span>
                  </div>
                  <span class="rating-count">
                    (<?php echo $listing['review_count']; ?> đánh giá)
                  </span>
                </div>
              <?php else: ?>
                <div class="listing-rating no-rating">
                  Chưa có đánh giá
                </div>
              <?php endif; ?>
              
              <div class="listing-footer">
                <div class="listing-price">
                  <span class="price"><?php echo number_format($listing['price']); ?>₫</span>
                  <span class="price-unit">/đêm</span>
                </div>
              </div>
            </article>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-results-container">
          <p class="no-results-title">Không tìm thấy chỗ ở phù hợp với tìm kiếm của bạn</p>
          <p class="no-results-subtitle">Thử tìm kiếm với địa điểm khác</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <!-- <div class="pagination">
      <button class="btn-page" disabled>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
      </button>
      
      <button class="btn-page active">1</button>
      <button class="btn-page">2</button>
      <button class="btn-page">3</button>
      <button class="btn-page">4</button>
      <button class="btn-page">5</button>
      <span class="page-dots">...</span>
      <button class="btn-page">30</button>
      
      <button class="btn-page">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div> -->
  </main>
</div>

<!-- Filter JavaScript -->
<script src="../../../public/js/listing-filter.js?v=<?php echo time(); ?>"></script>
<script src="../../../public/js/toggle-search-form.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
