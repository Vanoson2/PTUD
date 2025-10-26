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

$cAdmin = new cAdmin();
$stats = $cAdmin->cGetDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - WeGo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="admin-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1>🏠 WeGo Admin Dashboard</h1>
        </div>
        <div class="col-md-6">
          <div class="admin-info">
            <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <span class="badge bg-light text-dark"><?php echo strtoupper($_SESSION['admin_role']); ?></span>
            <a href="./logout.php" class="btn-logout">Đăng xuất</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <h2 class="page-title">Tổng quan hệ thống</h2>
    
    <div class="row">
      <!-- Total Applications -->
      <div class="col-md-4">
        <div class="stats-card total">
          <div class="stats-icon total">📋</div>
          <div class="stats-number"><?php echo $stats['total_applications']; ?></div>
          <div class="stats-label">Tổng đơn đăng ký</div>
        </div>
      </div>
      
      <!-- Pending Applications -->
      <div class="col-md-4">
        <div class="stats-card pending">
          <div class="stats-icon pending">⏳</div>
          <div class="stats-number"><?php echo $stats['pending_applications']; ?></div>
          <div class="stats-label">Đơn chờ duyệt</div>
        </div>
      </div>
      
      <!-- Approved Applications -->
      <div class="col-md-4">
        <div class="stats-card approved">
          <div class="stats-icon approved">✅</div>
          <div class="stats-number"><?php echo $stats['approved_applications']; ?></div>
          <div class="stats-label">Đơn đã duyệt</div>
        </div>
      </div>
      
      <!-- Rejected Applications -->
      <div class="col-md-4">
        <div class="stats-card rejected">
          <div class="stats-icon rejected">❌</div>
          <div class="stats-number"><?php echo $stats['rejected_applications']; ?></div>
          <div class="stats-label">Đơn bị từ chối</div>
        </div>
      </div>
      
      <!-- Total Users -->
      <div class="col-md-4">
        <div class="stats-card users">
          <div class="stats-icon users">👥</div>
          <div class="stats-number"><?php echo $stats['total_users']; ?></div>
          <div class="stats-label">Tổng người dùng</div>
        </div>
      </div>
      
      <!-- Total Hosts -->
      <div class="col-md-4">
        <div class="stats-card hosts">
          <div class="stats-icon hosts">🏡</div>
          <div class="stats-number"><?php echo $stats['total_hosts']; ?></div>
          <div class="stats-label">Tổng hosts hoạt động</div>
        </div>
      </div>
    </div>
    
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="quick-links">
          <h3>Quản lý nhanh</h3>
          <div class="row">
            <div class="col-md-3">
              <a href="./applications.php?status=pending" class="quick-link-btn">
                ⏳ Xem đơn chờ duyệt (<?php echo $stats['pending_applications']; ?>)
              </a>
            </div>
            <div class="col-md-3">
              <a href="./applications.php" class="quick-link-btn">
                📋 Xem tất cả đơn đăng ký
              </a>
            </div>
            <div class="col-md-3">
              <a href="./listings.php?status=pending" class="quick-link-btn">
                🏠 Phòng chờ duyệt
              </a>
            </div>
            <div class="col-md-3">
              <a href="./listings.php" class="quick-link-btn">
                📋 Quản lý phòng
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
