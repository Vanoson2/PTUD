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
  
  if ($action === 'update_status') {
    $ticketId = intval($_POST['ticket_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $result = $cAdmin->cUpdateTicketStatus($ticketId, $status);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
  }
}

// Get filter parameters
$filterStatus = $_GET['status'] ?? null;
$filterCategory = $_GET['category'] ?? null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;

// Get tickets
$ticketData = $cAdmin->cGetAllSupportTickets($filterStatus, $filterCategory, $page, $limit);
$tickets = $ticketData['tickets'];
$totalPages = $ticketData['pages'];
$totalTickets = $ticketData['total'];

// Get counts by status
$openData = $cAdmin->cGetAllSupportTickets('open', null, 1, 1);
$inProgressData = $cAdmin->cGetAllSupportTickets('in_progress', null, 1, 1);
$resolvedData = $cAdmin->cGetAllSupportTickets('resolved', null, 1, 1);
$closedData = $cAdmin->cGetAllSupportTickets('closed', null, 1, 1);

$openCount = $openData['total'];
$inProgressCount = $inProgressData['total'];
$resolvedCount = $resolvedData['total'];
$closedCount = $closedData['total'];

// Get count for service requests
$serviceRequestData = $cAdmin->cGetAllSupportTickets(null, 'de_xuat_dich_vu', 1, 1);
$serviceRequestCount = $serviceRequestData['total'];

// Category translations
$categoryLabels = [
  'dat_phong' => 'Đặt phòng',
  'tai_khoan' => 'Tài khoản',
  'nha_cung_cap' => 'Nhà cung cấp',
  'de_xuat_dich_vu' => 'Đề xuất dịch vụ',
  'khac' => 'Khác'
];

$priorityLabels = [
  'normal' => 'Bình thường',
  'high' => 'Cao',
  'urgent' => 'Khẩn cấp'
];

$statusLabels = [
  'open' => 'Mới',
  'in_progress' => 'Đang xử lý',
  'resolved' => 'Đã giải quyết',
  'closed' => 'Đã đóng'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yêu cầu Hỗ trợ - Admin</title>
  <link rel="stylesheet" href="../css/admin-layout.css">
  <link rel="stylesheet" href="../css/admin-dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin-support.css?v=<?php echo time(); ?>">
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
        <a href="support.php" class="active">
          <i class="fas fa-headset"></i>
          <span>Hỗ trợ khách hàng</span>
        </a>
        <a href="amenities-services.php">
          <i class="fas fa-cog"></i>
          <span>Tiện nghi & Dịch vụ</span>
        </a>
        <a href="logout.php">
          <i class="fas fa-sign-out-alt"></i>
          <span>Đăng xuất</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="support-container">
        <?php if ($message): ?>
          <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>
        
        <div class="header-section">
          <h1>Yêu cầu Hỗ trợ</h1>
          <p style="color: #6B7280;">Quản lý và trả lời các yêu cầu hỗ trợ từ người dùng</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
          <div class="stat-card open">
            <div class="stat-number"><?php echo $openCount; ?></div>
            <div class="stat-label">Yêu cầu mới</div>
          </div>
          <div class="stat-card in-progress">
            <div class="stat-number"><?php echo $inProgressCount; ?></div>
            <div class="stat-label">Đang xử lý</div>
          </div>
          <div class="stat-card resolved">
            <div class="stat-number"><?php echo $resolvedCount; ?></div>
            <div class="stat-label">Đã giải quyết</div>
          </div>
          <div class="stat-card closed">
            <div class="stat-number"><?php echo $closedCount; ?></div>
            <div class="stat-label">Đã đóng</div>
          </div>
          <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stat-number" style="color: white;"><?php echo $serviceRequestCount; ?></div>
            <div class="stat-label" style="color: white;">Đề xuất dịch vụ</div>
          </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
          <a href="support.php" class="filter-tab <?php echo $filterStatus === null && $filterCategory === null ? 'active' : ''; ?>">
            Tất cả (<?php echo $totalTickets; ?>)
          </a>
          <a href="support.php?status=open" class="filter-tab <?php echo $filterStatus === 'open' ? 'active' : ''; ?>">
            Mới (<?php echo $openCount; ?>)
          </a>
          <a href="support.php?status=in_progress" class="filter-tab <?php echo $filterStatus === 'in_progress' ? 'active' : ''; ?>">
            Đang xử lý (<?php echo $inProgressCount; ?>)
          </a>
          <a href="support.php?status=resolved" class="filter-tab <?php echo $filterStatus === 'resolved' ? 'active' : ''; ?>">
            Đã giải quyết (<?php echo $resolvedCount; ?>)
          </a>
          <a href="support.php?status=closed" class="filter-tab <?php echo $filterStatus === 'closed' ? 'active' : ''; ?>">
            Đã đóng (<?php echo $closedCount; ?>)
          </a>
          <a href="support.php?category=de_xuat_dich_vu" class="filter-tab <?php echo $filterCategory === 'de_xuat_dich_vu' ? 'active' : ''; ?>" style="border-left: 3px solid #667eea;">
            <i class="fas fa-lightbulb"></i> Đề xuất dịch vụ (<?php echo $serviceRequestCount; ?>)
          </a>
        </div>
        
        <!-- Tickets Table -->
        <div class="tickets-table">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>TIÊU ĐỀ</th>
                <th>NGƯỜI GỬI</th>
                <th>DANH MỤC</th>
                <th>ƯU TIÊN</th>
                <th>TRẠNG THÁI</th>
                <th>CẬP NHẬT</th>
                <th>THAO TÁC</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($tickets)): ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 40px;">
                    <i class="fas fa-headset" style="font-size: 48px; color: #ccc;"></i>
                    <p>Không có yêu cầu hỗ trợ</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                  <tr onclick="window.location='support-detail.php?id=<?php echo $ticket['ticket_id']; ?>'">
                    <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                    <td>
                      <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                      <div class="time-ago">
                        <?php 
                        $created = strtotime($ticket['created_at']);
                        $diff = time() - $created;
                        if ($diff < 3600) {
                          echo floor($diff / 60) . ' phút trước';
                        } elseif ($diff < 86400) {
                          echo floor($diff / 3600) . ' giờ trước';
                        } else {
                          echo floor($diff / 86400) . ' ngày trước';
                        }
                        ?>
                      </div>
                    </td>
                    <td>
                      <?php echo htmlspecialchars($ticket['full_name']); ?><br>
                      <small style="color: #9CA3AF;"><?php echo htmlspecialchars($ticket['email']); ?></small>
                    </td>
                    <td>
                      <span class="category-badge">
                        <?php echo $categoryLabels[$ticket['category']] ?? $ticket['category']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="priority-badge priority-<?php echo $ticket['priority']; ?>">
                        <?php echo $priorityLabels[$ticket['priority']] ?? $ticket['priority']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge status-<?php echo $ticket['status']; ?>">
                        <?php echo $statusLabels[$ticket['status']] ?? $ticket['status']; ?>
                      </span>
                    </td>
                    <td>
                      <?php 
                      if ($ticket['last_message_at']) {
                        echo date('d/m/Y H:i', strtotime($ticket['last_message_at']));
                      } else {
                        echo '-';
                      }
                      ?>
                    </td>
                    <td onclick="event.stopPropagation();">
                      <a href="support-detail.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn-view">
                        Xem chi tiết
                      </a>
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
              <a href="?page=<?php echo $page - 1; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>">
                <i class="fas fa-chevron-left"></i> Previous
              </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <?php if ($i === $page): ?>
                <span class="active"><?php echo $i; ?></span>
              <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>">
                  <?php echo $i; ?>
                </a>
              <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
              <a href="?page=<?php echo $page + 1; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>">
                Next <i class="fas fa-chevron-right"></i>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
