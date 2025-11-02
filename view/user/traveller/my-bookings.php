<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

$userId = $_SESSION['user_id'];
$currentPage = 'bookings'; // For sidebar active state
$rootPath = '../../../';

// Include controllers
include_once(__DIR__ . '/../../../controller/cBooking.php');
include_once(__DIR__ . '/../../../model/mUser.php');

$cBooking = new cBooking();
$mUser = new mUser();

// Get user info
$user = $mUser->mGetUserById($userId);

// Get active tab from URL
$activeTab = $_GET['tab'] ?? 'upcoming';

// Get bookings based on tab
if ($activeTab === 'completed') {
  $bookingsResult = $cBooking->cGetUserBookings($userId, 'completed');
} else {
  $bookingsResult = $cBooking->cGetUserBookings($userId, 'upcoming');
}

// Convert result to array
$bookings = [];
if ($bookingsResult && $bookingsResult->num_rows > 0) {
  while ($row = $bookingsResult->fetch_assoc()) {
    $bookings[] = $row;
  }
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/profile.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/my-bookings.css?v=<?php echo time(); ?>">

<?php include __DIR__ . '/../partials/profile-layout-start.php'; ?>

<!-- Page Content -->
<div class="profile-header">
  <h1>Đơn đặt của tôi</h1>
  <p>Quản lý thông tin và cài đặt tài khoản của bạn</p>
</div>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php 
      echo htmlspecialchars($_SESSION['success_message']); 
      unset($_SESSION['success_message']);
    ?>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
  <div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <?php 
      echo htmlspecialchars($_SESSION['error_message']); 
      unset($_SESSION['error_message']);
    ?>
  </div>
<?php endif; ?>

<!-- Tabs -->
<div class="bookings-tabs">
  <a href="?tab=upcoming" class="tab-button <?php echo $activeTab === 'upcoming' ? 'active' : ''; ?>">
    Sắp tới
  </a>
  <a href="?tab=completed" class="tab-button <?php echo $activeTab === 'completed' ? 'active' : ''; ?>">
    Đã hoàn thành
  </a>
</div>

<!-- Bookings List -->
<div class="bookings-list">
  <?php if (count($bookings) > 0): ?>
    <?php foreach ($bookings as $booking): ?>
      <?php
      $checkinDate = new DateTime($booking['check_in']);
      $checkoutDate = new DateTime($booking['check_out']);
      $nights = $checkinDate->diff($checkoutDate)->days;
      ?>
      <div class="booking-card">
        <div class="booking-image">
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
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Listing">
          <?php else: ?>
            <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing">
          <?php endif; ?>
        </div>
        
        <div class="booking-info">
          <h3 class="booking-title"><?php echo htmlspecialchars($booking['listing_title']); ?></h3>
          
          <div class="booking-details">
            <div class="detail-item">
              <span class="detail-label">Check In:</span>
              <span class="detail-value"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Duration:</span>
              <span class="detail-value"><?php echo $nights; ?> Nights</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Guests:</span>
              <span class="detail-value"><?php echo $booking['guests']; ?> Adults</span>
            </div>
          </div>
          
          <div class="booking-price">
            <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND
          </div>
        </div>
        
        <div class="booking-actions">
          <?php if ($activeTab === 'upcoming' && $booking['status'] === 'confirmed'): ?>
            <a href="cancel-booking.php?booking_id=<?php echo $booking['booking_id']; ?>" 
               class="btn-cancel"
               onclick="return confirm('Bạn có chắc chắn muốn hủy đơn đặt này?');">
              <i class="fas fa-times-circle"></i> Hủy Đơn Đặt
            </a>
          <?php elseif ($booking['status'] === 'cancelled'): ?>
            <span class="badge-cancelled">
              <i class="fas fa-ban"></i> Đã hủy
            </span>
          <?php else: ?>
            <?php if (!$booking['user_reviewed']): ?>
              <a href="review-listing.php?listing_id=<?php echo $booking['listing_id']; ?>&booking_id=<?php echo $booking['booking_id']; ?>" 
                 class="btn-review">
                Review
              </a>
            <?php else: ?>
              <span class="badge-reviewed">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Reviewed
              </span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state">
      <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <h3>Chưa có đơn đặt nào</h3>
      <p>Bạn chưa có đơn đặt <?php echo $activeTab === 'upcoming' ? 'sắp tới' : 'đã hoàn thành'; ?></p>
      <a href="../../../index.php" class="btn-primary">Khám phá ngay</a>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/profile-layout-end.php'; ?>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
