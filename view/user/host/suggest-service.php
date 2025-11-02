<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Kiểm tra user có phải Host không
require_once __DIR__ . '/../../../model/mHost.php';
$mHost = new mHost();
$hostInfo = $mHost->mGetHostByUserId($_SESSION['user_id']);

if (!$hostInfo) {
    header('Location: ../../../index.php');
    exit();
}

require_once __DIR__ . '/../../../controller/cSupport.php';
require_once __DIR__ . '/../../../model/mEmailPHPMailer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $serviceName = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate
    $errors = [];
    if (empty($serviceName)) {
        $errors[] = 'Vui lòng nhập tên dịch vụ';
    } elseif (mb_strlen($serviceName) < 3) {
        $errors[] = 'Tên dịch vụ phải có ít nhất 3 ký tự';
    }
    
    if (empty($description)) {
        $errors[] = 'Vui lòng nhập mô tả dịch vụ';
    } elseif (mb_strlen($description) < 10) {
        $errors[] = 'Mô tả dịch vụ phải có ít nhất 10 ký tự';
    }
    
    if (empty($errors)) {
        $title = "Đề xuất dịch vụ mới: " . $serviceName;
        $content = "**Tên dịch vụ đề xuất:** " . $serviceName . "\n\n";
        $content .= "**Mô tả chi tiết:**\n" . $description;
        
        $cSupport = new cSupport();
        $result = $cSupport->cCreateTicket($userId, $title, $content, 'de_xuat_dich_vu', 'normal');
        
        if ($result['success']) {
            // Gửi email thông báo cho admin
            $emailModel = new mEmailPHPMailer();
            $emailModel->sendSupportTicketNotification(
                $result['ticket_id'],
                $_SESSION['full_name'] ?? 'Host',
                $_SESSION['email'] ?? '',
                $title,
                $content,
                'de_xuat_dich_vu',
                'normal'
            );
            
            $message = 'Đề xuất dịch vụ của bạn đã được gửi thành công! Admin sẽ xem xét và phản hồi sớm.';
            $messageType = 'success';
            
            // Redirect to ticket detail after 3 seconds
            header("refresh:3;url=../support/ticket-detail.php?ticket_id=" . $result['ticket_id']);
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } else {
        $message = '<ul class="mb-0">';
        foreach ($errors as $error) {
            $message .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $message .= '</ul>';
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đề xuất dịch vụ mới - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .suggest-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .suggest-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .suggest-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .suggest-header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .suggest-header p {
            color: #6c757d;
            font-size: 15px;
            margin: 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 14px 40px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .info-box i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .info-box p {
            margin: 0;
            color: #495057;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .back-link {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .back-link:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    
    <div class="suggest-container">
        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="suggest-card">
            <div class="suggest-header">
                <h1><i class="fas fa-lightbulb"></i> Đề xuất dịch vụ mới</h1>
                <p>Giúp chúng tôi hoàn thiện danh sách dịch vụ cho chỗ ở của bạn</p>
            </div>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p><strong>Lưu ý:</strong> Đề xuất của bạn sẽ được gửi đến quản trị viên để xem xét. Nếu được duyệt, dịch vụ này sẽ được thêm vào danh sách và bạn có thể sử dụng cho chỗ ở của mình.</p>
            </div>
            
            <form method="POST" id="suggestForm">
                <div class="mb-4">
                    <label for="service_name" class="form-label">
                        Tên dịch vụ <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="service_name" 
                           name="service_name" 
                           placeholder="Ví dụ: Massage, Spa, Dọn phòng hàng ngày..."
                           value="<?= htmlspecialchars($_POST['service_name'] ?? '') ?>"
                           required>
                    <small class="text-muted">Nhập tên ngắn gọn, dễ hiểu (tối thiểu 3 ký tự)</small>
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label">
                        Mô tả chi tiết <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              placeholder="Mô tả chi tiết về dịch vụ này, tại sao bạn nghĩ nó cần thiết..."
                              required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <small class="text-muted">Giải thích rõ ràng về dịch vụ (tối thiểu 10 ký tự)</small>
                </div>
                
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi đề xuất
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.getElementById('suggestForm').addEventListener('submit', function(e) {
            const serviceName = document.getElementById('service_name').value.trim();
            const description = document.getElementById('description').value.trim();
            const errors = [];
            
            if (!serviceName || serviceName.length < 3) {
                errors.push('Tên dịch vụ phải có ít nhất 3 ký tự');
            }
            
            if (!description || description.length < 10) {
                errors.push('Mô tả dịch vụ phải có ít nhất 10 ký tự');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('❌ Lỗi:\n\n' + errors.map((err, idx) => (idx + 1) + '. ' + err).join('\n'));
            }
        });
    </script>
</body>
</html>
