<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Travel - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="./view/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="./view/css/header.css?v=<?php echo time(); ?>">
</head>
<body>
  <?php
  // Start session if not already started
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  
  // Tính đường dẫn tương đối về root dựa trên vị trí file hiện tại
  $currentPath = $_SERVER['PHP_SELF'];
  $depth = substr_count(dirname($currentPath), '/');
  $rootPath = str_repeat('../', $depth);
  
  // Check if user is logged in
  $isLoggedIn = isset($_SESSION['user_id']);
  $userName = $_SESSION['user_name'] ?? '';
  
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
        <a class="navbar-brand" href="<?php echo $rootPath; ?>index.php">
          <span class="brand-icon">🏠</span> WEGO
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
          <?php if ($isLoggedIn): ?>
            <?php if ($isApprovedHost): ?>
              <!-- Dashboard HOST Button (for approved hosts) -->
              <a href="<?php echo $rootPath; ?>view/user/host/host-dashboard.php" class="btn btn-host dashboard-host">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                  <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                </svg>
                <span>Dashboard HOST</span>
              </a>
            <?php else: ?>
              <!-- Become Host Button (for non-hosts) -->
              <a href="<?php echo $rootPath; ?>view/user/host/become-host.php" class="btn btn-host">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Trở thành Host</span>
              </a>
            <?php endif; ?>
            
            <!-- User logged in -->
            <div class="dropdown">
              <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span><?php echo htmlspecialchars($userName); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/profile.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                  Thông tin cá nhân
                </a></li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/traveller/my-bookings.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                  </svg>
                  Đặt phòng của tôi
                </a></li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/support/my-tickets.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                  </svg>
                  Hỗ trợ
                </a></li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/change-password.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                  </svg>
                  Đổi mật khẩu
                </a></li>
                <?php if ($isApprovedHost): ?>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header host-section">Quản lý HOST</li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/host/host-dashboard.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                  </svg>
                  Dashboard HOST
                </a></li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/host/create-listing.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Đăng phòng
                </a></li>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/host/my-listings.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                  </svg>
                  Phòng của tôi
                </a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="<?php echo $rootPath; ?>view/user/host/application-status.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                  </svg>
                  Đơn đăng ký Host
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo $rootPath; ?>view/user/logout.php">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                  </svg>
                  Đăng xuất
                </a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- User not logged in -->
            <a href="<?php echo $rootPath; ?>view/user/login.php" class="btn btn-outline-primary auth-btn">Đăng nhập</a>
            <a href="<?php echo $rootPath; ?>view/user/register.php" class="btn btn-primary auth-btn">Đăng ký</a>
          <?php endif; ?>
        </div>  
      </div>
    </nav>
  </header>
  <main class="site-main">