<?php
/**
 * Test Booking Expiration Feature
 * 
 * File này để test tính năng hết hạn booking
 * Chạy: php helper/test-expiration.php
 */

require_once __DIR__ . '/../model/mConnect.php';

echo "=== Testing Booking Expiration Feature ===\n\n";

$p = new mConnect();
$conn = $p->mMoKetNoi();

if (!$conn) {
    die("ERROR: Could not connect to database\n");
}

// Test 1: Check if expires_at column exists
echo "Test 1: Checking if expires_at column exists...\n";
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'expires_at'");
if ($result && $result->num_rows > 0) {
    echo "  ✓ Column expires_at exists\n";
} else {
    echo "  ✗ Column expires_at NOT found. Run migration first!\n";
    $p->mDongKetNoi($conn);
    exit(1);
}

// Test 2: Check pending bookings with expiration
echo "\nTest 2: Checking pending bookings...\n";
$sql = "SELECT 
    booking_id, 
    code, 
    status, 
    payment_status,
    created_at,
    expires_at,
    CASE 
        WHEN expires_at IS NULL THEN 'No expiration'
        WHEN expires_at > NOW() THEN CONCAT('Active (', TIMESTAMPDIFF(MINUTE, NOW(), expires_at), ' min left)')
        ELSE CONCAT('Expired (', TIMESTAMPDIFF(MINUTE, expires_at, NOW()), ' min ago)')
    END as expiration_status
FROM bookings 
WHERE status = 'pending' 
ORDER BY created_at DESC 
LIMIT 10";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "  Found " . $result->num_rows . " pending booking(s):\n\n";
    echo "  ID    | Code         | Payment Status | Expiration Status\n";
    echo "  " . str_repeat("-", 70) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("  %-5s | %-12s | %-14s | %s\n", 
            $row['booking_id'],
            $row['code'],
            $row['payment_status'],
            $row['expiration_status']
        );
    }
} else {
    echo "  No pending bookings found.\n";
}

// Test 3: Count expired bookings
echo "\nTest 3: Checking for expired bookings...\n";
$sql = "SELECT COUNT(*) as count 
        FROM bookings 
        WHERE status = 'pending' 
          AND payment_status IN ('unpaid', 'pending')
          AND expires_at IS NOT NULL 
          AND expires_at < NOW()";

$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $expiredCount = $row['count'];
    
    if ($expiredCount > 0) {
        echo "  ⚠ Found $expiredCount expired booking(s) that should be cancelled\n";
        echo "  Run: php helper/cancel-expired-bookings.php\n";
    } else {
        echo "  ✓ No expired bookings found\n";
    }
}

// Test 4: Simulate creating a booking with expiration
echo "\nTest 4: Simulating booking creation with 10-minute expiration...\n";
$testCode = 'BK_TEST_' . time();
$sql = "INSERT INTO bookings 
        (code, user_id, listing_id, check_in, check_out, guests, total_amount, status, expires_at)
        VALUES 
        ('$testCode', 1, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 2, 1000000, 'pending', DATE_ADD(NOW(), INTERVAL 10 MINUTE))";

if ($conn->query($sql)) {
    $testBookingId = $conn->insert_id;
    echo "  ✓ Test booking created (ID: $testBookingId, Code: $testCode)\n";
    echo "  ✓ Expires at: " . date('Y-m-d H:i:s', strtotime('+10 minutes')) . "\n";
    
    // Clean up test booking
    $conn->query("DELETE FROM bookings WHERE booking_id = $testBookingId");
    echo "  ✓ Test booking cleaned up\n";
} else {
    echo "  ✗ Failed to create test booking: " . $conn->error . "\n";
}

$p->mDongKetNoi($conn);

echo "\n=== All Tests Completed ===\n";
?>
