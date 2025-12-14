<?php
include_once __DIR__ . '/../../../controller/cAdmin.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
  header('Location: ./login.php');
  exit;
}

// Get admin role and set permissions
$adminRole = $_SESSION['admin_role'] ?? 'support';
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);

// Get filter status
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : null;
$validStatuses = ['pending', 'approved', 'rejected'];
if ($filterStatus && !in_array($filterStatus, $validStatuses)) {
  $filterStatus = null;
}

$cAdmin = new cAdmin();
$applications = $cAdmin->cGetAllHostApplications($filterStatus);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý đơn đăng ký - WeGo Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-applications.css?v=<?php echo time(); ?>">
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
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="applications.php" class="active">
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
        <i class="fas fa-file-alt"></i>
        Quản lý đơn đăng ký Host
      </h1>
    </div>
    
    <div class="container">
    <div class="filter-tabs">
      <a href="./applications.php" class="filter-btn <?php echo $filterStatus === null ? 'active' : ''; ?>">
        <i class="fa-solid fa-clipboard-list"></i> Tất cả
      </a>
      <a href="./applications.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt
      </a>
      <a href="./applications.php?status=approved" class="filter-btn <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">
        <i class="fa-solid fa-check-circle"></i> Đã duyệt
      </a>
      <a href="./applications.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        <i class="fa-solid fa-times-circle"></i> Đã từ chối
      </a>
    </div>
    
    <div class="applications-table">
      <?php if (empty($applications)): ?>
        <div class="empty-state">
          <div class="empty-state-icon"><i class="fa-solid fa-inbox"></i></div>
          <h3>Không có đơn đăng ký nào</h3>
          <p class="text-muted">
            <?php if ($filterStatus): ?>
              Không có đơn đăng ký với trạng thái "<?php echo htmlspecialchars($filterStatus); ?>"
            <?php else: ?>
              Chưa có đơn đăng ký nào trong hệ thống
            <?php endif; ?>
          </p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Người đăng ký</th>
              <th>Email</th>
              <th>Số điện thoại</th>
              <th>Ngày đăng ký</th>
              <th>Trạng thái</th>
              <th>Người duyệt</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applications as $app): ?>
              <tr>
                <td><strong>#<?php echo $app['host_application_id']; ?></strong></td>
                <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                <td><?php echo htmlspecialchars($app['email']); ?></td>
                <td><?php echo htmlspecialchars($app['phone']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?></td>
                <td>
                  <?php
                  $statusClass = '';
                  $statusText = '';
                  switch ($app['status']) {
                    case 'pending':
                      $statusClass = 'badge-pending';
                      $statusText = 'Chờ duyệt';
                      break;
                    case 'approved':
                      $statusClass = 'badge-approved';
                      $statusText = 'Đã duyệt';
                      break;
                    case 'rejected':
                      $statusClass = 'badge-rejected';
                      $statusText = 'Đã từ chối';
                      break;
                  }
                  ?>
                  <span class="badge-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                </td>
                <td>
                  <?php if (!empty($app['reviewed_by_name'])): ?>
                    <?php echo htmlspecialchars($app['reviewed_by_name']); ?>
                    <br>
                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($app['reviewed_at'])); ?></small>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="application-detail.php?id=<?php echo $app['host_application_id']; ?>" class="btn-view">
                    Xem chi tiết
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
  
  </main>
</div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
