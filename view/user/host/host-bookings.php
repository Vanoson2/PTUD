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
<link rel="stylesheet" href="../../css/my-bookings.css?v=<?php echo time(); ?>">

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
          <?php if ($counts['ongoing'] > 0): ?>
            <span class="badge-count"><?php echo $counts['ongoing']; ?></span>
          <?php endif; ?>
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
      <div class="profile-header">
        <h1>Đơn đặt phòng</h1>
        <p>Quản lý các đơn đặt phòng của bạn</p>
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
                  <img src="../../../<?php echo htmlspecialchars($booking['listing_image']); ?>" alt="Listing">
                <?php else: ?>
                  <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing">
                <?php endif; ?>
              </div>
              
              <div class="booking-info">
                <h3 class="booking-title"><?php echo htmlspecialchars($booking['listing_title']); ?></h3>
                
                <div class="booking-guest">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="display: inline; margin-right: 5px;">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
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
                      ✓ Đã trả phòng
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
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
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

<style>
.booking-guest {
  font-size: 14px;
  color: #4a5568;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
}

.btn-complete {
  width: 100%;
  padding: 8px 16px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-complete:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-view-detail {
  display: block;
  width: 100%;
  padding: 8px 16px;
  background: white;
  color: #6366f1;
  border: 2px solid #6366f1;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  transition: all 0.2s;
  margin-top: 8px;
}

.btn-view-detail:hover {
  background: #6366f1;
  color: white;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  text-align: center;
  width: 100%;
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

.tab-count {
  display: inline-block;
  background: white;
  color: #6366f1;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 700;
  margin-left: 8px;
}

.tab-button.active .tab-count {
  background: #6366f1;
  color: white;
}

.badge-count {
  display: inline-block;
  background: #ef4444;
  color: white;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  margin-left: 8px;
}

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
</style>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
