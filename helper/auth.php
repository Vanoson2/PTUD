<?php
/**
 * Authentication Helper
 * Centralized authentication logic to avoid duplication in View files
 * Following MVC pattern: Helper provides utilities but doesn't contain business logic
 */

/**
 * Start session if not already started
 */
function ensureSessionStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    ensureSessionStarted();
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in, redirect to login if not
 * @param string|null $returnUrl URL to return to after login
 */
function requireLogin($returnUrl = null) {
    ensureSessionStarted();
    
    if (!isset($_SESSION['user_id'])) {
        if ($returnUrl === null) {
            $returnUrl = $_SERVER['REQUEST_URI'];
        }
        header('Location: /view/user/traveller/login.php?returnUrl=' . urlencode($returnUrl));
        exit;
    }
}

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    ensureSessionStarted();
    return isset($_SESSION['admin_id']);
}

/**
 * Require admin to be logged in, redirect to admin login if not
 */
function requireAdminLogin() {
    ensureSessionStarted();
    
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /view/user/admin/login.php');
        exit;
    }
}

/**
 * Check if user has specific admin role
 * @param string|array $allowedRoles Single role or array of allowed roles
 * @return bool
 */
function hasAdminRole($allowedRoles) {
    ensureSessionStarted();
    
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }
    
    $currentRole = $_SESSION['admin_role'];
    
    if (is_array($allowedRoles)) {
        return in_array($currentRole, $allowedRoles);
    }
    
    return $currentRole === $allowedRoles;
}

/**
 * Require specific admin role, redirect if not authorized
 * @param string|array $allowedRoles
 */
function requireAdminRole($allowedRoles) {
    requireAdminLogin();
    
    if (!hasAdminRole($allowedRoles)) {
        header('Location: /view/user/admin/dashboard.php?error=unauthorized');
        exit;
    }
}

/**
 * Check if user is a host
 * @return bool
 */
function isHost() {
    ensureSessionStarted();
    return isset($_SESSION['is_host']) && $_SESSION['is_host'] === true;
}

/**
 * Require user to be a host
 */
function requireHost() {
    requireLogin();
    
    // Check session first
    if (isHost()) {
        return;
    }
    
    // Session not set, check database
    $userId = getCurrentUserId();
    if ($userId) {
        require_once __DIR__ . '/../controller/cHost.php';
        require_once __DIR__ . '/../model/mHost.php';
        
        $cHost = new cHost();
        $mHost = new mHost();
        
        // Check if has host record in database
        $hostInfo = $mHost->mGetHostByUserId($userId);
        
        if ($hostInfo) {
            // Has host record, update session
            $_SESSION['is_host'] = true;
            return;
        }
        
        // No host record, check if isUserHost returns true (handles pending status)
        if ($cHost->cIsUserHost($userId)) {
            $_SESSION['is_host'] = true;
            return;
        }
        
        // Still no host record? Try to create one automatically
        // This handles users who registered before the fix
        require_once __DIR__ . '/../controller/cUser.php';
        $cUser = new cUser();
        $userProfile = $cUser->cGetUserProfile($userId);
        
        if ($userProfile) {
            // Create pending host record automatically
            $created = $mHost->mCreatePendingHost($userId, $userProfile['full_name'], '');
            
            if ($created) {
                // Set session and allow access
                $_SESSION['is_host'] = true;
                return;
            }
        }
    }
    
    // Not a host, redirect
    header('Location: /view/user/host/become-host.php');
    exit;
}

/**
 * Get current logged in user ID
 * @return int|null
 */
function getCurrentUserId() {
    ensureSessionStarted();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in admin ID
 * @return int|null
 */
function getCurrentAdminId() {
    ensureSessionStarted();
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Logout user
 */
function logoutUser() {
    ensureSessionStarted();
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Delete remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

/**
 * Logout admin
 */
function logoutAdmin() {
    ensureSessionStarted();
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user's email is verified
 * @return bool
 */
function isEmailVerified() {
    ensureSessionStarted();
    return isset($_SESSION['email_verified']) && $_SESSION['email_verified'] === true;
}

/**
 * Set flash message in session
 * @param string $message
 * @param string $type success|error|warning|info
 */
function setFlashMessage($message, $type = 'info') {
    ensureSessionStarted();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * @return array|null ['message' => string, 'type' => string]
 */
function getFlashMessage() {
    ensureSessionStarted();
    
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    
    return null;
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    ensureSessionStarted();
    session_regenerate_id(true);
}
