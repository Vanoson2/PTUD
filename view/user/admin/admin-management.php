<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit();
}

// Check if admin is superadmin
$adminRole = $_SESSION['admin_role'] ?? 'support';
if ($adminRole !== 'superadmin') {
  header('Location: dashboard.php');
  exit();
}

// Set permissions
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);

include_once(__DIR__ . "/../../../controller/cAdmin.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'create') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'support';
    
    $result = $cAdmin->cCreateAdmin($username, $password, $fullName, $role);
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'error';
    
    header('Location: admin-management.php');
    exit();
    
  } elseif ($action === 'update_role') {
    $targetAdminId = intval($_POST['admin_id'] ?? 0);
    $newRole = $_POST['new_role'] ?? '';
    
    $result = $cAdmin->cUpdateAdminRole($targetAdminId, $newRole);
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'error';
    
    header('Location: admin-management.php');
    exit();
    
  } elseif ($action === 'delete') {
    $targetAdminId = intval($_POST['admin_id'] ?? 0);
    
    // Prevent self-deletion
    if ($targetAdminId == $adminId) {
      $_SESSION['message'] = 'Không thể xóa chính tài khoản của bạn';
      $_SESSION['messageType'] = 'error';
    } else {
      $result = $cAdmin->cDeleteAdmin($targetAdminId);
      $_SESSION['message'] = $result['message'];
      $_SESSION['messageType'] = $result['success'] ? 'success' : 'error';
    }
    
    header('Location: admin-management.php');
    exit();
    
  } elseif ($action === 'reset_password') {
    $targetAdminId = intval($_POST['admin_id'] ?? 0);
    $newPassword = trim($_POST['new_password'] ?? '');
    
    $result = $cAdmin->cResetAdminPassword($targetAdminId, $newPassword);
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'error';
    
    header('Location: admin-management.php');
    exit();
  }
}

// Get message from session (after redirect)
if (isset($_SESSION['message'])) {
  $message = $_SESSION['message'];
  $messageType = $_SESSION['messageType'] ?? 'info';
  unset($_SESSION['message']);
  unset($_SESSION['messageType']);
}

// Get all admins
$admins = $cAdmin->cGetAllAdmins();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Admin - WeGo Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-dashboard.css">
  <link rel="stylesheet" href="../../css/admin-management.css">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-shield-alt"></i>
      <div style="flex: 1;">
        <h2>Quản trị WeGo</h2>
        <small class="role-badge">
          <?php 
          if ($isSuperAdmin) echo '👑 Superadmin';
          elseif ($isManager) echo '🔧 Manager';
          else echo '<i class="fa-solid fa-headset"></i> Support';
          ?>
        </small>
      </div>
    </div>
    
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Tổng quan</span>
      </a>
      
      <?php if ($isManager): ?>
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <?php endif; ?>
      
      <a href="listings.php">
        <i class="fas fa-building"></i>
        <span>Quản lý Phòng</span>
      </a>
      <a href="support.php">
        <i class="fas fa-headset"></i>
        <span>Hỗ trợ khách hàng</span>
      </a>
      
      <?php if ($isManager): ?>
      <a href="amenities-services.php">
        <i class="fas fa-cog"></i>
        <span>Tiện nghi & Dịch vụ</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isSuperAdmin): ?>
      <a href="admin-management.php" class="active">
        <i class="fas fa-user-shield"></i>
        <span>Quản lý Admin</span>
      </a>
      <?php endif; ?>
      
      <hr class="sidebar-divider">
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="page-title">
      <h1>
        <i class="fas fa-user-shield"></i>
        Quản lý Admin
      </h1>
    </div>
    
    <?php if ($message): ?>
      <div class="alert alert-<?php echo $messageType; ?>">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
        
        <!-- Create Admin Form -->
        <div class="create-admin-card">
          <h3><i class="fas fa-plus-circle"></i> Tạo tài khoản Admin mới</h3>
          <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tên đăng nhập *</label>
                <input type="text" name="username" class="form-control" placeholder="username" required>
                <small style="color: rgba(255,255,255,0.8);">3-50 ký tự, chỉ chữ, số, dấu gạch dưới</small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Mật khẩu *</label>
                <input type="password" name="password" class="form-control" placeholder="••••••" required>
                <small style="color: rgba(255,255,255,0.8);">Tối thiểu 6 ký tự</small>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Họ và tên *</label>
                <input type="text" name="full_name" class="form-control" placeholder="Nguyễn Văn A" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Vai trò *</label>
                <select name="role" class="form-select" required>
                  <option value="support">Support</option>
                  <option value="manager">Manager</option>
                  <option value="superadmin">Superadmin</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-light btn-lg">
              <i class="fas fa-user-plus"></i> Tạo tài khoản
            </button>
          </form>
        </div>
        
        <!-- Admins List -->
        <div class="admin-card">
          <h3><i class="fas fa-users-cog"></i> Danh sách Admin (<?php echo count($admins); ?>)</h3>
          <div class="table-responsive mt-3">
            <table class="table admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Họ và tên</th>
                  <th>Vai trò</th>
                  <th style="text-align: center;">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($admins)): ?>
                  <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d;">
                      Chưa có tài khoản admin nào
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($admins as $admin): ?>
                    <tr>
                      <td>#<?php echo $admin['admin_id']; ?></td>
                      <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                      <td><?php echo htmlspecialchars($admin['full_name'] ?? 'N/A'); ?></td>
                      <td>
                        <span class="role-badge role-<?php echo $admin['role']; ?>">
                          <?php echo $admin['role']; ?>
                        </span>
                      </td>
                      <td style="text-align: center;">
                        <?php if ($admin['role'] !== 'superadmin'): ?>
                          <!-- Change Role -->
                          <button class="btn btn-sm btn-primary btn-action" 
                                  onclick="changeRole(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                            <i class="fas fa-exchange-alt"></i> Đổi quyền
                          </button>
                          
                          <!-- Reset Password -->
                          <button class="btn btn-sm btn-warning btn-action" 
                                  onclick="resetPassword(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                            <i class="fas fa-key"></i> Đặt lại MK
                          </button>
                          
                          <!-- Delete -->
                          <?php if ($admin['admin_id'] != $adminId): ?>
                            <button class="btn btn-sm btn-danger btn-action" 
                                    onclick="deleteAdmin(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                              <i class="fas fa-trash"></i> Xóa
                            </button>
                          <?php endif; ?>
                        <?php else: ?>
                          <span style="color: #6c757d; font-style: italic;">Tài khoản hệ thống</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
  </main>
