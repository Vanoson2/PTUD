<?php
// Encryption Helper cho dữ liệu nhạy cảm
// Sử dụng mã hóa AES-256-CBC
class EncryptionHelper {
    
    // QUAN TRỌNG: Đổi key này trong production! Lưu trong environment variable
    private static $encryptionKey = 'WeGo2025SecureKeyChangeThis!!32'; // Phải 32 ký tự cho AES-256
    private static $cipher = 'AES-256-CBC';
    
    // Mã hóa dữ liệu nhạy cảm
    // string $data - Dữ liệu cần mã hóa
    // return string - Dữ liệu đã mã hóa (base64 encoded)
    public static function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // Generate random IV (Initialization Vector)
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        // Encrypt the data
        $encrypted = openssl_encrypt(
            $data,
            self::$cipher,
            self::$encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV + encrypted data and encode to base64
        $result = base64_encode($iv . $encrypted);
        
        return $result;
    }
    
    // Giải mã dữ liệu nhạy cảm
    // string $encryptedData - Dữ liệu đã mã hóa (base64 encoded)
    // return string - Dữ liệu đã giải mã
    public static function decrypt($encryptedData) {
        if (empty($encryptedData)) {
            return '';
        }
        
        // Decode from base64
        $data = base64_decode($encryptedData);
        
        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        // Decrypt the data
        $decrypted = openssl_decrypt(
            $encrypted,
            self::$cipher,
            self::$encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $decrypted;
    }
    
    // Che dữ liệu nhạy cảm để hiển thị (show 4 ký tự cuối)
    // string $data - Dữ liệu cần che
    // int $visibleChars - Số ký tự hiển thị ở cuối
    // return string - Dữ liệu đã che
    public static function mask($data, $visibleChars = 4) {
        if (empty($data)) {
            return '';
        }
        
        $length = strlen($data);
        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }
        
        $masked = str_repeat('*', $length - $visibleChars) . substr($data, -$visibleChars);
        return $masked;
    }
    
    // Tạo SHA-256 hash cho các field có thể search
    // Dùng cho phone_hash, tax_code_hash để query trên dữ liệu đã mã hóa
    // string $data - Dữ liệu cần hash
    // return string|null - SHA-256 hash hoặc null nếu data trống
    public static function generateHash($data) {
        if (empty($data)) {
            return null;
        }
        return hash('sha256', $data);
    }
}
?>
