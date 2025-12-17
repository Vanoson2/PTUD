<?php
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cReview.php';

header('Content-Type: application/json');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập lại']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get POST data
$listingId = $_POST['listing_id'] ?? 0;
$bookingId = $_POST['booking_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';

// Call Controller to handle review submission with validation
try {
    $cReview = new cReview();
    $result = $cReview->cSubmitReview($userId, $listingId, $bookingId, $rating, $comment, $_FILES);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
}
?>
