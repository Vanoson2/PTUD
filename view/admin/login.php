<?php
include_once __DIR__ . '/../../controller/cAdmin.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
  header('Location: ./dashboard.php');
  exit;
}

$error = '';

// Xử lý login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  
  $cAdmin = new cAdmin();
  $result = $cAdmin->cAdminLogin($username, $password);
  
  // Check if result is valid and has success key
  if (isset($result['success']) && $result['success'] && isset($result['admin']) && $result['admin'] !== null) {
    $admin = $result['admin'];
    // Admin is guaranteed to be an array here
    if (is_array($admin)) {
      $_SESSION['admin_id'] = $admin['admin_id'];
      $_SESSION['admin_username'] = $admin['username'];
      $_SESSION['admin_name'] = $admin['full_name'];
      $_SESSION['admin_role'] = $admin['role'];
      
      header('Location: ./dashboard.php');
      exit;
    }
  }
  
  $error = isset($result['message']) ? $result['message'] : 'Đăng nhập thất bại';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - WeGo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin-login.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="admin-login-card">
    <div class="admin-login-header">
      <div class="admin-icon">
        <svg width="50" height="50" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
        </svg>
      </div>
      <h1>🏠 WeGo Admin</h1>
      <p>Đăng nhập vào hệ thống quản trị</p>
    </div>
    
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <strong>Lỗi!</strong> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <form method="POST" action="">
      <div class="form-group">
        <label for="username" class="form-label">Tên đăng nhập</label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          class="form-control" 
          placeholder="Nhập tên đăng nhập"
          required
          autofocus
        >
      </div>
      
      <div class="form-group">
        <label for="password" class="form-label">Mật khẩu</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="form-control" 
          placeholder="Nhập mật khẩu"
          required
        >
      </div>
      
      <button type="submit" class="btn-admin-login">
        Đăng Nhập
      </button>
    </form>
    
    <div class="back-link">
      <a href="../../index.php">
        ← Quay về trang chủ
      </a>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
