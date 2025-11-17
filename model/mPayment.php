<?php
/**
 * Model xử lý Payment Transaction
 */

include_once(__DIR__ . '/mConnect.php');

class mPayment {
    
    /**
     * Tạo transaction record mới
     */
    public function mCreateTransaction($bookingId, $orderId, $requestId, $amount, $orderInfo, $paymentUrl, $signature, $extraData = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $bookingId = intval($bookingId);
        $orderId = $conn->real_escape_string($orderId);
        $requestId = $conn->real_escape_string($requestId);
        $amount = floatval($amount);
        $orderInfo = $conn->real_escape_string($orderInfo);
        $paymentUrl = $conn->real_escape_string($paymentUrl);
        $signature = $conn->real_escape_string($signature);
        $extraData = $conn->real_escape_string($extraData);
        
        $sql = "INSERT INTO payment_transaction 
                (booking_id, order_id, request_id, amount, order_info, payment_url, signature, extra_data, status)
                VALUES 
                ($bookingId, '$orderId', '$requestId', $amount, '$orderInfo', '$paymentUrl', '$signature', '$extraData', 'pending')";
        
        if ($conn->query($sql)) {
            $transactionId = $conn->insert_id;
            $p->mDongKetNoi($conn);
            return $transactionId;
        }
        
        $p->mDongKetNoi($conn);
        return false;
    }
    
    /**
     * Cập nhật transaction khi nhận IPN từ MoMo
     */
    public function mUpdateTransaction($orderId, $transId, $resultCode, $message, $payType, $responseTime, $extraData = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $orderId = $conn->real_escape_string($orderId);
        $transId = $conn->real_escape_string($transId);
        $resultCode = intval($resultCode);
        $message = $conn->real_escape_string($message);
        $payType = $conn->real_escape_string($payType);
        $responseTime = intval($responseTime);
        $extraData = $conn->real_escape_string($extraData);
        
        // Xác định status dựa trên resultCode
        $status = ($resultCode == 0) ? 'success' : 'failed';
        
        $sql = "UPDATE payment_transaction 
                SET trans_id = '$transId',
                    result_code = $resultCode,
                    message = '$message',
                    pay_type = '$payType',
                    response_time = $responseTime,
                    extra_data = '$extraData',
                    status = '$status',
                    updated_at = CURRENT_TIMESTAMP
                WHERE order_id = '$orderId'";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result;
    }
    
    /**
     * Lấy thông tin transaction theo order_id
     */
    public function mGetTransactionByOrderId($orderId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $orderId = $conn->real_escape_string($orderId);
        
        $sql = "SELECT * FROM payment_transaction WHERE order_id = '$orderId' LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $transaction;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    /**
     * Lấy thông tin transaction theo booking_id
     */
    public function mGetTransactionByBookingId($bookingId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $bookingId = intval($bookingId);
        
        $sql = "SELECT * FROM payment_transaction WHERE booking_id = $bookingId LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $transaction;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    /**
     * Cập nhật payment status của booking
     */
    public function mUpdateBookingPaymentStatus($bookingId, $paymentStatus, $paymentMethod = 'momo', $paymentId = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $bookingId = intval($bookingId);
        $paymentStatus = $conn->real_escape_string($paymentStatus);
        $paymentMethod = $conn->real_escape_string($paymentMethod);
        $paymentId = $paymentId ? "'" . $conn->real_escape_string($paymentId) . "'" : "NULL";
        
        // Nếu paid thì set paid_at
        $paidAtClause = ($paymentStatus === 'paid') ? ", paid_at = CURRENT_TIMESTAMP" : "";
        
        // Nếu thanh toán thành công, chuyển status booking sang confirmed
        $statusClause = ($paymentStatus === 'paid') ? ", status = 'confirmed'" : "";
        
        $sql = "UPDATE bookings 
                SET payment_status = '$paymentStatus',
                    payment_method = '$paymentMethod',
                    payment_id = $paymentId
                    $paidAtClause
                    $statusClause
                WHERE booking_id = $bookingId";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result;
    }
    
    /**
     * Lấy danh sách transactions với filter
     */
    public function mGetTransactions($status = null, $limit = 50, $offset = 0) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $whereClause = "";
        if ($status) {
            $status = $conn->real_escape_string($status);
            $whereClause = "WHERE status = '$status'";
        }
        
        $limit = intval($limit);
        $offset = intval($offset);
        
        $sql = "SELECT pt.*, b.code as booking_code, b.user_id, u.full_name as user_name
                FROM payment_transaction pt
                INNER JOIN bookings b ON pt.booking_id = b.booking_id
                INNER JOIN user u ON b.user_id = u.user_id
                $whereClause
                ORDER BY pt.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($sql);
        return $result;
    }
}

?>
