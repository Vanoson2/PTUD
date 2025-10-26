<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Get listing ID from URL
$listingId = $_GET['id'] ?? 0;

if (empty($listingId)) {
  header('Location: ../../../index.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cListing.php');
include_once(__DIR__ . '/../../../controller/cType&Amenties.php');

// Get listing details
$cListing = new cListing();
$listing = $cListing->cGetListingDetail($listingId);

if (!$listing) {
  header('Location: ../../../index.php');
  exit;
}

// Get listing images
$images = $cListing->cGetListingImages($listingId);

// Get listing amenities
$amenities = $cListing->cGetListingAmenities($listingId);

// Get all amenities details
$cType = new cTypeAndAmenties();
$allAmenitiesResult = $cType->cGetAllAmenities();
$allAmenities = [];
if ($allAmenitiesResult) {
  while ($row = $allAmenitiesResult->fetch_assoc()) {
    $allAmenities[$row['amenity_id']] = $row;
  }
}

// Get reviews
$reviews = $cListing->cGetListingReviews($listingId, 4);

// Get booked dates
$bookedDates = $cListing->cGetBookedDates($listingId);

// Get search params for booking
$checkin = $_GET['checkin'] ?? date('Y-m-d', strtotime('+1 day'));
$checkout = $_GET['checkout'] ?? date('Y-m-d', strtotime('+2 days'));
$guests = $_GET['guests'] ?? 1;

// Calculate nights
$nights = 0;
if ($checkin && $checkout) {
  $checkinDate = strtotime($checkin);
  $checkoutDate = strtotime($checkout);
  $nights = round(($checkoutDate - $checkinDate) / (60 * 60 * 24));
}

// Calculate total price
$totalPrice = $listing['price'] * $nights;
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../../../view/css/detailListing.css?v=<?php echo time(); ?>">

<div class="detail-container">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="../../../index.php">Trang chủ</a>
    <span>/</span>
    <a href="./listListings.php?location=<?php echo urlencode($listing['province_name']); ?>">
      <?php echo htmlspecialchars($listing['province_name']); ?>
    </a>
    <span>/</span>
    <span><?php echo htmlspecialchars($listing['title']); ?></span>
  </div>

  <!-- Title and Location -->
  <div class="detail-header">
    <h1 class="detail-title"><?php echo htmlspecialchars($listing['title']); ?></h1>
    <div class="detail-meta">
      <div class="rating-badge">
        <i class="fa-solid fa-star"></i>
        <span><?php echo number_format($listing['avg_rating'], 2); ?></span>
        <span class="review-count">(<?php echo $listing['review_count']; ?> đánh giá)</span>
      </div>
      <div class="location-badge">
        <i class="fa-solid fa-location-dot"></i>
        <span><?php echo htmlspecialchars($listing['address']); ?>, <?php echo htmlspecialchars($listing['ward_name']); ?>, <?php echo htmlspecialchars($listing['province_name']); ?></span>
      </div>
    </div>
  </div>

  <!-- Images Gallery -->
  <div class="images-gallery">
    <div class="main-image">
      <?php 
      // Tạm thời dùng ảnh mặc định vì file_url trong DB chỉ là example
      $mainImageUrl = '../../../public/img/placeholder_listing/demo.png';
      
      /* Sau khi có ảnh thật từ chức năng thêm chỗ ở, bỏ comment code này
      if (!empty($images) && !empty($images[0]['file_url'])) {
        // Nếu là URL đầy đủ (http/https)
        if (strpos($images[0]['file_url'], 'http') === 0) {
          $mainImageUrl = $images[0]['file_url'];
        } else {
          // Nếu là đường dẫn tương đối trong project
          $mainImageUrl = '../../../' . ltrim($images[0]['file_url'], '/');
        }
      }
      */
      ?>
      <img src="<?php echo htmlspecialchars($mainImageUrl); ?>" 
           alt="<?php echo htmlspecialchars($listing['title']); ?>">
    </div>
    
    <div class="thumbnail-grid">
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <?php 
        // Tạm thời dùng ảnh mặc định
        $thumbUrl = '../../../public/img/placeholder_listing/demo.png';
        
        /* Sau khi có ảnh thật, bỏ comment code này
        if (!empty($images) && isset($images[$i]) && !empty($images[$i]['file_url'])) {
          if (strpos($images[$i]['file_url'], 'http') === 0) {
            $thumbUrl = $images[$i]['file_url'];
          } else {
            $thumbUrl = '../../../' . ltrim($images[$i]['file_url'], '/');
          }
        }
        */
        ?>
        <div class="thumbnail-item">
          <img src="<?php echo htmlspecialchars($thumbUrl); ?>" 
               alt="Image <?php echo $i; ?>">
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Main Content -->
  <div class="detail-content-wrapper">
    <!-- Left Column -->
    <div class="detail-main-content">
      <!-- Overview -->
      <div class="detail-section">
        <h2 class="section-title">TỔNG QUAN</h2>
        <div class="overview-grid">
          <div class="overview-item">
            <i class="fa-solid fa-users"></i>
            <span><?php echo $listing['capacity']; ?> khách</span>
          </div>
          <div class="overview-item">
            <i class="fa-solid fa-door-open"></i>
            <span><?php echo htmlspecialchars($listing['place_type_name'] ?? 'Chỗ ở'); ?></span>
          </div>
        </div>
        <div class="cancellation-policy">
          <i class="fa-solid fa-ban"></i>
          <span>Hủy phòng trong vòng 24 tiếng</span>
        </div>
      </div>

      <!-- Description -->
      <div class="detail-section">
        <h2 class="section-title">MÔ TẢ</h2>
        <p class="description-text">
          <?php echo nl2br(htmlspecialchars($listing['description'] ?? 'Sunt ut elit cupidatat do quis incididunt sint mollit culpa consequat occaecat exercitat anim ad sint adipisicing nulla:')); ?>
        </p>
      </div>

      <!-- Amenities -->
      <div class="detail-section">
        <h2 class="section-title">CÁC TIỆN NGHI CHỖ Ở</h2>
        <div class="amenities-grid">
          <?php if (!empty($amenities)): ?>
            <?php 
            $amenityCount = 0;
            foreach ($amenities as $amenityId): 
              if (isset($allAmenities[$amenityId]) && $amenityCount < 6):
                $amenityCount++;
            ?>
                <div class="amenity-item">
                  <i class="fa-solid fa-check"></i>
                  <span><?php echo htmlspecialchars($allAmenities[$amenityId]['name']); ?></span>
                </div>
              <?php 
              endif;
            endforeach; 
            ?>
          <?php else: ?>
            <div class="amenity-item">
              <i class="fa-solid fa-check"></i>
              <span>Chưa có tiện nghi</span>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($amenities) && count($amenities) > 6): ?>
          <button class="btn-show-all">Hiển thị toàn bộ <?php echo count($amenities); ?> tiện nghi</button>
        <?php endif; ?>
      </div>

      <!-- Reviews -->
      <div class="detail-section">
        <div class="reviews-header">
          <i class="fa-solid fa-star"></i>
          <h2><?php echo number_format($listing['avg_rating'], 2); ?> (<?php echo $listing['review_count']; ?> đánh giá)</h2>
        </div>
        
        <div class="reviews-grid">
          <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
              <div class="review-card">
                <div class="review-header">
                  <div class="review-avatar">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div class="review-info">
                    <div class="review-user-rating">
                      <h4><?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?></h4>
                      <div class="review-stars">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                          <i class="fa-<?php echo ($s <= $review['rating']) ? 'solid' : 'regular'; ?> fa-star"></i>
                        <?php endfor; ?>
                      </div>
                    </div>
                    <span class="review-datetime">
                      <?php 
                      $reviewDate = strtotime($review['created_at']);
                      echo date('d/m/Y', $reviewDate) . ' lúc ' . date('H:i', $reviewDate); 
                      ?>
                    </span>
                  </div>
                </div>
                <p class="review-text"><?php echo htmlspecialchars($review['comment']); ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-center text-muted">Chưa có đánh giá nào.</p>
          <?php endif; ?>
        </div>
        
        <?php if ($listing['review_count'] > 4): ?>
          <button class="btn-show-all">Hiển thị toàn bộ đánh giá</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column - Booking Card -->
    <div class="booking-sidebar">
      <div class="booking-card">
        <div class="booking-price">
          <span class="price-amount"><?php echo number_format($listing['price']); ?>VNĐ</span>
          <span class="price-unit">/night</span>
          <div class="price-rating">
            <i class="fa-solid fa-star"></i>
            <span><?php echo number_format($listing['avg_rating'], 2); ?></span>
          </div>
        </div>

        <form class="booking-form" method="POST" action="./booking.php" id="bookingForm">
          <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
          <input type="hidden" name="checkin" id="hiddenCheckin" value="<?php echo $checkin; ?>">
          <input type="hidden" name="checkout" id="hiddenCheckout" value="<?php echo $checkout; ?>">
          
          <!-- Date Button để mở modal -->
          <div class="booking-date-button" id="openDateModal">
            <div class="booking-date-display">
              <div class="booking-date-field">
                <label>NHẬN PHÒNG</label>
                <div class="date-value" id="displayCheckin">
                  <?php echo $checkin ? date('d/m/Y', strtotime($checkin)) : 'Thêm ngày'; ?>
                </div>
              </div>
              <div class="booking-date-field">
                <label>TRẢ PHÒNG</label>
                <div class="date-value" id="displayCheckout">
                  <?php echo $checkout ? date('d/m/Y', strtotime($checkout)) : 'Thêm ngày'; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="booking-field">
            <label>KHÁCH</label>
            <div class="booking-guest-counter">
              <button type="button" 
                      class="btn-booking-guest minus" 
                      aria-label="Giảm"
                      <?php echo $guests <= 1 ? 'disabled' : ''; ?>>−</button>
              <input type="number" 
                     name="guests" 
                     id="bookingGuestsInput"
                     class="booking-guest-input" 
                     value="<?php echo $guests; ?>" 
                     min="1" 
                     max="<?php echo $listing['capacity']; ?>" 
                     readonly />
              <span class="guest-label">khách</span>
              <button type="button" 
                      class="btn-booking-guest plus" 
                      aria-label="Tăng"
                      <?php echo $guests >= $listing['capacity'] ? 'disabled' : ''; ?>>+</button>
            </div>
          </div>

          <button type="submit" class="btn-booking">ĐẶT CHỖ</button>
        </form>

        <div class="booking-summary" id="bookingSummary">
          <div class="summary-row" id="priceBreakdown">
            <span id="priceText"><?php echo number_format($listing['price']); ?>₫ x <?php echo $nights; ?> đêm</span>
            <span id="priceAmount"><?php echo number_format($totalPrice); ?>₫</span>
          </div>
          <div class="summary-row total">
            <span>Tổng cộng</span>
            <span id="totalPrice"><?php echo number_format($totalPrice); ?>₫</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Date Picker Modal -->
