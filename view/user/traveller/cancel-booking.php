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
  header('Location: my-bookings.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cBooking.php');

$cBooking = new cBooking();

// Get booking details to verify it belongs to user and is cancellable
$bookingResult = $cBooking->cGetBookingById($bookingId);
if (!$bookingResult || $bookingResult->num_rows === 0) {
  header('Location: my-bookings.php');
  exit;
}

$booking = $bookingResult->fetch_assoc();

// Check if this booking belongs to current user
if ($booking['user_id'] != $_SESSION['user_id']) {
  header('Location: my-bookings.php');
  exit;
}

// Check if booking can be cancelled (only confirmed bookings)
if ($booking['status'] !== 'confirmed') {
  $_SESSION['error_message'] = 'Đơn đặt này không thể hủy (đã bị hủy hoặc đã hoàn thành).';
  header('Location: my-bookings.php');
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $cancelReason = trim($_POST['cancel_reason'] ?? '');
  
  // Validate
  if (empty($cancelReason)) {
    $error = 'Vui lòng nhập lý do hủy đơn.';
  } else {
    // Cancel booking
    $result = $cBooking->cCancelBooking($bookingId, $_SESSION['user_id'], $cancelReason);
    
    if ($result) {
      $_SESSION['success_message'] = 'Đã hủy đơn đặt thành công!';
      header('Location: my-bookings.php');
      exit;
    } else {
      $error = 'Không thể hủy đơn đặt. Vui lòng thử lại sau.';
    }
  }
}

// Calculate nights
$checkinDate = new DateTime($booking['check_in']);
$checkoutDate = new DateTime($booking['check_out']);
$nights = $checkinDate->diff($checkoutDate)->days;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hủy đơn đặt - WEGO</title>
  <link rel="stylesheet" href="../../css/cancel-booking.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include __DIR__ . '/../../partials/header.php'; ?>

  <div class="cancel-booking-container">
    <div class="cancel-booking-card">
      <div class="warning-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      
      <h1 class="cancel-title">Hủy đơn đặt phòng</h1>
      
      <p class="cancel-warning">
        Bạn có chắc chắn muốn hủy đơn đặt này? Hành động này không thể hoàn tác.
      </p>

      <!-- Booking Info Summary -->
      <div class="booking-summary">
        <h3>Thông tin đơn đặt</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="summary-label">Mã đơn:</span>
            <span class="summary-value"><?php echo htmlspecialchars($booking['code']); ?></span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Chỗ ở:</span>
            <span class="summary-value"><?php echo htmlspecialchars($booking['listing_title']); ?></span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Nhận phòng:</span>
            <span class="summary-value"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Trả phòng:</span>
            <span class="summary-value"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Số đêm:</span>
            <span class="summary-value"><?php echo $nights; ?> đêm</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Tổng tiền:</span>
            <span class="summary-value"><?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</span>
          </div>
        </div>
      </div>

      <!-- Error message -->
      <?php if (isset($error)): ?>
        <div class="error-message">
          <i class="fas fa-times-circle"></i>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <!-- Cancel Form -->
      <form method="POST" class="cancel-form">
        <div class="form-group">
          <label for="cancel_reason">
            <i class="fas fa-comment"></i> Lý do hủy đơn *
          </label>
          <textarea 
            id="cancel_reason" 
            name="cancel_reason" 
            rows="5" 
            placeholder="Vui lòng cho chúng tôi biết lý do bạn muốn hủy đơn đặt này..."
            required
          ><?php echo htmlspecialchars($_POST['cancel_reason'] ?? ''); ?></textarea>
          <small class="form-hint">Thông tin này sẽ được gửi đến chủ nhà</small>
        </div>

        <div class="form-actions">
          <a href="my-bookings.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
          </a>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times-circle"></i> Xác nhận hủy đơn
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>
</body>
</html>
