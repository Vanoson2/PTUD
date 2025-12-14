<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
  header('Location: ./login.php');
  exit;
}

require_once __DIR__ . '/../../../controller/cReport.php';

$cReport = new cReport();

// Get system overview
$overviewResult = $cReport->cGetSystemOverview();
$overview = $overviewResult ? $overviewResult->fetch_assoc() : [];

// Get charts data
$revenueByMonth = $cReport->cGetSystemRevenueByMonth();
$newListingsByMonth = $cReport->cGetNewListingsByMonth();
$newUsersByMonth = $cReport->cGetNewUsersByMonth();
$topHosts = $cReport->cGetTopHosts(10);
$listingsByProvince = $cReport->cGetListingsByProvince(10);

// Process revenue data
$revenueData = ['labels' => [], 'data' => []];
if ($revenueByMonth && $revenueByMonth->num_rows > 0) {
  while ($row = $revenueByMonth->fetch_assoc()) {
    $revenueData['labels'][] = $row['month_label'];
    $revenueData['data'][] = $row['total_revenue'];
  }
}

// Process listings data
$listingsData = ['labels' => [], 'data' => []];
if ($newListingsByMonth && $newListingsByMonth->num_rows > 0) {
  while ($row = $newListingsByMonth->fetch_assoc()) {
    $listingsData['labels'][] = $row['month_label'];
    $listingsData['data'][] = $row['count'];
  }
}

