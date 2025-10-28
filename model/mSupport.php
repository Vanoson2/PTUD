<?php
include_once(__DIR__ . "/mConnect.php");

class mSupport {
    
    // User: Tạo ticket mới
    public function mCreateTicket($userId, $title, $content, $category = 'khac', $priority = 'normal') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $title = $conn->real_escape_string($title);
        $content = $conn->real_escape_string($content);
        $category = $conn->real_escape_string($category);
        $priority = $conn->real_escape_string($priority);
        
        $sql = "INSERT INTO support_ticket (user_id, title, content, category, priority, status, last_message_at, last_message_by) 
                VALUES ($userId, '$title', '$content', '$category', '$priority', 'open', NOW(), 'user')";
        
        if ($conn->query($sql)) {
            $ticketId = $conn->insert_id;
            
            // Insert first message
            $msgSql = "INSERT INTO support_message (ticket_id, sender_type, user_id, content) 
                       VALUES ($ticketId, 'user', $userId, '$content')";
            $conn->query($msgSql);
            
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đã gửi yêu cầu hỗ trợ thành công',
                'ticket_id' => $ticketId
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể tạo yêu cầu: ' . $conn->error
        ];
    }
    
    // User: Lấy danh sách tickets của mình
    public function mGetUserTickets($userId, $status = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $whereClause = "WHERE st.user_id = $userId";
        if ($status) {
            $status = $conn->real_escape_string($status);
            $whereClause .= " AND st.status = '$status'";
        }
        
        $sql = "SELECT st.*, 
                       (SELECT COUNT(*) FROM support_message WHERE ticket_id = st.ticket_id) as message_count,
                       (SELECT COUNT(*) FROM support_message 
                        WHERE ticket_id = st.ticket_id AND sender_type = 'admin' 
                        AND created_at > IFNULL((SELECT MAX(created_at) FROM support_message sm2 
                                                 WHERE sm2.ticket_id = st.ticket_id AND sm2.sender_type = 'user'), '1970-01-01')
                       ) as unread_count
                FROM support_ticket st
                $whereClause
                ORDER BY st.last_message_at DESC, st.created_at DESC";
        
        $result = $conn->query($sql);
        $tickets = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tickets[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $tickets;
    }
    
    // User: Lấy chi tiết ticket
    public function mGetTicketDetail($ticketId, $userId = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $whereClause = "WHERE st.ticket_id = $ticketId";
        if ($userId !== null) {
            $whereClause .= " AND st.user_id = $userId";
        }
        
        $sql = "SELECT st.*, u.full_name, u.email, u.phone
                FROM support_ticket st
                INNER JOIN user u ON st.user_id = u.user_id
                $whereClause
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $ticket = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $ticket;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    // User: Lấy messages của ticket
    public function mGetTicketMessages($ticketId, $userId = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        // Verify user owns this ticket if userId provided
        if ($userId !== null) {
            $checkSql = "SELECT ticket_id FROM support_ticket WHERE ticket_id = $ticketId AND user_id = $userId";
            $checkResult = $conn->query($checkSql);
            if (!$checkResult || $checkResult->num_rows === 0) {
                $p->mDongKetNoi($conn);
                return [];
            }
        }
        
        $sql = "SELECT sm.*, 
                       CASE 
                           WHEN sm.sender_type = 'user' THEN u.full_name
                           WHEN sm.sender_type = 'admin' THEN a.full_name
                       END as sender_name
                FROM support_message sm
                LEFT JOIN user u ON sm.user_id = u.user_id
                LEFT JOIN admin a ON sm.admin_id = a.admin_id
                WHERE sm.ticket_id = $ticketId
                ORDER BY sm.created_at ASC";
        
        $result = $conn->query($sql);
        $messages = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $messages;
    }
    
    // User: Gửi tin nhắn reply
    public function mReplyTicket($ticketId, $userId, $content) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        // Verify user owns this ticket
        $checkSql = "SELECT status FROM support_ticket WHERE ticket_id = $ticketId AND user_id = $userId";
        $checkResult = $conn->query($checkSql);
        
        if (!$checkResult || $checkResult->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ];
        }
        
        $ticket = $checkResult->fetch_assoc();
        if ($ticket['status'] === 'closed') {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Yêu cầu đã đóng, không thể trả lời'
            ];
        }
        
        $content = $conn->real_escape_string($content);
        
        // Insert message
        $msgSql = "INSERT INTO support_message (ticket_id, sender_type, user_id, content) 
                   VALUES ($ticketId, 'user', $userId, '$content')";
        
        if ($conn->query($msgSql)) {
            // Update ticket last_message
            $updateSql = "UPDATE support_ticket 
                         SET last_message_at = NOW(), 
                             last_message_by = 'user',
                             status = CASE WHEN status = 'resolved' THEN 'open' ELSE status END
                         WHERE ticket_id = $ticketId";
            $conn->query($updateSql);
            
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đã gửi tin nhắn'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể gửi tin nhắn: ' . $conn->error
        ];
    }
    
    // User: Đóng ticket
    public function mCloseTicket($ticketId, $userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $sql = "UPDATE support_ticket 
                SET status = 'closed', closed_at = NOW() 
                WHERE ticket_id = $ticketId AND user_id = $userId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đã đóng yêu cầu hỗ trợ'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể đóng yêu cầu'
        ];
    }
    
    // Get ticket counts by status for user
    public function mGetUserTicketCounts($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'open' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed' => 0,
                'total' => 0
            ];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM support_ticket
                WHERE user_id = $userId";
        
        $result = $conn->query($sql);
        $counts = [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
            'total' => 0
        ];
        
        if ($result) {
            $counts = $result->fetch_assoc();
        }
        
        $p->mDongKetNoi($conn);
        return $counts;
    }
}
?>
