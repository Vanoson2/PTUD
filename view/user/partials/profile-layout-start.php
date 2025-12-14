<!-- Profile Layout với Sidebar -->
<div class="profile-container">
  <div class="profile-wrapper">
    
    <!-- Sidebar -->
    <aside class="profile-sidebar">
      <div class="profile-card">
        <div class="profile-avatar">
          <div class="avatar-placeholder">
            <?php 
            $initials = '';
            $nameParts = explode(' ', $user['full_name']);
            if (count($nameParts) >= 2) {
              $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
            } else {
              $initials = strtoupper(substr($user['full_name'], 0, 2));
            }
            echo $initials;
            ?>
          </div>
        </div>
        <h3 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        
        <?php if ($user['is_email_verified'] == 1): ?>
          <span class="badge badge-success">
            <i class="fa-solid fa-check-circle"></i>
            Email đã xác thực
          </span>
        <?php else: ?>
          <span class="badge badge-warning">
            <i class="fa-solid fa-exclamation-triangle"></i>
            Chưa xác thực email
          </span>
          
          <?php if (isset($showVerifyButton) && $showVerifyButton): ?>
          <div class="verify-email-cta">
            <a href="<?php echo $rootPath ?? '../'; ?>view/user/traveller/verify-code.php?user_id=<?php echo $userId; ?>&email=<?php echo urlencode($user['email']); ?>&auto_send=1" 
               class="verify-email-button">
              <i class="fa-solid fa-envelope"></i>
              Xác thực email ngay
            </a>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      
      <nav class="profile-nav">
        <a href="./profile.php" 
           class="nav-item <?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>">
          <i class="fa-solid fa-user"></i>
          Thông tin cá nhân
        </a>
        
        <a href="./my-bookings.php" 
           class="nav-item <?php echo ($currentPage ?? '') === 'bookings' ? 'active' : ''; ?>">
          <i class="fa-solid fa-clipboard-list"></i>
          Đơn đặt của tôi
        </a>
        
        <a href="./trust-score.php" 
           class="nav-item <?php echo ($currentPage ?? '') === 'trust-score' ? 'active' : ''; ?>">
          <i class="fa-solid fa-star"></i>
          Điểm Tín Nhiệm
        </a>
        
        <a href="./logout.php" class="nav-item">
          <i class="fa-solid fa-right-from-bracket"></i>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="profile-main">
