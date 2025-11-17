<?php
/**
 * Retry Payment - Khởi tạo lại thanh toán cho booking đã tạo
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$bookingId = $_GET['booking_id'] ?? 0;

if (empty($bookingId)) {
    $_SESSION['error'] = 'Không tìm thấy thông tin đơn đặt chỗ';
    header('Location: my-bookings.php');
    exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cBooking.php');
include_once(__DIR__ . '/../../../controller/cPayment.php');
include_once(__DIR__ . '/../../../model/mUser.php');

$userId = $_SESSION['user_id'];

// Get booking info
$cBooking = new cBooking();
$bookingResult = $cBooking->cGetBookingById($bookingId);

if (!$bookingResult || $bookingResult->num_rows == 0) {
    $_SESSION['error'] = 'Không tìm thấy đơn đặt chỗ';
    header('Location: my-bookings.php');
    exit;
}

$booking = $bookingResult->fetch_assoc();

// Check if booking belongs to user
if ($booking['user_id'] != $userId) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập đơn đặt chỗ này';
    header('Location: my-bookings.php');
    exit;
}

// Check if booking is already paid
if ($booking['payment_status'] === 'paid') {
    $_SESSION['success'] = 'Đơn đặt chỗ này đã được thanh toán';
    header('Location: booking-success.php?booking_id=' . $bookingId);
    exit;
}

// Check if booking is cancelled
if ($booking['status'] === 'cancelled') {
    $_SESSION['error'] = 'Đơn đặt chỗ đã bị hủy, không thể thanh toán';
    header('Location: my-bookings.php');
    exit;
}

// Get user info
$mUser = new mUser();
$userInfo = $mUser->mGetUserById($userId);

// Khởi tạo lại thanh toán MoMo
$cPayment = new cPayment();
$paymentResult = $cPayment->cInitiateMoMoPayment(
    $bookingId,
    $booking['total_amount'],
    $booking['code'],
    $booking['listing_title'],
    [
        'full_name' => $userInfo['full_name'] ?? '',
        'email' => $userInfo['email'] ?? ''
    ]
);

if (!$paymentResult['success']) {
    $_SESSION['payment_init_error'] = $paymentResult['message'];
    header('Location: payment-error.php?booking_id=' . $bookingId);
    exit;
}

// Lưu thông tin vào session
$_SESSION['booking_id'] = $bookingId;
$_SESSION['payment_order_id'] = $paymentResult['orderId'];

// Redirect đến MoMo payment page
header('Location: ' . $paymentResult['payUrl']);
exit;
?>
