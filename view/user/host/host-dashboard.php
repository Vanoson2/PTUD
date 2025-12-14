<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
$rootPath = '../../../';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . $rootPath . 'view/user/traveller/login.php');
  exit();
}

// Check if user is an approved HOST
require_once __DIR__ . '/../../../controller/cHost.php';
$cHost = new cHost();
$isHost = $cHost->cIsUserHost($_SESSION['user_id']);

if (!$isHost) {
  header('Location: ' . $rootPath . 'view/user/host/become-host.php');
  exit();
}

// Get HOST info
$hostInfo = $cHost->cGetHostByUserId($_SESSION['user_id']);
if (!$hostInfo) {
  header('Location: ' . $rootPath . 'view/user/host/become-host.php');
  exit();
}

// Get HOST statistics
$hostStats = $cHost->cGetHostStatistics($_SESSION['user_id']);

// Get recent listings (limit 5)
$recentListingsResult = $cHost->cGetHostListings($hostInfo['host_id']);
$recentListings = [];
if (is_array($recentListingsResult) && !empty($recentListingsResult)) {
  $recentListings = array_slice($recentListingsResult, 0, 5);
}

include_once __DIR__ . '/../../partials/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo $rootPath; ?>view/css/host-dashboard.css?v=<?php echo time(); ?>">

<div class="dashboard-container">
  <div class="dashboard-header">
    <div>
      <h1>
        <i class="fa-solid fa-chart-line" style="font-size: 32px;"></i>
        Dashboard HOST
      </h1>
      <p class="welcome-text">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Host'); ?></strong>! Chào mừng đến với bảng điều khiển HOST.</p>
    </div>
    <a href="host-reports.php" class="btn-reports">
      <i class="fas fa-chart-line"></i> Xem báo cáo chi tiết
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card stat-listings">
      <div class="stat-icon">
        <i class="fa-solid fa-home" style="font-size: 32px;"></i>
      </div>
      <div class="stat-content">
        <h3>Tổng số phòng</h3>
        <p class="stat-number"><?php echo $hostStats['total_listings'] ?? 0; ?></p>
        <span class="stat-label">Đang hoạt động</span>
      </div>
    </div>

    <div class="stat-card stat-bookings">
      <div class="stat-icon">
        <i class="fa-solid fa-calendar-check" style="font-size: 32px;"></i>
      </div>
      <div class="stat-content">
        <h3>Lượt đặt phòng</h3>
        <p class="stat-number"><?php echo $hostStats['total_bookings'] ?? 0; ?></p>
        <span class="stat-label">Tất cả thời gian</span>
      </div>
    </div>

    <div class="stat-card stat-revenue">
      <div class="stat-icon">
        <i class="fa-solid fa-dollar-sign" style="font-size: 32px;"></i>
      </div>
      <div class="stat-content">
        <h3>Doanh thu</h3>
        <p class="stat-number"><?php echo number_format($hostStats['total_revenue'] ?? 0, 0, ',', '.'); ?> đ</p>
        <span class="stat-label">Tất cả thời gian</span>
      </div>
    </div>

    <div class="stat-card stat-reviews">
      <div class="stat-icon">
        <i class="fa-solid fa-star" style="font-size: 32px;"></i>
      </div>
      <div class="stat-content">
        <h3>Đánh giá trung bình</h3>
        <p class="stat-number"><?php echo number_format($hostStats['average_rating'] ?? 0, 1); ?></p>
        <span class="stat-label"><i class="fa-solid fa-star" style="color: gold;"></i> Từ <?php echo $hostStats['total_reviews'] ?? 0; ?> đánh giá</span>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions">
    <h2>Hành động nhanh</h2>
    <div class="actions-grid">
      <a href="create-listing.php" class="action-card action-create">
        <div class="action-icon">
          <i class="fa-solid fa-plus-circle" style="font-size: 40px;"></i>
        </div>
        <h3>Đăng phòng mới</h3>
        <p>Tạo listing mới cho thuê</p>
      </a>

      <a href="my-listings.php" class="action-card action-manage">
        <div class="action-icon">
          <i class="fa-solid fa-bed" style="font-size: 40px;"></i>
        </div>
        <h3>Quản lý phòng</h3>
        <p>Xem và chỉnh sửa listings</p>
      </a>

      <a href="host-bookings.php" class="action-card action-bookings">
        <div class="action-icon">
          <i class="fa-solid fa-calendar-alt" style="font-size: 40px;"></i>
        </div>
        <h3>Đặt phòng</h3>
        <p>Xem các booking đang chờ</p>
      </a>

      <a href="application-status.php" class="action-card action-status">
        <div class="action-icon">
          <i class="fa-solid fa-info-circle" style="font-size: 40px;"></i>
        </div>
        <h3>Trạng thái đơn</h3>
        <p>Xem thông tin đăng ký HOST</p>
      </a>
    </div>
  </div>

  <!-- Recent Listings -->
  <?php if (!empty($recentListings)): ?>
  <div class="recent-listings">
    <div class="section-header">
      <h2>Phòng gần đây</h2>
      <a href="my-listings.php" class="view-all">Xem tất cả →</a>
    </div>
    <div class="listings-grid">
      <?php foreach ($recentListings as $listing): ?>
      <a href="./listing-detail.php?id=<?php echo $listing['listing_id']; ?>" class="listing-card">
        <div class="listing-image">
          <?php if (!empty($listing['image_url'])): ?>
            <?php
            // Determine correct image path
            $imagePath = $listing['image_url'];
            if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
              // Local path - add rootPath
              $imagePath = $rootPath . $imagePath;
            }
            // else: Keep full URL as is (Pexels)
            ?>
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
          <?php else: ?>
            <div class="no-image">
              <i class="fa-solid fa-image" style="font-size: 48px;"></i>
            </div>
          <?php endif; ?>
          <span class="listing-status status-<?php echo strtolower($listing['status'] ?? 'draft'); ?>">
            <?php 
              $status = $listing['status'] ?? 'draft';
              echo $status === 'published' ? 'Đang hoạt động' : ($status === 'pending' ? 'Chờ duyệt' : 'Nháp');
            ?>
          </span>
        </div>
        <div class="listing-info">
          <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
          <p class="listing-price"><?php echo number_format($listing['price_per_night'] ?? 0, 0, ',', '.'); ?> đ/đêm</p>
          <p class="listing-location">
            <i class="fa-solid fa-map-marker-alt"></i>
            <?php echo htmlspecialchars($listing['location'] ?? 'Chưa cập nhật'); ?>
          </p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa-solid fa-home" style="font-size: 80px;"></i>
    <h3>Bạn chưa có phòng nào</h3>
    <p>Hãy bắt đầu bằng cách tạo listing đầu tiên của bạn</p>
    <a href="create-listing.php" class="btn-primary">
      <i class="fa-solid fa-plus-circle"></i>
      Đăng phòng đầu tiên
    </a>
  </div>
  <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
