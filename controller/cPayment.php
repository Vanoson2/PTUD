<?php
/**
 * Controller xử lý Payment
 */

include_once(__DIR__ . '/../model/mPayment.php');
include_once(__DIR__ . '/../helper/MoMoHelper.php');

class cPayment {
    
    /**
     * Khởi tạo thanh toán MoMo cho booking
     */
    public function cInitiateMoMoPayment($bookingId, $amount, $bookingCode, $listingTitle, $userInfo = []) {
        try {
            // Tạo order info
            $orderInfo = "WeGo - " . $bookingCode . " - " . $listingTitle;
            
            // Extra data (có thể chứa thêm thông tin)
            $extraData = [
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
                'user_name' => $userInfo['full_name'] ?? '',
                'user_email' => $userInfo['email'] ?? ''
            ];
            
            // Khởi tạo MoMo payment
            $momoHelper = new MoMoHelper();
            $result = $momoHelper->createPayment($bookingId, $amount, $orderInfo, $extraData);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'PAYMENT_INIT_FAILED'
                ];
            }
            
            // Lưu transaction vào database
            $mPayment = new mPayment();
            $transactionId = $mPayment->mCreateTransaction(
                $bookingId,
                $result['orderId'],
                $result['requestId'],
                $amount,
                $orderInfo,
                $result['payUrl'],
                $result['signature'],
                json_encode($extraData)
            );
            
            if (!$transactionId) {
                return [
                    'success' => false,
                    'message' => 'Không thể lưu thông tin giao dịch',
                    'error_code' => 'DATABASE_ERROR'
                ];
            }
            
            // Cập nhật booking payment status sang pending
            $mPayment->mUpdateBookingPaymentStatus($bookingId, 'pending', 'momo', $result['orderId']);
            
            return [
                'success' => true,
                'payUrl' => $result['payUrl'],
                'deeplink' => $result['deeplink'],
                'qrCodeUrl' => $result['qrCodeUrl'],
                'orderId' => $result['orderId'],
                'transactionId' => $transactionId,
                'message' => 'Khởi tạo thanh toán thành công'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Xử lý IPN callback từ MoMo
     */
    public function cProcessMoMoIPN($ipnData) {
        try {
            // Verify signature
            $momoHelper = new MoMoHelper();
            if (!$momoHelper->verifySignature($ipnData)) {
                return [
                    'success' => false,
                    'message' => 'Invalid signature',
                    'error_code' => 'INVALID_SIGNATURE'
                ];
            }
            
            $orderId = $ipnData['orderId'] ?? '';
            $resultCode = $ipnData['resultCode'] ?? -1;
            $transId = $ipnData['transId'] ?? '';
            $message = $ipnData['message'] ?? '';
            $payType = $ipnData['payType'] ?? '';
            $responseTime = $ipnData['responseTime'] ?? 0;
            $extraData = $ipnData['extraData'] ?? '';
            
            // Cập nhật transaction
            $mPayment = new mPayment();
            $updateResult = $mPayment->mUpdateTransaction(
                $orderId,
                $transId,
                $resultCode,
                $message,
                $payType,
                $responseTime,
                $extraData
            );
            
            if (!$updateResult) {
                return [
                    'success' => false,
                    'message' => 'Failed to update transaction',
                    'error_code' => 'UPDATE_FAILED'
                ];
            }
            
            // Lấy thông tin transaction để biết booking_id
            $transaction = $mPayment->mGetTransactionByOrderId($orderId);
            
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                    'error_code' => 'TRANSACTION_NOT_FOUND'
                ];
            }
            
            $bookingId = $transaction['booking_id'];
            
            // Cập nhật booking payment status
            if ($resultCode == 0) {
                // Thanh toán thành công
                $mPayment->mUpdateBookingPaymentStatus($bookingId, 'paid', 'momo', $transId);
                
                // TODO: Có thể gửi email xác nhận thanh toán ở đây
                
                return [
                    'success' => true,
                    'message' => 'Payment successful',
                    'booking_id' => $bookingId
                ];
            } else {
                // Thanh toán thất bại
                $mPayment->mUpdateBookingPaymentStatus($bookingId, 'unpaid', 'momo', null);
                
                return [
                    'success' => true,
                    'message' => 'Payment failed',
                    'booking_id' => $bookingId,
                    'result_code' => $resultCode
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'System error: ' . $e->getMessage(),
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Kiểm tra trạng thái thanh toán của booking
     */
    public function cCheckPaymentStatus($bookingId) {
        $mPayment = new mPayment();
        $transaction = $mPayment->mGetTransactionByBookingId($bookingId);
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found',
                'status' => 'not_found'
            ];
        }
        
        return [
            'success' => true,
            'status' => $transaction['status'],
            'result_code' => $transaction['result_code'],
            'message' => $transaction['message'],
            'trans_id' => $transaction['trans_id'],
            'transaction' => $transaction
        ];
    }
    
    /**
     * Query transaction từ MoMo (để check lại status)
     */
    public function cQueryMoMoTransaction($bookingId) {
        $mPayment = new mPayment();
        $transaction = $mPayment->mGetTransactionByBookingId($bookingId);
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }
        
        $momoHelper = new MoMoHelper();
        $result = $momoHelper->queryTransaction($transaction['order_id'], $transaction['request_id']);
        
        if ($result && isset($result['resultCode'])) {
            // Cập nhật lại transaction nếu có thay đổi
            if ($result['resultCode'] == 0 && $transaction['status'] !== 'success') {
                $mPayment->mUpdateTransaction(
                    $transaction['order_id'],
                    $result['transId'] ?? '',
                    $result['resultCode'],
                    $result['message'] ?? '',
                    $result['payType'] ?? '',
                    $result['responseTime'] ?? 0
                );
                
                $mPayment->mUpdateBookingPaymentStatus($bookingId, 'paid', 'momo', $result['transId'] ?? null);
            }
            
            return [
                'success' => true,
                'result' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to query MoMo'
        ];
    }
}

?>
