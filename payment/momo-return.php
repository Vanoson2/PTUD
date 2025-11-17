<?php
/**
 * MoMo Return URL Handler
 * 
 * Người dùng sẽ được redirect về trang này sau khi thanh toán trên MoMo
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include dependencies
require_once(__DIR__ . '/../controller/cPayment.php');
require_once(__DIR__ . '/../controller/cBooking.php');
require_once(__DIR__ . '/../config/momo.php');

try {
    // Log return request
    logMoMoPayment('Return URL accessed', [
        'GET' => $_GET,
        'session_user_id' => $_SESSION['user_id'] ?? 'not_set'
    ]);
    
    // Lấy parameters từ MoMo
    $partnerCode = $_GET['partnerCode'] ?? '';
    $orderId = $_GET['orderId'] ?? '';
    $requestId = $_GET['requestId'] ?? '';
    $amount = $_GET['amount'] ?? 0;
    $orderInfo = $_GET['orderInfo'] ?? '';
    $orderType = $_GET['orderType'] ?? '';
    $transId = $_GET['transId'] ?? '';
    $resultCode = $_GET['resultCode'] ?? -1;
    $message = $_GET['message'] ?? '';
    $payType = $_GET['payType'] ?? '';
    $responseTime = $_GET['responseTime'] ?? 0;
    $extraData = $_GET['extraData'] ?? '';
    $signature = $_GET['signature'] ?? '';
    
    // Verify signature
    $momoHelper = new MoMoHelper();
    $isValidSignature = $momoHelper->verifySignature($_GET);
    
    if (!$isValidSignature) {
        logMoMoPayment('Return URL - Invalid signature', $_GET);
        $_SESSION['payment_error'] = 'Chữ ký không hợp lệ. Giao dịch có thể bị giả mạo.';
        header('Location: ../view/user/traveller/payment-error.php');
        exit;
    }
    
    // Lấy thông tin booking từ extraData
    $extraDataArray = $momoHelper->parseExtraData($extraData);
    $bookingId = $extraDataArray['booking_id'] ?? null;
    
    if (!$bookingId) {
        // Thử extract từ orderId
        preg_match('/WEGO_(\d+)_/', $orderId, $matches);
        $bookingId = $matches[1] ?? null;
    }
    
    if (!$bookingId) {
        logMoMoPayment('Return URL - Cannot find booking ID', [
            'orderId' => $orderId,
            'extraData' => $extraData
        ]);
        $_SESSION['payment_error'] = 'Không tìm thấy thông tin đơn đặt chỗ.';
        header('Location: ../view/user/traveller/payment-error.php');
        exit;
    }
    
    // Kiểm tra kết quả thanh toán
    if ($resultCode == 0) {
        // Thanh toán thành công
        logMoMoPayment('Return URL - Payment successful', [
            'bookingId' => $bookingId,
            'orderId' => $orderId,
            'transId' => $transId,
            'amount' => $amount
        ]);
        
        // Set session success
        $_SESSION['payment_success'] = true;
        $_SESSION['payment_trans_id'] = $transId;
        $_SESSION['payment_amount'] = $amount;
        
        // Redirect đến trang booking success
        header('Location: ../view/user/traveller/booking-success.php?booking_id=' . $bookingId . '&payment=success');
        exit;
        
    } else {
        // Thanh toán thất bại
        logMoMoPayment('Return URL - Payment failed', [
            'bookingId' => $bookingId,
            'orderId' => $orderId,
            'resultCode' => $resultCode,
            'message' => $message
        ]);
        
        // Map error message
        $errorMessage = $message;
        switch ($resultCode) {
            case 1006:
                $errorMessage = 'Giao dịch bị từ chối bởi người dùng';
                break;
            case 1001:
                $errorMessage = 'Giao dịch thất bại do tài khoản người dùng không đủ tiền';
                break;
            case 9000:
                $errorMessage = 'Giao dịch đã được xác nhận thành công nhưng giao dịch bị trùng';
                break;
            case 8000:
                $errorMessage = 'Giao dịch đang được xử lý';
                break;
            default:
                $errorMessage = $message ?: 'Thanh toán không thành công';
        }
        
        $_SESSION['payment_error'] = $errorMessage;
        $_SESSION['payment_result_code'] = $resultCode;
        $_SESSION['payment_booking_id'] = $bookingId;
        
        // Redirect đến trang payment error
        header('Location: ../view/user/traveller/payment-error.php?booking_id=' . $bookingId);
        exit;
    }
    
} catch (Exception $e) {
    logMoMoPayment('Return URL Exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    $_SESSION['payment_error'] = 'Có lỗi xảy ra khi xử lý kết quả thanh toán.';
    header('Location: ../view/user/traveller/payment-error.php');
    exit;
}

?>
