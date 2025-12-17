<?php
/**
 * Model xử lý gửi email sử dụng PHPMailer
 * PHPMailer hỗ trợ tốt hơn cho Gmail SMTP với TLS/SSL
 */

// Load PHPMailer through autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class mEmailPHPMailer {
    
    private $config;
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;
    
    /**
     * Get base URL dynamically for email links
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    public function __construct() {
        // Load email configuration
        $this->config = require __DIR__ . '/../config/email.php';
        
        // Set properties from config
        $this->fromEmail = $this->config['from_email'];
        $this->fromName = $this->config['from_name'];
        $this->smtpHost = $this->config['smtp_host'];
        $this->smtpPort = $this->config['smtp_port'];
        $this->smtpUsername = $this->config['smtp_username'];
        $this->smtpPassword = $this->config['smtp_password'];
        $this->smtpSecure = $this->config['smtp_secure'];
    }
    
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
            
            // Set encryption based on config
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = $this->config['charset'];
            
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
            
            // Set encryption based on config
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = $this->config['charset'];
            
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
    
    /**
     * Gửi email thông báo ticket mới cho admin
     */
    public function sendSupportTicketNotification($ticketId, $userName, $userEmail, $title, $content, $category, $priority) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = $this->smtpPort;
            $mail->CharSet = $this->config['charset'];
            
            // Recipients - Gửi đến admin email
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($this->fromEmail, 'WeGo Admin'); // Admin nhận email
            $mail->addReplyTo($userEmail, $userName); // Reply về user
            
            // Định nghĩa category và priority
            $categoryMap = [
                'dat_phong' => 'Đặt phòng',
                'tai_khoan' => 'Tài khoản',
                'nha_cung_cap' => 'Nhà cung cấp',
                'khac' => 'Khác'
            ];
            $priorityMap = [
                'normal' => 'Bình thường',
                'high' => 'Cao',
                'urgent' => 'Khẩn cấp'
            ];
            
            $categoryText = $categoryMap[$category] ?? 'Khác';
            $priorityText = $priorityMap[$priority] ?? 'Bình thường';
            $priorityColor = $priority === 'urgent' ? '#dc3545' : ($priority === 'high' ? '#fd7e14' : '#28a745');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "[Hỗ trợ #{$ticketId}] $title";
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                    .label { font-weight: bold; color: #667eea; }
                    .priority { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; font-weight: bold; }
                    .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎫 Yêu cầu hỗ trợ mới</h1>
                        <p>Ticket #$ticketId</p>
                    </div>
                    <div class='content'>
                        <div class='info-box'>
                            <p><span class='label'>Từ:</span> $userName ($userEmail)</p>
                            <p><span class='label'>Danh mục:</span> $categoryText</p>
                            <p><span class='label'>Độ ưu tiên:</span> <span class='priority' style='background: $priorityColor;'>$priorityText</span></p>
                            <p><span class='label'>Tiêu đề:</span> $title</p>
                        </div>
                        
                        <div class='info-box'>
                            <p><span class='label'>Nội dung:</span></p>
                            <p>" . nl2br(htmlspecialchars($content)) . "</p>
                        </div>
                        
                        <a href='" . $this->getBaseUrl() . "/view/user/admin/support.php?ticket_id=$ticketId' class='btn'>Xem & Trả lời</a>
                    </div>
                    <div class='footer'>
                        <p>Email này được gửi tự động từ hệ thống WeGo Travel</p>
                    </div>
                </div>
            </body>
            </html>";
            
            $mail->AltBody = "Yêu cầu hỗ trợ mới #$ticketId\n\nTừ: $userName ($userEmail)\nDanh mục: $categoryText\nĐộ ưu tiên: $priorityText\nTiêu đề: $title\n\nNội dung:\n$content";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Gửi email xác nhận ticket cho khách vãng lai
     */
    public function sendGuestTicketConfirmation($guestEmail, $guestName, $ticketId, $title, $content) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = $this->smtpPort;
            $mail->CharSet = $this->config['charset'];
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($guestEmail, $guestName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "[WeGo] Xác nhận yêu cầu hỗ trợ #$ticketId";
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                    .ticket-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                    .ticket-id { font-size: 24px; font-weight: bold; color: #667eea; }
                    .info-row { margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
                    .info-label { font-weight: bold; color: #6c757d; display: inline-block; width: 100px; }
                    .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef; color: #6c757d; font-size: 14px; }
                    .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>✓ Yêu Cầu Đã Nhận</h1>
                        <p style='margin: 10px 0 0 0; opacity: 0.9;'>Cảm ơn bạn đã liên hệ với WeGo</p>
                    </div>
                    
                    <div class='content'>
                        <p>Xin chào <strong>{$guestName}</strong>,</p>
                        
                        <p>Chúng tôi đã nhận được yêu cầu hỗ trợ của bạn và sẽ phản hồi trong thời gian sớm nhất.</p>
                        
                        <div class='ticket-box'>
                            <div class='ticket-id'>Mã yêu cầu: #{$ticketId}</div>
                            <div class='info-row'>
                                <span class='info-label'>Tiêu đề:</span>
                                <span>" . htmlspecialchars($title) . "</span>
                            </div>
                            <div class='info-row'>
                                <span class='info-label'>Nội dung:</span>
                                <div style='margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 4px;'>
                                    " . nl2br(htmlspecialchars($content)) . "
                                </div>
                            </div>
                        </div>
                        
                        <div class='note'>
                            <strong>📧 Lưu ý:</strong> Chúng tôi sẽ phản hồi qua email này trong vòng <strong>24-48 giờ</strong>. 
                            Vui lòng kiểm tra cả hộp thư spam/junk.
                        </div>
                        
                        <p>Nếu cần hỗ trợ khẩn cấp, vui lòng liên hệ:</p>
                        <ul>
                            <li>📞 Hotline: 1900-xxxx (8:00 - 22:00 hàng ngày)</li>
                            <li>📧 Email: support@wego.vn</li>
                        </ul>
                        
                        <div class='footer'>
                            <p><strong>WeGo - Nền tảng chia sẻ chỗ ở</strong></p>
                            <p>Email này được gửi tự động, vui lòng không reply.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->AltBody = "Xác nhận yêu cầu hỗ trợ #$ticketId\n\nXin chào $guestName,\n\nChúng tôi đã nhận được yêu cầu hỗ trợ của bạn:\n\nTiêu đề: $title\nNội dung: $content\n\nChúng tôi sẽ phản hồi trong vòng 24-48 giờ qua email này.\n\nTrân trọng,\nWeGo Support Team";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Gửi email trả lời ticket cho user
     */
    public function sendSupportReply($toEmail, $toName, $ticketId, $title, $replyContent, $adminName) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = $this->smtpPort;
            $mail->CharSet = $this->config['charset'];
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "[Trả lời #$ticketId] $title";
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                    .label { font-weight: bold; color: #667eea; }
                    .reply { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>💬 Có phản hồi mới từ WeGo</h1>
                        <p>Ticket #$ticketId</p>
                    </div>
                    <div class='content'>
                        <p>Xin chào <strong>$toName</strong>,</p>
                        <p>Chúng tôi đã phản hồi yêu cầu hỗ trợ của bạn:</p>
                        
                        <div class='info-box'>
                            <p><span class='label'>Tiêu đề:</span> $title</p>
                            <p><span class='label'>Người trả lời:</span> $adminName</p>
                        </div>
                        
                        <div class='reply'>
                            <p><strong>Nội dung phản hồi:</strong></p>
                            <p>" . nl2br(htmlspecialchars($replyContent)) . "</p>
                        </div>
                        
                        <p>Bạn có thể tiếp tục trao đổi bằng cách trả lời tin nhắn này hoặc truy cập:</p>
                        <a href='" . $this->getBaseUrl() . "/view/user/support/ticket-detail.php?ticket_id=$ticketId' class='btn'>Xem chi tiết</a>
                    </div>
                    <div class='footer'>
                        <p>Cảm ơn bạn đã sử dụng dịch vụ WeGo Travel!</p>
                    </div>
                </div>
            </body>
            </html>";
            
            $mail->AltBody = "Có phản hồi mới từ WeGo\n\nTicket #$ticketId: $title\nNgười trả lời: $adminName\n\nNội dung:\n$replyContent";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>
