<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
  header("Location: ./login.php");
  exit();
}

include_once(__DIR__ . "/../../controller/cAdmin.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Get filter status
$filterStatus = $_GET['status'] ?? null;

// Get listings
$listings = $cAdmin->cGetAllListings($filterStatus);

// Count by status
$pendingCount = count($cAdmin->cGetAllListings('pending'));
$activeCount = count($cAdmin->cGetAllListings('active'));
$rejectedCount = count($cAdmin->cGetAllListings('rejected'));
$draftCount = count($cAdmin->cGetAllListings('draft'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý phòng - WeGo Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin-listings.css?v=<?php echo time(); ?>">
</head>
<body>
  <!-- Header -->
  <nav class="admin-navbar">
    <div class="container-fluid">
      <div class="navbar-brand">
        <h1>🏠 WeGo Admin</h1>
        <span class="admin-name">Xin chào, <?php echo htmlspecialchars($adminName); ?></span>
      </div>
      <div class="navbar-links">
        <a href="./dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="./applications.php" class="nav-link">📝 Đơn đăng ký Host</a>
        <a href="./listings.php" class="nav-link active">🏠 Quản lý phòng</a>
        <a href="./logout.php" class="nav-link logout">🚪 Đăng xuất</a>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="page-header">
      <h2>🏠 Quản lý phòng</h2>
      <p>Quản lý và duyệt các phòng đăng bởi host</p>
    </div>

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
      <div class="stat-card draft">
        <div class="stat-number"><?php echo $draftCount; ?></div>
        <div class="stat-label">Bản nháp</div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <a href="./listings.php" class="filter-btn <?php echo $filterStatus === null ? 'active' : ''; ?>">
        📋 Tất cả (<?php echo count($listings); ?>)
      </a>
      <a href="./listings.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        ⏳ Chờ duyệt (<?php echo $pendingCount; ?>)
      </a>
      <a href="./listings.php?status=active" class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>">
        ✅ Hoạt động (<?php echo $activeCount; ?>)
      </a>
      <a href="./listings.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        ❌ Từ chối (<?php echo $rejectedCount; ?>)
      </a>
      <a href="./listings.php?status=draft" class="filter-btn <?php echo $filterStatus === 'draft' ? 'active' : ''; ?>">
        📝 Bản nháp (<?php echo $draftCount; ?>)
      </a>
    </div>

    <!-- Listings Table -->
    <div class="table-container">
      <?php if (empty($listings)): ?>
        <div class="empty-state">
          <div class="empty-icon">🏠</div>
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
                    <img src="../../<?php echo htmlspecialchars($listing['cover_image']); ?>" 
                         alt="Cover" class="listing-thumb">
                  <?php else: ?>
                    <div class="no-image">📷</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></div>
                  <div class="listing-type"><?php echo htmlspecialchars($listing['place_type_name'] ?? 'N/A'); ?></div>
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
                      $statusIcon = '⏳';
                      break;
                    case 'active':
                      $statusText = 'Hoạt động';
                      $statusIcon = '✅';
                      break;
                    case 'rejected':
                      $statusText = 'Từ chối';
                      $statusIcon = '❌';
                      break;
                    case 'draft':
                      $statusText = 'Bản nháp';
                      $statusIcon = '📝';
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
