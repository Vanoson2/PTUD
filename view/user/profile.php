<?php
include_once __DIR__ . '/../../controller/cUser.php';
include_once __DIR__ . '/../../model/mUser.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
  header('Location: ./login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

$userId = $_SESSION['user_id'];
$mUser = new mUser();
$user = $mUser->mGetUserById($userId);

if (!$user) {
  session_destroy();
  header('Location: ./login.php');
  exit;
}

$errors = [];
$successMessage = '';

// Xử lý cập nhật profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $fullName = trim($_POST['full_name'] ?? '');
  $job = trim($_POST['job'] ?? '');
  $hobbies = trim($_POST['hobbies'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $gender = $_POST['gender'] ?? 'unknown';
  
  // Validate
  if (empty($fullName)) {
    $errors['full_name'] = 'Vui lòng nhập họ tên';
  }
  
  if (empty($errors)) {
    $result = $mUser->mUpdateProfile($userId, $fullName, $job, $hobbies, $location, $gender);
    
    if ($result['success']) {
      $successMessage = $result['message'];
      // Reload user data
      $user = $mUser->mGetUserById($userId);
      $_SESSION['user_name'] = $user['full_name'];
    } else {
      $errors['general'] = $result['message'];
    }
  }
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../css/profile.css?v=<?php echo time(); ?>">

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
          
          <div class="verify-email-cta">
            <a href="./verify-code.php?user_id=<?php echo $userId; ?>&email=<?php echo urlencode($user['email']); ?>&auto_send=1" 
               class="verify-email-button">
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
              </svg>
              Xác thực email ngay
            </a>
          </div>
        <?php endif; ?>
      </div>
      
      <nav class="profile-nav">
        <a href="#profile" class="nav-item active">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
          Thông tin cá nhân
        </a>
        <a href="#bookings" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
          </svg>
          Booking của tôi
        </a>
        <a href="#settings" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
          </svg>
          Cài đặt
        </a>
        <a href="./logout.php" class="nav-item">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
          </svg>
          Đăng xuất
        </a>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="profile-main">
      <div class="profile-header">
        <h1>Thông tin cá nhân</h1>
        <p>Quản lý thông tin cá nhân và cài đặt tài khoản của bạn</p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
      <?php endif; ?>
      
      <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo htmlspecialchars($errors['general']); ?></span>
        </div>
      <?php endif; ?>
      
      <form action="" method="POST" class="profile-form">
        <div class="form-section">
          <h2>Thông tin cơ bản</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Họ và tên <span class="required">*</span></label>
              <input 
                type="text" 
                id="full_name" 
                name="full_name" 
                class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                required
              >
              <?php if (isset($errors['full_name'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="email">Email</label>
              <input 
                type="email" 
                id="email" 
                class="form-control" 
                value="<?php echo htmlspecialchars($user['email']); ?>"
                disabled
              >
              <small class="form-text">Email không thể thay đổi</small>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="phone">Số điện thoại</label>
              <input 
                type="tel" 
                id="phone" 
                class="form-control" 
                value="<?php echo htmlspecialchars($user['phone']); ?>"
                disabled
              >
              <small class="form-text">Số điện thoại không thể thay đổi</small>
            </div>
            
            <div class="form-group">
              <label for="gender">Giới tính</label>
              <select id="gender" name="gender" class="form-control">
                <option value="unknown" <?php echo ($user['gender'] ?? 'unknown') == 'unknown' ? 'selected' : ''; ?>>Không xác định</option>
                <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Nam</option>
                <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Nữ</option>
                <option value="other" <?php echo ($user['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Khác</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="form-section">
          <h2>Thông tin bổ sung</h2>
          
          <div class="form-group">
            <label for="job">Nghề nghiệp</label>
            <input 
              type="text" 
              id="job" 
              name="job" 
              class="form-control"
              value="<?php echo htmlspecialchars($user['job'] ?? ''); ?>"
              placeholder="Ví dụ: Kỹ sư phần mềm, Giáo viên..."
            >
          </div>
          
          <div class="form-group">
            <label for="location">Địa chỉ</label>
            <input 
              type="text" 
              id="location" 
              name="location" 
              class="form-control"
              value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>"
              placeholder="Thành phố, Quốc gia"
            >
          </div>
          
          <div class="form-group">
            <label for="hobbies">Sở thích</label>
            <textarea 
              id="hobbies" 
              name="hobbies" 
              class="form-control"
              rows="3"
              placeholder="Ví dụ: Du lịch, Đọc sách, Thể thao..."
            ><?php echo htmlspecialchars($user['hobbies'] ?? ''); ?></textarea>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" name="update_profile" class="btn btn-primary">Lưu thay đổi</button>
          <button type="button" class="btn btn-outline" onclick="window.location.reload()">Hủy</button>
        </div>
      </form>
      
    </main>
    
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
