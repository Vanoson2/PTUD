<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
  exit;
}

$userId = $_SESSION['user_id'];
$listingId = $_POST['listing_id'] ?? 0;
$bookingId = $_POST['booking_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';

// Validate input
if (empty($listingId) || empty($bookingId) || empty($rating) || empty($comment)) {
  echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
  exit;
}

// Validate rating (1-5)
if ($rating < 1 || $rating > 5) {
  echo json_encode(['success' => false, 'message' => 'Đánh giá không hợp lệ']);
  exit;
}

// Include models
include_once(__DIR__ . '/../../../model/mBooking.php');
include_once(__DIR__ . '/../../../model/mReview.php');

$mBooking = new mBooking();
$mReview = new mReview();

// Verify booking belongs to user and not yet rated
$bookingResult = $mBooking->mGetBookingById($bookingId);
if (!$bookingResult || $bookingResult->num_rows == 0) {
  echo json_encode(['success' => false, 'message' => 'Booking không tồn tại']);
  exit;
}

$booking = $bookingResult->fetch_assoc();

if ($booking['user_id'] != $userId) {
  echo json_encode(['success' => false, 'message' => 'Bạn không có quyền đánh giá booking này']);
  exit;
}

if ($booking['is_rated']) {
  echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá booking này rồi']);
  exit;
}

// Handle image uploads
$imageUrls = [];
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
  $uploadDir = __DIR__ . '/../../../public/uploads/reviews/';
  
  // Create directory if not exists
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  
  $fileCount = count($_FILES['images']['name']);
  $maxFiles = 5; // Tối đa 5 ảnh
  
  for ($i = 0; $i < min($fileCount, $maxFiles); $i++) {
    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['images']['tmp_name'][$i];
      $originalName = $_FILES['images']['name'][$i];
      $fileSize = $_FILES['images']['size'][$i];
      $fileMimeType = $_FILES['images']['type'][$i];
      $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
      
      // Validate file type (chỉ cho phép PNG, JPG, JPEG)
      $allowedTypes = ['jpg', 'jpeg', 'png'];
      $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
      
      if (!in_array($extension, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes)) {
        continue;
      }
      
      // Validate file size (tối đa 5MB)
      $maxSize = 5 * 1024 * 1024; // 5MB
      if ($fileSize > $maxSize) {
        continue;
      }
      
      // Generate filename theo format: userId_img01, userId_img02, ...
      $imageNumber = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
      $newFilename = $userId . '_img' . $imageNumber . '.' . $extension;
      $uploadPath = $uploadDir . $newFilename;
      
      if (move_uploaded_file($tmpName, $uploadPath)) {
        $imageUrls[] = 'public/uploads/reviews/' . $newFilename;
      }
    }
  }
}

// Convert images array to JSON string
$imagesJson = !empty($imageUrls) ? json_encode($imageUrls) : null;

// Create review
$result = $mReview->mCreateReview($listingId, $userId, $rating, $comment, $imagesJson);

if ($result) {
  // Mark booking as rated
  $mBooking->mMarkBookingAsRated($bookingId, $userId);
  
  echo json_encode([
    'success' => true, 
    'message' => 'Đánh giá thành công'
  ]);
} else {
  echo json_encode([
    'success' => false, 
    'message' => 'Có lỗi xảy ra khi lưu đánh giá'
  ]);
}
?>
