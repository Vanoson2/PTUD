<?php
/**
 * MoMo Payment Helper
 * Xử lý tất cả các tương tác với MoMo API
 */

require_once(__DIR__ . '/../config/momo.php');
require_once(__DIR__ . '/../model/mPaymentLog.php');

class MoMoHelper {
    
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $endpoint;
    private $paymentLogger;
    
    public function __construct() {
        $this->partnerCode = MOMO_PARTNER_CODE;
        $this->accessKey = MOMO_ACCESS_KEY;
        $this->secretKey = MOMO_SECRET_KEY;
        $this->endpoint = MOMO_ENDPOINT;
        $this->paymentLogger = new mPaymentLog();
    }
    
    /**
     * Tạo payment request đến MoMo
     * 
     * @param int $bookingId ID của booking
     * @param float $amount Số tiền thanh toán
     * @param string $orderInfo Thông tin đơn hàng
     * @param array $extraData Dữ liệu bổ sung (optional)
     * @return array Kết quả từ MoMo hoặc error
     */
    public function createPayment($bookingId, $amount, $orderInfo, $extraData = []) {
        try {
            // Convert amount to integer (MoMo yêu cầu integer, không có decimal)
            $amount = (int)$amount;
            
            // Generate unique IDs
            $orderId = 'WEGO_' . $bookingId . '_' . time();
            $requestId = time() . "_" . $bookingId;
            
            // Convert extra data to JSON string
            $extraDataString = !empty($extraData) ? json_encode($extraData) : "";
            
            // Tạo chữ ký (signature)
            $rawHash = "accessKey=" . $this->accessKey . 
                       "&amount=" . $amount . 
                       "&extraData=" . $extraDataString . 
                       "&ipnUrl=" . MOMO_IPN_URL . 
                       "&orderId=" . $orderId . 
                       "&orderInfo=" . $orderInfo . 
                       "&partnerCode=" . $this->partnerCode . 
                       "&redirectUrl=" . MOMO_RETURN_URL . 
                       "&requestId=" . $requestId . 
                       "&requestType=" . MOMO_REQUEST_TYPE;
            
            $signature = hash_hmac("sha256", $rawHash, $this->secretKey);
            
            // Chuẩn bị data gửi đến MoMo
            $data = [
                'partnerCode' => $this->partnerCode,
                'partnerName' => MOMO_PARTNER_NAME,
                'storeId' => MOMO_STORE_ID,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => MOMO_RETURN_URL,
                'ipnUrl' => MOMO_IPN_URL,
                'lang' => MOMO_LANG,
                'extraData' => $extraDataString,
                'requestType' => MOMO_REQUEST_TYPE,
                'signature' => $signature
            ];
            
            // Log request vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId,
                null,
                'init',
                $data,
                null,
                null,
                null
            );
            
            // Log ra file (minimal - chỉ event type)
            logMoMoPayment('Create payment request', [
                'bookingId' => $bookingId,
                'orderId' => $orderId,
                'amount' => $amount
            ]);
            
            // Gửi request đến MoMo
            $result = $this->execPostRequest($this->endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);
            
            // Log response vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId,
                null,
                'init',
                null,
                $jsonResult,
                $jsonResult['resultCode'] ?? null,
                $jsonResult['message'] ?? null
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Create payment response', [
                'resultCode' => $jsonResult['resultCode'] ?? null,
                'message' => $jsonResult['message'] ?? null
            ]);
            
            if (!$jsonResult) {
                return [
                    'success' => false,
                    'message' => 'Không thể kết nối đến MoMo. Vui lòng thử lại sau.',
                    'error_code' => 'CONNECTION_ERROR'
                ];
            }
            
            // Kiểm tra kết quả
            if (isset($jsonResult['resultCode']) && $jsonResult['resultCode'] == 0) {
                return [
                    'success' => true,
                    'payUrl' => $jsonResult['payUrl'],
                    'deeplink' => $jsonResult['deeplink'] ?? null,
                    'qrCodeUrl' => $jsonResult['qrCodeUrl'] ?? null,
                    'orderId' => $orderId,
                    'requestId' => $requestId,
                    'signature' => $signature,
                    'message' => 'Tạo thanh toán thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $jsonResult['message'] ?? 'Có lỗi xảy ra khi tạo thanh toán',
                    'error_code' => $jsonResult['resultCode'] ?? 'UNKNOWN_ERROR',
                    'details' => $jsonResult
                ];
            }
            
        } catch (Exception $e) {
            // Log error vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId ?? null,
                null,
                'error',
                null,
                null,
                null,
                'Create payment exception: ' . $e->getMessage()
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Create payment exception', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Xác thực chữ ký từ MoMo IPN/Return
     * 
     * @param array $data Dữ liệu nhận từ MoMo
     * @return bool True nếu signature hợp lệ
     */
    public function verifySignature($data) {
        try {
            $momoSignature = $data['signature'] ?? '';
            
            // Tạo lại chữ ký để so sánh
            $rawHash = "accessKey=" . $this->accessKey . 
                       "&amount=" . ($data['amount'] ?? '') . 
                       "&extraData=" . ($data['extraData'] ?? '') . 
                       "&message=" . ($data['message'] ?? '') . 
                       "&orderId=" . ($data['orderId'] ?? '') . 
                       "&orderInfo=" . ($data['orderInfo'] ?? '') . 
                       "&orderType=" . ($data['orderType'] ?? '') . 
                       "&partnerCode=" . ($data['partnerCode'] ?? '') . 
                       "&payType=" . ($data['payType'] ?? '') . 
                       "&requestId=" . ($data['requestId'] ?? '') . 
                       "&responseTime=" . ($data['responseTime'] ?? '') . 
                       "&resultCode=" . ($data['resultCode'] ?? '') . 
                       "&transId=" . ($data['transId'] ?? '');
            
            $partnerSignature = hash_hmac("sha256", $rawHash, $this->secretKey);
            
            $isValid = ($momoSignature === $partnerSignature);
            
            // Tìm booking_id từ orderId hoặc extraData
            $bookingId = null;
            if (isset($data['orderId']) && strpos($data['orderId'], 'WEGO_') === 0) {
                $parts = explode('_', $data['orderId']);
                $bookingId = isset($parts[1]) ? intval($parts[1]) : null;
            }
            
            // Log vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId,
                isset($data['transId']) ? intval($data['transId']) : null,
                'ipn_verified',
                null,
                $data,
                $data['resultCode'] ?? null,
                $isValid ? null : 'Invalid signature'
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Verify signature', [
                'isValid' => $isValid,
                'resultCode' => $data['resultCode'] ?? null
            ]);
            
            return $isValid;
            
        } catch (Exception $e) {
            // Log error vào database
            $this->paymentLogger->mLogPaymentEvent(
                null,
                null,
                'error',
                null,
                null,
                null,
                'Verify signature exception: ' . $e->getMessage()
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Verify signature exception', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Query trạng thái giao dịch từ MoMo
     * 
     * @param string $orderId Order ID cần query
     * @param string $requestId Request ID
     * @return array Kết quả từ MoMo
     */
    public function queryTransaction($orderId, $requestId) {
        try {
            $queryEndpoint = str_replace('/create', '/query', $this->endpoint);
            
            $rawHash = "accessKey=" . $this->accessKey . 
                       "&orderId=" . $orderId . 
                       "&partnerCode=" . $this->partnerCode . 
                       "&requestId=" . $requestId;
            
            $signature = hash_hmac("sha256", $rawHash, $this->secretKey);
            
            $data = [
                'partnerCode' => $this->partnerCode,
                'requestId' => $requestId,
                'orderId' => $orderId,
                'lang' => MOMO_LANG,
                'signature' => $signature
            ];
            
            // Tìm booking_id từ orderId
            $bookingId = null;
            if (strpos($orderId, 'WEGO_') === 0) {
                $parts = explode('_', $orderId);
                $bookingId = isset($parts[1]) ? intval($parts[1]) : null;
            }
            
            // Log request vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId,
                null,
                'query',
                $data,
                null,
                null,
                null
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Query transaction request', [
                'orderId' => $orderId
            ]);
            
            $result = $this->execPostRequest($queryEndpoint, json_encode($data));
            $jsonResult = json_decode($result, true);
            
            // Log response vào database
            $this->paymentLogger->mLogPaymentEvent(
                $bookingId,
                isset($jsonResult['transId']) ? intval($jsonResult['transId']) : null,
                'query',
                null,
                $jsonResult,
                $jsonResult['resultCode'] ?? null,
                $jsonResult['message'] ?? null
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Query transaction response', [
                'resultCode' => $jsonResult['resultCode'] ?? null,
                'message' => $jsonResult['message'] ?? null
            ]);
            
            return $jsonResult;
            
        } catch (Exception $e) {
            // Log error vào database
            $this->paymentLogger->mLogPaymentEvent(
                null,
                null,
                'error',
                null,
                null,
                null,
                'Query transaction exception: ' . $e->getMessage()
            );
            
            // Log ra file (minimal)
            logMoMoPayment('Query transaction exception', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Thực hiện POST request với cURL
     * 
     * @param string $url URL endpoint
     * @param string $data JSON data
     * @return string Response body
     */
    private function execPostRequest($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, MOMO_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MOMO_CONNECT_TIMEOUT);
        
        // Disable SSL verification for test environment only
        if (MOMO_ENVIRONMENT === 'test') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            // Log CURL error vào database
            $this->paymentLogger->mLogPaymentEvent(
                null,
                null,
                'error',
                null,
                null,
                null,
                'CURL Error: ' . curl_error($ch) . ' (errno: ' . curl_errno($ch) . ')'
            );
            
            // Log ra file (minimal)
            logMoMoPayment('CURL Error', [
                'errno' => curl_errno($ch)
            ]);
        }
        
        curl_close($ch);
        return $result;
    }
    
    /**
     * Parse và trả về thông tin từ extraData
     * 
     * @param string $extraData JSON string
     * @return array|null
     */
    public function parseExtraData($extraData) {
        if (empty($extraData)) {
            return null;
        }
        
        $decoded = json_decode($extraData, true);
        return $decoded ?: null;
    }
}

?>
