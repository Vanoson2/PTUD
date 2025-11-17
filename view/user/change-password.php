<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../model/mUser.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($currentPassword)) {
        $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
    }
    
    if (empty($newPassword)) {
        $errors[] = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    }
    
    if (empty($confirmPassword)) {
        $errors[] = 'Vui lòng xác nhận mật khẩu mới';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if ($currentPassword === $newPassword) {
        $errors[] = 'Mật khẩu mới phải khác mật khẩu hiện tại';
    }
    
    if (empty($errors)) {
        $mUser = new mUser();
        $result = $mUser->mChangePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
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
    <title>Đổi mật khẩu - WeGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .password-container {
            max-width: 550px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .password-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .password-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .password-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .password-header p {
            color: #6c757d;
            font-size: 15px;
            margin: 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-label .required {
            color: #dc3545;
        }
        
        .password-input-wrapper {
            position: relative;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 45px 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s;
        }
        
        .toggle-password:hover {
            color: #667eea;
        }
        
        .password-requirements {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .password-requirements h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #495057;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .btn-change {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 14px 40px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .back-link:hover {
            background: #667eea;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    
    <div class="password-container">
        <a href="../../index.php" class="back-link">
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
