<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit();
}

include_once(__DIR__ . "/../../controller/cAdmin.php");
include_once(__DIR__ . "/../../model/mEmailPHPMailer.php");

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
  <link rel="stylesheet" href="../css/admin-dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .ticket-detail-container {
      padding: 20px;
    }
    
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .card-body {
      padding: 30px;
    }
    
    .ticket-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      border-radius: 12px 12px 0 0;
    }
    
    .ticket-header h2 {
      color: white;
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .ticket-meta {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-top: 20px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      background: rgba(255,255,255,0.15);
      padding: 8px 15px;
      border-radius: 20px;
      color: white;
    }
    
    .meta-item i {
      opacity: 0.9;
    }
    
    .messages-container {
      max-height: 500px;
      overflow-y: auto;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .message {
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 15px;
      animation: fadeIn 0.3s ease-in;
      position: relative;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .message.user {
      background: white;
      margin-left: auto;
      margin-right: 0;
      max-width: 85%;
      border: 2px solid #e3f2fd;
      box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);
    }
    
    .message.user::before {
      content: '';
      position: absolute;
      left: -10px;
      top: 20px;
      width: 0;
      height: 0;
      border-top: 10px solid transparent;
      border-bottom: 10px solid transparent;
      border-right: 10px solid #e3f2fd;
    }
    
    .message.admin {
      background: white;
      margin-right: auto;
      margin-left: 0;
      max-width: 85%;
      border: 2px solid #f3e5f5;
      box-shadow: 0 2px 8px rgba(156, 39, 176, 0.1);
    }
    
    .message.admin::before {
      content: '';
      position: absolute;
      right: -10px;
      top: 20px;
      width: 0;
      height: 0;
      border-top: 10px solid transparent;
      border-bottom: 10px solid transparent;
      border-left: 10px solid #f3e5f5;
    }
    
    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .message-sender {
      font-weight: 600;
      font-size: 15px;
    }
    
    .message.user .message-sender {
      color: #2196F3;
    }
    
    .message.admin .message-sender {
      color: #9C27B0;
    }
    
    .message-time {
      color: #999;
      font-size: 12px;
    }
    
    .message-content {
      color: #333;
      line-height: 1.6;
    }
    
    .btn {
      border-radius: 8px;
      padding: 12px 24px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    }
    
    .btn-secondary:hover {
      transform: translateY(-2px);
    }
    
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    textarea.form-control {
      resize: vertical;
      min-height: 120px;
    }
    
    .status-badge {
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: bold;
      display: inline-block;
      font-size: 14px;
    }
    
    .status-badge.status-open { background: #28a745; color: white; }
    .status-badge.status-in_progress { background: #17a2b8; color: white; }
    .status-badge.status-resolved { background: #ffc107; color: #333; }
    .status-badge.status-closed { background: #6c757d; color: white; }
    
    .priority-badge {
      padding: 5px 15px;
      border-radius: 15px;
      font-size: 0.85em;
      font-weight: bold;
    }
    
    .priority-badge.priority-urgent { background: #dc3545; color: white; }
    .priority-badge.priority-high { background: #fd7e14; color: white; }
    .priority-badge.priority-normal { background: #28a745; color: white; }
    
    .badge {
      padding: 5px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }
    
    .badge.bg-secondary {
      background: #6c757d;
      color: white;
    }
    
    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .alert-secondary {
      background: #e2e3e5;
      color: #383d41;
      border: 1px solid #d6d8db;
    }
    
    .initial-content {
      margin-top: 20px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 4px solid #667eea;
    }
    
    .initial-content strong {
      color: #333;
      display: block;
      margin-bottom: 10px;
    }
    
    .status-update-form {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    
    .status-update-form select {
      max-width: 300px;
    }
    
    hr {
      border: none;
      border-top: 1px solid #dee2e6;
      margin: 20px 0;
    }
    
    /* Admin Container & Sidebar Layout */
    .admin-container {
      display: flex;
      min-height: 100vh;
    }
    
    .sidebar {
      width: 260px;
      background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 0;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }
    
    .sidebar-header {
      padding: 0 20px 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .sidebar-header i {
      font-size: 24px;
    }
    
    .sidebar-header h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
    }
    
    .sidebar-nav {
      padding: 0;
    }
    
    .main-content {
      margin-left: 260px;
      padding: 30px;
      width: calc(100% - 260px);
      background: #f8f9fa;
    }
    
    /* Custom Sidebar Menu Styling */
    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 15px !important;
      margin: 5px 10px !important;
      color: white;
      text-decoration: none;
      border-radius: 10px !important;
      border: 2px solid transparent !important;
      transition: all 0.3s !important;
    }
    
    .sidebar-nav a i {
      font-size: 18px;
      width: 20px;
    }
    
    .sidebar-nav a:hover {
      border-color: rgba(255,255,255,0.3) !important;
      background: rgba(255,255,255,0.1) !important;
    }
    
    .sidebar-nav a.active {
      border-color: white !important;
      background: rgba(255,255,255,0.2) !important;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    
    /* Simple Clean Header */
    .page-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e9ecef;
    }
    
    .page-title h1 {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .page-title h1 i {
      color: #667eea;
      font-size: 26px;
    }
    
    .btn-back {
      background: #6c757d;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-back:hover {
      background: #5a6268;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
  </style>
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
