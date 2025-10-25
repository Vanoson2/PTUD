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

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../../../view/css/listListing.css?v=<?php echo time(); ?>">

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
          echo '<p style="color: #6b7280; font-size: 0.9rem;">Không có loại chỗ ở</p>';
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
          echo '<p style="color: #6b7280; font-size: 0.9rem;">Không có tiện nghi</p>';
        }
        ?>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Search Header -->
    <div class="search-header">
      <div class="search-info">
        <h1 class="search-title"><?php echo $totalResults; ?>+ chỗ ở tại <?php echo htmlspecialchars($location); ?></h1>
        <div class="search-params">
          <span><?php echo date('d/m', strtotime($checkin)); ?> - <?php echo date('d/m', strtotime($checkout)); ?></span>
          <span>•</span>
          <span><?php echo $guests; ?> khách</span>
          <button class="btn-search-modify">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Listings Grid -->
    <div class="listings-grid">
      <?php if (!empty($listings)): ?>
        <?php foreach ($listings as $listing): ?>
          <?php
          // Lấy amenities của listing
          $amenities = $cListing->cGetListingAmenities($listing['listing_id']);
          $amenitiesStr = implode(',', $amenities);
          ?>
          <article class="listing-card" 
                   data-place-type-id="<?php echo $listing['place_type_id'] ?? ''; ?>"
                   data-price="<?php echo $listing['price']; ?>"
                   data-rating="<?php echo $listing['avg_rating']; ?>"
                   data-amenities="<?php echo $amenitiesStr; ?>">
            <div class="listing-image">
              <!-- Dùng ảnh mặc định tạm thời nhé Sơn nào đăng chỗ ở thì xóa comment -->
              <?php 
              // Tạm thời dùng ảnh mặc định vì file_url trong DB chỉ là example
              $imageUrl = '../../../public/img/placeholder_listing/demo.png';
              
              /* Sau khi có ảnh thật, bỏ comment code này
              if (!empty($listing['file_url'])) {
                if (strpos($listing['file_url'], 'http') === 0) {
                  $imageUrl = $listing['file_url'];
                } else {
                  $imageUrl = '../../../' . $listing['file_url'];
                }
              } else {
                $imageUrl = '../../../public/img/home/DaNang.jpg';
              }
              */
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
              <div class="listing-capacity" style="display: flex; align-items: center; gap: 0.25rem; color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0;">
                <i class="fa-solid fa-users" style="font-size: 14px;"></i>
                <span>Tối đa <?php echo $listing['capacity']; ?> khách</span>
              </div>
              
              <?php if (!empty($listing['description'])): ?>
                <div class="listing-description" style="color: #6b7280; font-size: 0.875rem; margin: 0.5rem 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?php echo htmlspecialchars(substr($listing['description'], 0, 100)) . '...'; ?>
                </div>
              <?php endif; ?>
              
              <!-- Rating and Review Count -->
              <?php if ($listing['review_count'] > 0): ?>
                <div class="listing-rating" style="display: flex; align-items: center; gap: 0.5rem; margin: 0.5rem 0;">
                  <div style="display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fa-solid fa-star" style="color: #FFC107; font-size: 16px;"></i>
                    <span style="font-weight: 600; color: #1f2937;"><?php echo number_format($listing['avg_rating'], 1); ?></span>
                  </div>
                  <span style="color: #6b7280; font-size: 0.875rem;">
                    (<?php echo $listing['review_count']; ?> đánh giá)
                  </span>
                </div>
              <?php else: ?>
                <div class="listing-rating" style="color: #9ca3af; font-size: 0.875rem; margin: 0.5rem 0;">
                  Chưa có đánh giá
                </div>
              <?php endif; ?>
              
              <div class="listing-footer">
                <div class="listing-price">
                  <span class="price"><?php echo number_format($listing['price']); ?>₫</span>
                  <span class="price-unit">/đêm</span>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
          <p style="font-size: 1.25rem; color: #6b7280;">Không tìm thấy chỗ ở phù hợp với tìm kiếm của bạn</p>
          <p style="color: #9ca3af; margin-top: 0.5rem;">Thử tìm kiếm với địa điểm khác</p>
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

<?php include __DIR__ . '/../../partials/footer.php'; ?>
