<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check admin login
if (!isset($_SESSION['admin_id'])) {
  header("Location: ./login.php");
  exit();
}

include_once(__DIR__ . "/../../../controller/cAdmin.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'support';

// Define permissions
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);
$canApprove = $isManager; // Chỉ Manager và Superadmin mới duyệt được

// Get filter status
$filterStatus = $_GET['status'] ?? null;

// Get listings
$listings = $cAdmin->cGetAllListings($filterStatus);

// Count by status
$pendingCount = count($cAdmin->cGetAllListings('pending'));
$activeCount = count($cAdmin->cGetAllListings('active'));
$rejectedCount = count($cAdmin->cGetAllListings('rejected'));

// Tổng số listing (KHÔNG tính draft)
$totalCount = $pendingCount + $activeCount + $rejectedCount;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý phòng - WeGo Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-listings.css?v=<?php echo time(); ?>">
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
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <?php endif; ?>
      
      <a href="listings.php" class="active">
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
        <i class="fas fa-building"></i>
        Quản lý Phòng
      </h1>
    </div>
    
    <div class="container mt-5">

    <!-- Statistics -->
    <div class="stats-row">
      <div class="stat-card pending">
        <div class="stat-number"><?php echo $pendingCount; ?></div>
        <div class="stat-label">Chờ duyệt</div>
      </div>
      <div class="stat-card active">
        <div class="stat-number"><?php echo $activeCount; ?></div>
        <div class="stat-label">Đang hoạt động</div>
      </div>
      <div class="stat-card rejected">
        <div class="stat-number"><?php echo $rejectedCount; ?></div>
        <div class="stat-label">Đã từ chối</div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <a href="./listings.php" class="filter-btn <?php echo $filterStatus === null ? 'active' : ''; ?>">
        <i class="fa-solid fa-clipboard"></i> Tất cả (<?php echo $totalCount; ?>)
      </a>
      <a href="./listings.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt (<?php echo $pendingCount; ?>)
      </a>
      <a href="./listings.php?status=active" class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>">
        <i class="fa-solid fa-check-circle"></i> Hoạt động (<?php echo $activeCount; ?>)
      </a>
      <a href="./listings.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        <i class="fa-solid fa-times-circle"></i> Từ chối (<?php echo $rejectedCount; ?>)
      </a>
    </div>

    <!-- Listings Table -->
    <div class="table-container">
      <?php if (empty($listings)): ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-house"></i></div>
          <h3>Không có phòng nào</h3>
          <p>Chưa có phòng nào trong danh sách này.</p>
        </div>
      <?php else: ?>
        <table class="listings-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ảnh</th>
              <th>Tiêu đề</th>
              <th>Địa chỉ</th>
              <th>Host</th>
              <th>Giá/đêm</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($listings as $listing): ?>
              <tr>
                <td>#<?php echo $listing['listing_id']; ?></td>
                <td>
                  <?php if (!empty($listing['cover_image'])): ?>
                    <?php 
                    // Xử lý đường dẫn ảnh
                    $imagePath = $listing['cover_image'];
                    // Nếu là URL Pexels thì dùng trực tiếp
                    if (strpos($imagePath, 'http') === 0) {
                        $displayPath = $imagePath;
                    } 
                    // Nếu đã có public/ ở đầu thì bỏ đi và thêm / ở đầu
                    elseif (strpos($imagePath, 'public/') === 0) {
                        $displayPath = '/' . $imagePath;
                    }
                    // Nếu không có thì thêm /public/
                    else {
                        $displayPath = '/public/' . $imagePath;
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($displayPath); ?>" 
                         alt="Cover" class="listing-thumb"
                         onerror="this.src='/public/img/placeholder.jpg'; this.onerror=null;">
                  <?php else: ?>
                    <div class="no-image"><i class="fa-solid fa-camera"></i></div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></div>
                  <div class="listing-type"><?php echo htmlspecialchars($listing['place_type_name'] ?? 'N/A'); ?></div>
                </td>
                <td>
                  <div class="listing-address">
                    <?php 
                      $addressParts = [];
                      if (!empty($listing['ward_name'])) {
                        $addressParts[] = htmlspecialchars($listing['ward_name']);
                      }
                      if (!empty($listing['province_name'])) {
                        $addressParts[] = htmlspecialchars($listing['province_name']);
                      }
                      echo !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                    ?>
                  </div>
                </td>
                <td>
                  <div class="host-info">
                    <div class="host-name"><?php echo htmlspecialchars($listing['host_name'] ?? $listing['user_name'] ?? 'N/A'); ?></div>
                    <div class="host-id">Host ID: <?php echo $listing['host_id']; ?></div>
                  </div>
                </td>
                <td class="price"><?php echo number_format($listing['price'], 0, ',', '.'); ?> đ</td>
                <td>
                  <?php
                  $statusClass = $listing['status'];
                  $statusText = '';
                  $statusIcon = '';
                  switch ($listing['status']) {
                    case 'pending':
                      $statusText = 'Chờ duyệt';
                      $statusIcon = '<i class="fa-solid fa-hourglass-half"></i>';
                      break;
                    case 'active':
                      $statusText = 'Hoạt động';
                      $statusIcon = '<i class="fa-solid fa-check-circle"></i>';
                      break;
                    case 'rejected':
                      $statusText = 'Từ chối';
                      $statusIcon = '<i class="fa-solid fa-times-circle"></i>';
                      break;
                    case 'draft':
                      $statusText = 'Bản nháp';
                      $statusIcon = '<i class="fa-solid fa-file-alt"></i>';
                      break;
                  }
                  ?>
                  <span class="status-badge <?php echo $statusClass; ?>">
                    <?php echo $statusIcon . ' ' . $statusText; ?>
                  </span>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($listing['created_at'])); ?></td>
                <td>
                  <a href="./listing-detail.php?id=<?php echo $listing['listing_id']; ?>" class="btn-view">
                    👁️ Xem
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
