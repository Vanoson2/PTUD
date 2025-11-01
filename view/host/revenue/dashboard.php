<?php
session_start();

// Check if host is logged in
if (!isset($_SESSION['host_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../../controller/cRevenue.php';

$cRevenue = new cRevenue();
$hostId = $_SESSION['host_id'];
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get filter dates
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get revenue data
$totalResult = $cRevenue->cGetHostTotalRevenue($hostId, $startDate, $endDate);
$listingResult = $cRevenue->cGetRevenueByListing($hostId, $startDate, $endDate);
$monthlyResult = $cRevenue->cGetMonthlyRevenue($hostId, $year);
$statsResult = $cRevenue->cGetBookingStatistics($hostId);

$totalData = $totalResult['data'] ?? [];
$listingData = $listingResult['data'] ?? [];
$monthlyData = $monthlyResult['data'] ?? [];
$statsData = $statsResult['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê Doanh thu - WeGo Host</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f7fa;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .page-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card.revenue {
            border-left-color: #10b981;
        }
        .stat-card.commission {
            border-left-color: #f59e0b;
        }
        .stat-card.net {
            border-left-color: #667eea;
        }
        .stat-card.bookings {
            border-left-color: #ec4899;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .stat-card.revenue .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .stat-card.commission .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        .stat-card.net .stat-icon {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        .stat-card.bookings .stat-icon {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 10px 0 5px 0;
        }
        .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        .chart-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .chart-card h3 {
            margin: 0 0 25px 0;
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .table thead {
            background: #f9fafb;
        }
        .table th {
            font-weight: 600;
            color: #374151;
            border: none;
            padding: 15px;
        }
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #f3f4f6;
        }
        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .back-btn {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background: #f3f4f6;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <a href="../../index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Thống kê Doanh thu</h1>
            <p>Xin chào, <?php echo htmlspecialchars($_SESSION['host_name'] ?? 'Host'); ?>! Đây là báo cáo doanh thu của bạn.</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><strong>Từ ngày</strong></label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><strong>Đến ngày</strong></label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>Năm</strong></label>
                    <select name="year" class="form-select">
                        <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value"><?php echo cRevenue::formatCurrency($totalData['total_revenue'] ?? 0); ?></div>
                <div class="stat-label">Tổng doanh thu</div>
            </div>
            
            <div class="stat-card commission">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value"><?php echo cRevenue::formatCurrency($totalData['total_commission'] ?? 0); ?></div>
                <div class="stat-label">Hoa hồng hệ thống</div>
            </div>
            
            <div class="stat-card net">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value"><?php echo cRevenue::formatCurrency($totalData['net_revenue'] ?? 0); ?></div>
                <div class="stat-label">Doanh thu thực nhận</div>
            </div>
            
            <div class="stat-card bookings">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?php echo cRevenue::formatNumber($totalData['total_bookings'] ?? 0); ?></div>
                <div class="stat-label">Tổng số booking</div>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Doanh thu theo tháng năm <?php echo $year; ?></h3>
            <canvas id="revenueChart" style="max-height: 400px;"></canvas>
        </div>

        <!-- Revenue by Listing Table -->
        <div class="chart-card">
            <h3><i class="fas fa-building"></i> Doanh thu theo từng phòng</h3>
            <div class="table-container">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Giá/đêm</th>
                            <th>Số booking</th>
                            <th>Số đêm</th>
                            <th>Doanh thu</th>
                            <th>Hoa hồng</th>
                            <th>Thực nhận</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listingData)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có dữ liệu doanh thu</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listingData as $listing): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($listing['listing_name']); ?></strong></td>
                                    <td><?php echo cRevenue::formatCurrency($listing['price_per_night']); ?></td>
                                    <td><?php echo cRevenue::formatNumber($listing['total_bookings'] ?? 0); ?></td>
                                    <td><?php echo cRevenue::formatNumber($listing['total_nights'] ?? 0); ?> đêm</td>
                                    <td><strong class="text-success"><?php echo cRevenue::formatCurrency($listing['revenue'] ?? 0); ?></strong></td>
                                    <td class="text-warning"><?php echo cRevenue::formatCurrency($listing['commission'] ?? 0); ?></td>
                                    <td><strong class="text-primary"><?php echo cRevenue::formatCurrency($listing['net_revenue'] ?? 0); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Booking Statistics -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Thống kê Booking</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center p-3">
                        <h4 class="text-success"><?php echo cRevenue::formatNumber($statsData['completed_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Hoàn thành</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3">
                        <h4 class="text-primary"><?php echo cRevenue::formatNumber($statsData['confirmed_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Đã xác nhận</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3">
                        <h4 class="text-danger"><?php echo cRevenue::formatNumber($statsData['cancelled_bookings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Đã hủy</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3">
                        <h4 class="text-info"><?php echo cRevenue::formatNumber($statsData['avg_stay_duration'] ?? 0, 1); ?> đêm</h4>
                        <p class="text-muted mb-0">Trung bình lưu trú</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js - Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [
                    {
                        label: 'Doanh thu (VNĐ)',
                        data: monthlyData.map(m => m.revenue || 0),
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 2,
                        borderRadius: 8
                    },
                    {
                        label: 'Thực nhận (VNĐ)',
                        data: monthlyData.map(m => m.net_revenue || 0),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
