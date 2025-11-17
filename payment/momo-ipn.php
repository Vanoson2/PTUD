<?php
/**
 * MoMo IPN (Instant Payment Notification) Handler
 * 
 * File này nhận callback từ MoMo server khi có kết quả thanh toán
 * ⚠️ QUAN TRỌNG: URL này phải public và accessible từ internet
 */

// Chặn truy cập trực tiếp từ browser
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Set header
header('Content-Type: application/json; charset=utf-8');

// Include dependencies
require_once(__DIR__ . '/../controller/cPayment.php');
require_once(__DIR__ . '/../config/momo.php');

try {
    // Log IPN request
    logMoMoPayment('IPN Request received', [
        'POST' => $_POST,
        'raw_input' => file_get_contents('php://input'),
        'headers' => getallheaders()
    ]);
    
    // Lấy dữ liệu từ MoMo
    // MoMo có thể gửi dưới dạng POST form hoặc JSON
    $ipnData = $_POST;
    
    // Nếu không có POST data, thử parse JSON
    if (empty($ipnData)) {
        $rawInput = file_get_contents('php://input');
        $ipnData = json_decode($rawInput, true);
    }
    
    if (empty($ipnData)) {
        logMoMoPayment('IPN Error: Empty data');
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request data'
        ]);
        exit;
    }
    
    // Xử lý IPN
    $cPayment = new cPayment();
    $result = $cPayment->cProcessMoMoIPN($ipnData);
    
    // Log kết quả
    logMoMoPayment('IPN Processing result', $result);
    
    // Response cho MoMo
    // MoMo yêu cầu response với HTTP 200 và message
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    } else {
        // Vẫn trả 200 để MoMo không retry
        // Nhưng log lại để check
        http_response_code(200);
        echo json_encode([
            'status' => 'received',
            'message' => $result['message']
        ]);
    }
    
    // Gửi email thông báo (nếu cần)
    if ($result['success'] && isset($result['booking_id'])) {
        // TODO: Implement email notification
        // $this->sendPaymentConfirmationEmail($result['booking_id']);
    }
    
} catch (Exception $e) {
    logMoMoPayment('IPN Exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}

?>
