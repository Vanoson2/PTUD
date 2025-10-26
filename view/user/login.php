<?php
include_once __DIR__ . '/../../controller/cUser.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: ../../index.php');
  exit;
}

$errors = [];
$formData = ['email_or_phone' => ''];

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $emailOrPhone = trim($_POST['email_or_phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember']);
  
  // Lưu lại dữ liệu form
  $formData['email_or_phone'] = $emailOrPhone;
  
  // Gọi controller để xử lý đăng nhập
  $cUser = new cUser();
  $result = $cUser->cLoginUser($emailOrPhone, $password);
  
  if ($result['success']) {
    $user = $result['user'];
    
    // Lưu thông tin vào session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['is_email_verified'] = $user['is_email_verified'];
    
    // Remember me (cookie 30 days)
    if ($remember) {
      $token = bin2hex(random_bytes(32));
      setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
    }
    
    // Redirect về trang trước đó hoặc trang chủ
    $redirect = $_GET['redirect'] ?? '../../index.php';
    header('Location: ' . $redirect);
    exit;
  } else {
    $errors = $result['errors'];
  }
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../css/auth.css?v=<?php echo time(); ?>">

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Đăng nhập</h1>
      <p>Chào mừng bạn quay trở lại!</p>
    </div>
    
    <?php if (isset($errors['general'])): ?>
      <div class="alert alert-danger">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span><?php echo htmlspecialchars($errors['general']); ?></span>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['register_success'])): ?>
      <div class="alert alert-success">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?php echo htmlspecialchars($_SESSION['register_success']); unset($_SESSION['register_success']); ?></span>
      </div>
    <?php endif; ?>
    
    <form action="" method="POST" class="auth-form" id="loginForm">
      
      <!-- Email or Phone -->
      <div class="form-group">
        <label for="email_or_phone">Email hoặc Số điện thoại <span class="required">*</span></label>
        <input 
          type="text" 
          id="email_or_phone" 
          name="email_or_phone" 
          class="form-control <?php echo isset($errors['email_or_phone']) ? 'is-invalid' : ''; ?>"
          value="<?php echo htmlspecialchars($formData['email_or_phone']); ?>"
          placeholder="example@email.com hoặc 0912345678"
          required
          autofocus
        >
        <?php if (isset($errors['email_or_phone'])): ?>
          <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email_or_phone']); ?></div>
        <?php endif; ?>
      </div>
      
      <!-- Password -->
      <div class="form-group">
        <label for="password">Mật khẩu <span class="required">*</span></label>
        <div class="password-input-wrapper">
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
            placeholder="Nhập mật khẩu"
            required
          >
          <button type="button" class="toggle-password" data-target="password">
            <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
        <?php if (isset($errors['password'])): ?>
          <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
        <?php endif; ?>
      </div>
      
      <!-- Remember & Forgot -->
      <div class="form-group remember-forgot">
        <label class="checkbox-label">
          <input type="checkbox" name="remember" value="1">
          <span>Ghi nhớ đăng nhập</span>
        </label>
        <a href="./forgot-password.php" class="forgot-password-link">Quên mật khẩu?</a>
      </div>
      
      <!-- Submit Button -->
      <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
    </form>
    
    <div class="auth-footer">
      <p>Chưa có tài khoản? <a href="./register.php">Đăng ký ngay</a></p>
    </div>
  </div>
</div>

<script defer src="../../public/js/login-validation.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
