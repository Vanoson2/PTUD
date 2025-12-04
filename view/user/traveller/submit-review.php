<?php
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cReview.php';

header('Content-Type: application/json');

// Check if user is logged in
requireLogin();
$userId = getCurrentUserId();

// Get POST data
$listingId = $_POST['listing_id'] ?? 0;
$bookingId = $_POST['booking_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';

// Call Controller to handle review submission with validation
$cReview = new cReview();
$result = $cReview->cSubmitReview($userId, $listingId, $bookingId, $rating, $comment, $_FILES);

echo json_encode($result);
?>
