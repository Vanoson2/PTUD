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

// Get admin info
$adminRole = $_SESSION['admin_role'] ?? 'support';
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Define role permissions
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);
$isSupport = ($adminRole === 'support');

$cAdmin = new cAdmin();
$stats = $cAdmin->cGetDashboardStats();

// Default values nếu không có data
if (!$stats || !is_array($stats)) {
  $stats = [
    'total_applications' => 0,
    'pending_applications' => 0,
    'approved_applications' => 0,
    'rejected_applications' => 0,
    'total_users' => 0,
    'total_hosts' => 0,
    'total_tickets' => 0,
    'unread_tickets' => 0
  ];
}

// Ensure all keys exist
$stats = array_merge([
  'total_applications' => 0,
  'pending_applications' => 0,
  'approved_applications' => 0,
  'rejected_applications' => 0,
  'total_users' => 0,
  'total_hosts' => 0,
  'total_tickets' => 0,
  'unread_tickets' => 0
], $stats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - WeGo</title>
  <link rel="stylesheet" href="../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-shield-alt"></i>
      <h2>Quản trị WeGo</h2>
      <small class="role-badge">
        <?php 
        if ($isSuperAdmin) echo '👑 Superadmin';
        elseif ($isManager) echo '🔧 Manager';
        else echo '💬 Support';
        ?>
      </small>
    </div>
    <nav class="sidebar-nav">
      <!-- Dashboard - Tất cả role -->
      <a href="dashboard.php" class="active">
        <i class="fas fa-home"></i>
        <span>Tổng quan</span>
      </a>
      
      <!-- Users - Manager & Superadmin -->
      <?php if ($isManager): ?>
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <?php endif; ?>
      
      <!-- Hosts - Manager & Superadmin -->
      <?php if ($isManager): ?>
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <?php endif; ?>
      
      <!-- Host Applications - Manager & Superadmin -->
      <?php if ($isManager): ?>
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <?php endif; ?>
      
      <!-- Listings - Tất cả xem, Manager & Superadmin duyệt -->
      <a href="listings.php">
        <i class="fas fa-building"></i>
        <span>Quản lý Phòng</span>
      </a>
      
      <!-- Support Tickets - Tất cả role -->
      <a href="support.php">
        <i class="fas fa-headset"></i>
        <span>Hỗ trợ khách hàng</span>
      </a>
      
      <!-- Settings - Manager & Superadmin -->
      <?php if ($isManager): ?>
      <a href="amenities-services.php">
        <i class="fas fa-cog"></i>
        <span>Tiện nghi & Dịch vụ</span>
      </a>
      <?php endif; ?>
      
      <!-- Logout -->
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
        <i class="fas fa-tachometer-alt"></i>
        Tổng quan Dashboard
      </h1>
      <div class="admin-header-flex">
        <span class="greeting">Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
        <span class="badge bg-primary"><?php echo strtoupper($adminRole); ?></span>
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
      
      <!-- Support Tickets -->
      <div class="col-md-4">
        <div class="stats-card gradient-purple">
          <div class="stats-icon">🎫</div>
          <div class="stats-number"><?php echo $stats['total_tickets'] ?? 0; ?></div>
          <div class="stats-label">Tổng yêu cầu hỗ trợ</div>
        </div>
      </div>
      
      <!-- Open Tickets -->
      <div class="col-md-4">
        <div class="stats-card gradient-pink">
          <div class="stats-icon">📬</div>
          <div class="stats-number"><?php echo $stats['unread_tickets'] ?? 0; ?></div>
          <div class="stats-label">Yêu cầu chưa trả lời</div>
        </div>
      </div>
    </div>
    
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="quick-links">
          <h3>Quản lý nhanh</h3>
          <div class="row">
            <div class="col-md-3 mb-3">
              <a href="./users.php" class="quick-link-btn">
                👥 Quản lý người dùng
              </a>
            </div>
            <div class="col-md-3 mb-3">
              <a href="./hosts.php" class="quick-link-btn">
                🏠 Quản lý chủ nhà
              </a>
            </div>
            <div class="col-md-3 mb-3">
              <a href="./applications.php?status=pending" class="quick-link-btn">
                📋 Đơn đăng ký Host (<?php echo $stats['pending_applications']; ?>)
              </a>
            </div>
            <div class="col-md-3 mb-3">
              <a href="./listings.php?status=pending" class="quick-link-btn">
                🏠 Quản lý phòng
              </a>
            </div>
            <div class="col-md-3 mb-3">
              <a href="./amenities-services.php" class="quick-link-btn">
                🛠️ Tiện nghi & Dịch vụ
              </a>
            </div>
            <div class="col-md-3 mb-3">
              <a href="./support.php" class="quick-link-btn">
                🎫 Yêu cầu hỗ trợ
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  </main>
</div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
