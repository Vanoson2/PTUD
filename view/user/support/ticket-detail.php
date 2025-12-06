<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

require_once __DIR__ . '/../../../controller/cSupport.php';
require_once __DIR__ . '/../../../model/mEmailPHPMailer.php';

$ticketId = $_GET['ticket_id'] ?? 0;
$userId = $_SESSION['user_id'];

$cSupport = new cSupport();

// Get ticket detail
$ticket = $cSupport->cGetTicketDetail($ticketId, $userId);

if (!$ticket) {
    header('Location: my-tickets.php');
    exit();
}

// Get messages
$messages = $cSupport->cGetTicketMessages($ticketId, $userId);

$message = '';
$messageType = '';

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $content = trim($_POST['content'] ?? '');
    
    $result = $cSupport->cReplyTicket($ticketId, $userId, $content);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        
        // Refresh to show new message
        header("refresh:1");
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Handle close ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close'])) {
    $result = $cSupport->cCloseTicket($ticketId, $userId);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        header("refresh:1");
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

$statusMap = [
    'open' => 'Mới',
    'in_progress' => 'Đang xử lý',
    'resolved' => 'Đã giải quyết',
    'closed' => 'Đã đóng'
];

$priorityMap = [
    'normal' => 'Bình thường',
    'high' => 'Cao',
    'urgent' => 'Khẩn cấp'
];

$categoryMap = [
    'dat_phong' => 'Đặt phòng',
    'tai_khoan' => 'Tài khoản',
    'nha_cung_cap' => 'Nhà cung cấp',
    'khac' => 'Khác'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết yêu cầu #<?= $ticketId ?> - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/support-ticket-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Ticket Header -->
        <div class="ticket-header">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2>#<?= $ticket['ticket_id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h2>
                    <p class="text-muted mb-0">
                        <span class="badge bg-secondary"><?= $categoryMap[$ticket['category']] ?></span>
                        <span class="badge bg-<?= $ticket['priority'] === 'urgent' ? 'danger' : ($ticket['priority'] === 'high' ? 'warning' : 'success') ?>">
                            <?= $priorityMap[$ticket['priority']] ?>
                        </span>
                    </p>
                </div>
                <span class="badge-status badge-<?= $ticket['status'] ?>">
                    <?= $statusMap[$ticket['status']] ?>
                </span>
            </div>
            
            <div class="text-muted small">
                <i class="fas fa-calendar-plus"></i> Tạo: <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                <?php if ($ticket['last_message_at']): ?>
                    <span class="ms-3">
                        <i class="fas fa-clock"></i> Cập nhật cuối: <?= date('d/m/Y H:i', strtotime($ticket['last_message_at'])) ?>
                    </span>
                <?php endif; ?>
                <?php if ($ticket['closed_at']): ?>
                    <span class="ms-3">
                        <i class="fas fa-check-circle"></i> Đóng: <?= date('d/m/Y H:i', strtotime($ticket['closed_at'])) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="messages-container">
            <h5 class="mb-4"><i class="fas fa-comments"></i> Trao đổi</h5>
            
            <?php if (empty($messages)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Chưa có tin nhắn nào.
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender_type'] ?>">
                        <div class="message-header">
                            <span class="message-sender">
                                <?php if ($msg['sender_type'] === 'user'): ?>
                                    <i class="fas fa-user"></i> Bạn
                                <?php else: ?>
                                    <i class="fas fa-user-shield"></i> <?= htmlspecialchars($msg['sender_name']) ?> (Hỗ trợ)
                                <?php endif; ?>
                            </span>
                            <span class="message-time">
                                <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                            </span>
                        </div>
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($msg['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Reply Form -->
        <?php if ($ticket['status'] !== 'closed'): ?>
            <div class="reply-form">
                <h5 class="mb-3"><i class="fas fa-reply"></i> Trả lời</h5>
                <form method="POST">
                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="4" required minlength="5"
                                  placeholder="Nhập tin nhắn của bạn..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="reply" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                        </button>
                        <?php if ($ticket['status'] !== 'closed'): ?>
                            <button type="submit" name="close" class="btn btn-secondary" 
                                    onclick="return confirm('Bạn có chắc muốn đóng yêu cầu này?')">
                                <i class="fas fa-times-circle"></i> Đóng yêu cầu
                            </button>
                        <?php endif; ?>
                        <a href="my-tickets.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary text-center">
                <i class="fas fa-lock"></i> Yêu cầu này đã được đóng. 
                <a href="my-tickets.php" class="btn btn-sm btn-secondary ms-2">Quay lại danh sách</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto scroll to bottom of messages
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.querySelector('.messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    </script>
</body>
</html>