<div class="date-modal" id="dateModal">
  <div class="date-modal-content">
    <div class="date-modal-header">
      <button class="date-modal-close" id="closeDateModal">&times;</button>
      <h3>Thêm ngày để xem giá</h3>
    </div>
    
    <div class="date-modal-body">
      <div class="date-tabs">
        <div class="date-tab active" data-tab="checkin">
          <label>NHẬN PHÒNG</label>
          <div class="date-tab-value" id="modalCheckinValue">Thêm ngày</div>
        </div>
        <div class="date-tab" data-tab="checkout">
          <label>TRẢ PHÒNG</label>
          <div class="date-tab-value" id="modalCheckoutValue">Thêm ngày</div>
        </div>
      </div>
      
      <div class="date-modal-title">Chọn ngày</div>
      <div class="date-modal-subtitle">Thêm ngày đi để biết giá chính xác</div>
      
      <div class="calendar-container" id="calendarContainer"></div>
      
      <div class="date-modal-footer">
        <button class="btn-clear-dates" id="clearDates">Xóa ngày</button>
        <button class="btn-done-dates" id="doneDates">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- Pass booked dates to JavaScript -->
<script>
const bookedDatesData = <?php echo json_encode($bookedDates); ?>;
const listingCapacity = <?php echo $listing['capacity']; ?>;
const pricePerNight = <?php echo $listing['price']; ?>;
</script>

<!-- Date Picker Scripts -->
<script src="../../../public/js/booking-date-picker.js"></script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
