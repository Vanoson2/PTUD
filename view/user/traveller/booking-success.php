<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

// Get booking ID from URL
$bookingId = $_GET['booking_id'] ?? 0;
if (empty($bookingId)) {
  header('Location: ../../../index.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cBooking.php');
include_once(__DIR__ . '/../../../controller/cUser.php');

$cBooking = new cBooking();
$cUser = new cUser();

// Get booking details
$bookingResult = $cBooking->cGetBookingById($bookingId);
if (!$bookingResult || $bookingResult->num_rows === 0) {
  header('Location: ../../../index.php');
  exit;
}

$booking = $bookingResult->fetch_assoc();

// Check if this booking belongs to current user
if ($booking['user_id'] != $_SESSION['user_id']) {
  header('Location: ../../../index.php');
  exit;
}

// Award points for first booking (check from database, not session)
$bookingCount = $cBooking->cCountUserBookings($_SESSION['user_id']);
if ($bookingCount == 1) {
  // This is user's first booking - award bonus points
  $cUser->cAddScoreByAction($_SESSION['user_id'], 'first_booking', 'booking', $bookingId);
}

// Get booking services
$servicesResult = $cBooking->cGetBookingServices($bookingId);
$services = [];
if ($servicesResult && is_array($servicesResult)) {
  $services = $servicesResult;
}

// Calculate subtotal (listing price * nights)
$checkinDate = new DateTime($booking['check_in']);
$checkoutDate = new DateTime($booking['check_out']);
$nights = $checkinDate->diff($checkoutDate)->days;
$listingPrice = $booking['listing_price'];
$subtotal = $listingPrice * $nights;
$servicesTotal = array_sum(array_column($services, 'price'));

?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../../css/traveller-booking-success.css?v=<?php echo time(); ?>">

<?php include __DIR__ . '/../../partials/header.php'; ?>

<div class="booking-success-container">
  <div class="booking-success-card">
    <!-- Success Image -->
    <div class="success-image-wrapper">
      <img src="../../../public/img/booking-complete/complete.png" alt="Booking Complete" class="success-image">
    </div>

    <!-- Success Title -->
    <h1 class="success-title">ĐƠN ĐẶT ĐÃ ĐƯỢC HOÀN TẤT!</h1>

    <!-- Booking Details Card -->
    <div class="booking-info-card">
      <!-- Dates -->
      <div class="booking-dates">
        <div class="date-item">
          <i class="fa-solid fa-calendar-days calendar-icon"></i>
          <span class="date-label"><?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('d, Y', strtotime($booking['check_out'])); ?></span>
        </div>
      </div>

      <!-- Location -->
      <div class="booking-location">
        <i class="fa-solid fa-location-dot location-icon"></i>
        <span class="location-text"><?php echo htmlspecialchars($booking['address']); ?></span>
      </div>

      <!-- Price Summary -->
      <div class="price-summary">
        <div class="price-row">
          <span class="price-label"><?php echo number_format($listingPrice, 0, ',', '.'); ?> VND x <?php echo $nights; ?> đêm</span>
          <span class="price-amount"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span>
        </div>
        
        <?php if (count($services) > 0): ?>
          <?php foreach ($services as $service): ?>
          <div class="price-row">
            <span class="price-label"><?php echo htmlspecialchars($service['name']); ?></span>
            <span class="price-amount"><?php echo number_format($service['price'], 0, ',', '.'); ?> VND</span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="price-row price-total">
          <span class="price-label">Khoảng tiền phải trả</span>
          <span class="price-amount"><?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</span>
        </div>
      </div>

      <!-- Booking Code -->
      <div class="booking-code-section">
        <span class="code-label">MÃ ĐƠN ĐẶT</span>
        <span class="code-value"><?php echo htmlspecialchars($booking['code']); ?></span>
      </div>

      <!-- Listing Preview -->
      <div class="listing-preview">
        <?php if (!empty($booking['image_url'])): ?>
          <?php
          // Determine correct image path
          $imagePath = $booking['image_url'];
          if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
            // Local path - add relative path
            $imagePath = '../../../' . $imagePath;
          }
          // else: Keep full URL as is (Pexels)
          ?>
          <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Listing" class="listing-thumbnail">
        <?php else: ?>
          <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing" class="listing-thumbnail">
        <?php endif; ?>
        <div class="listing-info-wrapper">
          <div class="listing-badge">
            Chỗ ở tại 
            <?php 
              echo htmlspecialchars($booking['ward_name']); 
              if (!empty($booking['province_name'])) {
                echo ', ' . htmlspecialchars($booking['province_name']);
              }
            ?>
          </div>
          <h3 class="listing-name"><?php echo htmlspecialchars($booking['listing_title']); ?></h3>
          <div class="listing-meta">
            <span><?php echo $booking['capacity']; ?> guests</span>
          </div>
          <?php if (!empty($booking['avg_rating']) && $booking['review_count'] > 0): ?>
          <div class="listing-rating">
            <i class="fa-solid fa-star star-icon"></i>
            <span class="rating-value"><?php echo number_format($booking['avg_rating'], 2); ?></span>
            <span class="rating-count">(<?php echo $booking['review_count']; ?> reviews)</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Back to Home Button -->
    <div class="back-home-section">
      <a href="../../../index.php" class="btn-back-home">Quay về trang chủ</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
