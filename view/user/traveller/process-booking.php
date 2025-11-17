<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../../index.php');
  exit;
}

// Get POST data
$userId = $_SESSION['user_id'];
$listingId = $_POST['listing_id'] ?? 0;
$checkin = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$guests = $_POST['guests'] ?? 1;
$nights = $_POST['nights'] ?? 0;
$listingPrice = $_POST['listing_price'] ?? 0;
$selectedServices = $_POST['services'] ?? [];

if (empty($listingId) || empty($checkin) || empty($checkout)) {
  $_SESSION['error'] = 'Thiếu thông tin đặt chỗ';
  header('Location: ../../index.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cBooking.php');
include_once(__DIR__ . '/../../../controller/cListing.php');
include_once(__DIR__ . '/../../../controller/cPayment.php');
include_once(__DIR__ . '/../../../controller/cUser.php');
include_once(__DIR__ . '/../../../model/mEmailPHPMailer.php');

$cBooking = new cBooking();
$cListing = new cListing();
$cPayment = new cPayment();

// Check 1: User có đơn đặt nào khác trùng ngày không?
$userConflictResult = $cBooking->cCheckUserBookingConflict($userId, $checkin, $checkout, $listingId);
if ($userConflictResult && $userConflictResult->num_rows > 0) {
  $conflict = $userConflictResult->fetch_assoc();
  $_SESSION['error'] = "Bạn đã có đơn đặt '{$conflict['listing_title']}' trùng thời gian (Mã: {$conflict['code']})";
  header("Location: confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests");
  exit;
}

// Check 2: Listing còn trống không?
$listingAvailabilityResult = $cBooking->cCheckListingAvailability($listingId, $checkin, $checkout);
if ($listingAvailabilityResult && $listingAvailabilityResult->num_rows > 0) {
  $_SESSION['error'] = 'Chỗ ở này đã được đặt trong khoảng thời gian bạn chọn';
  header("Location: confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests");
  exit;
}

// Calculate total amount
$subtotal = $listingPrice * $nights;
$servicesTotal = 0;
$servicesData = [];

if (count($selectedServices) > 0) {
  // Get services info
  $servicesResult = $cListing->cGetListingServices($listingId);
  if ($servicesResult && $servicesResult->num_rows > 0) {
    while ($serviceRow = $servicesResult->fetch_assoc()) {
      if (in_array($serviceRow['service_id'], $selectedServices)) {
        $servicesData[] = [
          'service_id' => $serviceRow['service_id'],
          'name' => $serviceRow['name'],
          'price' => $serviceRow['price']
        ];
        $servicesTotal += $serviceRow['price'];
      }
    }
  }
}

$totalAmount = $subtotal + $servicesTotal;

// Create booking với status pending payment
$bookingId = $cBooking->cCreateBooking($userId, $listingId, $checkin, $checkout, $guests, $totalAmount);

if (!$bookingId) {
  $_SESSION['error'] = 'Có lỗi xảy ra khi tạo đơn đặt chỗ';
  header("Location: confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests");
  exit;
}

// Add services to booking
if (count($servicesData) > 0) {
  $cBooking->cAddBookingServices($bookingId, $servicesData);
}

// Get booking details
$bookingResult = $cBooking->cGetBookingById($bookingId);
if (!$bookingResult || $bookingResult->num_rows == 0) {
  $_SESSION['error'] = 'Không thể lấy thông tin đơn đặt chỗ';
  header('Location: ../../index.php');
  exit;
}

$booking = $bookingResult->fetch_assoc();

// Get user info
include_once(__DIR__ . '/../../../model/mUser.php');
$mUser = new mUser();
$userInfo = $mUser->mGetUserById($userId);

// Khởi tạo thanh toán MoMo
$paymentResult = $cPayment->cInitiateMoMoPayment(
  $bookingId,
  $totalAmount,
  $booking['code'],
  $booking['listing_title'],
  [
    'full_name' => $userInfo['full_name'] ?? '',
    'email' => $userInfo['email'] ?? ''
  ]
);

if (!$paymentResult['success']) {
  // Nếu không thể khởi tạo thanh toán, log error nhưng vẫn cho phép đặt
  error_log('MoMo payment init failed: ' . $paymentResult['message']);
  
  $_SESSION['payment_init_error'] = $paymentResult['message'];
  
  // Redirect đến trang booking success với thông báo thanh toán sau
  header("Location: booking-success.php?booking_id=$bookingId&payment=pending");
  exit;
}

// Lưu thông tin vào session
$_SESSION['booking_id'] = $bookingId;
$_SESSION['payment_order_id'] = $paymentResult['orderId'];

// Redirect đến MoMo payment page
header('Location: ' . $paymentResult['payUrl']);
exit;
?>
