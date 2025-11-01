<?php
/**
 * Test Booking Email
 */

require_once __DIR__ . '/model/mEmailPHPMailer.php';

$mailer = new mEmailPHPMailer();

$subject = "✅ Đặt chỗ thành công - Mã đơn #TEST123";
$body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
</head>
<body style='font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0;'>
    <div style='max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
        <div style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px 30px; text-align: center;'>
            <h1 style='margin: 0; font-size: 28px;'>🎉 Đặt Chỗ Thành Công!</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>Chuyến đi của bạn đã được xác nhận</p>
        </div>
        <div style='padding: 40px 30px;'>
            <h2 style='color: #1f2937; margin-top: 0;'>Xin chào Test User! 👋</h2>
            <p style='color: #4b5563; line-height: 1.6;'>Đây là email test. Nếu bạn nhận được email này, nghĩa là hệ thống gửi email đã hoạt động!</p>
            
            <div style='background: #f0fdf4; border: 2px dashed #10b981; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;'>
                <div style='font-size: 32px; font-weight: bold; color: #10b981; letter-spacing: 2px; font-family: monospace;'>TEST123</div>
            </div>
            
            <p style='color: #6b7280; font-size: 14px; margin-top: 30px; text-align: center;'>Email test thành công! ✅</p>
        </div>
        <div style='background: #f8f9fa; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;'>
            <p style='margin: 5px 0;'><strong>WeGo Travel</strong></p>
            <p style='margin: 15px 0 5px 0; color: #9ca3af; font-size: 12px;'>© " . date('Y') . " WeGo Travel. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

$result = $mailer->sendEmail(
    'your-email@gmail.com',  // ← Thay bằng email của bạn
    $subject,
    $body,
    'Test User'
);

if ($result) {
    echo "✅ Email đã được gửi thành công!<br>";
    echo "Kiểm tra hộp thư của bạn (hoặc spam).";
} else {
    echo "❌ Lỗi khi gửi email.<br>";
    echo "Kiểm tra lại cấu hình trong config/email.php";
}
?>
