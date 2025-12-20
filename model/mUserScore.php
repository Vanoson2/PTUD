<?php
/**
 * Model xử lý Trust Score System (Hệ thống điểm tín nhiệm)
 */

include_once(__DIR__ . "/mConnect.php");
include_once(__DIR__ . "/../helper/EncryptionHelper.php");

class mUserScore {
    
    /**
     * Lấy điểm tín nhiệm hiện tại của user
     */
    public function mGetUserScore($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) return null;
        
        $userId = intval($userId);
        $sql = "SELECT trust_score, is_verified, verified_phone, verified_id, 
                       verification_docs, last_score_update
                FROM user 
                WHERE user_id = $userId";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            // Decrypt verification_docs if exists
            if (!empty($data['verification_docs'])) {
                $data['verification_docs'] = EncryptionHelper::decrypt($data['verification_docs']);
            }
            
            return $data;
        }
        
        return null;
    }
    
    /**
     * Cập nhật điểm tín nhiệm
     * @param int $userId
     * @param int $scoreChange Số điểm thay đổi (+/-)
     * @param string $reason Lý do
     * @param string $reasonDetail Chi tiết
     * @param string $relatedType booking|review|listing|verification|admin_action|auto|other
     * @param int $relatedId ID liên quan
     * @param int $adminId ID admin (nếu là admin action)
     * @return array
     */
    public function mUpdateUserScore($userId, $scoreChange, $reason, $reasonDetail = null, $relatedType = null, $relatedId = null, $adminId = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $userId = intval($userId);
        $scoreChange = intval($scoreChange);
        
        // Lấy điểm hiện tại
        $currentScore = $this->mGetUserScore($userId);
        
        if (!$currentScore) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy user'
            ];
        }
        
        $oldScore = $currentScore['trust_score'];
        $newScore = max(0, min(150, $oldScore + $scoreChange)); // Giới hạn 0-150
        
        // Tự động khóa tài khoản nếu điểm < 30
        $autoLock = ($newScore < 30);
        $statusUpdate = $autoLock ? ", status = 'locked'" : "";
        
        // Update điểm trong bảng user
        $sqlUpdate = "UPDATE user 
                      SET trust_score = $newScore,
                          last_score_update = NOW()
                          $statusUpdate
                      WHERE user_id = $userId";
        
        if (!$conn->query($sqlUpdate)) {
            return [
                'success' => false,
                'message' => 'Không thể cập nhật điểm'
            ];
        }
        
        // Lưu vào lịch sử
        $reason = $conn->real_escape_string($reason);
        $reasonDetail = $reasonDetail ? "'" . $conn->real_escape_string($reasonDetail) . "'" : 'NULL';
        $relatedType = $relatedType ? "'" . $conn->real_escape_string($relatedType) . "'" : 'NULL';
        $relatedId = $relatedId ? intval($relatedId) : 'NULL';
        $adminId = $adminId ? intval($adminId) : 'NULL';
        
        // Combine reason and reasonDetail into single reason field
        $fullReason = $reason;
        if ($reasonDetail) {
            $fullReason .= ' - ' . $conn->real_escape_string($reasonDetail);
        }
        
        $sqlHistory = "INSERT INTO user_score_history 
                       (user_id, score_change, old_score, new_score, reason, related_type, related_id, admin_id)
                       VALUES 
                       ($userId, $scoreChange, $oldScore, $newScore, '$fullReason', $relatedType, $relatedId, $adminId)";
        
        $conn->query($sqlHistory);
        
        // Xác định mức cảnh báo
        $hasWarning = ($newScore >= 30 && $newScore < 50);
        $warningLevel = null;
        
        $message = 'Cập nhật điểm thành công';
        
        if ($autoLock) {
            $message .= '. Tài khoản đã bị khóa do điểm tín nhiệm thấp (< 30)';
            $warningLevel = 'danger';
        } elseif ($hasWarning) {
            $message .= '. Cảnh báo: Điểm tín nhiệm của bạn đang ở mức thấp (' . $newScore . '/150). Vui lòng cẩn thận để tránh bị khóa tài khoản';
            $warningLevel = 'warning';
        }
        
        return [
            'success' => true,
            'message' => $message,
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'change' => $scoreChange,
            'auto_locked' => $autoLock,
            'has_warning' => $hasWarning,
            'warning_level' => $warningLevel
        ];
    }
    
    /**
     * Cộng điểm dựa trên action type từ bảng config
     */
    public function mAddScoreByAction($userId, $actionType, $reasonDetail = null, $relatedType = null, $relatedId = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) return ['success' => false, 'message' => 'Không thể kết nối database'];
        
        $userId = intval($userId);
        $actionType = $conn->real_escape_string($actionType);
        
        // Kiểm tra xem action loại "verify_email" đã được cộng điểm chưa (tránh trùng lặp)
        if (in_array($actionType, ['verify_email', 'verify_phone', 'verify_id', 'first_booking'])) {
            $checkSql = "SELECT history_id FROM user_score_history 
                         WHERE user_id = $userId 
                         AND reason LIKE '%$actionType%'
                         LIMIT 1";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult && $checkResult->num_rows > 0) {
                // Đã cộng điểm rồi, không cộng nữa
                return ['success' => false, 'message' => 'Đã nhận điểm cho hành động này rồi'];
            }
        }
        
        // Lấy score change từ config
        $sql = "SELECT score_change, description FROM score_config WHERE action_type = '$actionType' AND is_active = 1";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            return ['success' => false, 'message' => 'Không tìm thấy cấu hình action'];
        }
        
        $config = $result->fetch_assoc();
        $scoreChange = $config['score_change'];
        $reason = $config['description'];
        
        return $this->mUpdateUserScore($userId, $scoreChange, $reason, $reasonDetail, $relatedType, $relatedId);
    }
    
    /**
     * Lấy lịch sử thay đổi điểm
     */
    public function mGetScoreHistory($userId, $limit = 20) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) return [];
        
        $userId = intval($userId);
        $limit = intval($limit);
        
        $sql = "SELECT * FROM user_score_history 
                WHERE user_id = $userId 
                ORDER BY created_at DESC 
                LIMIT $limit";
        
        $result = $conn->query($sql);
        $history = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        
        return $history;
    }
    
    /**
     * Kiểm tra và cập nhật điểm theo thời gian hoạt động
     */
    public function mCheckAccountAgeBonus($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) return false;
        
        $userId = intval($userId);
        
        // Lấy ngày tạo tài khoản
        $sql = "SELECT created_at FROM user WHERE user_id = $userId";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) return false;
        
        $user = $result->fetch_assoc();
        $createdDate = new DateTime($user['created_at']);
        $now = new DateTime();
        $diff = $now->diff($createdDate);
        $months = $diff->y * 12 + $diff->m;
        
        // Kiểm tra xem đã nhận bonus chưa
        $checkSql = "SELECT * FROM user_score_history 
                     WHERE user_id = $userId 
                     AND reason IN ('Tài khoản hoạt động > 6 tháng', 'Tài khoản hoạt động > 1 năm')";
        $checkResult = $conn->query($checkSql);
        $received = [];
        
        if ($checkResult) {
            while ($row = $checkResult->fetch_assoc()) {
                $received[] = $row['reason'];
            }
        }
        
        // Tặng điểm nếu chưa nhận
        if ($months >= 6 && !in_array('Tài khoản hoạt động > 6 tháng', $received)) {
            $this->mAddScoreByAction($userId, 'account_6_months', 'Thưởng tự động cho tài khoản 6 tháng');
        }
        
        if ($months >= 12 && !in_array('Tài khoản hoạt động > 1 năm', $received)) {
            $this->mAddScoreByAction($userId, 'account_1_year', 'Thưởng tự động cho tài khoản 1 năm');
        }
        
        return true;
    }
    
    /**
     * Cập nhật trạng thái xác thực
     * @return array ['success' => bool, 'message' => string]
     */
    public function mUpdateVerificationStatus($userId, $type, $status, $docs = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        $userId = intval($userId);
        $status = intval($status);
        
        $column = '';
        $action = '';
        
        switch ($type) {
            case 'phone':
                $column = 'verified_phone';
                $action = 'verify_phone';
                break;
            case 'id':
                $column = 'verified_id';
                $action = 'verify_id';
                break;
            case 'email':
                // Email verification is in is_email_verified
                $column = 'is_email_verified';
                $action = 'verify_email';
                break;
            default:
                return ['success' => false, 'message' => 'Loại xác thực không hợp lệ'];
        }
        
        // Update verification status
        $sql = "UPDATE user SET $column = $status";
        
        if ($docs && $type === 'id') {
            $docsJson = json_encode($docs);
            // Encrypt verification_docs before storing
            $encryptedDocs = EncryptionHelper::encrypt($docsJson);
            $encryptedDocs = $conn->real_escape_string($encryptedDocs);
            $sql .= ", verification_docs = '$encryptedDocs'";
        }
        
        $sql .= " WHERE user_id = $userId";
        
        if ($conn->query($sql) && $status == 1) {
            // Cộng điểm khi xác thực thành công
            $this->mAddScoreByAction($userId, $action, "Xác thực $type thành công", 'verification');
            
            // Kiểm tra nếu đã verify đủ thì set is_verified = 1
            $checkSql = "SELECT is_email_verified, verified_phone, verified_id FROM user WHERE user_id = $userId";
            $result = $conn->query($checkSql);
            
            if ($result) {
                $user = $result->fetch_assoc();
                if ($user['is_email_verified'] && $user['verified_phone'] && $user['verified_id']) {
                    $conn->query("UPDATE user SET is_verified = 1 WHERE user_id = $userId");
                }
            }
            
            return ['success' => true, 'message' => 'Cập nhật xác thực thành công'];
        }
        
        return ['success' => false, 'message' => 'Không thể cập nhật trạng thái xác thực'];
    }
    
    /**
     * Lấy level/rank dựa trên điểm
     */
    public function mGetUserLevel($score) {
        if ($score >= 90) {
            return [
                'level' => 'Xuất sắc',
                'icon' => '🏆',
                'color' => 'gold',
                'description' => 'Người dùng đáng tin cậy cao'
            ];
        } elseif ($score >= 80) {
            return [
                'level' => 'Tốt',
                'icon' => '⭐',
                'color' => 'success',
                'description' => 'Người dùng đáng tin cậy'
            ];
        } elseif ($score >= 60) {
            return [
                'level' => 'Trung bình',
                'icon' => '✓',
                'color' => 'info',
                'description' => 'Người dùng bình thường'
            ];
        } elseif ($score >= 40) {
            return [
                'level' => 'Thấp',
                'icon' => '⚠️',
                'color' => 'warning',
                'description' => 'Cần cải thiện'
            ];
        } else {
            return [
                'level' => 'Nguy hiểm',
                'icon' => '🚫',
                'color' => 'danger',
                'description' => 'Nguy cơ bị khóa'
            ];
        }
    }
    
    /**
     * Lấy gợi ý cải thiện điểm
     */
    public function mGetImprovementSuggestions($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) return [];
        
        $userId = intval($userId);
        
        // Lấy trạng thái hiện tại
        $sql = "SELECT is_email_verified FROM user WHERE user_id = $userId";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) return [];
        
        $user = $result->fetch_assoc();
        $suggestions = [];
        
        // 1. Xác thực email (ĐÃ IMPLEMENT)
        if (!$user['is_email_verified']) {
            $suggestions[] = [
                'action' => 'Xác thực email để tăng độ tin cậy',
                'points' => '+5',
                'icon' => '📧'
            ];
        }
        
        // 2. Tips chung
        $suggestions[] = [
            'action' => 'Tránh hủy booking sau khi xác nhận',
            'points' => '-5 nếu hủy',
            'icon' => '�'
        ];
        
        $suggestions[] = [
            'action' => 'Không vi phạm chính sách nền tảng',
            'points' => '-15 đến -50',
            'icon' => '⚠️'
        ];
        
        $suggestions[] = [
            'action' => 'Tài khoản hoạt động trên 6 tháng',
            'points' => '+10 tự động',
            'icon' => '📅'
        ];
        
        return $suggestions;
    }
}
?>
