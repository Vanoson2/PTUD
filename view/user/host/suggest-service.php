<?php
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cSupport.php';

requireLogin();
requireHost();
$userId = getCurrentUserId();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceName = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $cSupport = new cSupport();
    $result = $cSupport->cSuggestService($userId, $serviceName, $description);
    
    if ($result['success']) {
        $message = 'Cảm ơn bạn đã đề xuất dịch vụ mới! Chúng tôi sẽ xem xét và phản hồi sớm nhất.';
        $messageType = 'success';
        // Clear form
        $serviceName = '';
        $description = '';
    } else {
        $message = $result['message'];
        $messageType = 'error';
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
    <link rel="stylesheet" href="../../css/host-suggest-service.css">
</head>
<body>
    
    <div class="suggest-container">
        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
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
