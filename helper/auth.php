<?php
// Authentication Helper
// Logic xác thực tập trung để tránh trùng lặp trong View files
// Theo pattern MVC: Helper cung cấp utilities nhưng không chứa business logic

// Khởi động session nếu chưa được khởi động
function ensureSessionStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Kiểm tra xem user đã đăng nhập chưa
// return bool
function isUserLoggedIn() {
    ensureSessionStarted();
    return isset($_SESSION['user_id']);
}

// Yêu cầu user phải đăng nhập, redirect đến login nếu chưa
// string|null $returnUrl - URL để quay lại sau khi đăng nhập
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

// Kiểm tra xem admin đã đăng nhập chưa
// return bool
function isAdminLoggedIn() {
    ensureSessionStarted();
    return isset($_SESSION['admin_id']);
}

// Yêu cầu admin phải đăng nhập, redirect đến admin login nếu chưa
function requireAdminLogin() {
    ensureSessionStarted();
    
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /view/user/admin/login.php');
        exit;
    }
}

// Kiểm tra xem user có role admin cụ thể không
// string|array $allowedRoles - Role đơn hoặc mảng các role được phép
// return bool
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

// Yêu cầu admin role cụ thể, redirect nếu không có quyền
// string|array $allowedRoles - Role được phép
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

// Lấy user ID hiện đang đăng nhập
// return int|null
function getCurrentUserId() {
    ensureSessionStarted();
    return $_SESSION['user_id'] ?? null;
}

// Lấy admin ID hiện đang đăng nhập
// return int|null
function getCurrentAdminId() {
    ensureSessionStarted();
    return $_SESSION['admin_id'] ?? null;
}

// Đăng xuất user
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

// Đăng xuất admin
function logoutAdmin() {
    ensureSessionStarted();
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

// Kiểm tra xem email của user đã verified chưa
// return bool
function isEmailVerified() {
    ensureSessionStarted();
    return isset($_SESSION['email_verified']) && $_SESSION['email_verified'] === true;
}

// Đặt flash message trong session
// string $message - Nội dung message
// string $type - Loại message: success|error|warning|info
function setFlashMessage($message, $type = 'info') {
    ensureSessionStarted();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Lấy và xóa flash message
// return array|null - ['message' => string, 'type' => string]
function getFlashMessage() {
    ensureSessionStarted();
    
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    
    return null;
}

// Regenerate session ID để bảo mật
function regenerateSession() {
    ensureSessionStarted();
    session_regenerate_id(true);
}
