<?php
include_once __DIR__ . '/../../controller/cAdmin.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
  header('Location: ./login.php');
  exit;
}

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/applications.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="admin-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1>📋 Quản lý đơn đăng ký Host</h1>
        </div>
        <div class="col-md-6">
          <div class="admin-info">
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</span>
            <a href="./dashboard.php" class="btn-dashboard">📊 Dashboard</a>
            <a href="./users.php" class="btn-dashboard">👥 Người dùng</a>
            <a href="./hosts.php" class="btn-dashboard">🏡 Chủ nhà</a>
            <a href="./listings.php" class="btn-dashboard">🏠 Phòng</a>
            <a href="./amenities-services.php" class="btn-dashboard">🛠️ Tiện nghi & DV</a>
            <a href="./logout.php" class="btn-dashboard">🚪 Đăng xuất</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <div class="filter-tabs">
      <a href="./applications.php" class="filter-btn <?php echo $filterStatus === null ? 'active' : ''; ?>">
        📋 Tất cả
      </a>
      <a href="./applications.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        ⏳ Chờ duyệt
      </a>
      <a href="./applications.php?status=approved" class="filter-btn <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">
        ✅ Đã duyệt
      </a>
      <a href="./applications.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        ❌ Đã từ chối
      </a>
    </div>
    
    <div class="applications-table">
      <?php if (empty($applications)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📭</div>
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
                  <a href="./application-detail.php?id=<?php echo $app['host_application_id']; ?>" class="btn-view">
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
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
