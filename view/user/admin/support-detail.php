<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit();
}

include_once(__DIR__ . "/../../../controller/cAdmin.php");
include_once(__DIR__ . "/../../../model/mEmailPHPMailer.php");

$cAdmin = new cAdmin();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];

$ticketId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ticketId <= 0) {
  header('Location: support.php');
  exit();
}

// Get ticket detail
$ticket = $cAdmin->cGetTicketDetail($ticketId);
if (!$ticket) {
  header('Location: support.php');
  exit();
}

// Get messages
$messages = $cAdmin->cGetTicketMessages($ticketId);

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'reply') {
    $content = trim($_POST['content'] ?? '');
    $result = $cAdmin->cReplyToTicket($ticketId, $adminId, $content);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
      // Refresh to show new message
      header("Refresh: 1");
    }
  } elseif ($action === 'update_status') {
    $status = $_POST['status'] ?? '';
    $result = $cAdmin->cUpdateTicketStatus($ticketId, $status);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
      header("Refresh: 1");
    }
  }
}

// Labels
$categoryLabels = [
  'dat_phong' => 'Đặt phòng',
  'tai_khoan' => 'Tài khoản',
  'nha_cung_cap' => 'Nhà cung cấp',
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
  <title>Chi tiết Ticket #<?php echo $ticketId; ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/admin-dashboard.css">
  <link rel="stylesheet" href="../../css/admin-support-detail.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
      <!-- Simple Header -->
      <div class="page-title">
        <h1>
          <i class="fas fa-ticket-alt"></i>
          Chi tiết Ticket #<?php echo $ticketId; ?>
        </h1>
        <a href="support.php" class="btn-back">
          <i class="fas fa-arrow-left"></i>
          Quay lại danh sách
        </a>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <div class="ticket-detail-container">
        <!-- Ticket Header -->
        <div class="card">
          <div class="ticket-header">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h2><i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($ticket['title']); ?></h2>
                <div class="ticket-meta">
                  <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <strong><?php echo htmlspecialchars($ticket['full_name']); ?></strong>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($ticket['email']); ?>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-phone"></i>
                    <?php echo htmlspecialchars($ticket['phone'] ?? 'N/A'); ?>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                  </div>
                </div>
                <div class="ticket-meta">
                  <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <?php echo $categoryLabels[$ticket['category']]; ?>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $priorityLabels[$ticket['priority']]; ?>
                  </div>
                </div>
              </div>
              <span class="status-badge status-<?php echo $ticket['status']; ?>">
                <?php echo $statusLabels[$ticket['status']]; ?>
              </span>
            </div>
          </div>
          
          <!-- Initial Content -->
          <div class="card-body bg-light">
            <h5 class="mb-3"><i class="fas fa-file-alt text-primary"></i> Nội dung ban đầu</h5>
            <p class="mb-0" style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($ticket['content'])); ?></p>
          </div>
        </div>

        <!-- Messages -->
        <div class="card">
          <div class="card-body">
            <h4 class="mb-4">
              <i class="fas fa-comments text-primary"></i> 
              Trao đổi 
              <span class="badge bg-primary ms-2"><?php echo count($messages); ?></span>
            </h4>
            
            <div class="messages-container">
              <?php if (empty($messages)): ?>
                <div class="text-center text-muted py-5">
                  <i class="fas fa-inbox fa-3x mb-3"></i>
                  <p>Chưa có tin nhắn trao đổi</p>
                </div>
              <?php else: ?>
                <?php foreach ($messages as $msg): 
                  $senderName = ($msg['sender_type'] === 'user') ? $msg['user_name'] : $msg['admin_name'];
                ?>
                  <div class="message <?php echo $msg['sender_type']; ?>">
                    <div class="message-header">
                      <span class="message-sender">
                        <?php if ($msg['sender_type'] === 'user'): ?>
                          <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($senderName); ?>
                        <?php else: ?>
                          <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($senderName); ?>
                        <?php endif; ?>
                      </span>
                      <span class="message-time">
                        <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                      </span>
                    </div>
                    <div class="message-content">
                      <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Reply Form -->
        <?php if ($ticket['status'] !== 'closed'): ?>
          <div class="card">
            <div class="card-body">
              <h4 class="mb-4"><i class="fas fa-reply text-primary"></i> Trả lời khách hàng</h4>
              <form method="POST">
                <input type="hidden" name="action" value="reply">
                <div class="mb-3">
                  <textarea name="content" class="form-control" rows="5" required
                            placeholder="Nhập nội dung trả lời... (Email sẽ tự động được gửi đến khách hàng)"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="fas fa-paper-plane"></i> Gửi trả lời & Email
                </button>
              </form>
            </div>
          </div>
          
          <!-- Status Update -->
          <div class="card">
            <div class="card-body">
              <h4 class="mb-4"><i class="fas fa-tasks text-success"></i> Cập nhật trạng thái</h4>
              <form method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="update_status">
                <div class="col-md-8">
                  <label class="form-label">Chọn trạng thái mới</label>
                  <select name="status" class="form-select form-select-lg">
                    <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>🆕 Mới</option>
                    <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>⏳ Đang xử lý</option>
                    <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>✅ Đã giải quyết</option>
                    <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>🔒 Đã đóng</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-check"></i> Cập nhật
                  </button>
                </div>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-secondary">
            <i class="fas fa-lock me-2"></i> Ticket này đã được đóng.
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    // Auto scroll messages to bottom
    window.addEventListener('load', function() {
      const messagesCard = document.querySelector('.messages-card');
      if (messagesCard) {
        messagesCard.scrollTop = messagesCard.scrollHeight;
      }
    });
  </script>
</body>
</html>
