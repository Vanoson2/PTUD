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
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Email đã xác thực
          </span>
        <?php else: ?>
          <span class="badge badge-warning">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Chưa xác thực email
          </span>
          
          <?php if (isset($showVerifyButton) && $showVerifyButton): ?>
          <div class="verify-email-cta">
            <a href="<?php echo $rootPath ?? '../'; ?>view/user/verify-code.php?user_id=<?php echo $userId; ?>&email=<?php echo urlencode($user['email']); ?>&auto_send=1" 
               class="verify-email-button">
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
              </svg>
              Xác thực email ngay
            </a>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      
      <nav class="profile-nav">
        <a href="<?php echo $rootPath ?? '../'; ?>view/user/profile.php" 
           class="nav-item <?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
          Thông tin cá nhân
        </a>
        
        <a href="<?php echo $rootPath ?? '../'; ?>view/user/traveller/my-bookings.php" 
           class="nav-item <?php echo ($currentPage ?? '') === 'bookings' ? 'active' : ''; ?>">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
          </svg>
          Đơn đặt của tôi
        </a>
        
        <a href="<?php echo $rootPath ?? '../'; ?>view/user/logout.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
          </svg>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="profile-main">
