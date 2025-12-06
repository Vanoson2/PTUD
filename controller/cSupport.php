<?php
include_once(__DIR__ . "/../model/mSupport.php");

class cSupport {
    
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
    
    public function cGetUserTickets($userId, $status = null) {
        $mSupport = new mSupport();
        return $mSupport->mGetUserTickets($userId, $status);
    }
    
    public function cGetTicketDetail($ticketId, $userId = null) {
        if ($ticketId <= 0) {
            return null;
        }
        
        $mSupport = new mSupport();
        return $mSupport->mGetTicketDetail($ticketId, $userId);
    }
    
    public function cGetTicketMessages($ticketId, $userId = null) {
        if ($ticketId <= 0) {
            return [];
        }
        
        $mSupport = new mSupport();
        return $mSupport->mGetTicketMessages($ticketId, $userId);
    }
    
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
    
    public function cGetUserTicketCounts($userId) {
        $mSupport = new mSupport();
        return $mSupport->mGetUserTicketCounts($userId);
    }
    
    // ========== ADMIN METHODS ==========
    
    public function cAdminGetAllTickets($status = null, $category = null, $priority = null, $search = null) {
        $mSupport = new mSupport();
        return $mSupport->mAdminGetAllTickets($status, $category, $priority, $search);
    }
    
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
    
    public function cAdminGetStatistics() {
        $mSupport = new mSupport();
        return $mSupport->mAdminGetStatistics();
    }

    /**
     * Suggest new service with validation
     * @param int $userId User ID suggesting service
     * @param string $serviceName Suggested service name
     * @param string $description Service description
     * @return array ['success' => bool, 'message' => string]
     */
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
