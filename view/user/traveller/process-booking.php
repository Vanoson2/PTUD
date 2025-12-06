<?php
// Include Authentication Helper and Controllers
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cBooking.php';
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

// Booking thành công - redirect đến trang thành công
$_SESSION['success_message'] = 'Đặt chỗ thành công! Mã đơn đặt: ' . $bookingResult['booking_code'];
header('Location: booking-success.php?booking_id=' . $bookingId);
exit;
?>