// Process users data
$usersData = ['labels' => [], 'data' => []];
if ($newUsersByMonth && $newUsersByMonth->num_rows > 0) {
  while ($row = $newUsersByMonth->fetch_assoc()) {
    $usersData['labels'][] = $row['month_label'];
    $usersData['data'][] = $row['count'];
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Báo cáo hệ thống - WEGO Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-reports.css?v=<?php echo time(); ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <a href="listings.php">
        <i class="fas fa-building"></i>
        <span>Quản lý Phòng</span>
      </a>
      <a href="support.php">
        <i class="fas fa-headset"></i>
        <span>Hỗ trợ khách hàng</span>
      </a>
      <a href="amenities-services.php">
        <i class="fas fa-cog"></i>
        <span>Tiện nghi & Dịch vụ</span>
      </a>
      <a href="admin-reports.php" class="active">
        <i class="fas fa-chart-pie"></i>
        <span>Báo cáo</span>
      </a>
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="reports-header">
      <div>
        <h1><i class="fas fa-chart-pie"></i> Báo cáo hệ thống</h1>
        <p>Tổng quan và phân tích dữ liệu toàn hệ thống</p>
      </div>
      <a href="dashboard.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại Dashboard
      </a>
    </div>

    <!-- Overview Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card stat-users">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Tổng người dùng</p>
          <p class="stat-number"><?php echo number_format($overview['total_users'] ?? 0); ?></p>
          <p class="stat-sublabel"><?php echo number_format($overview['total_hosts'] ?? 0); ?> hosts</p>
        </div>
      </div>

      <div class="stat-card stat-listings">
        <div class="stat-icon">
          <i class="fas fa-home"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Chỗ ở</p>
          <p class="stat-number"><?php echo number_format($overview['active_listings'] ?? 0); ?></p>
          <p class="stat-sublabel"><?php echo number_format($overview['pending_listings'] ?? 0); ?> chờ duyệt</p>
        </div>
      </div>

      <div class="stat-card stat-bookings">
        <div class="stat-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Đơn đặt</p>
          <p class="stat-number"><?php echo number_format(($overview['confirmed_bookings'] ?? 0) + ($overview['completed_bookings'] ?? 0)); ?></p>
          <p class="stat-sublabel"><?php echo number_format($overview['confirmed_bookings'] ?? 0); ?> đang hoạt động</p>
        </div>
      </div>

      <div class="stat-card stat-revenue">
        <div class="stat-icon">
          <i class="fas fa-money-bill-trend-up"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Tổng doanh thu</p>
          <p class="stat-number"><?php echo number_format($overview['total_revenue'] ?? 0, 0, ',', '.'); ?> đ</p>
          <p class="stat-sublabel"><?php echo number_format($overview['open_tickets'] ?? 0); ?> tickets đang mở</p>
        </div>
      </div>
    </div>

    <!-- Revenue Chart -->
    <div class="report-section">
      <div class="section-header">
        <h2><i class="fas fa-chart-line"></i> Doanh thu 12 tháng gần nhất</h2>
      </div>
      <div class="chart-container">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Growth Charts -->
    <div class="charts-grid">
      <div class="report-section">
        <div class="section-header">
          <h2><i class="fas fa-home-alt"></i> Chỗ ở mới theo tháng</h2>
        </div>
        <div class="chart-container">
          <canvas id="listingsChart"></canvas>
        </div>
      </div>

      <div class="report-section">
        <div class="section-header">
          <h2><i class="fas fa-user-plus"></i> Người dùng mới theo tháng</h2>
        </div>
        <div class="chart-container">
          <canvas id="usersChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Hosts Table -->
    <div class="report-section">
      <div class="section-header">
        <h2><i class="fas fa-crown"></i> Top 10 Hosts có doanh thu cao nhất</h2>
      </div>
      <div class="table-container">
        <table class="report-table">
          <thead>
            <tr>
              <th>Hạng</th>
              <th>Host</th>
              <th>Email</th>
              <th>Số listings</th>
              <th>Số bookings</th>
              <th>Doanh thu</th>
              <th>Đánh giá TB</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($topHosts && $topHosts->num_rows > 0): ?>
              <?php 
              $rank = 1;
              while ($host = $topHosts->fetch_assoc()): 
              ?>
                <tr>
                  <td>
                    <?php if ($rank <= 3): ?>
                      <span class="rank-badge rank-<?php echo $rank; ?>">
                        <i class="fas fa-medal" style="color: <?php echo $rank === 1 ? '#FFD700' : ($rank === 2 ? '#C0C0C0' : '#CD7F32'); ?>;"></i>
                      </span>
                    <?php else: ?>
                      <?php echo $rank; ?>
                    <?php endif; ?>
                  </td>
                  <td class="host-name"><?php echo htmlspecialchars($host['fullname']); ?></td>
                  <td><?php echo htmlspecialchars($host['email']); ?></td>
                  <td><?php echo number_format($host['total_listings']); ?></td>
                  <td><?php echo number_format($host['total_bookings'] ?? 0); ?></td>
                  <td class="revenue-cell">
                    <?php echo number_format($host['total_revenue'] ?? 0, 0, ',', '.'); ?> đ
                  </td>
                  <td>
                    <?php if ($host['avg_rating']): ?>
                      <span class="rating-badge">
                        <?php echo number_format($host['avg_rating'], 1); ?> <i class="fas fa-star" style="color: #FDB022;"></i>
                      </span>
                    <?php else: ?>
                      <span class="no-rating">N/A</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php 
              $rank++;
              endwhile; 
              ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="empty-message">Chưa có dữ liệu</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Listings by Province Table -->
    <div class="report-section">
      <div class="section-header">
        <h2><i class="fas fa-map-marked-alt"></i> Top 10 Tỉnh/Thành có nhiều chỗ ở nhất</h2>
      </div>
      <div class="table-container">
        <table class="report-table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tỉnh/Thành</th>
              <th>Số chỗ ở</th>
              <th>Số bookings</th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($listingsByProvince && $listingsByProvince->num_rows > 0): ?>
              <?php 
              $index = 1;
              while ($province = $listingsByProvince->fetch_assoc()): 
              ?>
                <tr>
                  <td><?php echo $index++; ?></td>
                  <td class="province-name">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($province['province_name']); ?>
                  </td>
                  <td><?php echo number_format($province['total_listings']); ?></td>
                  <td><?php echo number_format($province['total_bookings'] ?? 0); ?></td>
                  <td class="revenue-cell">
                    <?php echo number_format($province['total_revenue'] ?? 0, 0, ',', '.'); ?> đ
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="empty-message">Chưa có dữ liệu</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

  <script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($revenueData['labels']); ?>,
        datasets: [{
          label: 'Doanh thu (VND)',
          data: <?php echo json_encode($revenueData['data']); ?>,
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.4,
          fill: true,
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString('vi-VN') + ' đ';
              }
            }
          }
        }
      }
    });

    // Listings Chart
    const listingsCtx = document.getElementById('listingsChart').getContext('2d');
    new Chart(listingsCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($listingsData['labels']); ?>,
        datasets: [{
          label: 'Số lượng',
          data: <?php echo json_encode($listingsData['data']); ?>,
          backgroundColor: '#10b981',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Users Chart
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    new Chart(usersCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($usersData['labels']); ?>,
        datasets: [{
          label: 'Số lượng',
          data: <?php echo json_encode($usersData['data']); ?>,
          backgroundColor: '#f59e0b',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  </script>

  </main>
</div>

</body>
</html>
