<?php
/**
 * Encryption Helper for sensitive data
 * Uses AES-256-CBC encryption
 */
class EncryptionHelper {
    
    // IMPORTANT: Change this key in production! Store in environment variable
    private static $encryptionKey = 'WeGo2025SecureKeyChangeThis!!32'; // Must be 32 chars for AES-256
    private static $cipher = 'AES-256-CBC';
    
    /**
     * Encrypt sensitive data
     * @param string $data Data to encrypt
     * @return string Encrypted data (base64 encoded)
     */
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
    
    /**
     * Decrypt sensitive data
     * @param string $encryptedData Encrypted data (base64 encoded)
     * @return string Decrypted data
     */
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
    
    /**
     * Mask sensitive data for display (show last 4 chars)
     * @param string $data Data to mask
     * @param int $visibleChars Number of chars to show at end
     * @return string Masked data
     */
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
    
    /**
     * Generate SHA-256 hash for searchable fields
     * Used for phone_hash, tax_code_hash to enable queries on encrypted data
     * @param string $data Data to hash
     * @return string|null SHA-256 hash or null if data is empty
     */
    public static function generateHash($data) {
        if (empty($data)) {
            return null;
        }
        return hash('sha256', $data);
    }
}
?>
