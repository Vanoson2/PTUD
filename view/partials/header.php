<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
  <title>Travel - Home</title>
=======
  <title>WEGO - Du lịch & Khám phá Việt Nam</title>
  <link rel="icon" type="image/png" href="/public/img/logo/logo.png">
  <link rel="shortcut icon" type="image/x-icon" href="/public/img/logo/logo.png">
>>>>>>> bae8384d57c302b18675c29de5e26bc79f829006
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="./view/css/shared-style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="./view/css/components-header.css?v=<?php echo time(); ?>">
</head>
<body>
  <?php
  // Start session if not already started
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  
  // Check if user is logged in
  $isLoggedIn = isset($_SESSION['user_id']);
  $userName = $_SESSION['user_name'] ?? '';
  
  // ⚠️ SECURITY CHECK: Verify user account status if logged in
  if ($isLoggedIn) {
    require_once __DIR__ . '/../../controller/cUser.php';
    $cUser = new cUser();
    $accountStatus = $cUser->cCheckUserAccountStatus($_SESSION['user_id']);
    
    // If account is locked, destroy session and redirect to login
    if ($accountStatus['is_locked']) {
      // Store error message before destroying session
      $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin.';
      
      session_unset();
      session_destroy();
      
      // Start new session to preserve error message
      session_start();
      $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin.';
      
      // Redirect to login
      header('Location: /view/user/traveller/login.php');
      exit;
    }
  }
  
  // Check if user is an approved HOST
  $isApprovedHost = false;
  if ($isLoggedIn) {
    require_once __DIR__ . '/../../controller/cHost.php';
    $cHost = new cHost();
    $isApprovedHost = $cHost->cIsUserHost($_SESSION['user_id']);
  }
  ?>
  <header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
<<<<<<< HEAD
        <a class="navbar-brand" href="/index.php">
          <i class="fa-solid fa-house brand-icon"></i> WEGO
=======
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
          <img src="/public/img/logo/logo.png" alt="WEGO Logo" style="height: 80px; margin-right: 10px;">
          <span style="font-weight: 600; font-size: 24px;">WEGO</span>
>>>>>>> bae8384d57c302b18675c29de5e26bc79f829006
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
          <?php if ($isLoggedIn): ?>
            <?php if ($isApprovedHost): ?>
              <!-- Dashboard HOST Button (for approved hosts) -->
              <a href="/view/user/host/host-dashboard.php" class="btn btn-host dashboard-host">
                <i class="fa-solid fa-briefcase"></i>
                <span>Dashboard HOST</span>
              </a>
            <?php else: ?>
              <!-- Become Host Button (for non-hosts) -->
              <a href="/view/user/host/become-host.php" class="btn btn-host">
                <i class="fa-solid fa-house-user"></i>
                <span>Trở thành Host</span>
              </a>
            <?php endif; ?>
            
            <!-- User logged in -->
            <div class="dropdown">
              <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
                <span><?php echo htmlspecialchars($userName); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="/view/user/traveller/profile.php">
                  <i class="fa-solid fa-user-circle"></i>
                  Thông tin cá nhân
                </a></li>
                <li><a class="dropdown-item" href="/view/user/traveller/my-bookings.php">
                  <i class="fa-solid fa-calendar-check"></i>
                  Đặt phòng của tôi
                </a></li>
                <li><a class="dropdown-item" href="/view/user/support/my-tickets.php">
                  <i class="fa-solid fa-headset"></i>
                  Hỗ trợ
                </a></li>
                <li><a class="dropdown-item" href="/view/user/traveller/change-password.php">
                  <i class="fa-solid fa-key"></i>
                  Đổi mật khẩu
                </a></li>
                <?php if ($isApprovedHost): ?>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header host-section">Quản lý HOST</li>
                <li><a class="dropdown-item" href="/view/user/host/host-dashboard.php">
                  <i class="fa-solid fa-chart-line"></i>
                  Dashboard HOST
                </a></li>
                <li><a class="dropdown-item" href="/view/user/host/create-listing.php">
                  <i class="fa-solid fa-plus-circle"></i>
                  Đăng phòng
                </a></li>
                <li><a class="dropdown-item" href="/view/user/host/my-listings.php">
                  <i class="fa-solid fa-bed"></i>
                  Phòng của tôi
                </a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="/view/user/host/application-status.php">
                  <i class="fa-solid fa-file-alt"></i>
                  Đơn đăng ký Host
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/view/user/traveller/logout.php">
                  <i class="fa-solid fa-right-from-bracket"></i>
                  Đăng xuất
                </a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- User not logged in -->
            <a href="/view/user/traveller/login.php" class="btn btn-outline-primary auth-btn">Đăng nhập</a>
            <a href="/view/user/traveller/register.php" class="btn btn-primary auth-btn">Đăng ký</a>
          <?php endif; ?>
        </div>  
      </div>
    </nav>
  </header>
  <main class="site-main">
