<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit();
}

include_once(__DIR__ . "/../../../controller/cAdmin.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];

// Get admin role and set permissions
$adminRole = $_SESSION['admin_role'] ?? 'support';
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'create') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    
    $result = $cAdmin->cCreateUser($email, $password, $phone, $fullName);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
  } elseif ($action === 'update') {
    $userId = intval($_POST['user_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    
    $result = $cAdmin->cUpdateUser($userId, $email, $phone, $fullName, $password);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
  } elseif ($action === 'toggle_status') {
    $userId = intval($_POST['user_id'] ?? 0);
    
    $result = $cAdmin->cToggleUserStatus($userId);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
  }
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;

// Get users
$userData = $cAdmin->cGetAllUsers($page, $limit, $search);
$users = $userData['users'];
$totalPages = $userData['pages'];
$totalUsers = $userData['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Người dùng - Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-users.css?v=<?php echo time(); ?>">
</head>
<body>
  
<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-shield-alt"></i>
      <h2>Quản trị</h2>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Tổng quan</span>
      </a>
      
      <?php if ($isManager): ?>
      <a href="users.php" class="active">
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
      <a href="admin-management.php">
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
        <i class="fas fa-users"></i>
        Quản lý Người dùng
      </h1>
    </div>
    
    <!-- Content -->
  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
      <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>
  
  <div class="header-actions">
    <div class="search-bar">
      <form method="GET">
        <input type="text" name="search" placeholder="Tìm kiếm theo tên, email, số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
      </form>
    </div>
    <button class="add-user-btn" onclick="openAddModal()">
      <i class="fas fa-plus"></i>
      Thêm người dùng mới
    </button>
  </div>
  
  <div class="users-table">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>EMAIL</th>
          <th>TÊN ĐẦY ĐỦ</th>
          <th>SỐ ĐIỆN THOẠI</th>
          <th>VAI TRÒ</th>
          <th>ĐIỂM TN</th>
          <th>TRẠNG THÁI</th>
          <th>THAO TÁC</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Không tìm thấy người dùng</h3>
                <p>Không có người dùng nào phù hợp với tìm kiếm của bạn</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo htmlspecialchars($user['user_id']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['full_name'] ?: '-'); ?></td>
              <td><?php echo htmlspecialchars($user['phone']); ?></td>
              <td><?php echo htmlspecialchars($user['role']); ?></td>
              <td>
                <?php 
                $score = $user['trust_score'] ?? 100;
                $colorClass = 'score-excellent';
                if ($score < 40) $colorClass = 'score-danger';
                elseif ($score < 60) $colorClass = 'score-low';
                elseif ($score < 80) $colorClass = 'score-average';
                elseif ($score < 90) $colorClass = 'score-good';
                ?>
                <span class="trust-score-badge <?php echo $colorClass; ?>" 
                      onclick="openScoreModal(<?php echo $user['user_id']; ?>)" 
                      style="cursor: pointer;"
                      title="Click để xem chi tiết">
                  <?php echo $score; ?>
                </span>
              </td>
              <td>
                <span class="status-badge status-<?php echo $user['status'] === 'active' ? 'active' : 'locked'; ?>">
                  <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Bị khóa'; ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($user); ?>)'>
                    Sửa
                  </button>
                  <button class="btn-lock" onclick="openLockModal(<?php echo $user['user_id']; ?>, '<?php echo $user['status']; ?>')">
                    <?php echo $user['status'] === 'active' ? 'Khóa' : 'Mở khóa'; ?>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
      <?php endif; ?>
      
      <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <?php if ($i === $page): ?>
          <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>
      
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
      
      <span style="margin-left: 20px;">Trang <?php echo $page; ?> của <?php echo $totalPages; ?></span>
    </div>
  <?php endif; ?>
</div>
<!-- End Container -->

<!-- Add User Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Thêm Người Dùng</h2>
      <button class="close-btn" onclick="closeAddModal()">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
          <label>Mật khẩu *</label>
          <input type="password" name="password" required>
          <p class="help-text">Mật khẩu mặc định. Người dùng có thể đổi sau.</p>
        </div>
        
        <div class="form-group">
          <label>Tên đầy đủ</label>
          <input type="text" name="full_name">
        </div>
        
        <div class="form-group">
          <label>Số điện thoại *</label>
          <input type="tel" name="phone" required pattern="[0-9]{10,11}">
          <p class="help-text">10-11 chữ số</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeAddModal()">Hủy</button>
        <button type="submit" class="btn-submit">Tạo người dùng</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Sửa Người Dùng</h2>
      <button class="close-btn" onclick="closeEditModal()">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="modal-body">
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" id="edit_email" required>
        </div>
        
        <div class="form-group">
          <label>Mật khẩu</label>
          <input type="password" name="password" id="edit_password">
          <p class="help-text">Để trống nếu không muốn đổi mật khẩu</p>
        </div>
        
        <div class="form-group">
          <label>Tên đầy đủ</label>
          <input type="text" name="full_name" id="edit_full_name">
        </div>
        
        <div class="form-group">
          <label>Số điện thoại *</label>
          <input type="tel" name="phone" id="edit_phone" required pattern="[0-9]{10,11}">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
        <button type="submit" class="btn-submit">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<!-- Lock/Unlock Confirmation Modal -->
<div id="lockModal" class="modal confirm-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Xác nhận hành động</h2>
      <button class="close-btn" onclick="closeLockModal()">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="toggle_status">
      <input type="hidden" name="user_id" id="lock_user_id">
      <div class="modal-body">
        <p id="lock_message">Bạn có chắc muốn khóa người dùng này?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeLockModal()">Hủy</button>
        <button type="submit" class="btn-submit" style="background: #DC2626;">Xác nhận</button>
      </div>
    </form>
  </div>
</div>

<!-- Trust Score Modal -->
<div id="scoreModal" class="modal">
  <div class="modal-content" style="max-width: 800px;">
    <div class="modal-header">
      <h2>Chi tiết Điểm Tín Nhiệm</h2>
      <button class="close-btn" onclick="closeScoreModal()">&times;</button>
    </div>
    <div class="modal-body" id="scoreModalContent">
      <div style="text-align: center; padding: 40px;">
        <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #3B82F6;"></i>
        <p>Đang tải dữ liệu...</p>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-cancel" onclick="closeScoreModal()">Đóng</button>
    </div>
  </div>
</div>

<style>
.trust-score-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
}

.trust-score-badge:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.score-excellent {
  background: #10B981;
  color: white;
}

.score-good {
  background: #3B82F6;
  color: white;
}

.score-average {
  background: #F59E0B;
  color: white;
}

.score-low {
  background: #F97316;
  color: white;
}

.score-danger {
  background: #EF4444;
  color: white;
}

.score-history-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.score-history-table th,
.score-history-table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.score-history-table th {
  background: #F3F4F6;
  font-weight: 600;
}

.score-change-positive {
  color: #10B981;
  font-weight: 600;
}

.score-change-negative {
  color: #EF4444;
  font-weight: 600;
}
</style>

<script>
  function openAddModal() {
    document.getElementById('addModal').classList.add('show');
  }
  
  function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
  }
  
  function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_full_name').value = user.full_name || '';
    document.getElementById('edit_phone').value = user.phone;
    document.getElementById('edit_password').value = '';
    document.getElementById('editModal').classList.add('show');
  }
  
  function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
  }
  
  function openLockModal(userId, status) {
    document.getElementById('lock_user_id').value = userId;
    const message = status === 'active' 
      ? 'Bạn có chắc muốn khóa người dùng này?' 
      : 'Bạn có chắc muốn mở khóa người dùng này?';
    document.getElementById('lock_message').textContent = message;
    document.getElementById('lockModal').classList.add('show');
  }
  
  function closeLockModal() {
    document.getElementById('lockModal').classList.remove('show');
  }

  function openScoreModal(userId) {
    document.getElementById('scoreModal').classList.add('show');
    
    // Fetch score data via AJAX
    fetch(`get-user-score.php?user_id=${userId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayScoreData(data.data);
        } else {
          document.getElementById('scoreModalContent').innerHTML = `
            <div style="text-align: center; padding: 40px; color: #EF4444;">
              <i class="fas fa-exclamation-circle" style="font-size: 32px;"></i>
              <p>${data.message || 'Không thể tải dữ liệu'}</p>
            </div>
          `;
        }
      })
      .catch(error => {
        document.getElementById('scoreModalContent').innerHTML = `
          <div style="text-align: center; padding: 40px; color: #EF4444;">
            <i class="fas fa-times-circle" style="font-size: 32px;"></i>
            <p>Lỗi kết nối: ${error.message}</p>
          </div>
        `;
      });
  }

  function closeScoreModal() {
    document.getElementById('scoreModal').classList.remove('show');
  }

  function displayScoreData(data) {
    const { score, level, history } = data;
    
    let historyHTML = '';
    if (history && history.length > 0) {
      historyHTML = `
        <table class="score-history-table">
          <thead>
            <tr>
              <th>Thời gian</th>
              <th>Thay đổi</th>
              <th>Điểm cũ</th>
              <th>Điểm mới</th>
              <th>Lý do</th>
            </tr>
          </thead>
          <tbody>
            ${history.map(item => `
              <tr>
                <td>${new Date(item.created_at).toLocaleString('vi-VN')}</td>
                <td class="${item.score_change > 0 ? 'score-change-positive' : 'score-change-negative'}">
                  ${item.score_change > 0 ? '+' : ''}${item.score_change}
                </td>
                <td>${item.old_score}</td>
                <td>${item.new_score}</td>
                <td>${item.reason}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;
    } else {
      historyHTML = '<p style="text-align: center; color: #9CA3AF;">Chưa có lịch sử thay đổi điểm</p>';
    }
    
    document.getElementById('scoreModalContent').innerHTML = `
      <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 48px; font-weight: bold; color: ${level.color};">
          ${score.trust_score}
        </div>
        <div style="font-size: 18px; color: #6B7280; margin-top: 8px;">/ 100 điểm</div>
        <div style="display: inline-block; margin-top: 15px; padding: 8px 16px; border-radius: 20px; background: ${level.color}20; color: ${level.color};">
          ${level.badge} ${level.name}
        </div>
        <p style="margin-top: 10px; color: #6B7280;">${level.description}</p>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;">
        <div style="text-align: center; padding: 15px; background: #F3F4F6; border-radius: 8px;">
          <div style="font-size: 24px;">${score.verified_phone ? '✓' : '○'}</div>
          <div style="font-size: 14px; color: #6B7280; margin-top: 5px;">Số điện thoại</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #F3F4F6; border-radius: 8px;">
          <div style="font-size: 24px;">${score.verified_id ? '✓' : '○'}</div>
          <div style="font-size: 14px; color: #6B7280; margin-top: 5px;">CCCD/CMND</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #F3F4F6; border-radius: 8px;">
          <div style="font-size: 24px;">${score.is_verified ? '✓' : '○'}</div>
          <div style="font-size: 14px; color: #6B7280; margin-top: 5px;">Tài khoản đầy đủ</div>
        </div>
      </div>
      
      <h3 style="margin-bottom: 15px;">Lịch sử thay đổi điểm</h3>
      ${historyHTML}
    `;
  }
  
  // Close modals when clicking outside
  window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
      event.target.classList.remove('show');
    }
  }
  
  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(() => alert.remove(), 500);
    });
  }, 5000);
</script>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
