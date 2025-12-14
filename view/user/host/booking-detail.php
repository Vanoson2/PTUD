<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in and is a host
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

$userId = $_SESSION['user_id'];
$bookingId = $_GET['id'] ?? 0;

if (empty($bookingId)) {
  header('Location: ./host-bookings.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cHostBooking.php');
include_once(__DIR__ . '/../../../model/mHost.php');

// Check if user is a host
$mHost = new mHost();
$host = $mHost->mGetHostByUserId($userId);

if (!$host) {
  header('Location: ./become-host.php');
  exit;
}

$hostId = $host['host_id'];
$cHostBooking = new cHostBooking();

// Get booking detail
$booking = $cHostBooking->cGetBookingDetail($bookingId, $hostId);

if (!$booking) {
  header('Location: ./host-bookings.php');
  exit;
}

// Get services
$services = $cHostBooking->cGetBookingServices($bookingId);

// Calculate
$checkinDate = new DateTime($booking['check_in']);
$checkoutDate = new DateTime($booking['check_out']);
$nights = $checkinDate->diff($checkoutDate)->days;

$successMessage = '';
$errorMessage = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $newStatus = $_POST['new_status'];
  
  $result = $cHostBooking->cUpdateBookingStatus($bookingId, $hostId, $newStatus);
  
  if ($result['success']) {
    $successMessage = $result['message'];
    // Refresh page
    header("Location: booking-detail.php?id=$bookingId&success=1");
    exit;
  } else {
    $errorMessage = $result['message'];
  }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
  $successMessage = 'Cập nhật trạng thái thành công!';
  // Refresh booking data
  $booking = $cHostBooking->cGetBookingDetail($bookingId, $hostId);
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/host-dashboard.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/traveller-booking-success.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/host-booking-detail.css?v=<?php echo time(); ?>">

<div class="host-container">
  <div class="host-wrapper">
    
    <!-- Sidebar -->
    <aside class="host-sidebar">
      <div class="host-brand">
        <h2><i class="fa-solid fa-house"></i> Host Dashboard</h2>
        <p>Quản lý chỗ ở của bạn</p>
      </div>
      
      <nav class="host-nav">
        <a href="./host-dashboard.php" class="nav-item">
          <i class="fa-solid fa-home"></i>
          Tổng quan
        </a>
        
        <a href="./my-listings.php" class="nav-item">
          <i class="fa-solid fa-building"></i>
          Danh sách chỗ ở
        </a>
        
        <a href="./host-bookings.php" class="nav-item active">
          <i class="fa-solid fa-clipboard-list"></i>
          Đơn đặt phòng
        </a>
        
        <a href="./create-listing.php" class="nav-item">
          <i class="fa-solid fa-plus-circle"></i>
          Thêm chỗ ở mới
        </a>
        
        <a href="../profile.php" class="nav-item">
          <i class="fa-solid fa-user"></i>
          Hồ sơ cá nhân
        </a>
        
        <a href="../logout.php" class="nav-item">
          <i class="fa-solid fa-right-from-bracket"></i>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="host-main">
      <div class="booking-detail-header">
        <a href="./host-bookings.php" class="back-link">
          <i class="fa-solid fa-arrow-left"></i>
          Quay lại danh sách
        </a>
        <h1>Chi Tiết Đơn Đặt</h1>
        <p>Mã đơn: <strong><?php echo htmlspecialchars($booking['code']); ?></strong></p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-check-circle"></i>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <i class="fa-solid fa-times-circle"></i>
          <span><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <div class="booking-success-card">
        <div class="booking-info-card">
          
          <!-- Status Badge -->
          <div class="status-badge-center">
            <?php
            $statusClass = '';
            $statusText = '';
            switch($booking['status']) {
              case 'confirmed':
                $statusClass = 'status-confirmed';
                $statusText = 'Đã xác nhận';
                break;
              case 'completed':
                $statusClass = 'status-completed';
                $statusText = 'Đã hoàn thành';
                break;
              case 'cancelled':
                $statusClass = 'status-cancelled';
                $statusText = 'Đã hủy';
                break;
            }
            ?>
            <span class="status-badge-large <?php echo $statusClass; ?>">
              <?php echo $statusText; ?>
            </span>
          </div>
          
          <!-- Guest Info -->
          <div class="guest-info-card">
            <h3>
              <i class="fa-solid fa-user"></i>
              Thông tin khách
            </h3>
            <p><strong>Tên:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['guest_email']); ?></p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($booking['guest_phone']); ?></p>
          </div>
          
          <!-- Booking Dates -->
          <div class="booking-dates">
            <div>
              <div class="date-item">
                <i class="fa-solid fa-calendar-check calendar-icon"></i>
                <div>
                  <div class="date-label-small">Nhận phòng</div>
                  <div class="date-label"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></div>
                </div>
              </div>
            </div>
            <div>
              <div class="date-item">
                <i class="fa-solid fa-calendar-xmark calendar-icon"></i>
                <div>
                  <div class="date-label-small">Trả phòng</div>
                  <div class="date-label"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="booking-details-flex">
            <div>
              <strong>Số đêm:</strong> <?php echo $nights; ?> đêm
            </div>
            <div>
              <strong>Số khách:</strong> <?php echo $booking['guests']; ?> người
            </div>
          </div>
          
          <!-- Location -->
          <div class="booking-location">
            <i class="fa-solid fa-map-marker-alt location-icon"></i>
            <div class="location-text">
              <strong>Chỗ ở tại <?php echo htmlspecialchars($booking['ward_name']); ?>, <?php echo htmlspecialchars($booking['province_name']); ?></strong><br>
              <?php echo htmlspecialchars($booking['address']); ?>
            </div>
          </div>
          
          <!-- Price Summary -->
          <div class="price-summary">
            <h3>Chi tiết giá</h3>
            
            <?php
            $listingPrice = $booking['total_amount'];
            $servicesTotal = 0;
            
            foreach ($services as $service) {
              $servicesTotal += $service['price'];
            }
            
            $subtotal = $listingPrice - $servicesTotal;
            $pricePerNight = $nights > 0 ? $subtotal / $nights : 0;
            ?>
            
            <div class="price-row">
              <span class="price-label"><?php echo number_format($pricePerNight, 0, ',', '.'); ?> VND x <?php echo $nights; ?> đêm</span>
              <span class="price-label"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span>
            </div>
            
            <?php if (count($services) > 0): ?>
              <?php foreach ($services as $service): ?>
                <div class="price-row">
                  <span class="price-label"><?php echo htmlspecialchars($service['name']); ?></span>
                  <span class="price-label"><?php echo number_format($service['price'], 0, ',', '.'); ?> VND</span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="price-row total">
              <span class="price-label">Tổng cộng</span>
              <span class="price-amount"><?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</span>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <?php if ($booking['status'] === 'confirmed'): ?>
            <div class="action-section">
              <form method="POST" onsubmit="return confirm('Xác nhận khách đã trả phòng?');">
                <input type="hidden" name="new_status" value="completed">
                <button type="submit" name="update_status" class="btn-complete-large">
                  ✓ Xác nhận đã trả phòng
                </button>
              </form>
            </div>
          <?php endif; ?>
          
          <!-- Listing Preview -->
          <div class="listing-preview-section">
            <h3>Thông tin chỗ ở</h3>
            <div class="listing-preview">
              <?php if (!empty($booking['listing_image'])): ?>
                <img src="../../../<?php echo htmlspecialchars($booking['listing_image']); ?>" alt="Listing" class="listing-thumbnail">
              <?php else: ?>
                <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing" class="listing-thumbnail">
              <?php endif; ?>
              
              <div class="listing-info-wrapper">
                <h3 class="listing-name"><?php echo htmlspecialchars($booking['listing_title']); ?></h3>
                <div class="listing-meta">
                  <span><?php echo $booking['capacity']; ?> khách</span>
                  <?php if (!empty($booking['avg_rating'])): ?>
                  <span>•</span>
                  <span><i class="fa-solid fa-star" style="color: gold;"></i> <?php echo number_format($booking['avg_rating'], 1); ?> (<?php echo $booking['review_count']; ?> đánh giá)</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
      
    </main>
  </div>
</div>

  <link rel="stylesheet" href="../../css/shared-booking-detail.css">
</head>
<body>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
