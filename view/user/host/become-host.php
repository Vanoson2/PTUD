<?php
// Include Authentication Helper and Controllers
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';
require_once __DIR__ . '/../../../controller/cHost.php';

// Use helper for authentication
requireLogin();

$userId = getCurrentUserId();
$cUser = new cUser();
$cHost = new cHost();

// Get user profile through Controller
$user = $cUser->cGetUserProfile($userId);

if (!$user) {
  logoutUser();
  header('Location: ../traveller/login.php');
  exit;
}

// Check if user is already a host
$isHost = $cHost->cIsUserHost($userId);

// Check for pending application
$application = $cHost->cGetUserHostApplication($userId);
$hasPendingApplication = ($application && $application['status'] === 'pending');

// If already a host or has pending application, redirect
if ($isHost) {
  header('Location: ./my-listings.php?msg=already_host');
  exit;
}

if ($hasPendingApplication) {
  // Show pending message
  $pendingMessage = 'Bạn đã gửi đơn đăng ký host vào ngày ' . date('d/m/Y', strtotime($application['created_at'])) . '. Chúng tôi đang xem xét hồ sơ của bạn.';
}

?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/host-become-host.css">

<div class="become-host-container">
  <div class="container">
    
    <!-- Hero Section -->
    <div class="hero-section">
      <h1><i class="fa-solid fa-house"></i> Trở Thành Host</h1>
      <p>Chia sẻ không gian của bạn và kiếm thu nhập từ việc cho thuê nhà</p>
    </div>
    
    <!-- Benefits Grid -->
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-dollar-sign"></i></div>
        <h3>Thu Nhập Thêm</h3>
        <p>Kiếm tiền từ căn nhà, phòng trống của bạn. Bạn quyết định giá và thời gian cho thuê.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-shield-alt"></i></div>
        <h3>An Toàn & Bảo Mật</h3>
        <p>Chúng tôi xác minh danh tính khách hàng và cung cấp bảo hiểm tài sản cho host.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-mobile-alt"></i></div>
        <h3>Quản Lý Dễ Dàng</h3>
        <p>Công cụ quản lý đặt phòng, lịch trình và giao tiếp với khách hàng đơn giản.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-globe"></i></div>
        <h3>Kết Nối Toàn Cầu</h3>
        <p>Tiếp cận hàng triệu du khách trên khắp thế giới đang tìm kiếm nơi lưu trú.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-bolt"></i></div>
        <h3>Linh Hoạt</h3>
        <p>Tự do quyết định thời gian, giá cả và quy định cho thuê phù hợp với bạn.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon"><i class="fa-solid fa-comments"></i></div>
        <h3>Hỗ Trợ 24/7</h3>
        <p>Đội ngũ hỗ trợ host luôn sẵn sàng giúp đỡ bạn bất cứ lúc nào.</p>
      </div>
    </div>
    
    <!-- CTA Section -->
    <div class="cta-section">
      <?php if (isset($pendingMessage)): ?>
        <!-- Pending Application Message -->
        <div class="pending-application-box">
          <div class="pending-application-header">
            <i class="fa-solid fa-clock fa-2x" style="color: #f59e0b;"></i>
            <h3>Đơn Đăng Ký Đang Chờ Duyệt</h3>
          </div>
          <p>
            <?php echo htmlspecialchars($pendingMessage); ?>
          </p>
          <p class="email-notice">
            <i class="fa-solid fa-envelope"></i> Chúng tôi sẽ gửi email thông báo khi hồ sơ của bạn được xét duyệt.
          </p>
          <div class="pending-application-cta">
            <a href="application-status.php" class="btn btn-warning">
              <i class="fa-solid fa-clipboard-list"></i>
              Xem Chi Tiết Đơn
            </a>
          </div>
        </div>
      <?php else: ?>
        <h2>Bắt Đầu Hành Trình Host</h2>
        <p>Chỉ cần vài bước đơn giản để đăng ký trở thành host và bắt đầu cho thuê nhà của bạn!</p>
        
        <a href="./register-host.php" class="btn-become-host">
          <i class="fa-solid fa-house"></i>
          Đăng Ký Ngay
        </a>
      <?php endif; ?>
      
      <!-- Requirements -->
      <div class="requirements">
        <h4>Yêu Cầu:</h4>
        <ul>
          <li>
            <i class="fa-solid fa-check-circle"></i>
            <span>Có tài khoản đã xác thực email</span>
          </li>
          <li>
            <i class="fa-solid fa-check-circle"></i>
            <span>Cung cấp ảnh CMND/CCCD (mặt trước và sau)</span>
          </li>
          <li>
            <i class="fa-solid fa-check-circle"></i>
            <span>Upload ảnh Giấy phép kinh doanh hoặc giấy tờ sở hữu</span>
          </li>
          <li>
            <i class="fa-solid fa-check-circle"></i>
            <span>Có ít nhất một căn nhà/phòng để cho thuê</span>
          </li>
          <li>
            <i class="fa-solid fa-check-circle"></i>
            <span>Chấp nhận các điều khoản và chính sách của WeGo</span>
          </li>
        </ul>
      </div>
    </div>
    
  </div>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
