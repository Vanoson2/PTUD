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
include_once(__DIR__ . '/../../../model/mEmailPHPMailer.php');

$cBooking = new cBooking();
$cListing = new cListing();

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

// Create booking
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

// Get booking details for email
$bookingResult = $cBooking->cGetBookingById($bookingId);
if ($bookingResult && $bookingResult->num_rows > 0) {
  $booking = $bookingResult->fetch_assoc();
  
  // Send confirmation email
  try {
    $mailer = new mEmailPHPMailer();
    
    $subject = "✅ Đặt chỗ thành công - Mã đơn #{$booking['code']}";
    
    // Build services list HTML
    $servicesHtml = '';
    if (count($servicesData) > 0) {
      $servicesHtml = "<div style='margin-top: 20px;'>";
      $servicesHtml .= "<h3 style='color: #1f2937; font-size: 16px; margin-bottom: 10px;'>Dịch vụ đã chọn:</h3>";
      $servicesHtml .= "<ul style='list-style: none; padding: 0; margin: 0;'>";
      foreach ($servicesData as $service) {
        $servicesHtml .= "<li style='padding: 8px 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between;'>";
        $servicesHtml .= "<span style='color: #4b5563;'>{$service['name']}</span>";
        $servicesHtml .= "<span style='color: #1f2937; font-weight: 600;'>" . number_format($service['price'], 0, ',', '.') . " VND</span>";
        $servicesHtml .= "</li>";
      }
      $servicesHtml .= "</ul></div>";
    }
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
            <!-- Header -->
            <div style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px 30px; text-align: center;'>
                <h1 style='margin: 0; font-size: 28px;'>🎉 Đặt Chỗ Thành Công!</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Chuyến đi của bạn đã được xác nhận</p>
            </div>
            
            <!-- Content -->
            <div style='padding: 40px 30px;'>
                <h2 style='color: #1f2937; margin-top: 0;'>Xin chào " . htmlspecialchars($booking['user_name']) . "! 👋</h2>
                <p style='color: #4b5563; line-height: 1.6;'>Cảm ơn bạn đã tin tưởng và đặt chỗ tại <strong>{$booking['listing_title']}</strong>. Đơn đặt của bạn đã được xác nhận thành công!</p>
                
                <!-- Booking Code -->
                <div style='background: #f0fdf4; border: 2px dashed #10b981; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;'>
                    <div style='color: #059669; font-size: 12px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;'>Mã Đơn Đặt</div>
                    <div style='font-size: 32px; font-weight: bold; color: #10b981; letter-spacing: 2px; font-family: monospace;'>{$booking['code']}</div>
                </div>
                
                <!-- Booking Details -->
                <div style='background: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #1f2937; font-size: 18px; margin-top: 0; margin-bottom: 15px;'>📋 Thông tin đặt chỗ</h3>
                    
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;'>📅 Ngày nhận phòng:</td>
                            <td style='padding: 10px 0; color: #1f2937; font-weight: 600; text-align: right; border-bottom: 1px solid #e5e7eb;'>" . date('d/m/Y', strtotime($booking['check_in'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;'>📅 Ngày trả phòng:</td>
                            <td style='padding: 10px 0; color: #1f2937; font-weight: 600; text-align: right; border-bottom: 1px solid #e5e7eb;'>" . date('d/m/Y', strtotime($booking['check_out'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;'>👥 Số khách:</td>
                            <td style='padding: 10px 0; color: #1f2937; font-weight: 600; text-align: right; border-bottom: 1px solid #e5e7eb;'>{$booking['guests']} khách</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;'>📍 Địa chỉ:</td>
                            <td style='padding: 10px 0; color: #1f2937; font-weight: 600; text-align: right; border-bottom: 1px solid #e5e7eb;'>{$booking['address']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 15px 0 10px 0; color: #6b7280; font-size: 18px;'><strong>💰 Tổng tiền:</strong></td>
                            <td style='padding: 15px 0 10px 0; color: #10b981; font-weight: 700; font-size: 20px; text-align: right;'>" . number_format($booking['total_amount'], 0, ',', '.') . " VND</td>
                        </tr>
                    </table>
                    
                    {$servicesHtml}
                </div>
                
                <!-- Important Info -->
                <div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <strong style='color: #92400e;'>⚠️ Lưu ý quan trọng:</strong>
                    <ul style='margin: 10px 0 0 0; padding-left: 20px; color: #78350f; line-height: 1.6;'>
                        <li>Vui lòng mang theo giấy tờ tùy thân khi check-in</li>
                        <li>Check-in sau 2:00 PM, Check-out trước 11:00 AM</li>
                        <li>Liên hệ chủ nhà nếu có thay đổi kế hoạch</li>
                    </ul>
                </div>
                
                <p style='color: #6b7280; font-size: 14px; margin-top: 30px; text-align: center;'>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua email hoặc hotline.</p>
            </div>
            
            <!-- Footer -->
            <div style='background: #f8f9fa; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;'>
                <p style='margin: 5px 0;'><strong>WeGo Travel</strong></p>
                <p style='margin: 5px 0;'>Email: support@wego.com | Hotline: 1900-xxxx</p>
                <p style='margin: 15px 0 5px 0; color: #9ca3af; font-size: 12px;'>© " . date('Y') . " WeGo Travel. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mailer->sendEmail($booking['user_email'], $subject, $body, $booking['user_name']);
    
  } catch (Exception $e) {
    // Log error but don't stop the booking process
    error_log("Email send error: " . $e->getMessage());
  }
}

// Success - redirect to booking success page
header("Location: booking-success.php?booking_id=$bookingId");
exit;
?>
