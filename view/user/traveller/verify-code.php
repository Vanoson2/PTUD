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
      
      header('Location: ../../../index.php?verified=1');
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
        <i class="fas fa-envelope-open-text" style="font-size: 48px;"></i>
      </div>
      <h1>Xác Thực Email</h1>
      <p>Chúng tôi đã gửi mã xác thực 6 số đến</p>
      <p class="email-display"><?php echo htmlspecialchars($email); ?></p>
    </div>
    
    <?php if ($successMessage): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle" style="width: 20px; height: 20px;"></i>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
      </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
      <div class="alert alert-danger">
        <i class="fas fa-times-circle" style="width: 20px; height: 20px;"></i>
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
        <i class="fas fa-check-circle" style="width: 20px; height: 20px;"></i>
        Xác Thực
      </button>
    </form>
    
    <div class="auth-divider">
      <span>Không nhận được mã?</span>
    </div>
    
    <form method="POST" action="" class="resend-form">
      <button type="submit" name="resend_code" class="btn btn-outline btn-block">
        <i class="fas fa-redo" style="width: 20px; height: 20px;"></i>
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
