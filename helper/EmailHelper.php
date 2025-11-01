<?php
/**
 * Email Helper Class
 * 
 * Quản lý việc gửi email xác thực và các loại email khác
 * 
 * Requirements: PHPMailer (install via Composer)
 * Run: composer require phpmailer/phpmailer
 */

// Load Composer autoload (PHPMailer will be autoloaded)
require_once __DIR__ . '/../vendor/autoload.php';  // Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $config;
    private $mail;
    
    public function __construct() {
        // Load email configuration
        $this->config = require __DIR__ . '/../config/email.php';
        
        // Initialize PHPMailer
        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    /**
     * Cấu hình SMTP
     */
    private function setupSMTP() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = $this->config['smtp_host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->config['smtp_username'];
            $this->mail->Password   = $this->config['smtp_password'];
            $this->mail->SMTPSecure = $this->config['smtp_secure'];
            $this->mail->Port       = $this->config['smtp_port'];
            $this->mail->CharSet    = $this->config['charset'];
            $this->mail->SMTPDebug  = $this->config['debug'];
            
            // From address
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
        } catch (Exception $e) {
            error_log("Email Setup Error: " . $e->getMessage());
        }
    }
    
    /**
     * Gửi email xác thực với mã 6 số
     * 
     * @param string $toEmail Email người nhận
     * @param string $toName Tên người nhận
     * @param string $verifyCode Mã xác thực 6 số
     * @return bool True nếu gửi thành công
     */
    public function sendVerificationCode($toEmail, $toName, $verifyCode) {
        try {
            // Clear any previous recipients
            $this->mail->clearAddresses();
            
            // Recipient
            $this->mail->addAddress($toEmail, $toName);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Mã xác thực tài khoản WeGo';
            $this->mail->Body    = $this->getVerificationCodeEmailTemplate($toName, $verifyCode);
            $this->mail->AltBody = "Xin chào $toName,\n\nMã xác thực của bạn là: $verifyCode\n\nMã này sẽ hết hạn sau 15 phút.\n\nNếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email này.\n\nTrân trọng,\nĐội ngũ WeGo";
            
            // Send email
            $result = $this->mail->send();
            
            if ($result) {
                error_log("Verification email sent successfully to: " . $toEmail);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Email Send Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Template HTML cho email xác thực với mã 6 số
     */
    private function getVerificationCodeEmailTemplate($name, $code) {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .code-box { 
            background: #fff; 
            border: 2px dashed #667eea; 
            padding: 20px; 
            text-align: center; 
            margin: 30px 0; 
            border-radius: 10px; 
        }
        .code { 
            font-size: 36px; 
            font-weight: bold; 
            color: #667eea; 
            letter-spacing: 10px; 
            font-family: "Courier New", monospace; 
        }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏡 WeGo</h1>
            <p>Đặt phòng du lịch dễ dàng</p>
        </div>
        <div class="content">
            <h2>Xin chào ' . htmlspecialchars($name) . '!</h2>
            <p>Cảm ơn bạn đã đăng ký tài khoản tại WeGo.</p>
            <p>Để hoàn tất đăng ký, vui lòng nhập mã xác thực bên dưới:</p>
            <div class="code-box">
                <p style="margin: 0; color: #666; font-size: 14px;">MÃ XÁC THỰC CỦA BẠN</p>
                <div class="code">' . htmlspecialchars($code) . '</div>
                <p style="margin: 10px 0 0 0; color: #999; font-size: 12px;">Mã này sẽ hết hạn sau 15 phút</p>
            </div>
            <p><strong>Lưu ý:</strong></p>
            <ul>
                <li>Mã xác thực chỉ có hiệu lực trong 15 phút</li>
                <li>Không chia sẻ mã này với bất kỳ ai</li>
                <li>Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email</li>
            </ul>
        </div>
        <div class="footer">
            <p>© 2025 WeGo. All rights reserved.</p>
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Gửi email reset password (dự phòng cho tương lai)
     */
    public function sendPasswordResetEmail($toEmail, $toName, $resetToken) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            
            $resetLink = 'http://localhost/PTUD/index.php?action=reset-password&token=' . $resetToken;
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Đặt lại mật khẩu WeGo';
            $this->mail->Body    = "
                <h2>Xin chào $toName!</h2>
                <p>Bạn đã yêu cầu đặt lại mật khẩu. Nhấn vào link bên dưới để tiếp tục:</p>
                <a href='$resetLink'>Đặt lại mật khẩu</a>
                <p>Link này sẽ hết hạn sau 1 giờ.</p>
            ";
            
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Password reset email error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
