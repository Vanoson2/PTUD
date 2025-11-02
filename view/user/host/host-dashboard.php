<?php
session_start();
$rootPath = '../../../';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . $rootPath . 'view/user/login.php');
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
        <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
          <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
        </svg>
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
        <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Tổng số phòng</h3>
        <p class="stat-number"><?php echo $hostStats['total_listings'] ?? 0; ?></p>
        <span class="stat-label">Đang hoạt động</span>
      </div>
    </div>

    <div class="stat-card stat-bookings">
      <div class="stat-icon">
        <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Lượt đặt phòng</h3>
        <p class="stat-number"><?php echo $hostStats['total_bookings'] ?? 0; ?></p>
        <span class="stat-label">Tất cả thời gian</span>
      </div>
    </div>

    <div class="stat-card stat-revenue">
      <div class="stat-icon">
        <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
          <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Doanh thu</h3>
        <p class="stat-number"><?php echo number_format($hostStats['total_revenue'] ?? 0, 0, ',', '.'); ?> đ</p>
        <span class="stat-label">Tất cả thời gian</span>
      </div>
    </div>

    <div class="stat-card stat-reviews">
      <div class="stat-icon">
        <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Đánh giá trung bình</h3>
        <p class="stat-number"><?php echo number_format($hostStats['average_rating'] ?? 0, 1); ?></p>
        <span class="stat-label">⭐ Từ <?php echo $hostStats['total_reviews'] ?? 0; ?> đánh giá</span>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions">
    <h2>Hành động nhanh</h2>
    <div class="actions-grid">
      <a href="create-listing.php" class="action-card action-create">
        <div class="action-icon">
          <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
          </svg>
        </div>
        <h3>Đăng phòng mới</h3>
        <p>Tạo listing mới cho thuê</p>
      </a>

      <a href="my-listings.php" class="action-card action-manage">
        <div class="action-icon">
          <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20">
            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
          </svg>
        </div>
        <h3>Quản lý phòng</h3>
        <p>Xem và chỉnh sửa listings</p>
      </a>

      <a href="host-bookings.php" class="action-card action-bookings">
        <div class="action-icon">
          <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
          </svg>
        </div>
        <h3>Đặt phòng</h3>
        <p>Xem các booking đang chờ</p>
      </a>

      <a href="application-status.php" class="action-card action-status">
        <div class="action-icon">
          <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
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
              <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
              </svg>
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
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            <?php echo htmlspecialchars($listing['location'] ?? 'Chưa cập nhật'); ?>
          </p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <svg width="80" height="80" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
    </svg>
    <h3>Bạn chưa có phòng nào</h3>
    <p>Hãy bắt đầu bằng cách tạo listing đầu tiên của bạn</p>
    <a href="create-listing.php" class="btn-primary">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
      </svg>
      Đăng phòng đầu tiên
    </a>
  </div>
  <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>
