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
<link rel="stylesheet" href="../../css/booking-success.css?v=<?php echo time(); ?>">

<div class="host-container">
  <div class="host-wrapper">
    
    <!-- Sidebar -->
    <aside class="host-sidebar">
      <div class="host-brand">
        <h2>🏠 Host Dashboard</h2>
        <p>Quản lý chỗ ở của bạn</p>
      </div>
      
      <nav class="host-nav">
        <a href="./host-dashboard.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
          </svg>
          Tổng quan
        </a>
        
        <a href="./my-listings.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
          </svg>
          Danh sách chỗ ở
        </a>
        
        <a href="./host-bookings.php" class="nav-item active">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
          </svg>
          Đơn đặt phòng
        </a>
        
        <a href="./create-listing.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
          </svg>
          Thêm chỗ ở mới
        </a>
        
        <a href="../profile.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
          Hồ sơ cá nhân
        </a>
        
        <a href="../logout.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
          </svg>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="host-main">
      <div class="booking-detail-header" style="margin-bottom: 30px;">
        <a href="./host-bookings.php" style="color: #6366f1; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
          </svg>
          Quay lại danh sách
        </a>
        <h1>Chi Tiết Đơn Đặt</h1>
        <p>Mã đơn: <strong><?php echo htmlspecialchars($booking['code']); ?></strong></p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <div class="booking-success-card" style="max-width: 100%;">
        <div class="booking-info-card">
          
          <!-- Status Badge -->
          <div style="text-align: center; margin-bottom: 24px;">
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
          <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 24px;">
            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #1f2937;">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="display: inline; margin-right: 8px;">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              Thông tin khách
            </h3>
            <p style="margin: 8px 0;"><strong>Tên:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
            <p style="margin: 8px 0;"><strong>Email:</strong> <?php echo htmlspecialchars($booking['guest_email']); ?></p>
            <p style="margin: 8px 0;"><strong>SĐT:</strong> <?php echo htmlspecialchars($booking['guest_phone']); ?></p>
          </div>
          
          <!-- Booking Dates -->
          <div class="booking-dates" style="display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
              <div class="date-item">
                <svg class="calendar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                  <div style="font-size: 13px; color: #6b7280;">Nhận phòng</div>
                  <div class="date-label"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></div>
                </div>
              </div>
            </div>
            <div style="flex: 1; min-width: 200px;">
              <div class="date-item">
                <svg class="calendar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                  <div style="font-size: 13px; color: #6b7280;">Trả phòng</div>
                  <div class="date-label"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></div>
                </div>
              </div>
            </div>
          </div>
          
          <div style="display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap;">
            <div>
              <strong>Số đêm:</strong> <?php echo $nights; ?> đêm
            </div>
            <div>
              <strong>Số khách:</strong> <?php echo $booking['guests']; ?> người
            </div>
          </div>
          
          <!-- Location -->
          <div class="booking-location">
            <svg class="location-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div class="location-text">
              <strong>Chỗ ở tại <?php echo htmlspecialchars($booking['ward_name']); ?>, <?php echo htmlspecialchars($booking['province_name']); ?></strong><br>
              <?php echo htmlspecialchars($booking['address']); ?>
            </div>
          </div>
          
          <!-- Price Summary -->
          <div class="price-summary">
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Chi tiết giá</h3>
            
            <?php
            $listingPrice = $booking['total_amount'];
            $servicesTotal = 0;
            
            foreach ($services as $service) {
              $servicesTotal += $service['price'];
            }
            
            $subtotal = $listingPrice - $servicesTotal;
            $pricePerNight = $nights > 0 ? $subtotal / $nights : 0;
            ?>
            
            <div class="price-row" style="margin-bottom: 12px;">
              <span class="price-label"><?php echo number_format($pricePerNight, 0, ',', '.'); ?> VND x <?php echo $nights; ?> đêm</span>
              <span class="price-label"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span>
            </div>
            
            <?php if (count($services) > 0): ?>
              <?php foreach ($services as $service): ?>
                <div class="price-row" style="margin-bottom: 12px;">
                  <span class="price-label"><?php echo htmlspecialchars($service['name']); ?></span>
                  <span class="price-label"><?php echo number_format($service['price'], 0, ',', '.'); ?> VND</span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="price-row" style="margin-top: 16px; padding-top: 16px; border-top: 2px solid #EBEBEB;">
              <span class="price-label" style="font-weight: 600;">Tổng cộng</span>
              <span class="price-amount"><?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</span>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <?php if ($booking['status'] === 'confirmed'): ?>
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #EBEBEB;">
              <form method="POST" onsubmit="return confirm('Xác nhận khách đã trả phòng?');">
                <input type="hidden" name="new_status" value="completed">
                <button type="submit" name="update_status" class="btn-complete-large">
                  ✓ Xác nhận đã trả phòng
                </button>
              </form>
            </div>
          <?php endif; ?>
          
          <!-- Listing Preview -->
          <div style="margin-top: 32px; padding-top: 32px; border-top: 2px solid #EBEBEB;">
            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;">Thông tin chỗ ở</h3>
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
                  <span>⭐ <?php echo number_format($booking['avg_rating'], 1); ?> (<?php echo $booking['review_count']; ?> đánh giá)</span>
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

<style>
.alert {
  padding: 14px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

.alert svg {
  flex-shrink: 0;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert-danger {
  background: #fee;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.status-badge-large {
  display: inline-block;
  padding: 12px 32px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-confirmed {
  background: #dbeafe;
  color: #1e40af;
}

.status-completed {
  background: #d1fae5;
  color: #065f46;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.btn-complete-large {
  width: 100%;
  padding: 16px 32px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-complete-large:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

.listing-thumbnail {
  height: 200px;
}
</style>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
