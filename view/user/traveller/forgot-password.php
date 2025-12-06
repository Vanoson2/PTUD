<?php
include_once __DIR__ . '/../../../controller/cUser.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$message = '';
$messageType = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  
  // Call controller to send reset email
  $cUser = new cUser();
  $result = $cUser->cSendPasswordResetEmail($email);
  
  $message = $result['message'];
  $messageType = $result['success'] ? 'success' : 'danger';
}

include __DIR__ . '/../../partials/header.php';
?>

<link rel="stylesheet" href="../../css/traveller-auth.css">

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Quên mật khẩu</h1>
      <p>Nhập email của bạn để nhận link đặt lại mật khẩu</p>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="auth-form">
      <div class="form-group">
        <label for="email">Email</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control" 
          placeholder="Nhập email của bạn"
          required
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
        >
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Gửi link đặt lại mật khẩu
      </button>
    </form>

    <div class="auth-footer">
      <p>Đã nhớ mật khẩu? <a href="./login.php">Đăng nhập</a></p>
      <p>Chưa có tài khoản? <a href="./register.php">Đăng ký ngay</a></p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
