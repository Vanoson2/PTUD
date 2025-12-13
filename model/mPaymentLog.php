<?php
/**
 * Payment Log Model
 * Xử lý việc ghi log thanh toán vào database
 */

include_once(__DIR__ . '/mConnect.php');

class mPaymentLog {
    
    /**
     * Ghi log vào database
     * 
     * @param int|null $bookingId ID đơn đặt chỗ
     * @param int|null $transactionId ID giao dịch
     * @param string $eventType Loại sự kiện (init, ipn_received, return_received, etc.)
     * @param array|null $requestData Dữ liệu request
     * @param array|null $responseData Dữ liệu response
     * @param int|null $resultCode Mã kết quả
     * @param string|null $errorMessage Thông báo lỗi
     * @return bool
     */
    public function mLogPaymentEvent(
        $bookingId = null,
        $transactionId = null,
        $eventType,
        $requestData = null,
        $responseData = null,
        $resultCode = null,
        $errorMessage = null
    ) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        try {
            // Lấy thông tin request
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $sessionId = session_id() ?: null;
            
            // Escape các giá trị
            $eventType = $conn->real_escape_string($eventType);
            $errorMessage = $errorMessage ? "'" . $conn->real_escape_string($errorMessage) . "'" : "NULL";
            $ipAddress = $ipAddress ? "'" . $conn->real_escape_string($ipAddress) . "'" : "NULL";
            $userAgent = $userAgent ? "'" . $conn->real_escape_string($userAgent) . "'" : "NULL";
            $sessionId = $sessionId ? "'" . $conn->real_escape_string($sessionId) . "'" : "NULL";
            
            // Convert arrays to JSON, loại bỏ sensitive data
            $requestDataJson = $requestData ? $this->sanitizeData($requestData) : null;
            $responseDataJson = $responseData ? $this->sanitizeData($responseData) : null;
            
            $requestDataStr = $requestDataJson ? "'" . $conn->real_escape_string(json_encode($requestDataJson)) . "'" : "NULL";
            $responseDataStr = $responseDataJson ? "'" . $conn->real_escape_string(json_encode($responseDataJson)) . "'" : "NULL";
            
            $bookingIdStr = $bookingId ? intval($bookingId) : "NULL";
            $transactionIdStr = $transactionId ? intval($transactionId) : "NULL";
            $resultCodeStr = $resultCode !== null ? intval($resultCode) : "NULL";
            
            $sql = "INSERT INTO payment_logs 
                    (booking_id, transaction_id, event_type, request_data, response_data, 
                     result_code, error_message, ip_address, user_agent, session_id) 
                    VALUES 
                    ($bookingIdStr, $transactionIdStr, '$eventType', $requestDataStr, $responseDataStr, 
                     $resultCodeStr, $errorMessage, $ipAddress, $userAgent, $sessionId)";
            
            $result = $conn->query($sql);
            $p->mDongKetNoi($conn);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('mPaymentLog Error: ' . $e->getMessage());
            $p->mDongKetNoi($conn);
            return false;
        }
    }
    
    /**
     * Sanitize data để loại bỏ sensitive information và mã hóa MD5
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeData($data) {
        if (!is_array($data)) {
            return $data;
        }
        
        $sanitized = $data;
        
        // Danh sách các key nhạy cảm cần mã hóa MD5
        $sensitiveKeys = ['accessKey', 'secretKey', 'password', 'signature'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key]) && !empty($sanitized[$key])) {
                // Mã hóa MD5 thay vì mask để có thể so sánh sau này nếu cần
                $sanitized[$key] = md5($sanitized[$key]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Lấy logs theo booking_id
     * 
     * @param int $bookingId
     * @param int $limit
     * @return mysqli_result|false
     */
    public function mGetLogsByBookingId($bookingId, $limit = 50) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $bookingId = intval($bookingId);
        $limit = intval($limit);
        
        $sql = "SELECT * FROM payment_logs 
                WHERE booking_id = $bookingId 
                ORDER BY created_at DESC 
                LIMIT $limit";
        
        return $conn->query($sql);
    }
    
    /**
     * Lấy logs theo transaction_id
     * 
     * @param int $transactionId
     * @return mysqli_result|false
     */
    public function mGetLogsByTransactionId($transactionId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $transactionId = intval($transactionId);
        
        $sql = "SELECT * FROM payment_logs 
                WHERE transaction_id = $transactionId 
                ORDER BY created_at ASC";
        
        return $conn->query($sql);
    }
    
    /**
     * Thống kê logs theo event_type trong khoảng thời gian
     * 
     * @param string $startDate
     * @param string $endDate
     * @return mysqli_result|false
     */
    public function mGetLogStatistics($startDate, $endDate) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $startDate = $conn->real_escape_string($startDate);
        $endDate = $conn->real_escape_string($endDate);
        
        $sql = "SELECT 
                    event_type,
                    COUNT(*) as total_count,
                    SUM(CASE WHEN result_code = 0 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN result_code != 0 OR result_code IS NULL THEN 1 ELSE 0 END) as error_count
                FROM payment_logs 
                WHERE created_at BETWEEN '$startDate' AND '$endDate'
                GROUP BY event_type";
        
        return $conn->query($sql);
    }
}
?>
