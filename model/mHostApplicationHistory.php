<?php
/**
 * Model xử lý lịch sử thay đổi host application
 */
include_once __DIR__ . '/mConnect.php';

class mHostApplicationHistory {
    
    /**
     * Log status change to history
     * 
     * @param int $applicationId
     * @param string $previousStatus (NULL for first submission)
     * @param string $newStatus
     * @param string $actionType ('submitted', 'reviewed', 'resubmitted')
     * @param int $adminId (NULL if user action)
     * @param string $reason (rejection reason or admin note)
     * @return bool
     */
    public function logStatusChange($applicationId, $previousStatus, $newStatus, $actionType, $adminId = null, $reason = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        // Escape values
        $prevStatus = $previousStatus ? "'" . $conn->real_escape_string($previousStatus) . "'" : "NULL";
        $newStat = $conn->real_escape_string($newStatus);
        $actType = $conn->real_escape_string($actionType);
        $adminVal = $adminId ? $adminId : "NULL";
        $reasonVal = $reason ? "'" . $conn->real_escape_string($reason) . "'" : "NULL";
        
        $sql = "INSERT INTO host_application_history 
                (host_application_id, previous_status, new_status, action_type, 
                 reviewed_by_admin_id, rejection_reason, created_at) 
                VALUES 
                ($applicationId, $prevStatus, '$newStat', '$actType', 
                 $adminVal, $reasonVal, CURRENT_TIMESTAMP)";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result ? true : false;
    }
    
    /**
     * Get history of an application
     * 
     * @param int $applicationId
     * @return array
     */
    public function getApplicationHistory($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT h.*, 
                       a.username as admin_username,
                       a.full_name as admin_name
                FROM host_application_history h
                LEFT JOIN admin a ON h.reviewed_by_admin_id = a.admin_id
                WHERE h.host_application_id = $applicationId
                ORDER BY h.created_at DESC";
        
        $result = $conn->query($sql);
        $history = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $history;
    }
    
    /**
     * Count resubmissions (how many times status changed from rejected to pending)
     * 
     * @param int $applicationId
     * @return int
     */
    public function countResubmissions($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count 
                FROM host_application_history 
                WHERE host_application_id = $applicationId 
                  AND action_type = 'resubmitted'
                  AND previous_status = 'rejected'
                  AND new_status = 'pending'";
        
        $result = $conn->query($sql);
        $count = 0;
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $count = (int)$row['count'];
        }
        
        $p->mDongKetNoi($conn);
        return $count;
    }
    
    /**
     * Get latest rejection reason
     * 
     * @param int $applicationId
     * @return string|null
     */
    public function getLatestRejectionReason($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT rejection_reason 
                FROM host_application_history 
                WHERE host_application_id = $applicationId 
                  AND new_status = 'rejected'
                  AND rejection_reason IS NOT NULL
                ORDER BY created_at DESC
                LIMIT 1";
        
        $result = $conn->query($sql);
        $reason = null;
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $reason = $row['rejection_reason'];
        }
        
        $p->mDongKetNoi($conn);
        return $reason;
    }
}
?>
