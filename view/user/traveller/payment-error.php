<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$bookingId = $_GET['booking_id'] ?? ($_SESSION['payment_booking_id'] ?? null);
$errorMessage = $_SESSION['payment_error'] ?? 'Thanh toán không thành công';
$resultCode = $_SESSION['payment_result_code'] ?? null;

// Clear session
unset($_SESSION['payment_error']);
unset($_SESSION['payment_result_code']);
unset($_SESSION['payment_booking_id']);

// Get booking info if available
$booking = null;
if ($bookingId) {
    include_once(__DIR__ . '/../../../controller/cBooking.php');
    $cBooking = new cBooking();
    $bookingResult = $cBooking->cGetBookingById($bookingId);
    if ($bookingResult && $bookingResult->num_rows > 0) {
        $booking = $bookingResult->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán không thành công - WEGO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/traveller-payment-error.css">
</head>
<body>
    <?php include(__DIR__ . '/../../partials/header.php'); ?>

    <div class="payment-error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fa-solid fa-times"></i>
            </div>
            
            <h1 class="error-title">Thanh Toán Không Thành Công</h1>
            
            <p class="error-message">
                <?php echo htmlspecialchars($errorMessage); ?>
            </p>
            
            <?php if ($resultCode): ?>
            <div class="error-code">
                Mã lỗi: <?php echo $resultCode; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($booking): ?>
            <div class="booking-info">
                <h5><i class="fa-solid fa-info-circle"></i> Thông tin đặt chỗ</h5>
                <div class="info-row">
                    <span class="info-label">Mã đơn:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['code']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Chỗ ở:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['listing_title']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tổng tiền:</span>
                    <span class="info-value"><?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái thanh toán:</span>
                    <span class="info-value text-danger">Chưa thanh toán</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <?php if ($booking): ?>
                <a href="retry-payment.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-retry">
                    <i class="fa-solid fa-rotate-right"></i> Thử lại thanh toán
                </a>
                <a href="my-bookings.php" class="btn btn-home">
                    <i class="fa-solid fa-list"></i> Xem đơn đặt của tôi
                </a>
                <?php else: ?>
                <a href="../../../index.php" class="btn btn-home">
                    <i class="fa-solid fa-home"></i> Về trang chủ
                </a>
                <?php endif; ?>
            </div>
            
            <div class="help-text">
                <h6><i class="fa-solid fa-lightbulb"></i> Lưu ý quan trọng:</h6>
                <ul>
                    <li>Đơn đặt chỗ của bạn đã được tạo nhưng chưa thanh toán</li>
                    <li>Bạn có thể thử lại thanh toán hoặc thanh toán sau</li>
                    <li>Vui lòng hoàn tất thanh toán trong vòng 24h để giữ chỗ</li>
                    <li>Nếu cần hỗ trợ, vui lòng liên hệ: <strong>1900-xxxx</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/../../partials/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
