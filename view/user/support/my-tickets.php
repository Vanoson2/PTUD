<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../../controller/cSupport.php';

$cSupport = new cSupport();
$userId = $_SESSION['user_id'];

// Get filter
$statusFilter = $_GET['status'] ?? null;

// Get user tickets
$tickets = $cSupport->cGetUserTickets($userId, $statusFilter);
$counts = $cSupport->cGetUserTicketCounts($userId);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu hỗ trợ của tôi - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .container {
            max-width: 1200px;
        }
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats-row {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            flex: 1;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: white;
        }
        .stat-card.open { background: #28a745; }
        .stat-card.in-progress { background: #17a2b8; }
        .stat-card.resolved { background: #ffc107; color: #333; }
        .stat-card.closed { background: #6c757d; }
        .stat-card h3 { margin: 0; font-size: 2em; }
        .stat-card p { margin: 5px 0 0 0; }
        
        .tickets-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ticket-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .ticket-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        .ticket-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .badge-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        .badge-open { background: #28a745; color: white; }
        .badge-in_progress { background: #17a2b8; color: white; }
        .badge-resolved { background: #ffc107; color: #333; }
        .badge-closed { background: #6c757d; color: white; }
        
        .badge-priority {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75em;
            margin-left: 10px;
        }
        .badge-urgent { background: #dc3545; color: white; }
        .badge-high { background: #fd7e14; color: white; }
        .badge-normal { background: #28a745; color: white; }
        
        .ticket-meta {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .unread-badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-ticket-alt"></i> Yêu cầu hỗ trợ của tôi</h1>
                    <p class="text-muted mb-0">Quản lý các yêu cầu hỗ trợ của bạn</p>
                </div>
                <a href="create-ticket.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo yêu cầu mới
                </a>
            </div>
            
            <div class="stats-row">
                <div class="stat-card open">
                    <h3><?= $counts['open'] ?></h3>
                    <p>Mới</p>
                </div>
                <div class="stat-card in-progress">
                    <h3><?= $counts['in_progress'] ?></h3>
                    <p>Đang xử lý</p>
                </div>
                <div class="stat-card resolved">
                    <h3><?= $counts['resolved'] ?></h3>
                    <p>Đã giải quyết</p>
                </div>
                <div class="stat-card closed">
                    <h3><?= $counts['closed'] ?></h3>
                    <p>Đã đóng</p>
                </div>
            </div>
        </div>
        
        <div class="tickets-container">
            <!-- Filters -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <a href="?" class="btn btn-outline-primary <?= !$statusFilter ? 'active' : '' ?>">
                        Tất cả (<?= $counts['total'] ?>)
                    </a>
                    <a href="?status=open" class="btn btn-outline-success <?= $statusFilter === 'open' ? 'active' : '' ?>">
                        Mới (<?= $counts['open'] ?>)
                    </a>
                    <a href="?status=in_progress" class="btn btn-outline-info <?= $statusFilter === 'in_progress' ? 'active' : '' ?>">
                        Đang xử lý (<?= $counts['in_progress'] ?>)
                    </a>
                    <a href="?status=resolved" class="btn btn-outline-warning <?= $statusFilter === 'resolved' ? 'active' : '' ?>">
                        Đã giải quyết (<?= $counts['resolved'] ?>)
                    </a>
                    <a href="?status=closed" class="btn btn-outline-secondary <?= $statusFilter === 'closed' ? 'active' : '' ?>">
                        Đã đóng (<?= $counts['closed'] ?>)
                    </a>
                </div>
            </div>
            
            <!-- Tickets List -->
            <?php if (empty($tickets)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <?php if ($statusFilter): ?>
                        Không có yêu cầu nào với trạng thái này.
                    <?php else: ?>
                        Bạn chưa có yêu cầu hỗ trợ nào. <a href="create-ticket.php">Tạo yêu cầu mới</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): 
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
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <div class="flex-grow-1">
                                <div class="ticket-title">
                                    #<?= $ticket['ticket_id'] ?> - <?= htmlspecialchars($ticket['title']) ?>
                                    <?php if ($ticket['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?= $ticket['unread_count'] ?> mới</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge-status badge-<?= $ticket['status'] ?>">
                                        <?= $statusMap[$ticket['status']] ?>
                                    </span>
                                    <span class="badge-priority badge-<?= $ticket['priority'] ?>">
                                        <?= $priorityMap[$ticket['priority']] ?>
                                    </span>
                                    <span class="badge bg-secondary"><?= $categoryMap[$ticket['category']] ?></span>
                                </div>
                            </div>
                            <a href="ticket-detail.php?ticket_id=<?= $ticket['ticket_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </div>
                        
                        <div class="ticket-meta">
                            <i class="fas fa-comments"></i> <?= $ticket['message_count'] ?> tin nhắn
                            <span class="ms-3">
                                <i class="fas fa-clock"></i> 
                                Cập nhật: <?= date('d/m/Y H:i', strtotime($ticket['last_message_at'] ?? $ticket['created_at'])) ?>
                            </span>
                            <span class="ms-3">
                                <i class="fas fa-calendar-plus"></i> 
                                Tạo: <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="../../../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại Trang chủ
            </a>
            <a href="create-ticket.php" class="btn btn-primary ms-2">
                <i class="fas fa-plus"></i> Tạo yêu cầu mới
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
