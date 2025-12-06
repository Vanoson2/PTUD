<?php
// Include Authentication Helper and Controllers
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cBooking.php';
require_once __DIR__ . '/../../../controller/cPayment.php';
require_once __DIR__ . '/../../../controller/cUser.php';

// Use helper for authentication
requireLogin();

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../../index.php');
  exit;
}

// Get POST data
$userId = getCurrentUserId();

// Validate user exists in database
$cUser = new cUser();
$user = $cUser->cGetUserById($userId);
if (!$user) {
  // User không tồn tại trong database - logout và redirect
  session_destroy();
  header('Location: ./login.php?error=session_expired&message=' . urlencode('Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.'));
  exit;
}

$listingId = $_POST['listing_id'] ?? 0;
$checkin = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$guests = $_POST['guests'] ?? 1;
$nights = $_POST['nights'] ?? 0;
$listingPrice = $_POST['listing_price'] ?? 0;
$selectedServices = $_POST['services'] ?? [];

$cBooking = new cBooking();
$cPayment = new cPayment();

// Process booking through Controller (handles all validation and business logic)
$bookingResult = $cBooking->cProcessBooking(
  $userId, 
  $listingId, 
  $checkin, 
  $checkout, 
  $guests, 
  $nights, 
  $listingPrice, 
  $selectedServices
);

// Handle booking errors
if (!$bookingResult['success']) {
  $_SESSION['error'] = $bookingResult['message'];
  header('Location: ' . $bookingResult['redirect']);
  exit;
}

$bookingId = $bookingResult['booking_id'];

// Get booking details
$bookingDetailResult = $cBooking->cGetBookingById($bookingId);
if (!$bookingDetailResult || $bookingDetailResult->num_rows == 0) {
  $_SESSION['error'] = 'Không thể lấy thông tin đơn đặt chỗ';
  header('Location: ../../index.php');
  exit;
}

$booking = $bookingDetailResult->fetch_assoc();

// Get user info through Controller
$cUser = new cUser();
$userInfo = $cUser->cGetUserProfile($userId);

// Khởi tạo thanh toán MoMo
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
  // Nếu không thể khởi tạo thanh toán, báo lỗi rõ ràng
  error_log('MoMo payment init failed: ' . ($paymentResult['message'] ?? 'Unknown error'));
  
  // Hiển thị lỗi chi tiết cho user
  $_SESSION['error'] = 'Không thể khởi tạo thanh toán MoMo: ' . ($paymentResult['message'] ?? 'Vui lòng thử lại sau');
  
  // Redirect về trang confirm để user thử lại
  header("Location: confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests&error=payment_init_failed");
  exit;
}

// Lưu thông tin vào session
$_SESSION['booking_id'] = $bookingId;
$_SESSION['payment_order_id'] = $paymentResult['orderId'];

// Redirect đến MoMo payment page
header('Location: ' . $paymentResult['payUrl']);
exit;
?>
