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
      <h1>🏡 Trở Thành Host</h1>
      <p>Chia sẻ không gian của bạn và kiếm thu nhập từ việc cho thuê nhà</p>
    </div>
    
    <!-- Benefits Grid -->
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-icon">💰</div>
        <h3>Thu Nhập Thêm</h3>
        <p>Kiếm tiền từ căn nhà, phòng trống của bạn. Bạn quyết định giá và thời gian cho thuê.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">🛡️</div>
        <h3>An Toàn & Bảo Mật</h3>
        <p>Chúng tôi xác minh danh tính khách hàng và cung cấp bảo hiểm tài sản cho host.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">📱</div>
        <h3>Quản Lý Dễ Dàng</h3>
        <p>Công cụ quản lý đặt phòng, lịch trình và giao tiếp với khách hàng đơn giản.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">🌍</div>
        <h3>Kết Nối Toàn Cầu</h3>
        <p>Tiếp cận hàng triệu du khách trên khắp thế giới đang tìm kiếm nơi lưu trú.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">⚡</div>
        <h3>Linh Hoạt</h3>
        <p>Tự do quyết định thời gian, giá cả và quy định cho thuê phù hợp với bạn.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">💬</div>
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
            <svg width="32" height="32" fill="#f59e0b" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
            <h3>Đơn Đăng Ký Đang Chờ Duyệt</h3>
          </div>
          <p>
            <?php echo htmlspecialchars($pendingMessage); ?>
          </p>
          <p class="email-notice">
            📧 Chúng tôi sẽ gửi email thông báo khi hồ sơ của bạn được xét duyệt.
          </p>
          <div class="pending-application-cta">
            <a href="application-status.php" class="btn btn-warning">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
              Xem Chi Tiết Đơn
            </a>
          </div>
        </div>
      <?php else: ?>
        <h2>Bắt Đầu Hành Trình Host</h2>
        <p>Chỉ cần vài bước đơn giản để đăng ký trở thành host và bắt đầu cho thuê nhà của bạn!</p>
        
        <a href="./register-host.php" class="btn-become-host">
          <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
          </svg>
          Đăng Ký Ngay
        </a>
      <?php endif; ?>
      
      <!-- Requirements -->
      <div class="requirements">
        <h4>Yêu Cầu:</h4>
        <ul>
          <li>
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Có tài khoản đã xác thực email</span>
          </li>
          <li>
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Cung cấp ảnh CMND/CCCD (mặt trước và sau)</span>
          </li>
          <li>
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Upload ảnh Giấy phép kinh doanh hoặc giấy tờ sở hữu</span>
          </li>
          <li>
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Có ít nhất một căn nhà/phòng để cho thuê</span>
          </li>
          <li>
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Chấp nhận các điều khoản và chính sách của WeGo</span>
          </li>
        </ul>
      </div>
    </div>
    
  </div>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
