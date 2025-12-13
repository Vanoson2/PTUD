<?php
include_once __DIR__ . '/mConnect.php';

class mAdmin {
    
    public function mAdminLogin($username, $password) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database',
                'admin' => null
            ];
        }
        
        $username = $conn->real_escape_string($username);
        
        $sql = "SELECT * FROM admin WHERE username = '$username' LIMIT 1";
        $result = $conn->query($sql);
        $password = md5($password); 
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if ($password === $admin['password_hash']) {
                $p->mDongKetNoi($conn);
                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'admin' => $admin
                ];
            }
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác',
            'admin' => null
        ];
    }
    
    public function mGetAllHostApplications($status = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT ha.*, 
                       u.full_name, u.email, u.phone, u.is_email_verified,
                       a.full_name as reviewed_by_name
                FROM host_application ha
                INNER JOIN user u ON ha.user_id = u.user_id
                LEFT JOIN admin a ON ha.reviewed_by_admin_id = a.admin_id";
        
        if ($status) {
            $status = $conn->real_escape_string($status);
            $sql .= " WHERE ha.status = '$status'";
        }
        
        $sql .= " ORDER BY ha.created_at DESC";
        
        $result = $conn->query($sql);
        $applications = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $applications;
    }
    
    public function mGetHostApplicationDetail($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT ha.*, 
                       u.full_name, u.email, u.phone, u.is_email_verified, u.created_at as user_created_at,
                       a.full_name as reviewed_by_name
                FROM host_application ha
                INNER JOIN user u ON ha.user_id = u.user_id
                LEFT JOIN admin a ON ha.reviewed_by_admin_id = a.admin_id
                WHERE ha.host_application_id = $applicationId
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $data;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    public function mApproveHostApplication($applicationId, $adminId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $sql = "UPDATE host_application 
                SET status = 'approved',
                    reviewed_by_admin_id = $adminId,
                    reviewed_at = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE host_application_id = $applicationId";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result ? true : false;
    }
    
    public function mRejectHostApplication($applicationId, $adminId, $reason) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $reason = $conn->real_escape_string($reason);
        
        $sql = "UPDATE host_application 
                SET status = 'rejected',
                    reviewed_by_admin_id = $adminId,
                    reviewed_at = NOW(),
                    rejection_reason = '$reason',
                    updated_at = CURRENT_TIMESTAMP
                WHERE host_application_id = $applicationId";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result ? true : false;
    }
    
    public function mGetDashboardStats() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $stats = [];
        
        // Tổng số applications
        $sql = "SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
                FROM host_application";
        
        $result = $conn->query($sql);
        if ($result) {
            $stats = $result->fetch_assoc();
        }
        
        // Tổng số users
        $sql2 = "SELECT COUNT(*) as total_users FROM user";
        $result2 = $conn->query($sql2);
        if ($result2) {
            $row = $result2->fetch_assoc();
            $stats['total_users'] = $row['total_users'];
        }
        
        // Tổng số hosts
        $sql3 = "SELECT COUNT(*) as total_hosts FROM host WHERE status = 'approved'";
        $result3 = $conn->query($sql3);
        if ($result3) {
            $row = $result3->fetch_assoc();
            $stats['total_hosts'] = $row['total_hosts'];
        }
        
        // Support ticket stats
        $sql4 = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                    SUM(CASE WHEN status IN ('open', 'in_progress') AND last_message_by = 'user' THEN 1 ELSE 0 END) as unread_tickets
                FROM support_ticket";
        $result4 = $conn->query($sql4);
        if ($result4) {
            $row = $result4->fetch_assoc();
            $stats['total_tickets'] = $row['total_tickets'] ?? 0;
            $stats['open_tickets'] = $row['open_tickets'] ?? 0;
            $stats['unread_tickets'] = $row['unread_tickets'] ?? 0;
        }
        
        $p->mDongKetNoi($conn);
        return $stats;
    }
    
    // User Management Methods
    public function mGetAllUsers($page = 1, $limit = 10, $search = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['users' => [], 'total' => 0, 'pages' => 0];
        }
        
        $offset = ($page - 1) * $limit;
        $search = $conn->real_escape_string($search);
        
        // Build WHERE clause
        $whereClause = "";
        if (!empty($search)) {
            $whereClause = " WHERE u.full_name LIKE '%$search%' 
                            OR u.email LIKE '%$search%' 
                            OR u.phone LIKE '%$search%'";
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM user u" . $whereClause;
        $countResult = $conn->query($countSql);
        $total = 0;
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $total = (int)$row['total'];
        }
        
        // Get users with pagination
        $sql = "SELECT u.*, 
                       CASE 
                           WHEN h.host_id IS NOT NULL THEN 'Host'
                           ELSE 'Traveller'
                       END as role
                FROM user u
                LEFT JOIN host h ON u.user_id = h.user_id AND h.status = 'approved'
                $whereClause
                ORDER BY u.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($sql);
        $users = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        $pages = ceil($total / $limit);
        
        $p->mDongKetNoi($conn);
        return [
            'users' => $users,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page
        ];
    }
    
    public function mGetUserById($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT * FROM user WHERE user_id = $userId LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $user;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    public function mCreateUser($email, $password, $phone, $fullName = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $email = $conn->real_escape_string($email);
        $phone = $conn->real_escape_string($phone);
        $fullName = $conn->real_escape_string($fullName);
        $passwordHash = md5($password);
        
        // Check email exists
        $checkSql = "SELECT user_id FROM user WHERE email = '$email' LIMIT 1";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Email đã tồn tại'
            ];
        }
        
        // Insert new user
        $sql = "INSERT INTO user (email, password_hash, phone, full_name, is_email_verified, status, created_at) 
                VALUES ('$email', '$passwordHash', '$phone', '$fullName', 1, 'active', CURRENT_TIMESTAMP)";
        
        if ($conn->query($sql)) {
            $userId = $conn->insert_id;
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Tạo người dùng thành công',
                'user_id' => $userId
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể tạo người dùng: ' . $conn->error
        ];
    }
    
    public function mUpdateUser($userId, $email, $phone, $fullName, $password = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $email = $conn->real_escape_string($email);
        $phone = $conn->real_escape_string($phone);
        $fullName = $conn->real_escape_string($fullName);
        
        // Check email exists for other users
        $checkSql = "SELECT user_id FROM user WHERE email = '$email' AND user_id != $userId LIMIT 1";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Email đã được sử dụng bởi người dùng khác'
            ];
        }
        
        // Build update query
        $sql = "UPDATE user 
                SET email = '$email', 
                    phone = '$phone', 
                    full_name = '$fullName'";
        
        if ($password !== null && !empty($password)) {
            $passwordHash = md5($password);
            $sql .= ", password_hash = '$passwordHash'";
        }
        
        $sql .= " WHERE user_id = $userId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Cập nhật người dùng thành công'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể cập nhật người dùng: ' . $conn->error
        ];
    }
    
    public function mToggleUserStatus($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        // Get current status
        $sql = "SELECT status FROM user WHERE user_id = $userId LIMIT 1";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ];
        }
        
        $user = $result->fetch_assoc();
        $newStatus = ($user['status'] === 'active') ? 'locked' : 'active';
        
        // Update status
        $updateSql = "UPDATE user SET status = '$newStatus' WHERE user_id = $userId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => $newStatus === 'locked' ? 'Đã khóa người dùng' : 'Đã mở khóa người dùng',
                'new_status' => $newStatus
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể thay đổi trạng thái: ' . $conn->error
        ];
    }
    
    // Host Management Methods
    public function mGetAllHosts($page = 1, $limit = 10, $search = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['hosts' => [], 'total' => 0, 'pages' => 0];
        }
        
        $offset = ($page - 1) * $limit;
        $search = $conn->real_escape_string($search);
        
        // Build WHERE clause
        $whereClause = "";
        if (!empty($search)) {
            $whereClause = " WHERE u.full_name LIKE '%$search%' 
                            OR u.email LIKE '%$search%' 
                            OR h.legal_name LIKE '%$search%'";
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total 
                     FROM host h
                     INNER JOIN user u ON h.user_id = u.user_id" . $whereClause;
        $countResult = $conn->query($countSql);
        $total = 0;
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $total = (int)$row['total'];
        }
        
        // Get hosts with pagination
        $sql = "SELECT h.*, u.full_name, u.email, u.phone, u.status as user_status,
                       COUNT(DISTINCT l.listing_id) as total_listings,
                       COUNT(DISTINCT CASE WHEN l.status = 'active' THEN l.listing_id END) as active_listings
                FROM host h
                INNER JOIN user u ON h.user_id = u.user_id
                LEFT JOIN listing l ON h.host_id = l.host_id
                $whereClause
                GROUP BY h.host_id
                ORDER BY h.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($sql);
        $hosts = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $hosts[] = $row;
            }
        }
        
        $pages = ceil($total / $limit);
        
        $p->mDongKetNoi($conn);
        return [
            'hosts' => $hosts,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page
        ];
    }
    
    public function mGetHostDetail($hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT h.*, u.full_name, u.email, u.phone, u.status as user_status, u.created_at as user_created_at
                FROM host h
                INNER JOIN user u ON h.user_id = u.user_id
                WHERE h.host_id = $hostId
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $host = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $host;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    public function mToggleHostStatus($hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        // Get current status
        $sql = "SELECT status FROM host WHERE host_id = $hostId LIMIT 1";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Không tìm thấy host'
            ];
        }
        
        $host = $result->fetch_assoc();
        $newStatus = ($host['status'] === 'approved') ? 'rejected' : 'approved';
        
        // Update status
        $updateSql = "UPDATE host SET status = '$newStatus' WHERE host_id = $hostId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => $newStatus === 'rejected' ? 'Đã đình chỉ chủ nhà' : 'Đã kích hoạt chủ nhà',
                'new_status' => $newStatus
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể thay đổi trạng thái: ' . $conn->error
        ];
    }
    
    // Support Ticket Methods
    public function mGetAllSupportTickets($status = null, $category = null, $page = 1, $limit = 10) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['tickets' => [], 'total' => 0, 'pages' => 0];
        }
        
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $conditions = [];
        if ($status) {
            $status = $conn->real_escape_string($status);
            $conditions[] = "st.status = '$status'";
        }
        if ($category) {
            $category = $conn->real_escape_string($category);
            $conditions[] = "st.category = '$category'";
        }
        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM support_ticket st" . $whereClause;
        $countResult = $conn->query($countSql);
        $total = 0;
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $total = (int)$row['total'];
        }
        
        // Get tickets - Support both logged-in users and guests
        $sql = "SELECT st.*, 
                       u.full_name, u.email
                FROM support_ticket st
                LEFT JOIN user u ON st.user_id = u.user_id
                $whereClause
                ORDER BY st.last_message_at DESC, st.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($sql);
        $tickets = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tickets[] = $row;
            }
        } else {
            // DEBUG: Log SQL error
            error_log("SQL Error in mGetAllSupportTickets: " . $conn->error);
            error_log("SQL Query: " . $sql);
        }
        
        $pages = ceil($total / $limit);
        
        $p->mDongKetNoi($conn);
        return [
            'tickets' => $tickets,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page
        ];
    }
    
    public function mGetTicketDetail($ticketId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        // Support both user tickets and guests
        $sql = "SELECT st.*, 
                       u.full_name, u.email, u.phone
                FROM support_ticket st
                LEFT JOIN user u ON st.user_id = u.user_id
                WHERE st.ticket_id = $ticketId
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
    
    public function mGetTicketMessages($ticketId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT sm.*,
                       u.full_name as user_name,
                       a.full_name as admin_name
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
    
    public function mReplyToTicket($ticketId, $adminId, $content) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        // Get ticket and user info first
        $ticketSql = "SELECT st.title, st.user_id, u.full_name, u.email, a.full_name as admin_name
                      FROM support_ticket st
                      JOIN user u ON st.user_id = u.user_id
                      LEFT JOIN admin a ON a.admin_id = $adminId
                      WHERE st.ticket_id = $ticketId";
        $ticketResult = $conn->query($ticketSql);
        
        if (!$ticketResult || $ticketResult->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Không tìm thấy ticket'
            ];
        }
        
        $ticketInfo = $ticketResult->fetch_assoc();
        
        $content = $conn->real_escape_string($content);
        
        // Insert message
        $sql = "INSERT INTO support_message (ticket_id, sender_type, admin_id, content, created_at)
                VALUES ($ticketId, 'admin', $adminId, '$content', CURRENT_TIMESTAMP)";
        
        if ($conn->query($sql)) {
            // Update ticket
            $updateSql = "UPDATE support_ticket 
                         SET last_message_at = CURRENT_TIMESTAMP,
                             last_message_by = 'admin',
                             status = 'in_progress'
                         WHERE ticket_id = $ticketId";
            $conn->query($updateSql);
            
            // Send email notification to user
            require_once(__DIR__ . '/mEmailPHPMailer.php');
            $emailModel = new mEmailPHPMailer();
            $emailModel->sendSupportReply(
                $ticketInfo['email'],
                $ticketInfo['full_name'],
                $ticketId,
                $ticketInfo['title'],
                $content,
                $ticketInfo['admin_name'] ?? 'WeGo Support'
            );
            
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đã gửi tin nhắn và email thông báo'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể gửi tin nhắn: ' . $conn->error
        ];
    }
    
    public function mUpdateTicketStatus($ticketId, $status) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $status = $conn->real_escape_string($status);
        $closedAt = ($status === 'closed') ? ', closed_at = CURRENT_TIMESTAMP' : '';
        
        $sql = "UPDATE support_ticket 
                SET status = '$status' $closedAt
                WHERE ticket_id = $ticketId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đã cập nhật trạng thái'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể cập nhật: ' . $conn->error
        ];
    }
    
    // ========== ADMIN MANAGEMENT (SUPERADMIN ONLY) ==========
    
    public function mGetAllAdmins() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT admin_id, username, full_name, role 
                FROM admin 
                ORDER BY 
                    CASE role 
                        WHEN 'superadmin' THEN 1
                        WHEN 'manager' THEN 2
                        WHEN 'support' THEN 3
                    END,
                    full_name ASC";
        
        $result = $conn->query($sql);
        $admins = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $admins[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $admins;
    }
    
    public function mCreateAdmin($username, $password, $fullName, $role) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        // Check username exists
        $username = $conn->real_escape_string($username);
        $checkSql = "SELECT admin_id FROM admin WHERE username = '$username' LIMIT 1";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }
        
        // Hash password
        $passwordHash = md5($password);
        $fullName = $conn->real_escape_string($fullName);
        $role = $conn->real_escape_string($role);
        
        $sql = "INSERT INTO admin (username, password_hash, full_name, role) 
                VALUES ('$username', '$passwordHash', '$fullName', '$role')";
        
        if ($conn->query($sql)) {
            $adminId = $conn->insert_id;
            $p->mDongKetNoi($conn);
            return [
                'success' => true, 
                'message' => 'Tạo tài khoản admin thành công',
                'admin_id' => $adminId
            ];
        }
        
        $errorMessage = $conn->error;
        $p->mDongKetNoi($conn);
        return ['success' => false, 'message' => 'Lỗi khi tạo admin: ' . $errorMessage];
    }
    
    public function mUpdateAdminRole($adminId, $newRole) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        $adminId = (int)$adminId;
        $newRole = $conn->real_escape_string($newRole);
        
        $sql = "UPDATE admin SET role = '$newRole' WHERE admin_id = $adminId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Cập nhật quyền thành công'];
        }
        
        $errorMessage = $conn->error;
        $p->mDongKetNoi($conn);
        return ['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $errorMessage];
    }
    
    public function mDeleteAdmin($adminId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        $adminId = (int)$adminId;
        
        // Check if admin is superadmin (cannot delete)
        $checkSql = "SELECT role FROM admin WHERE admin_id = $adminId LIMIT 1";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $admin = $checkResult->fetch_assoc();
            if ($admin['role'] === 'superadmin') {
                $p->mDongKetNoi($conn);
                return ['success' => false, 'message' => 'Không thể xóa tài khoản Superadmin'];
            }
        }
        
        $sql = "DELETE FROM admin WHERE admin_id = $adminId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Xóa tài khoản admin thành công'];
        }
        
        $errorMessage = $conn->error;
        $p->mDongKetNoi($conn);
        return ['success' => false, 'message' => 'Lỗi khi xóa: ' . $errorMessage];
    }
    
    public function mResetAdminPassword($adminId, $newPassword) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        $adminId = (int)$adminId;
        $passwordHash = md5($newPassword);
        
        $sql = "UPDATE admin SET password_hash = '$passwordHash' WHERE admin_id = $adminId";
        
        if ($conn->query($sql)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công'];
        }
        
        $errorMessage = $conn->error;
        $p->mDongKetNoi($conn);
        return ['success' => false, 'message' => 'Lỗi khi đặt lại mật khẩu: ' . $errorMessage];
    }
}
?>
