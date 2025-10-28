<?php
/**
 * Model xử lý gửi email sử dụng PHPMailer
 * PHPMailer hỗ trợ tốt hơn cho Gmail SMTP với TLS/SSL
 */

// Load PHPMailer
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/SMTP.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class mEmailPHPMailer {
    
    private $fromEmail = 'noreply@wego.com';
    private $fromName = 'WeGo Travel';
    
    // Gmail SMTP settings
    private $smtpHost = 'smtp.gmail.com';
    private $smtpPort = 465;  // SSL port
    private $smtpUsername = 'Thaivanson5555@gmail.com';
    private $smtpPassword = 'bwffokkbaahhsgjd';  // App Password mới
    
    /**
     * Gửi mã xác thực 6 số qua email
     */
    public function sendVerificationCode($toEmail, $toName, $verifyCode) {
        // Kiểm tra PHPMailer có tồn tại không
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // SSL cho port 465
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Mã xác thực tài khoản WeGo';
            
            // Get email template
            $mail->Body = $this->getVerificationCodeTemplate($toName, $verifyCode);
            $mail->AltBody = "Xin chào $toName,\n\nMã xác thực của bạn là: $verifyCode\n\nMã có hiệu lực trong 15 phút.\n\nTrân trọng,\nWeGo Travel";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Template email mã xác thực 6 số
     */
    private function getVerificationCodeTemplate($userName, $verifyCode) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 40px 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 40px 30px; }
                .code-box { background: #f8f9fa; border: 2px dashed #6366f1; border-radius: 8px; padding: 30px; text-align: center; margin: 30px 0; }
                .code { font-size: 48px; font-weight: bold; color: #6366f1; letter-spacing: 8px; font-family: 'Courier New', monospace; }
                .expires { color: #ef4444; font-size: 14px; margin-top: 15px; font-weight: 500; }
                .warning { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Xác Thực Tài Khoản</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>WeGo Travel - Đi đâu cũng dễ dàng</p>
                </div>
                
                <div class='content'>
                    <h2 style='color: #1f2937; margin-top: 0;'>Xin chào " . htmlspecialchars($userName) . "! 👋</h2>
                    <p style='color: #4b5563; line-height: 1.6;'>Cảm ơn bạn đã đăng ký tài khoản WeGo. Để hoàn tất quá trình đăng ký, vui lòng nhập mã xác thực sau:</p>
                    
                    <div class='code-box'>
                        <div style='color: #6b7280; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;'>Mã Xác Thực Của Bạn</div>
                        <div class='code'>" . $verifyCode . "</div>
                        <div class='expires'>⏰ Mã có hiệu lực trong 15 phút</div>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Lưu ý:</strong> Không chia sẻ mã này với bất kỳ ai. Chúng tôi sẽ không bao giờ yêu cầu mã xác thực qua điện thoại hoặc email.
                    </div>
                    
                    <p style='color: #6b7280; font-size: 14px; margin-top: 30px;'>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
                </div>
                
                <div class='footer'>
                    <p style='margin: 5px 0;'><strong>WeGo Travel</strong></p>
                    <p style='margin: 5px 0;'>Email: support@wego.com | Hotline: 1900-xxxx</p>
                    <p style='margin: 15px 0 5px 0; color: #9ca3af; font-size: 12px;'>© " . date('Y') . " WeGo Travel. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Gửi email tùy chỉnh
     */
    public function sendEmail($toEmail, $subject, $body, $toName = '') {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>
