<?php
include_once(__DIR__ . "/../model/mAdmin.php");
include_once(__DIR__ . "/../model/mHost.php");
include_once(__DIR__ . "/../model/mListing.php");

class cAdmin {
    
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
    
    public function cGetDashboardStats() {
        $mAdmin = new mAdmin();
        return $mAdmin->mGetDashboardStats();
    }
    
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
    
    public function cGetHostApplicationDetail($applicationId) {
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetHostApplicationDetail($applicationId);
    }
    
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
    
    // Listing management methods
    public function cGetAllListings($status = null) {
        // Validate status
        if ($status !== null) {
            $validStatuses = ['draft', 'pending', 'active', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        $mListing = new mListing();
        return $mListing->mGetAllListings($status);
    }
    
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
    
    // User Management Methods
    public function cGetAllUsers($page = 1, $limit = 10, $search = '') {
        // Validate pagination
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit))); // Max 100 items per page
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllUsers($page, $limit, $search);
    }
    
    public function cGetUserById($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetUserById($userId);
    }
    
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
    
    // Host Management Methods
    public function cGetAllHosts($page = 1, $limit = 10, $search = '') {
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit)));
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllHosts($page, $limit, $search);
    }
    
    public function cGetHostDetail($hostId) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetHostDetail($hostId);
    }
    
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
    
    // Support Ticket Methods
    public function cGetAllSupportTickets($status = null, $page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit)));
        
        if ($status !== null) {
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllSupportTickets($status, $page, $limit);
    }
    
    public function cGetTicketDetail($ticketId) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetTicketDetail($ticketId);
    }
    
    public function cGetTicketMessages($ticketId) {
        if (!is_numeric($ticketId) || $ticketId <= 0) {
            return [];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetTicketMessages($ticketId);
    }
    
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
}
?>
