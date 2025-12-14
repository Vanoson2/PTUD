<?php
include_once __DIR__ . '/../../../controller/cUser.php';
include_once __DIR__ . '/../../../helper/ReturnUrlHelper.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Store returnUrl if provided
if (isset($_GET['returnUrl'])) {
  ReturnUrlHelper::storeReturnUrl($_GET['returnUrl']);
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  // Check for return URL
  $returnUrl = ReturnUrlHelper::getAndClearReturnUrl();
  if ($returnUrl) {
    header('Location: ' . $returnUrl);
  } else {
    header('Location: ../../index.php');
  }
  exit;
}

$errors = [];
$successMessage = '';
$formData = [
  'email' => '',
  'phone' => '',
  'full_name' => ''
];

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  $fullName = trim($_POST['full_name'] ?? '');
  
  // Lưu lại dữ liệu form để hiển thị khi có lỗi
  $formData = [
    'email' => $email,
    'phone' => $phone,
    'full_name' => $fullName
  ];
  
  // Gọi controller để xử lý đăng ký
  $cUser = new cUser();
  $result = $cUser->cRegisterUser($email, $phone, $password, $confirmPassword, $fullName);
  
  if ($result['success']) {
    // Redirect đến trang nhập mã xác thực
    $_SESSION['pending_verify_user_id'] = $result['user_id'];
    $_SESSION['pending_verify_email'] = $email;
    header('Location: ./verify-code.php?user_id=' . $result['user_id'] . '&email=' . urlencode($email));
    exit;
  } else {
    $errors = $result['errors'];
  }
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../../css/traveller-auth.css?v=<?php echo time(); ?>">

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Đăng ký tài khoản</h1>
      <p>Tạo tài khoản mới để bắt đầu đặt phòng</p>
    </div>
    
    <?php if ($successMessage): ?>
      <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
      </div>
      <div class="auth-footer">
        <a href="./login.php" class="btn btn-primary">Đăng nhập ngay</a>
      </div>
    <?php else: ?>
      
      <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
          <i class="fa-solid fa-times-circle"></i>
          <span><?php echo htmlspecialchars($errors['general']); ?></span>
        </div>
      <?php endif; ?>
      
      <form action="" method="POST" class="auth-form" id="registerForm">
        
        <!-- Họ tên -->
        <div class="form-group">
          <label for="full_name">Họ và tên <span class="required">*</span></label>
          <input 
            type="text" 
            id="full_name" 
            name="full_name" 
            class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
            value="<?php echo htmlspecialchars($formData['full_name']); ?>"
            placeholder="Nguyễn Văn A"
            required
          >
          <?php if (isset($errors['full_name'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
          <?php endif; ?>
        </div>
        
        <!-- Email -->
        <div class="form-group">
          <label for="email">Email <span class="required">*</span></label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
            value="<?php echo htmlspecialchars($formData['email']); ?>"
            placeholder="example@email.com"
            required
          >
          <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
          <?php endif; ?>
        </div>
        
        <!-- Số điện thoại -->
        <div class="form-group">
          <label for="phone">Số điện thoại <span class="required">*</span></label>
          <input 
            type="tel" 
            id="phone" 
            name="phone" 
            class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
            value="<?php echo htmlspecialchars($formData['phone']); ?>"
            placeholder="0912345678"
            pattern="[0-9]{10,11}"
            required
          >
          <?php if (isset($errors['phone'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
          <?php endif; ?>
          <small class="form-text">Nhập 10-11 chữ số</small>
        </div>
        
        <!-- Mật khẩu -->
        <div class="form-group">
          <label for="password">Mật khẩu <span class="required">*</span></label>
          <div class="password-input-wrapper">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
              placeholder="Tối thiểu 6 ký tự"
              minlength="6"
              required
            >
            <button type="button" class="toggle-password" data-target="password">
              <i class="fa-solid fa-eye eye-icon"></i>
            </button>
          </div>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
          <?php endif; ?>
        </div>
        
        <!-- Xác nhận mật khẩu -->
        <div class="form-group">
          <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
          <div class="password-input-wrapper">
            <input 
              type="password" 
              id="confirm_password" 
              name="confirm_password" 
              class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
              placeholder="Nhập lại mật khẩu"
              required
            >
            <button type="button" class="toggle-password" data-target="confirm_password">
              <i class="fa-solid fa-eye eye-icon"></i>
            </button>
          </div>
          <?php if (isset($errors['confirm_password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
          <?php endif; ?>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
      </form>
      
      <div class="auth-footer">
        <p>Đã có tài khoản? <a href="./login.php" id="loginLink">Đăng nhập</a></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Handle return URL preservation
document.addEventListener('DOMContentLoaded', function() {
  // Check if coming from a page that needs redirect back
  const returnData = sessionStorage.getItem('returnUrl');
  
  if (returnData) {
    try {
      const data = JSON.parse(returnData);
      const now = Date.now();
      const maxAge = 30 * 60 * 1000; // 30 minutes
      
      // Validate timestamp
      if (now - data.timestamp < maxAge) {
        // Validate URL
        const returnUrl = new URL(data.url);
        const currentOrigin = window.location.origin;
        
        if (returnUrl.origin === currentOrigin) {
          sessionStorage.setItem('validatedReturnUrl', data.url);
        } else {
          sessionStorage.removeItem('returnUrl');
        }
      } else {
        sessionStorage.removeItem('returnUrl');
      }
    } catch (e) {
      sessionStorage.removeItem('returnUrl');
    }
  }
  
  // Handle "Đăng nhập" link - preserve returnUrl
  const loginLink = document.getElementById('loginLink');
  if (loginLink && returnData) {
    loginLink.addEventListener('click', function(e) {
      const validatedUrl = sessionStorage.getItem('validatedReturnUrl');
      if (validatedUrl) {
        e.preventDefault();
        const encodedUrl = encodeURIComponent(validatedUrl);
        window.location.href = `./login.php?returnUrl=${encodedUrl}`;
      }
    });
  }
});
</script>

<script defer src="../../../public/js/register-validation.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
