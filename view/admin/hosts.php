<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit();
}

include_once(__DIR__ . "/../../controller/cAdmin.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'toggle_status') {
    $hostId = intval($_POST['host_id'] ?? 0);
    $result = $cAdmin->cToggleHostStatus($hostId);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
  }
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin-hosts.css?v=<?php echo time(); ?>">
</head>
<body>
  
<!-- Header -->
<div class="admin-header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h1>🏡 Quản lý Chủ nhà</h1>
      </div>
      <div class="col-md-6">
        <div class="admin-info">
          <span>Xin chào, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</span>
          <a href="dashboard.php" class="btn-nav">📊 Dashboard</a>
          <a href="users.php" class="btn-nav">👥 Người dùng</a>
          <a href="applications.php" class="btn-nav">📋 Đơn đăng ký</a>
          <a href="listings.php" class="btn-nav">🏠 Phòng</a>
          <a href="amenities-services.php" class="btn-nav">🛠️ Tiện nghi & DV</a>
          <a href="logout.php" class="btn-nav">🚪 Đăng xuất</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Container -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
