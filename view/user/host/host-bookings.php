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

// Get active tab from URL
$activeTab = $_GET['tab'] ?? 'upcoming';

// Get bookings based on tab
$bookingsResult = $cHostBooking->cGetHostBookings($hostId, $activeTab);

// Convert result to array
$bookings = [];
if ($bookingsResult && $bookingsResult->num_rows > 0) {
  while ($row = $bookingsResult->fetch_assoc()) {
    $bookings[] = $row;
  }
}

// Get booking counts
$counts = $cHostBooking->cCountBookingsByStatus($hostId);

// Handle status update
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $bookingId = (int)$_POST['booking_id'];
  $newStatus = $_POST['new_status'];
  
  $result = $cHostBooking->cUpdateBookingStatus($bookingId, $hostId, $newStatus);
  
  if ($result['success']) {
    $successMessage = $result['message'];
    // Refresh bookings
    header("Location: host-bookings.php?tab=$activeTab&success=1");
    exit;
  } else {
    $errorMessage = $result['message'];
  }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
  $successMessage = 'Cập nhật trạng thái thành công!';
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/host-dashboard.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/traveller-my-bookings.css?v=<?php echo time(); ?>">

<div class="host-container">
  <div class="host-wrapper">
    
    <!-- Sidebar -->
    <aside class="host-sidebar">
      <div class="host-brand">
        <h2><i class="fas fa-home"></i> Host Dashboard</h2>
        <p>Quản lý chỗ ở của bạn</p>
      </div>
      
      <nav class="host-nav">
        <a href="./host-dashboard.php" class="nav-item">
          <i class="fas fa-chart-line"></i>
          Tổng quan
        </a>
        
        <a href="./my-listings.php" class="nav-item">
          <i class="fas fa-building"></i>
          Danh sách chỗ ở
        </a>
        
        <a href="./host-bookings.php" class="nav-item active">
          <i class="fas fa-clipboard-list"></i>
          Đơn đặt phòng
          <?php if ($counts['ongoing'] > 0): ?>
            <span class="badge-count"><?php echo $counts['ongoing']; ?></span>
          <?php endif; ?>
        </a>
        
        <a href="./create-listing.php" class="nav-item">
          <i class="fas fa-plus-circle"></i>
          Thêm chỗ ở mới
        </a>
        
        <a href="../profile.php" class="nav-item">
          <i class="fas fa-user"></i>
          Hồ sơ cá nhân
        </a>
        
        <a href="../logout.php" class="nav-item">
          <i class="fas fa-sign-out-alt"></i>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="host-main">
      <div class="profile-header">
        <h1>Đơn đặt phòng</h1>
        <p>Quản lý các đơn đặt phòng của bạn</p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <i class="fas fa-times-circle"></i>
          <span><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <!-- Tabs -->
      <div class="bookings-tabs">
        <a href="?tab=upcoming" class="tab-button <?php echo $activeTab === 'upcoming' ? 'active' : ''; ?>">
          Sắp tới
          <?php if ($counts['upcoming'] > 0): ?>
            <span class="tab-count"><?php echo $counts['upcoming']; ?></span>
          <?php endif; ?>
        </a>
        <a href="?tab=ongoing" class="tab-button <?php echo $activeTab === 'ongoing' ? 'active' : ''; ?>">
          Hiện tại
          <?php if ($counts['ongoing'] > 0): ?>
            <span class="tab-count"><?php echo $counts['ongoing']; ?></span>
          <?php endif; ?>
        </a>
        <a href="?tab=completed" class="tab-button <?php echo $activeTab === 'completed' ? 'active' : ''; ?>">
          Đã hoàn thành
          <?php if ($counts['completed'] > 0): ?>
            <span class="tab-count"><?php echo $counts['completed']; ?></span>
          <?php endif; ?>
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
            
            // Determine status badge
            $statusClass = '';
            $statusText = '';
            switch($booking['status']) {
              case 'confirmed':
                $statusClass = 'status-confirmed';
                $statusText = 'Đang đến';
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
            <div class="booking-card">
              <div class="booking-image">
                <?php if (!empty($booking['listing_image'])): ?>
                  <?php
                  // Handle both cases: with or without leading slash
                  $imagePath = $booking['listing_image'];
                  if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
                    // External URL (Pexels, etc.)
                    $displayPath = $imagePath;
                  } elseif (strpos($imagePath, '/') === 0) {
                    // Already has leading slash: /public/uploads/...
                    $displayPath = '../../..' . $imagePath;
                  } else {
                    // No leading slash: public/uploads/...
                    $displayPath = '../../../' . $imagePath;
                  }
                  ?>
                  <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Listing">
                <?php else: ?>
                  <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing">
                <?php endif; ?>
              </div>
              
              <div class="booking-info">
                <h3 class="booking-title"><?php echo htmlspecialchars($booking['listing_title']); ?></h3>
                
                <div class="booking-guest">
                  <i class="fas fa-user" style="margin-right: 5px;"></i>
                  Khách: <strong><?php echo htmlspecialchars($booking['guest_name']); ?></strong>
                </div>
                
                <div class="booking-details">
                  <div class="detail-item">
                    <span class="detail-label">Check In:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Check Out:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Số đêm:</span>
                    <span class="detail-value"><?php echo $nights; ?> đêm</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Khách:</span>
                    <span class="detail-value"><?php echo $booking['guests']; ?> người</span>
                  </div>
                </div>
                
                <div class="booking-price">
                  <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND
                </div>
              </div>
              
              <div class="booking-actions">
                <span class="status-badge <?php echo $statusClass; ?>">
                  <?php echo $statusText; ?>
                </span>
                
                <?php if ($booking['status'] === 'confirmed' && $activeTab === 'ongoing'): ?>
                  <form method="POST" style="margin-top: 10px;" onsubmit="return confirm('Xác nhận khách đã trả phòng?');">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                    <input type="hidden" name="new_status" value="completed">
                    <button type="submit" name="update_status" class="btn-complete">
                      <i class="fas fa-check"></i> Đã trả phòng
                    </button>
                  </form>
                <?php endif; ?>
                
                <a href="./booking-detail.php?id=<?php echo $booking['booking_id']; ?>" class="btn-view-detail">
                  Xem chi tiết
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-clipboard empty-icon"></i>
            <h3>Chưa có đơn đặt nào</h3>
            <p>
              <?php 
              if ($activeTab === 'upcoming') {
                echo 'Chưa có đơn đặt sắp tới';
              } elseif ($activeTab === 'ongoing') {
                echo 'Chưa có khách đang ở';
              } else {
                echo 'Chưa có đơn đặt đã hoàn thành';
              }
              ?>
            </p>
          </div>
        <?php endif; ?>
      </div>
      
    </main>
  </div>
</div>

  <link rel="stylesheet" href="../../css/host-bookings.css">
</head>
<body>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
