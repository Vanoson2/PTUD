<?php
include_once(__DIR__ . "/../model/mSupport.php");

class cSupport {
    
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
}
?>
