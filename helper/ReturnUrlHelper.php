<?php
/**
 * Helper functions for handling return URL after login
 * Provides security validation against open redirect attacks
 */

class ReturnUrlHelper {
    
    // Timeout for return URL (in seconds)
    const TIMEOUT = 1800; // 30 minutes
    
    /**
     * Validate if a return URL is safe to redirect to
     * Prevents open redirect vulnerabilities
     * 
     * @param string $url The URL to validate
     * @return bool True if URL is safe, false otherwise
     */
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
    
    /**
     * Store return URL in session with timestamp
     * 
     * @param string $url The URL to store
     */
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
    
    /**
     * Get stored return URL if valid and not expired
     * Automatically clears the stored URL after retrieval
     * 
     * @return string|null The return URL or null if invalid/expired
     */
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
    
    /**
     * Clear stored return URL
     */
    public static function clearReturnUrl() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['return_url'])) {
            unset($_SESSION['return_url']);
        }
    }
}
