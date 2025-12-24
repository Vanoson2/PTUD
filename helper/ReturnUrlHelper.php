<?php
// Helper functions xử lý return URL sau khi đăng nhập
// Bảo mật chống tấn công open redirect

class ReturnUrlHelper {
    
    // Thời gian timeout cho return URL (giây)
    const TIMEOUT = 1800; // 30 phút
    
    // Kiểm tra xem return URL có an toàn để redirect không
    // Ngăn chặn lỗ hổng open redirect
    // string $url - URL cần kiểm tra
    // return bool - True nếu URL an toàn, false nếu không
    public static function isValidReturnUrl($url) {
        if (empty($url)) {
            return false;
        }
        
        try {
            // Parse the URL
            $parsed = parse_url($url);
            
            if ($parsed === false) {
                return false;
            }
            
            // Get current host
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            $currentScheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            
            // If URL has a host, it must match current host
            if (isset($parsed['host'])) {
                if ($parsed['host'] !== $currentHost) {
                    return false; // Different host - not allowed
                }
                
                // Check scheme
                if (isset($parsed['scheme']) && $parsed['scheme'] !== $currentScheme) {
                    return false; // Different scheme - not allowed
                }
            }
            
            // Check for javascript: or data: URLs
            if (isset($parsed['scheme'])) {
                $scheme = strtolower($parsed['scheme']);
                if (in_array($scheme, ['javascript', 'data', 'vbscript', 'file'])) {
                    return false;
                }
            }
            
            // URL is safe
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Lưu return URL vào session kèm timestamp
    // string $url - URL cần lưu
    public static function storeReturnUrl($url) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (self::isValidReturnUrl($url)) {
            $_SESSION['return_url'] = [
                'url' => $url,
                'timestamp' => time()
            ];
        }
    }
    
    // Lấy return URL đã lưu nếu còn hợp lệ và chưa hết hạn
    // Tự động xóa URL sau khi lấy
    // return string|null - Return URL hoặc null nếu không hợp lệ/hết hạn
    public static function getAndClearReturnUrl() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['return_url'])) {
            return null;
        }
        
        $data = $_SESSION['return_url'];
        
        // Clear it immediately (one-time use)
        unset($_SESSION['return_url']);
        
        // Check if expired
        if (!isset($data['timestamp']) || (time() - $data['timestamp']) > self::TIMEOUT) {
            return null; // Expired
        }
        
        // Validate URL again
        if (!isset($data['url']) || !self::isValidReturnUrl($data['url'])) {
            return null;
        }
        
        return $data['url'];
    }
    
    // Xóa return URL đã lưu
    public static function clearReturnUrl() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['return_url'])) {
            unset($_SESSION['return_url']);
        }
    }
}
