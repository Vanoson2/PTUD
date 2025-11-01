<?php
/**
 * Composer Autoloader for PHPMailer
 * 
 * File này tự động load các class PHPMailer khi cần
 */

spl_autoload_register(function ($class) {
    // Chỉ load các class PHPMailer
    if (strpos($class, 'PHPMailer\\PHPMailer\\') === 0) {
        // Remove namespace prefix
        $class = str_replace('PHPMailer\\PHPMailer\\', '', $class);
        
        // Build file path
        $file = __DIR__ . '/phpmailer/phpmailer/' . $class . '.php';
        
        // Include if exists
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});
