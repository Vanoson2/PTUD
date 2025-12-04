<?php
// Include Authentication Helper and Controller
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';

// Use helper for authentication
requireLogin();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    // Additional validation in View (UI level)
    if ($currentPassword === $newPassword && !empty($currentPassword)) {
        $message = 'Mật khẩu mới phải khác mật khẩu hiện tại';
        $messageType = 'danger';
    } else {
        // Delegate to Controller (handles all validation and business logic)
        $cUser = new cUser();
        $result = $cUser->cChangePassword(getCurrentUserId(), $currentPassword, $newPassword, $confirmPassword);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/traveller-change-password.css">
</head>
<body>
    
    <div class="password-container">
        <a href="../../../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
        </a>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="password-card">
            <div class="password-header">
                <i class="fas fa-lock"></i>
                <h1>Đổi mật khẩu</h1>
                <p>Cập nhật mật khẩu để bảo mật tài khoản của bạn</p>
            </div>
            
            <form method="POST" id="changePasswordForm">
                <div class="mb-4">
                    <label for="current_password" class="form-label">
                        Mật khẩu hiện tại <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="Nhập mật khẩu hiện tại"
                               required>
                        <i class="fas fa-eye toggle-password" data-target="current_password"></i>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="new_password" class="form-label">
                        Mật khẩu mới <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Nhập mật khẩu mới"
                               required>
                        <i class="fas fa-eye toggle-password" data-target="new_password"></i>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        Xác nhận mật khẩu mới <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Nhập lại mật khẩu mới"
                               required>
                        <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                    </div>
                </div>
                
                <div class="password-requirements">
                    <h6><i class="fas fa-info-circle"></i> Yêu cầu mật khẩu:</h6>
                    <ul>
                        <li>Tối thiểu 6 ký tự</li>
                        <li>Khác mật khẩu hiện tại</li>
                        <li>Nên bao gồm chữ hoa, chữ thường và số</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-change">
                    <i class="fas fa-check"></i> Đổi mật khẩu
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });
        
        // Client-side validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            const errors = [];
            
            if (!currentPassword) {
                errors.push('Vui lòng nhập mật khẩu hiện tại');
            }
            
            if (!newPassword) {
                errors.push('Vui lòng nhập mật khẩu mới');
            } else if (newPassword.length < 6) {
                errors.push('Mật khẩu mới phải có ít nhất 6 ký tự');
            }
            
            if (!confirmPassword) {
                errors.push('Vui lòng xác nhận mật khẩu mới');
            } else if (newPassword !== confirmPassword) {
                errors.push('Mật khẩu xác nhận không khớp');
            }
            
            if (currentPassword === newPassword) {
                errors.push('Mật khẩu mới phải khác mật khẩu hiện tại');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('❌ Lỗi:\n\n' + errors.map((err, idx) => (idx + 1) + '. ' + err).join('\n'));
            }
        });
    </script>
</body>
</html>
