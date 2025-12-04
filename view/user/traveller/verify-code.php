<?php
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';

ensureSessionStarted();

// Kiểm tra đã đăng nhập và verified chưa - nếu rồi thì redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['is_email_verified']) && $_SESSION['is_email_verified'] == 1) {
  header('Location: ../../index.php');
  exit;
}

$successMessage = '';
$errorMessage = '';

// Lấy user_id và email từ URL hoặc session
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_SESSION['pending_verify_user_id']) ? $_SESSION['pending_verify_user_id'] : 0);
$email = isset($_GET['email']) ? $_GET['email'] : (isset($_SESSION['pending_verify_email']) ? $_SESSION['pending_verify_email'] : '');

if ($userId == 0 || empty($email)) {
  header('Location: ./register.php');
  exit;
}

// Lưu vào session để tái sử dụng
$_SESSION['pending_verify_user_id'] = $userId;
$_SESSION['pending_verify_email'] = $email;

$cUser = new cUser();

// Tự động gửi mã nếu từ profile (từ nút "Xác thực email ngay")
if (isset($_GET['auto_send']) && $_GET['auto_send'] == '1') {
  $result = $cUser->cResendVerificationCode($userId, $email);
  
  if ($result['success']) {
    $successMessage = 'Đã gửi mã xác thực đến email của bạn. Vui lòng kiểm tra!';
  } else {
    $errorMessage = $result['message'] ?? 'Không thể gửi mã. Vui lòng thử lại sau.';
  }
}

// Xử lý verify code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
  $code = trim($_POST['code'] ?? '');
  
  $result = $cUser->cVerifyCode($userId, $code);
  
  if ($result['success']) {
    // Xác thực thành công - tự động đăng nhập
    $user = $result['user'];
    
    if ($user) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['user_name'] = $user['full_name'];
      $_SESSION['user_phone'] = $user['phone'];
      $_SESSION['is_email_verified'] = 1;
      
      // Xóa session pending
      unset($_SESSION['pending_verify_user_id']);
      unset($_SESSION['pending_verify_email']);
      
      header('Location: ../../index.php?verified=1');
      exit;
    }
  } else {
    $errorMessage = $result['message'];
  }
}

// Xử lý gửi lại mã
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
  $result = $cUser->cResendVerificationCode($userId, $email);
  
  if ($result['success']) {
    $successMessage = 'Đã gửi lại mã xác thực đến email của bạn. Vui lòng kiểm tra!';
  } else {
    $errorMessage = $result['message'] ?? 'Không thể gửi lại mã. Vui lòng thử lại sau.';
  }
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/traveller-auth.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/traveller-verify-code.css?v=<?php echo time(); ?>">

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <div class="icon-wrapper">
        <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M2.94 6.412A2 2 0 002 8.108V16a2 2 0 002 2h12a2 2 0 002-2V8.108a2 2 0 00-.94-1.696l-6-3.75a2 2 0 00-2.12 0l-6 3.75zm2.615 2.423a1 1 0 10-1.11 1.664l5 3.333a1 1 0 001.11 0l5-3.333a1 1 0 00-1.11-1.664L10 11.798 5.555 8.835z" clip-rule="evenodd"/>
        </svg>
      </div>
      <h1>Xác Thực Email</h1>
      <p>Chúng tôi đã gửi mã xác thực 6 số đến</p>
      <p class="email-display"><?php echo htmlspecialchars($email); ?></p>
    </div>
    
    <?php if ($successMessage): ?>
      <div class="alert alert-success">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
      </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
      <div class="alert alert-danger">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span><?php echo htmlspecialchars($errorMessage); ?></span>
      </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="auth-form" id="verifyForm">
      <div class="form-group">
        <label for="code">Mã xác thực</label>
        <input 
          type="text" 
          id="code" 
          name="code" 
          class="form-control" 
          placeholder="Nhập 6 chữ số"
          maxlength="6"
          pattern="[0-9]{6}"
          required
          autofocus
        >
        <small class="form-text">Mã có hiệu lực trong 15 phút</small>
      </div>
      
      <button type="submit" name="verify_code" class="btn btn-primary btn-block">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Xác Thực
      </button>
    </form>
    
    <div class="auth-divider">
      <span>Không nhận được mã?</span>
    </div>
    
    <form method="POST" action="" class="resend-form">
      <button type="submit" name="resend_code" class="btn btn-outline btn-block">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
        </svg>
        Gửi Lại Mã
      </button>
    </form>
    
    <div class="auth-footer">
      <a href="./login.php">← Quay lại đăng nhập</a>
    </div>
  </div>
</div>

<script>
// Auto-focus và format input
const codeInput = document.getElementById('code');

codeInput.addEventListener('input', function(e) {
  // Chỉ cho phép nhập số
  this.value = this.value.replace(/[^0-9]/g, '');
  
  // Tự động submit khi đủ 6 số
  if (this.value.length === 6) {
    // Optional: Auto submit form
    // document.getElementById('verifyForm').submit();
  }
});

// Prevent paste non-numbers
codeInput.addEventListener('paste', function(e) {
  e.preventDefault();
  const paste = (e.clipboardData || window.clipboardData).getData('text');
  const cleaned = paste.replace(/[^0-9]/g, '').substring(0, 6);
  this.value = cleaned;
  
  if (cleaned.length === 6) {
    // Optional: Auto submit
    // document.getElementById('verifyForm').submit();
  }
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
