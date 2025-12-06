<?php
/**
 * MoMo Payment Configuration
 * 
 * Để sử dụng môi trường PRODUCTION, cần:
 * 1. Đăng ký tài khoản MoMo Business tại: https://business.momo.vn
 * 2. Lấy thông tin partnerCode, accessKey, secretKey từ dashboard
 * 3. Thay đổi MOMO_ENVIRONMENT thành 'production'
 * 4. Cập nhật endpoint thành URL production
 */

// Môi trường (test hoặc production)
define('MOMO_ENVIRONMENT', 'test'); // 'test' hoặc 'production'

// Thông tin MoMo Test (sandbox)
// ⚠️ CHỈ dùng cho môi trường TEST - KHÔNG dùng cho PRODUCTION
define('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
define('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
define('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

// Endpoint
if (MOMO_ENVIRONMENT === 'production') {
    // Production endpoint - Chỉ dùng khi đã có tài khoản Business
    define('MOMO_ENDPOINT', 'https://payment.momo.vn/v2/gateway/api/create');
} else {
    // Test endpoint
    define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
}

// URL callback (IPN - Instant Payment Notification)
// URL này MoMo sẽ gọi để thông báo kết quả thanh toán
// ⚠️ QUAN TRỌNG: URL này phải:
// 1. Là URL công khai (có thể truy cập từ internet)
// 2. Sử dụng HTTPS trong production
// 3. Không chứa localhost (dùng ngrok hoặc deploy lên server)
$baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$baseUrl = rtrim($baseUrl, '/');

// Trong môi trường development, bạn có thể sử dụng ngrok
// Ví dụ: https://abc123.ngrok.io
// Hoặc webhook.site để test: https://webhook.site/unique-id
define('MOMO_IPN_URL', $baseUrl . '/payment/momo-ipn.php');

// URL redirect sau khi thanh toán
// Người dùng sẽ được chuyển về URL này sau khi thanh toán
define('MOMO_RETURN_URL', $baseUrl . '/payment/momo-return.php');

// Cấu hình request
define('MOMO_REQUEST_TYPE', 'captureWallet'); // captureWallet hoặc payWithATM
define('MOMO_PARTNER_NAME', 'WeGo Travel');
define('MOMO_STORE_ID', 'WeGoStore');
define('MOMO_LANG', 'vi'); 

// Timeout (giây)
define('MOMO_TIMEOUT', 30);
define('MOMO_CONNECT_TIMEOUT', 30);

// Log config (cho debug - chỉ enable trong test environment)
define('MOMO_ENABLE_LOG', MOMO_ENVIRONMENT === 'test');
define('MOMO_LOG_PATH', __DIR__ . '/../logs/momo_payment.log');

/**
 * Ghi log thanh toán MoMo
 */
function logMoMoPayment($message, $data = null) {
    if (!MOMO_ENABLE_LOG) return;
    
    $logDir = dirname(MOMO_LOG_PATH);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= "\nData: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    
    file_put_contents(MOMO_LOG_PATH, $logMessage, FILE_APPEND);
}

/**
 * HƯỚNG DẪN SỬ DỤNG MoMo Test Account
 * 
 * Để test thanh toán MoMo trong môi trường sandbox:
 * 
 * 1. Tải app MoMo (iOS hoặc Android)
 * 2. Đăng ký tài khoản MoMo bình thường
 * 3. Trong app MoMo:
 *    - Chọn "Nạp tiền" 
 *    - Chọn "Chuyển khoản ngân hàng"
 *    - Chọn bất kỳ ngân hàng nào
 *    - Nhập số tiền: 10,000 VND
 *    - Xác nhận (không cần thực sự chuyển tiền)
 * 4. Tài khoản của bạn sẽ có 10,000 VND ảo để test
 * 
 * Hoặc sử dụng thông tin test của MoMo:
 * - Số điện thoại: 0963181714
 * - OTP: Bất kỳ (trong môi trường test)
 * 
 * ⚠️ LƯU Ý KHI CHUYỂN SANG PRODUCTION:
 * 1. Đăng ký tài khoản Business tại https://business.momo.vn
 * 2. Hoàn tất xác minh doanh nghiệp
 * 3. Lấy credentials từ MoMo Business Portal
 * 4. Cập nhật constants ở trên
 * 5. Đảm bảo IPN_URL và RETURN_URL là HTTPS và public
 */

?>
