<?php
/**
 * Email Configuration
 * 
 * Cấu hình SMTP cho việc gửi email xác thực
 */

return [
    // SMTP Settings
    'smtp_host' => 'smtp.gmail.com',        // Gmail SMTP server
    'smtp_port' => 587,                     // TLS port
    'smtp_secure' => 'tls',                 // tls or ssl
    
    // Authentication
    'smtp_username' => 'thaivanson5555@gmail.com',  // Thay bằng email của bạn
    'smtp_password' => 'qhps iupt ihhk clfz',     // App password từ Google
    
    // Sender Information
    'from_email' => 'thaivanson5555@gmail.com',     // Email người gửi
    'from_name' => 'WeGo - Đặt phòng du lịch',  // Tên người gửi
    
    // Email Settings
    'charset' => 'UTF-8',
    'debug' => 0,  // 0 = off, 1 = client, 2 = server and client, 3 = connection, 4 = low-level
];

/*
 * HƯỚNG DẪN TẠO APP PASSWORD CHO GMAIL:
 * 
 * 1. Vào Google Account: https://myaccount.google.com/
 * 2. Chọn "Security" → "2-Step Verification" (bật nếu chưa có)
 * 3. Sau khi bật 2-Step, quay lại Security → "App passwords"
 * 4. Chọn "Select app" → "Mail" và "Select device" → "Other"
 * 5. Nhập tên (vd: "XAMPP WeGo") và nhấn "Generate"
 * 6. Copy mật khẩu 16 ký tự và dán vào 'smtp_password' ở trên
 * 
 * LƯU Ý: Không dùng mật khẩu Gmail thường, phải dùng App Password!
 */
