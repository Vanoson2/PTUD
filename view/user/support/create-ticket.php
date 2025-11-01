<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../../controller/cSupport.php';
require_once __DIR__ . '/../../../model/mEmailPHPMailer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'khac';
    $priority = $_POST['priority'] ?? 'normal';
    
    $cSupport = new cSupport();
    $result = $cSupport->cCreateTicket($userId, $title, $content, $category, $priority);
    
    if ($result['success']) {
        // Gửi email thông báo cho admin
        $emailModel = new mEmailPHPMailer();
        $emailModel->sendSupportTicketNotification(
            $result['ticket_id'],
            $_SESSION['full_name'] ?? 'User',
            $_SESSION['email'] ?? '',
            $title,
            $content,
            $category,
            $priority
        );
        
        $message = $result['message'];
        $messageType = 'success';
        
        // Redirect to ticket detail after 2 seconds
        header("refresh:2;url=ticket-detail.php?ticket_id=" . $result['ticket_id']);
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo yêu cầu hỗ trợ - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .support-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
        }
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-section h1 {
            color: #667eea;
            font-weight: bold;
        }
        .header-section p {
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    
    <div class="support-container">
        <div style="margin-bottom: 20px;">
            <a href="../../../index.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
            </a>
            <a href="my-tickets.php" class="btn btn-light ms-2">
                <i class="fas fa-list"></i> Xem yêu cầu của tôi
            </a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <div class="header-section">
                <h1><i class="fas fa-headset"></i> Tạo yêu cầu hỗ trợ</h1>
                <p>Chúng tôi sẵn sàng giúp bạn! Vui lòng mô tả vấn đề của bạn.</p>
            </div>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required minlength="5" 
                           placeholder="Mô tả ngắn gọn vấn đề của bạn">
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Danh mục</label>
                        <select class="form-select" id="category" name="category">
                            <option value="khac">Khác</option>
                            <option value="dat_phong">Đặt phòng</option>
                            <option value="tai_khoan">Tài khoản</option>
                            <option value="nha_cung_cap">Nhà cung cấp</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Độ ưu tiên</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="normal">Bình thường</option>
                            <option value="high">Cao</option>
                            <option value="urgent">Khẩn cấp</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung chi tiết <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="6" required minlength="10"
                              placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..."></textarea>
                    <div class="form-text">Tối thiểu 10 ký tự</div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                    <a href="my-tickets.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Danh sách yêu cầu
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
