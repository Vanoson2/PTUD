<?php
include_once(__DIR__ . "/../model/mAdmin.php");
include_once(__DIR__ . "/../model/mHost.php");
include_once(__DIR__ . "/../model/mListing.php");

class cAdmin {
    
    // ===== XÁC THỰC & DASHBOARD =====
    
    // Đăng nhập admin
    // Validate username và password trước khi gọi Model
    // string $username - Tên đăng nhập
    // string $password - Mật khẩu
    // return array - ['success' => bool, 'admin' => array, 'message' => string]
    public function cAdminLogin($username, $password) {
        // Validate input
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors,
                'admin' => null
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mAdminLogin($username, $password);
    }
    
    // Lấy thống kê dashboard cho admin
    // Trả về số liệu tổng quan: users, hosts, bookings, revenue
    // return array - Dữ liệu dashboard
    public function cGetDashboardStats() {
        $mAdmin = new mAdmin();
        return $mAdmin->mGetDashboardStats();
    }
    
    // ===== QUẢN LÝ ĐƠN ĐĂNG KÝ HOST =====
    
    // Lấy tất cả đơn đăng ký host
    // string|null $status - Lọc theo trạng thái: 'pending', 'approved', 'rejected'
    // return array - Danh sách đơn đăng ký
    public function cGetAllHostApplications($status = null) {
        // Validate status
        if ($status !== null) {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllHostApplications($status);
    }
    
    // Lấy chi tiết đơn đăng ký host
    // int $applicationId - ID đơn đăng ký
    // return array|null - Thông tin chi tiết đơn
    public function cGetHostApplicationDetail($applicationId) {
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetHostApplicationDetail($applicationId);
    }
    
    // Duyệt đơn đăng ký host
    // Approve đơn và tự động tạo host record
    // int $applicationId - ID đơn đăng ký
    // int $adminId - ID admin duyệt
    // return array - ['success' => bool, 'message' => string]
    public function cApproveHostApplication($applicationId, $adminId) {
        // Validate input
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return [
                'success' => false,
                'message' => 'Application ID không hợp lệ'
            ];
        }
        
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [
                'success' => false,
                'message' => 'Admin ID không hợp lệ'
            ];
        }
        
        // Kiểm tra application tồn tại và đang pending
        $mAdmin = new mAdmin();
        $application = $mAdmin->mGetHostApplicationDetail($applicationId);
        
