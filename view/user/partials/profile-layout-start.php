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
              $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1, 'UTF-8') . mb_substr($nameParts[count($nameParts) - 1], 0, 1, 'UTF-8'), 'UTF-8');
            } else {
              $initials = mb_strtoupper(mb_substr($user['full_name'], 0, 2, 'UTF-8'), 'UTF-8');
            }
            echo $initials;
            ?>
          </div>
        </div>
        <h3 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        
        <!-- Trust Score Badge -->
        <?php 
        $trustScore = $_SESSION['trust_score'] ?? $user['trust_score'] ?? 100;
        $scoreClass = 'badge-danger';
        $scoreIcon = 'fa-lock';
        $scoreText = 'Tài khoản bị khóa';
        
        if ($trustScore >= 100) {
          $scoreClass = 'badge-excellent';
          $scoreIcon = 'fa-star';
          $scoreText = 'Xuất sắc';
        } elseif ($trustScore >= 70) {
          $scoreClass = 'badge-success';
          $scoreIcon = 'fa-check-circle';
          $scoreText = 'Tốt';
        } elseif ($trustScore >= 50) {
          $scoreClass = 'badge-info';
          $scoreIcon = 'fa-info-circle';
          $scoreText = 'Bình thường';
        } elseif ($trustScore >= 30) {
          $scoreClass = 'badge-warning';
          $scoreIcon = 'fa-exclamation-triangle';
          $scoreText = 'Cảnh báo';
        }
        ?>
        <div class="trust-score-badge <?php echo $scoreClass; ?>">
          <i class="fa-solid <?php echo $scoreIcon; ?>"></i>
          <span class="score-label"><?php echo $scoreText; ?>:</span>
          <span class="score-value"><?php echo $trustScore; ?>/150</span>
        </div>
        
        <!-- Email Verification Badge -->
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
      
      <?php 
      // Hiển thị cảnh báo điểm tín nhiệm (nếu có)
      if (isset($_SESSION['trust_score_warning']) && !empty($_SESSION['trust_score_warning'])): 
        $warningLevel = $_SESSION['trust_score_warning_level'] ?? 'warning';
        $trustScore = $_SESSION['trust_score'] ?? 100;
      ?>
        <div class="trust-score-warning-banner <?php echo $warningLevel; ?>">
          <div class="warning-icon">
            <i class="fa-solid fa-exclamation-triangle"></i>
          </div>
          <div class="warning-content">
            <h4 class="warning-title">
              <?php if ($trustScore < 30): ?>
                <i class="fa-solid fa-lock"></i> Tài khoản đã bị khóa
              <?php else: ?>
                <i class="fa-solid fa-triangle-exclamation"></i> Cảnh báo điểm tín nhiệm thấp
              <?php endif; ?>
            </h4>
            <p class="warning-message"><?php echo htmlspecialchars($_SESSION['trust_score_warning']); ?></p>
            <div class="warning-score">
              Điểm hiện tại: <strong><?php echo $trustScore; ?>/150</strong>
              <a href="./trust-score.php" class="view-score-link">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
            </div>
          </div>
          <button class="close-warning" onclick="this.parentElement.style.display='none';">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      <?php endif; ?>
