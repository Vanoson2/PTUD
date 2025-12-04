<?php
// Include Authentication Helper and Controller
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';

// Use helper for authentication
requireLogin();

$userId = getCurrentUserId();
$currentPage = 'profile'; // For sidebar active state
$rootPath = '../../';
$showVerifyButton = true; // Show verify email button in sidebar

// Use Controller to get user data
$cUser = new cUser();
$user = $cUser->cGetUserProfile($userId);

if (!$user) {
  logoutUser();
  header('Location: ./login.php');
  exit;
}

$errors = [];
$successMessage = '';

// Xử lý cập nhật profile - Delegate to Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $fullName = trim($_POST['full_name'] ?? '');
  $job = trim($_POST['job'] ?? '');
  $hobbies = trim($_POST['hobbies'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $gender = $_POST['gender'] ?? 'unknown';
  
  // Controller handles validation and business logic
  $result = $cUser->cUpdateUserProfile($userId, $fullName, $job, $hobbies, $location, $gender);
  
  if ($result['success']) {
    $successMessage = $result['message'];
    // Reload user data
    $user = $cUser->cGetUserProfile($userId);
    $_SESSION['user_name'] = $user['full_name'];
  } else {
    $errors['general'] = $result['message'];
  }
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/traveller-profile.css?v=<?php echo time(); ?>">

<?php include __DIR__ . '/../partials/profile-layout-start.php'; ?>

<!-- Page Content -->
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
    <button type="button" id="editBtn" class="btn btn-primary" onclick="enableEdit()">
      <i class="fas fa-edit"></i> Chỉnh sửa
    </button>
    <button type="submit" id="saveBtn" name="update_profile" class="btn btn-primary" style="display: none;">
      <i class="fas fa-save"></i> Lưu thay đổi
    </button>
    <button type="button" id="cancelBtn" class="btn btn-outline" onclick="cancelEdit()" style="display: none;">
      <i class="fas fa-times"></i> Hủy
    </button>
  </div>
</form>

<script>
// Disable all form fields initially
function disableEdit() {
  const form = document.querySelector('.profile-form');
  const inputs = form.querySelectorAll('input:not([type="email"]):not([type="tel"]), select, textarea');
  
  inputs.forEach(input => {
    input.disabled = true;
  });
  
  document.getElementById('editBtn').style.display = 'inline-block';
  document.getElementById('saveBtn').style.display = 'none';
  document.getElementById('cancelBtn').style.display = 'none';
}

// Enable form fields for editing
function enableEdit() {
  const form = document.querySelector('.profile-form');
  const inputs = form.querySelectorAll('input:not([type="email"]):not([type="tel"]), select, textarea');
  
  inputs.forEach(input => {
    input.disabled = false;
  });
  
  document.getElementById('editBtn').style.display = 'none';
  document.getElementById('saveBtn').style.display = 'inline-block';
  document.getElementById('cancelBtn').style.display = 'inline-block';
}

// Cancel editing
function cancelEdit() {
  window.location.reload();
}

// Initialize page in view mode
window.addEventListener('DOMContentLoaded', function() {
  <?php if (!$successMessage): ?>
  disableEdit();
  <?php else: ?>
  // After successful save, show success message then disable edit mode
  setTimeout(function() {
    disableEdit();
  }, 100);
  <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../partials/profile-layout-end.php'; ?>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
