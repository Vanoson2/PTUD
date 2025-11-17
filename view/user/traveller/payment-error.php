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
    <style>
        .payment-error-container {
            max-width: 700px;
            margin: 80px auto;
            padding: 20px;
        }
        
        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .error-icon i {
            font-size: 60px;
            color: white;
        }
        
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .booking-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        
        .booking-info h5 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .info-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn-retry {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            color: white;
        }
        
        .btn-home {
            background: white;
            color: #3b82f6;
            border: 2px solid #3b82f6;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-home:hover {
            background: #3b82f6;
            color: white;
        }
        
        .error-code {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .help-text {
            margin-top: 30px;
            padding: 20px;
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            text-align: left;
        }
        
        .help-text h6 {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .help-text ul {
            margin: 10px 0 0 20px;
            color: #78350f;
            font-size: 14px;
        }
        
        .help-text ul li {
            margin: 5px 0;
        }
    </style>
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
