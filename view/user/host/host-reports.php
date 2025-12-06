<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
$rootPath = '../../../';

// Check if user is logged in and is a host
if (!isset($_SESSION['user_id'])) {
  header('Location: ../traveller/login.php');
  exit();
}

require_once __DIR__ . '/../../../controller/cHost.php';
require_once __DIR__ . '/../../../controller/cReport.php';

$cHost = new cHost();
$cReport = new cReport();

$isHost = $cHost->cIsUserHost($_SESSION['user_id']);
if (!$isHost) {
  header('Location: become-host.php');
  exit();
}

$userId = $_SESSION['user_id'];

// Get report data
$revenueByMonth = $cReport->cGetHostRevenueByMonth($userId);
$topListings = $cReport->cGetHostTopListings($userId, 5);
$bookingsByStatus = $cReport->cGetHostBookingsByStatus($userId);
$ratingsDistribution = $cReport->cGetHostRatingsDistribution($userId);

// Process data for charts
$revenueData = ['labels' => [], 'data' => []];
if ($revenueByMonth && $revenueByMonth->num_rows > 0) {
  while ($row = $revenueByMonth->fetch_assoc()) {
    $revenueData['labels'][] = $row['month_label'];
    $revenueData['data'][] = $row['total_revenue'];
  }
}

$statusData = ['labels' => [], 'data' => []];
if ($bookingsByStatus && $bookingsByStatus->num_rows > 0) {
  while ($row = $bookingsByStatus->fetch_assoc()) {
    $statusLabels = [
      'confirmed' => 'Đã xác nhận',
      'cancelled' => 'Đã hủy',
      'completed' => 'Hoàn thành'
    ];
    $statusData['labels'][] = $statusLabels[$row['status']] ?? $row['status'];
    $statusData['data'][] = $row['count'];
  }
}

$ratingsData = ['labels' => [], 'data' => []];
if ($ratingsDistribution && $ratingsDistribution->num_rows > 0) {
  while ($row = $ratingsDistribution->fetch_assoc()) {
    $ratingsData['labels'][] = $row['rating'] . ' ⭐';
    $ratingsData['data'][] = $row['count'];
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Báo cáo thống kê - WEGO Host</title>
  <link rel="stylesheet" href="../../css/host-reports.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../../partials/header.php'; ?>

  <div class="reports-container">
    <div class="reports-header">
      <div>
        <h1><i class="fas fa-chart-line"></i> Báo cáo thống kê</h1>
        <p>Xem chi tiết hiệu suất kinh doanh của bạn</p>
      </div>
      <a href="host-dashboard.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại Dashboard
      </a>
    </div>

    <!-- Revenue Chart -->
    <div class="report-section">
      <div class="section-header">
        <h2><i class="fas fa-money-bill-trend-up"></i> Doanh thu 12 tháng gần nhất</h2>
      </div>
      <div class="chart-container">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Bookings Status Pie Chart -->
    <div class="charts-grid">
      <div class="report-section">
        <div class="section-header">
          <h2><i class="fas fa-calendar-check"></i> Đơn đặt theo trạng thái</h2>
        </div>
        <div class="chart-container">
          <canvas id="statusChart"></canvas>
        </div>
      </div>

      <!-- Ratings Distribution -->
      <div class="report-section">
        <div class="section-header">
          <h2><i class="fas fa-star"></i> Phân bố đánh giá</h2>
        </div>
        <div class="chart-container">
          <canvas id="ratingsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Listings Table -->
    <div class="report-section">
      <div class="section-header">
        <h2><i class="fas fa-trophy"></i> Top 5 chỗ ở có doanh thu cao nhất</h2>
      </div>
      <div class="table-container">
        <table class="report-table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên chỗ ở</th>
              <th>Số đơn đặt</th>
              <th>Doanh thu</th>
              <th>Đánh giá TB</th>
              <th>Số reviews</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($topListings && $topListings->num_rows > 0): ?>
              <?php 
              $index = 1;
              while ($listing = $topListings->fetch_assoc()): 
              ?>
                <tr>
                  <td><?php echo $index++; ?></td>
                  <td class="listing-name">
                    <a href="../traveller/detailListing.php?listing_id=<?php echo $listing['listing_id']; ?>" target="_blank">
                      <?php echo htmlspecialchars($listing['title']); ?>
                    </a>
                  </td>
                  <td><?php echo number_format($listing['total_bookings'] ?? 0); ?></td>
                  <td class="revenue-cell">
                    <?php echo number_format($listing['total_revenue'] ?? 0, 0, ',', '.'); ?> đ
                  </td>
                  <td>
                    <?php if ($listing['avg_rating']): ?>
                      <span class="rating-badge">
                        <?php echo number_format($listing['avg_rating'], 1); ?> ⭐
                      </span>
                    <?php else: ?>
                      <span class="no-rating">Chưa có</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo number_format($listing['total_reviews'] ?? 0); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="empty-message">Chưa có dữ liệu</td>
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
          fill: true
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

    // Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($statusData['labels']); ?>,
        datasets: [{
          data: <?php echo json_encode($statusData['data']); ?>,
          backgroundColor: [
            '#10b981',
            '#ef4444',
            '#6366f1'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Ratings Bar Chart
    const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
    new Chart(ratingsCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($ratingsData['labels']); ?>,
        datasets: [{
          label: 'Số lượng',
          data: <?php echo json_encode($ratingsData['data']); ?>,
          backgroundColor: '#f59e0b'
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
</body>
</html>
