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

$cBooking = new cBooking();

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
<link rel="stylesheet" href="../../css/booking-success.css?v=<?php echo time(); ?>">

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
          <svg class="calendar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <span class="date-label"><?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('d, Y', strtotime($booking['check_out'])); ?></span>
        </div>
      </div>

      <!-- Location -->
      <div class="booking-location">
        <svg class="location-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
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
            <svg class="star-icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
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
