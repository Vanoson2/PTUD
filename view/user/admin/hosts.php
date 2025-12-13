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
  
  if ($action === 'toggle_status') {
    $hostId = intval($_POST['host_id'] ?? 0);
    $result = $cAdmin->cToggleHostStatus($hostId);
    
    // Store message in session to display after redirect
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'error';
    
    // Redirect to avoid form resubmission (PRG pattern)
    $redirectUrl = 'hosts.php';
    if (isset($_GET['page'])) {
      $redirectUrl .= '?page=' . intval($_GET['page']);
    }
    if (isset($_GET['search'])) {
      $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . 'search=' . urlencode($_GET['search']);
    }
    
    header('Location: ' . $redirectUrl);
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

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;

// Get hosts
$hostData = $cAdmin->cGetAllHosts($page, $limit, $search);
$hosts = $hostData['hosts'];
$totalPages = $hostData['pages'];
$totalHosts = $hostData['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Chủ nhà - Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-hosts.css?v=<?php echo time(); ?>">
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
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="hosts.php" class="active">
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
        <i class="fas fa-hotel"></i>
        Quản lý Chủ nhà
      </h1>
    </div>
    
    <div class="container">
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
  </div>
  
  <div class="hosts-table">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>TÊN CHỦ NHÀ</th>
          <th>EMAIL</th>
          <th>SỐ ĐIỆN THOẠI</th>
          <th>MÃ SỐ THUẾ</th>
          <th>SỐ PHÒNG</th>
          <th>TRẠNG THÁI</th>
          <th>THAO TÁC</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($hosts)): ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <i class="fas fa-user-tie"></i>
                <h3>Không tìm thấy chủ nhà</h3>
                <p>Không có chủ nhà nào phù hợp với tìm kiếm của bạn</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($hosts as $host): ?>
            <tr>
              <td><?php echo htmlspecialchars($host['host_id']); ?></td>
              <td><?php echo htmlspecialchars($host['full_name'] ?: '-'); ?></td>
              <td><?php echo htmlspecialchars($host['email']); ?></td>
              <td><?php echo htmlspecialchars($host['phone']); ?></td>
              <td><?php echo htmlspecialchars($host['tax_code'] ?: '-'); ?></td>
              <td><?php echo htmlspecialchars($host['total_listings'] ?? 0); ?></td>
              <td>
                <span class="status-badge status-<?php echo $host['status'] === 'approved' ? 'active' : 'inactive'; ?>">
                  <?php echo $host['status'] === 'approved' ? 'Hoạt động' : ($host['status'] === 'pending' ? 'Chờ duyệt' : 'Đã từ chối'); ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="listings.php?host_id=<?php echo $host['host_id']; ?>" class="btn-view">
                    Xem phòng
                  </a>
                  <button class="btn-suspend" onclick="openSuspendModal(<?php echo $host['host_id']; ?>, '<?php echo $host['status']; ?>', '<?php echo htmlspecialchars($host['full_name'], ENT_QUOTES); ?>')">
                    <?php echo $host['status'] === 'approved' ? 'Đình chỉ' : 'Kích hoạt'; ?>
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

<!-- Suspend/Activate Confirmation Modal -->
<div id="suspendModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Xác nhận hành động</h2>
      <button class="close-btn" onclick="closeSuspendModal()">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="toggle_status">
      <input type="hidden" name="host_id" id="suspend_host_id">
      <div class="modal-body">
        <p id="suspend_message">Bạn có chắc muốn đình chỉ chủ nhà này?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeSuspendModal()">Hủy</button>
        <button type="submit" class="btn-submit">Xác nhận</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openSuspendModal(hostId, status, hostName) {
    document.getElementById('suspend_host_id').value = hostId;
    const message = status === 'approved' 
      ? `Bạn có chắc muốn đình chỉ chủ nhà "${hostName}"?` 
      : `Bạn có chắc muốn kích hoạt lại chủ nhà "${hostName}"?`;
    document.getElementById('suspend_message').textContent = message;
    document.getElementById('suspendModal').classList.add('show');
  }
  
  function closeSuspendModal() {
    document.getElementById('suspendModal').classList.remove('show');
  }
  
  // Close modal when clicking outside
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
