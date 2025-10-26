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
        $sql3 = "SELECT COUNT(*) as total_hosts FROM host WHERE status = 'active'";
        $result3 = $conn->query($sql3);
        if ($result3) {
            $row = $result3->fetch_assoc();
            $stats['total_hosts'] = $row['total_hosts'];
        }
        
        $p->mDongKetNoi($conn);
        return $stats;
    }
}
?>