</div>
  
  <!-- Hidden Forms -->
  <form id="roleForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_role">
    <input type="hidden" name="admin_id" id="roleAdminId">
    <input type="hidden" name="new_role" id="newRole">
  </form>
  
  <form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="admin_id" id="deleteAdminId">
  </form>
  
  <form id="resetPasswordForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="admin_id" id="resetAdminId">
    <input type="hidden" name="new_password" id="resetNewPassword">
  </form>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function changeRole(adminId, username) {
      const newRole = prompt(`Đổi quyền cho ${username}\n\nNhập vai trò mới (superadmin, manager, support):`);
      if (newRole) {
        const validRoles = ['superadmin', 'manager', 'support'];
        if (!validRoles.includes(newRole.toLowerCase())) {
          alert('Vai trò không hợp lệ!');
          return;
        }
        
        if (confirm(`Xác nhận đổi quyền ${username} thành ${newRole}?`)) {
          document.getElementById('roleAdminId').value = adminId;
          document.getElementById('newRole').value = newRole.toLowerCase();
          document.getElementById('roleForm').submit();
        }
      }
    }
    
    function resetPassword(adminId, username) {
      const newPassword = prompt(`Đặt lại mật khẩu cho ${username}\n\nNhập mật khẩu mới (tối thiểu 6 ký tự):`);
      if (newPassword) {
        if (newPassword.length < 6) {
          alert('Mật khẩu phải có ít nhất 6 ký tự!');
          return;
        }
        
        if (confirm(`Xác nhận đặt lại mật khẩu cho ${username}?`)) {
          document.getElementById('resetAdminId').value = adminId;
          document.getElementById('resetNewPassword').value = newPassword;
          document.getElementById('resetPasswordForm').submit();
        }
      }
    }
    
    function deleteAdmin(adminId, username) {
      if (confirm(`⚠️ CẢNH BÁO!\n\nBạn có chắc chắn muốn XÓA tài khoản admin "${username}"?\n\nHành động này KHÔNG THỂ hoàn tác!`)) {
        document.getElementById('deleteAdminId').value = adminId;
        document.getElementById('deleteForm').submit();
      }
    }
  </script>
</body>
</html>