        if (!$application) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn đăng ký'
            ];
        }
        
        if ($application['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Đơn này đã được xử lý rồi'
            ];
        }
        
        // Approve application
        $result = $mAdmin->mApproveHostApplication($applicationId, $adminId);
        
        if ($result) {
            // Tạo host record
            $mHost = new mHost();
            $hostCreated = $mHost->mCreateHostFromApplication($applicationId);
            
            if ($hostCreated) {
                return [
                    'success' => true,
                    'message' => 'Đã duyệt đơn thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Đã duyệt đơn nhưng không thể tạo host record'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt đơn'
            ];
        }
    }
    
    // Từ chối đơn đăng ký host
    // Reject đơn và lưu lý do từ chối
    // int $applicationId - ID đơn đăng ký
    // int $adminId - ID admin từ chối
    // string $reason - Lý do từ chối (tối đa 500 ký tự)
    // return array - ['success' => bool, 'message' => string]
    public function cRejectHostApplication($applicationId, $adminId, $reason) {
        // Validate input
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return [
                'success' => false,
                'message' => 'Application ID không hợp lệ'
            ];
        }
        
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [
                'success' => false,
                'message' => 'Admin ID không hợp lệ'
            ];
        }
        
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập lý do từ chối'
            ];
        }
        
        if (strlen($reason) > 500) {
            return [
                'success' => false,
                'message' => 'Lý do từ chối quá dài (tối đa 500 ký tự)'
            ];
        }
        
        // Kiểm tra application tồn tại và đang pending
        $mAdmin = new mAdmin();
        $application = $mAdmin->mGetHostApplicationDetail($applicationId);
        
        if (!$application) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn đăng ký'
            ];
        }
        
        if ($application['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Đơn này đã được xử lý rồi'
            ];
        }
        
        // Reject application
        $result = $mAdmin->mRejectHostApplication($applicationId, $adminId, $reason);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã từ chối đơn thành công'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối đơn'
            ];
        }
    }
    
    // ===== QUẢN LÝ LISTING (PHÒNG/NHÀ) =====
    
    // Lấy tất cả listing
    // Admin xem toàn bộ listing (trừ draft), có thể lọc theo status
    // string|null $status - Lọc: 'draft', 'pending', 'active', 'rejected'
    // return array - Danh sách listing
    public function cGetAllListings($status = null) {
        // Validate status
        if ($status !== null) {
            $validStatuses = ['draft', 'pending', 'active', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        // Nếu status = null, loại draft ra (admin không cần thấy bản nháp)
        $excludeDraft = ($status === null);
        
        $mListing = new mListing();
        return $mListing->mGetAllListings($status, $excludeDraft);
    }
    
    // Duyệt listing (chuyển sang active)
    // int $listingId - ID listing cần duyệt
    // int $adminId - ID admin duyệt
    // return array - ['success' => bool, 'message' => string]
    public function cApproveListing($listingId, $adminId) {
        // Validate input
        if (!is_numeric($listingId) || $listingId <= 0) {
            return [
                'success' => false,
                'message' => 'Listing ID không hợp lệ'
            ];
        }
        
        $mListing = new mListing();
        $result = $mListing->mUpdateListingStatus($listingId, 'active');
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã phê duyệt phòng thành công'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi phê duyệt phòng'
            ];
        }
    }
    
    // Từ chối listing
    // Chuyển status sang rejected và lưu lý do
    // int $listingId - ID listing
    // int $adminId - ID admin từ chối
    // string $reason - Lý do từ chối
    // return array - ['success' => bool, 'message' => string]
    public function cRejectListing($listingId, $adminId, $reason) {
        // Validate input
        if (!is_numeric($listingId) || $listingId <= 0) {
            return [
                'success' => false,
                'message' => 'Listing ID không hợp lệ'
            ];
        }
        
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập lý do từ chối'
            ];
        }
        
        $mListing = new mListing();
        $result = $mListing->mUpdateListingStatus($listingId, 'rejected', $reason);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã từ chối phòng'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối phòng'
            ];
        }
    }
    
    // ===== QUẢN LÝ USER =====
    
    // Lấy danh sách tất cả user (có phân trang và search)
    // int $page - Trang hiện tại
    // int $limit - Số bản ghi mỗi trang (tối đa 100)
    // string $search - Từ khóa tìm kiếm (email, phone, họ tên)
    // return array - ['users' => array, 'total' => int, 'page' => int, 'limit' => int]
    public function cGetAllUsers($page = 1, $limit = 10, $search = '') {
        // Validate pagination
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit))); // Max 100 items per page
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllUsers($page, $limit, $search);
    }
    
    // Lấy thông tin user theo ID
    // int $userId - ID user
    // return array|null - Thông tin user
    public function cGetUserById($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetUserById($userId);
    }
    
    // Tạo user mới (Admin)
    // Validate email, password, phone trước khi tạo
    // string $email - Email
    // string $password - Mật khẩu (tối thiểu 6 ký tự)
    // string $phone - Số điện thoại (10-11 số)
    // string $fullName - Họ tên
    // return array - ['success' => bool, 'message' => string]
    public function cCreateUser($email, $password, $phone, $fullName = '') {
        // Validate input
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }
        
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10-11 số)';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mCreateUser($email, $password, $phone, $fullName);
    }
    
    // Cập nhật thông tin user
    // Cho phép admin sửa email, phone, họ tên, và đổi mật khẩu
    // int $userId - ID user
    // string $email - Email mới
    // string $phone - Số điện thoại mới
    // string $fullName - Họ tên mới
    // string|null $password - Mật khẩu mới (nullable)
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateUser($userId, $email, $phone, $fullName, $password = null) {
        // Validate input
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'success' => false,
                'message' => 'User ID không hợp lệ'
            ];
        }
        
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }
        
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10-11 số)';
        }
        
        if ($password !== null && !empty($password) && strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mUpdateUser($userId, $email, $phone, $fullName, $password);
    }
    
    // Khóa/Mở khóa tài khoản user
    // Toggle trạng thái active <-> locked
    // int $userId - ID user cần toggle
    // return array - ['success' => bool, 'message' => string]
    public function cToggleUserStatus($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'success' => false,
                'message' => 'User ID không hợp lệ'
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mToggleUserStatus($userId);
    }
    
    // ===== QUẢN LÝ HOST =====
    
    // Lấy danh sách tất cả host (có phân trang và search)
    // int $page - Trang hiện tại
    // int $limit - Số bản ghi mỗi trang (tối đa 100)
    // string $search - Từ khóa tìm kiếm
    // return array - ['hosts' => array, 'total' => int, 'page' => int, 'limit' => int]
    public function cGetAllHosts($page = 1, $limit = 10, $search = '') {
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit)));
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllHosts($page, $limit, $search);
    }
    
    // Lấy chi tiết host theo ID
    // int $hostId - ID host
    // return array|null - Thông tin chi tiết host
    public function cGetHostDetail($hostId) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetHostDetail($hostId);
    }
    
    // Khóa/Mở khóa tài khoản host
    // Toggle trạng thái active <-> inactive
    // int $hostId - ID host cần toggle
    // return array - ['success' => bool, 'message' => string]
    public function cToggleHostStatus($hostId) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return [
                'success' => false,
                'message' => 'Host ID không hợp lệ'
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mToggleHostStatus($hostId);
    }
    
    // ===== QUẢN LÝ HỖ TRỢ (SUPPORT TICKETS) =====
    
    // Lấy tất cả ticket hỗ trợ (có phân trang và filter)
    // string|null $status - Lọc: 'open', 'in_progress', 'resolved', 'closed'
    // string|null $category - Lọc: 'dat_phong', 'tai_khoan', 'nha_cung_cap', 'de_xuat_dich_vu', 'khac'
    // int $page - Trang hiện tại
    // int $limit - Số bản ghi mỗi trang
    // return array - Danh sách tickets
    public function cGetAllSupportTickets($status = null, $category = null, $page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit)));
        
        if ($status !== null) {
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        if ($category !== null) {
            $validCategories = ['dat_phong', 'tai_khoan', 'nha_cung_cap', 'de_xuat_dich_vu', 'khac'];
            if (!in_array($category, $validCategories)) {
                $category = null;
            }
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllSupportTickets($status, $category, $page, $limit);
    }
    
    // Lấy chi tiết ticket
    // int $ticketId - ID ticket
    // return array|null - Thông tin ticket
    public function cGetTicketDetail($ticketId) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetTicketDetail($ticketId);
    }
    
    // Lấy tất cả tin nhắn của ticket
    // int $ticketId - ID ticket
    // return array - Danh sách messages
    public function cGetTicketMessages($ticketId) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return [];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetTicketMessages($ticketId);
    }
    
    // Admin trả lời ticket
    // Validate input trước khi gọi Model
    // int $ticketId - ID ticket
    // int $adminId - ID admin đang reply
    // string $content - Nội dung trả lời
    // return array - ['success' => bool, 'message' => string]
    public function cReplyToTicket($ticketId, $adminId, $content) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'Ticket ID không hợp lệ'
            ];
        }
        
        if (empty($content)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập nội dung tin nhắn'
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mReplyToTicket($ticketId, $adminId, $content);
    }
    
    // Lấy chi tiết ticket theo ID
    // Dùng để lấy thông tin user/guest cho việc gửi email
    // int $ticketId - ID ticket
    // return array|null - Thông tin ticket
    public function cGetTicketById($ticketId) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetTicketById($ticketId);
    }
    
    // Cập nhật trạng thái ticket (open/in_progress/closed)
    // int $ticketId - ID ticket cần update
    // string $status - Trạng thái mới
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateTicketStatus($ticketId, $status) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'Ticket ID không hợp lệ'
            ];
        }
        
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Trạng thái không hợp lệ'
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mUpdateTicketStatus($ticketId, $status);
    }
    
    // ===== QUẢN LÝ ADMIN (SUPERADMIN ONLY) =====
    
    // Lấy danh sách tất cả admin
    // return array - Danh sách admin
    public function cGetAllAdmins() {
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllAdmins();
    }
    
    // Tạo admin mới (Superadmin only)
    // Validate username, password, role trước khi tạo
    // string $username - Tên đăng nhập (3-50 ký tự, chỉ chữ, số, gạch dưới)
    // string $password - Mật khẩu (tối thiểu 6 ký tự)
    // string $fullName - Họ tên
    // string $role - Vai trò: 'superadmin', 'manager', 'support'
    // return array - ['success' => bool, 'message' => string]
    public function cCreateAdmin($username, $password, $fullName, $role) {
        // Validate inputs
        if (empty($username) || empty($password) || empty($fullName) || empty($role)) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Validate username (alphanumeric, underscore, 3-50 chars)
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            return ['success' => false, 'message' => 'Tên đăng nhập không hợp lệ (3-50 ký tự, chỉ chữ, số, dấu gạch dưới)'];
        }
        
        // Validate password (min 6 chars)
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        // Validate role
        $validRoles = ['superadmin', 'manager', 'support'];
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Vai trò không hợp lệ'];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mCreateAdmin($username, $password, $fullName, $role);
    }
    
    // Cập nhật vai trò admin
    // int $adminId - ID admin
    // string $newRole - Vai trò mới: 'superadmin', 'manager', 'support'
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateAdminRole($adminId, $newRole) {
        $adminId = (int)$adminId;
        if ($adminId <= 0) {
            return ['success' => false, 'message' => 'ID admin không hợp lệ'];
        }
        
        $validRoles = ['superadmin', 'manager', 'support'];
        if (!in_array($newRole, $validRoles)) {
            return ['success' => false, 'message' => 'Vai trò không hợp lệ'];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mUpdateAdminRole($adminId, $newRole);
    }
    
    // Xóa admin
    // int $adminId - ID admin cần xóa
    // return array - ['success' => bool, 'message' => string]
    public function cDeleteAdmin($adminId) {
        $adminId = (int)$adminId;
        if ($adminId <= 0) {
            return ['success' => false, 'message' => 'ID admin không hợp lệ'];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mDeleteAdmin($adminId);
    }
    
    // Reset mật khẩu admin
    // int $adminId - ID admin
    // string $newPassword - Mật khẩu mới (tối thiểu 6 ký tự)
    // return array - ['success' => bool, 'message' => string]
    public function cResetAdminPassword($adminId, $newPassword) {
        $adminId = (int)$adminId;
        if ($adminId <= 0) {
            return ['success' => false, 'message' => 'ID admin không hợp lệ'];
        }
        
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mResetAdminPassword($adminId, $newPassword);
    }
}
?>
