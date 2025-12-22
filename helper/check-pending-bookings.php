<?php
/**
 * Check pending bookings for user
 */

require_once __DIR__ . '/../model/mConnect.php';

$p = new mConnect();
$conn = $p->mMoKetNoi();

if (!$conn) {
    die("ERROR: Could not connect to database\n");
}

// Check pending bookings for user_id = 13
$sql = "SELECT 
    booking_id, 
    code, 
    listing_id,
    status, 
    payment_status, 
    check_in, 
    check_out, 
    expires_at,
    TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_left,
    created_at
FROM bookings 
WHERE user_id = 13 
    AND status = 'pending'
ORDER BY created_at DESC 
LIMIT 5";

$result = $conn->query($sql);

if (!$result) {
    die("ERROR: Query failed - " . $conn->error . "\n");
}

echo "=== PENDING BOOKINGS FOR USER 13 ===\n";
echo "Found: " . $result->num_rows . " booking(s)\n\n";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Booking ID: " . $row['booking_id'] . "\n";
        echo "Code: " . $row['code'] . "\n";
        echo "Listing ID: " . $row['listing_id'] . "\n";
        echo "Status: " . $row['status'] . " / " . $row['payment_status'] . "\n";
        echo "Dates: " . $row['check_in'] . " to " . $row['check_out'] . "\n";
        echo "Expires at: " . $row['expires_at'] . "\n";
        echo "Minutes left: " . ($row['minutes_left'] ?? 'NULL') . "\n";
        echo "Created at: " . $row['created_at'] . "\n";
        echo "---\n\n";
    }
} else {
    echo "No pending bookings found.\n";
}

$p->mDongKetNoi($conn);
?>
