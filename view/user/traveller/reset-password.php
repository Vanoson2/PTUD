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
              <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
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
              <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
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
          <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
          </svg>
        `;
      } else {
        input.type = 'password';
        this.innerHTML = `
          <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
          </svg>
        `;
      }
    });
  });
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
