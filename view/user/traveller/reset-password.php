<?php
include_once __DIR__ . '/../../../controller/cUser.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$token = $_GET['token'] ?? '';
$message = '';
$messageType = '';
$validToken = false;

// Verify token
if (empty($token)) {
  $message = 'Link không hợp lệ. Vui lòng kiểm tra lại email của bạn.';
  $messageType = 'danger';
} else {
  $cUser = new cUser();
  $result = $cUser->cVerifyResetToken($token);
  
  if ($result['success']) {
    $validToken = true;
    $userInfo = $result['user'];
  } else {
    $message = $result['message'];
    $messageType = 'danger';
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  
  $cUser = new cUser();
  $result = $cUser->cResetPassword($token, $newPassword, $confirmPassword);
  
  $message = $result['message'];
  $messageType = $result['success'] ? 'success' : 'danger';
  
  if ($result['success']) {
    $validToken = false; // Hide form after successful reset
  }
}

include __DIR__ . '/../../partials/header.php';
?>

<link rel="stylesheet" href="../../css/traveller-auth.css">

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Đặt lại mật khẩu</h1>
      <p>Nhập mật khẩu mới của bạn</p>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
        
        <?php if ($messageType === 'success'): ?>
          <br><br>
          <a href="./login.php" class="btn btn-primary" style="text-decoration: none; color: white;">
            Đăng nhập ngay
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($validToken): ?>
      <form method="POST" action="" class="auth-form">
        <div class="form-group">
          <label for="new_password">Mật khẩu mới</label>
          <div class="password-input-wrapper">
            <input 
              type="password" 
              id="new_password" 
              name="new_password" 
              class="form-control" 
              placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
              required
              minlength="6"
            >
            <button type="button" class="toggle-password" data-target="new_password">
              <i class="fas fa-eye eye-icon" style="width: 20px; height: 20px;"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="confirm_password">Xác nhận mật khẩu</label>
          <div class="password-input-wrapper">
            <input 
              type="password" 
              id="confirm_password" 
              name="confirm_password" 
              class="form-control" 
              placeholder="Nhập lại mật khẩu mới"
              required
              minlength="6"
            >
            <button type="button" class="toggle-password" data-target="confirm_password">
              <i class="fas fa-eye eye-icon" style="width: 20px; height: 20px;"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          Đặt lại mật khẩu
        </button>
      </form>
    <?php endif; ?>

    <div class="auth-footer">
      <p>Đã nhớ mật khẩu? <a href="./login.php">Đăng nhập</a></p>
    </div>
  </div>
</div>

<script>
// Toggle password visibility
document.addEventListener('DOMContentLoaded', function() {
  const toggleButtons = document.querySelectorAll('.toggle-password');
  
  toggleButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
      
      if (input.type === 'password') {
        input.type = 'text';
        this.innerHTML = `
          <i class="fas fa-eye-slash eye-icon" style="width: 20px; height: 20px;"></i>
        `;
      } else {
        input.type = 'password';
        this.innerHTML = `
          <i class="fas fa-eye eye-icon" style="width: 20px; height: 20px;"></i>
        `;
      }
    });
  });
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
