<?php
include_once(__DIR__ . "/../model/mSupport.php");

class cSupport {
    
    // Tạo ticket hỗ trợ cho khách (chưa đăng nhập)
    // Validate đầy đủ: tên (min 2), email, phone (10-11 số), title (min 5), content (min 10)
    // string $guestName - Họ tên khách
    // string $guestEmail - Email khách
    // string $guestPhone - Số điện thoại
    // string $title - Tiêu đề ticket
    // string $content - Nội dung
    // string $category - Danh mục: 'dat_phong', 'tai_khoan', 'nha_cung_cap', 'khac'
    // string $priority - Độ ưu tiên: 'normal', 'high', 'urgent'
    // return array - ['success' => bool, 'message' => string, 'ticket_id' => int|null]
    public function cCreateGuestTicket($guestName, $guestEmail, $guestPhone, $title, $content, $category = 'khac', $priority = 'normal') {
        // Validation
        if (empty($guestName) || strlen($guestName) < 2) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập họ tên (ít nhất 2 ký tự)'
            ];
        }
        
        if (empty($guestEmail) || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập email hợp lệ'
            ];
        }
        
        if (!empty($guestPhone) && !preg_match('/^[0-9]{10,11}$/', $guestPhone)) {
            return [
                'success' => false,
                'message' => 'Số điện thoại không hợp lệ (10-11 chữ số)'
            ];
        }
        
        if (empty($title) || strlen($title) < 5) {
            return [
                'success' => false,
                'message' => 'Tiêu đề phải có ít nhất 5 ký tự'
            ];
        }
        
        if (empty($content) || strlen($content) < 10) {
            return [
                'success' => false,
                'message' => 'Nội dung phải có ít nhất 10 ký tự'
            ];
        }
        
        $validCategories = ['dat_phong', 'tai_khoan', 'nha_cung_cap', 'khac'];
        if (!in_array($category, $validCategories)) {
            $category = 'khac';
        }
        
        $validPriorities = ['normal', 'high', 'urgent'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'normal';
        }
        
        $mSupport = new mSupport();
        return $mSupport->mCreateGuestTicket($guestName, $guestEmail, $guestPhone, $title, $content, $category, $priority);
    }
    
    // Tạo ticket hỗ trợ cho user đã đăng nhập
    // Validate title (min 5), content (min 10), category, priority
    // int $userId - ID user
    // string $title - Tiêu đề ticket
    // string $content - Nội dung
    // string $category - Danh mục: 'dat_phong', 'tai_khoan', 'nha_cung_cap', 'khac'
    // string $priority - Độ ưu tiên: 'normal', 'high', 'urgent'
    // return array - ['success' => bool, 'message' => string, 'ticket_id' => int|null]
    public function cCreateTicket($userId, $title, $content, $category = 'khac', $priority = 'normal') {
        // Validation
        if (empty($title) || strlen($title) < 5) {
            return [
                'success' => false,
                'message' => 'Tiêu đề phải có ít nhất 5 ký tự'
            ];
        }
        
        if (empty($content) || strlen($content) < 10) {
            return [
                'success' => false,
                'message' => 'Nội dung phải có ít nhất 10 ký tự'
            ];
        }
        
        $validCategories = ['dat_phong', 'tai_khoan', 'nha_cung_cap', 'khac'];
        if (!in_array($category, $validCategories)) {
            $category = 'khac';
        }
        
        $validPriorities = ['normal', 'high', 'urgent'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'normal';
        }
        
        $mSupport = new mSupport();
        return $mSupport->mCreateTicket($userId, $title, $content, $category, $priority);
    }
    
    // Lấy danh sách ticket của user
    // int $userId - ID user
    // string|null $status - Trạng thái: 'open', 'resolved', 'closed', null = tất cả
    // return array - Danh sách tickets
    public function cGetUserTickets($userId, $status = null) {
        $mSupport = new mSupport();
        return $mSupport->mGetUserTickets($userId, $status);
    }
    
    // Lấy chi tiết ticket
    // int $ticketId - ID ticket
    // int|null $userId - ID user (để verify ownership)
    // return array|null - Dữ liệu ticket hoặc null
    public function cGetTicketDetail($ticketId, $userId = null) {
        if ($ticketId <= 0) {
            return null;
        }
        
        $mSupport = new mSupport();
        return $mSupport->mGetTicketDetail($ticketId, $userId);
    }
    
    // Lấy danh sách tin nhắn của ticket
    // int $ticketId - ID ticket
    // int|null $userId - ID user (để verify ownership)
    // return array - Danh sách messages
    public function cGetTicketMessages($ticketId, $userId = null) {
        if ($ticketId <= 0) {
            return [];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mGetTicketMessages($ticketId, $userId);
    }
    
    // User trả lời ticket
    // Validate content (min 5 ký tự)
    // int $ticketId - ID ticket
    // int $userId - ID user
    // string $content - Nội dung trả lời
    // return array - ['success' => bool, 'message' => string]
    public function cReplyTicket($ticketId, $userId, $content) {
        if ($ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'ID yêu cầu không hợp lệ'
            ];
        }
        
        if (empty($content) || strlen($content) < 5) {
            return [
                'success' => false,
                'message' => 'Nội dung tin nhắn phải có ít nhất 5 ký tự'
            ];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mReplyTicket($ticketId, $userId, $content);
    }
    
    // Đóng ticket (user hoặc admin)
    // int $ticketId - ID ticket
    // int $userId - ID user (để verify ownership)
    // return array - ['success' => bool, 'message' => string]
    public function cCloseTicket($ticketId, $userId) {
        if ($ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'ID yêu cầu không hợp lệ'
            ];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mCloseTicket($ticketId, $userId);
    }
    
    // Đếm số ticket của user theo trạng thái
    // int $userId - ID user
    // return array - ['open' => int, 'resolved' => int, 'closed' => int]
    public function cGetUserTicketCounts($userId) {
        $mSupport = new mSupport();
        return $mSupport->mGetUserTicketCounts($userId);
    }
    
    // ===== ADMIN FUNCTIONS =====
    
    // Admin lấy tất cả ticket với filter
    // string|null $status - Trạng thái: 'open', 'in_progress', 'resolved', 'closed'
    // string|null $category - Danh mục: 'dat_phong', 'tai_khoan', 'nha_cung_cap', 'khac'
    // string|null $priority - Độ ưu tiên: 'normal', 'high', 'urgent'
    // string|null $search - Từ khóa tìm kiếm
    // return array - Danh sách tickets
    public function cAdminGetAllTickets($status = null, $category = null, $priority = null, $search = null) {
        $mSupport = new mSupport();
        return $mSupport->mAdminGetAllTickets($status, $category, $priority, $search);
    }
    
    // Admin trả lời ticket
    // Validate content (min 5 ký tự)
    // int $ticketId - ID ticket
    // int $adminId - ID admin
    // string $content - Nội dung trả lời
    // return array - ['success' => bool, 'message' => string]
    public function cAdminReplyTicket($ticketId, $adminId, $content) {
        if ($ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'ID yêu cầu không hợp lệ'
            ];
        }
        
        if (empty($content) || strlen($content) < 5) {
            return [
                'success' => false,
                'message' => 'Nội dung tin nhắn phải có ít nhất 5 ký tự'
            ];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mAdminReplyTicket($ticketId, $adminId, $content);
    }
    
    // Admin cập nhật trạng thái ticket
    // Validate status: 'open', 'in_progress', 'resolved', 'closed'
    // int $ticketId - ID ticket
    // string $status - Trạng thái mới
    // return array - ['success' => bool, 'message' => string]
    public function cAdminUpdateStatus($ticketId, $status) {
        if ($ticketId <= 0) {
            return [
                'success' => false,
                'message' => 'ID yêu cầu không hợp lệ'
            ];
        }
        
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Trạng thái không hợp lệ'
            ];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mAdminUpdateStatus($ticketId, $status);
    }
    
    // Admin lấy thống kê ticket (tổng số, theo trạng thái, danh mục, độ ưu tiên)
    // return array - Dữ liệu thống kê
    public function cAdminGetStatistics() {
        $mSupport = new mSupport();
        return $mSupport->mAdminGetStatistics();
    }

    // Đề xuất dịch vụ mới
    // Validate tên dịch vụ (3-100 ký tự), mô tả (min 10 ký tự)
    // int $userId - ID user đề xuất
    // string $serviceName - Tên dịch vụ
    // string $description - Mô tả dịch vụ
    // return array - ['success' => bool, 'message' => string]
    public function cSuggestService($userId, $serviceName, $description) {
        // Validate service name
        if (empty($serviceName)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tên dịch vụ'];
        }

        $serviceName = trim($serviceName);
        if (mb_strlen($serviceName) < 3) {
            return ['success' => false, 'message' => 'Tên dịch vụ phải có ít nhất 3 ký tự'];
        }

        if (mb_strlen($serviceName) > 100) {
            return ['success' => false, 'message' => 'Tên dịch vụ quá dài (tối đa 100 ký tự)'];
        }

        // Validate description
        if (empty($description)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mô tả dịch vụ'];
        }

        $description = trim($description);
        if (mb_strlen($description) < 10) {
            return ['success' => false, 'message' => 'Mô tả dịch vụ phải có ít nhất 10 ký tự'];
        }

        if (mb_strlen($description) > 1000) {
            return ['success' => false, 'message' => 'Mô tả dịch vụ quá dài (tối đa 1000 ký tự)'];
        }

        // Sanitize inputs
        $serviceName = htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

        // Create support ticket for service suggestion
        $title = "Đề xuất dịch vụ mới: " . $serviceName;
        $content = "**Tên dịch vụ đề xuất:** " . $serviceName . "\n\n";
        $content .= "**Mô tả chi tiết:**\n" . $description;

        return $this->cCreateTicket($userId, $title, $content, 'de_xuat_dich_vu', 'normal');
    }
}
?>
