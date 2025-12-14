<?php
/**
 * Cron Job: Cancel Expired Bookings
 * 
 * Tự động hủy các booking pending đã hết hạn (quá 10 phút chưa thanh toán)
 * 
 * Cách chạy:
 * - Windows Task Scheduler: php "C:\xampp\htdocs\PTUD(Version 2)-TichHopMoMo\helper\cancel-expired-bookings.php"
 * - Linux Cron: * * * * * php /path/to/cancel-expired-bookings.php
 * - Nên chạy mỗi phút một lần
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && !isset($_GET['secret_key'])) {
    die('Access denied. This script should only be run via CLI or with secret key.');
}

// Optional: Add secret key for web-based cron (if needed)
$secretKey = 'your_secret_key_here_12345'; // Change this!
if (isset($_GET['secret_key']) && $_GET['secret_key'] !== $secretKey) {
    die('Invalid secret key.');
}

require_once __DIR__ . '/../model/mConnect.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting expired bookings cleanup...\n";

$p = new mConnect();
$conn = $p->mMoKetNoi();

if (!$conn) {
    echo "ERROR: Could not connect to database\n";
    exit(1);
}

// Find all expired pending bookings
$sqlFind = "SELECT booking_id, code, user_id, listing_id, expires_at 
            FROM bookings 
            WHERE status = 'pending' 
              AND payment_status IN ('unpaid', 'pending')
              AND expires_at IS NOT NULL 
              AND expires_at < NOW()
            LIMIT 100"; // Process max 100 at a time

$result = $conn->query($sqlFind);

if (!$result) {
    echo "ERROR: Query failed - " . $conn->error . "\n";
    $p->mDongKetNoi($conn);
    exit(1);
}

$expiredCount = $result->num_rows;

if ($expiredCount === 0) {
    echo "No expired bookings found.\n";
    $p->mDongKetNoi($conn);
    exit(0);
}

echo "Found $expiredCount expired booking(s). Cancelling...\n";

$cancelledCount = 0;
$errorCount = 0;

while ($booking = $result->fetch_assoc()) {
    $bookingId = $booking['booking_id'];
    $code = $booking['code'];
    
    // Cancel the booking
    $sqlCancel = "UPDATE bookings 
                  SET status = 'cancelled',
                      payment_status = 'unpaid',
                      cancelled_at = NOW(),
                      cancelled_by = 'system',
                      cancel_reason = 'Booking expired - No payment received within 10 minutes'
                  WHERE booking_id = $bookingId";
    
    if ($conn->query($sqlCancel)) {
        $cancelledCount++;
        echo "  ✓ Cancelled booking #$bookingId ($code)\n";
    } else {
        $errorCount++;
        echo "  ✗ Failed to cancel booking #$bookingId ($code): " . $conn->error . "\n";
    }
}

$p->mDongKetNoi($conn);

echo "\n";
echo "Summary:\n";
echo "  - Total expired: $expiredCount\n";
echo "  - Cancelled: $cancelledCount\n";
echo "  - Errors: $errorCount\n";
echo "[" . date('Y-m-d H:i:s') . "] Cleanup completed.\n";

exit(0);
?>
