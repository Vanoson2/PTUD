<?php
include_once(__DIR__ . "/../model/mReview.php");
include_once(__DIR__ . "/../model/mBooking.php");

class cReview {
    
    // Gửi đánh giá với đầy đủ validation
    // Validate rating (1-5), comment (10-2000 ký tự), verify booking ownership và status
    // Cho phép upload ảnh kèm theo, mark booking là đã rated
    // int $userId - ID user gửi đánh giá
    // int $listingId - ID listing
    // int $bookingId - ID booking
    // int $rating - Số sao (1-5)
    // string $comment - Nội dung đánh giá
    // array $filesData - Dữ liệu $_FILES cho ảnh upload
    // return array - ['success' => bool, 'message' => string, 'review_id' => int|null]
    public function cSubmitReview($userId, $listingId, $bookingId, $rating, $comment, $filesData = []) {
        // Validate inputs
        if (empty($listingId) || empty($bookingId) || empty($rating) || empty($comment)) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }

        // Validate rating (1-5)
        $rating = (int)$rating;
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Đánh giá không hợp lệ (phải từ 1-5 sao)'];
        }

        // Validate comment length
        $comment = trim($comment);
        if (strlen($comment) < 10) {
            return ['success' => false, 'message' => 'Nội dung đánh giá phải có ít nhất 10 ký tự'];
        }
        if (strlen($comment) > 2000) {
            return ['success' => false, 'message' => 'Nội dung đánh giá quá dài (tối đa 2000 ký tự)'];
        }

        // Verify booking belongs to user and not yet rated
        $mBooking = new mBooking();
        $bookingResult = $mBooking->mGetBookingById($bookingId);
        
        if (!$bookingResult || $bookingResult->num_rows == 0) {
            return ['success' => false, 'message' => 'Booking không tồn tại'];
        }

        $booking = $bookingResult->fetch_assoc();

        if ($booking['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Bạn không có quyền đánh giá booking này'];
        }

        if ($booking['is_rated']) {
            return ['success' => false, 'message' => 'Bạn đã đánh giá booking này rồi'];
        }

        // Verify booking status is 'completed'
        if ($booking['status'] !== 'completed') {
            return ['success' => false, 'message' => 'Chỉ có thể đánh giá booking đã hoàn thành'];
        }

        // Sanitize inputs
        $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

        // Create review in Model
        $mReview = new mReview();
        $reviewId = $mReview->mCreateReview($listingId, $userId, $rating, $comment);

        if (!$reviewId) {
            return ['success' => false, 'message' => 'Không thể tạo đánh giá. Vui lòng thử lại.'];
        }

        // Handle image uploads after review is created
        if (!empty($filesData['images']['name'][0])) {
            $uploadResult = $this->processReviewImages($filesData['images']);
            if ($uploadResult['success']) {
                // Save images to database
                $imageUrls = $uploadResult['urls'];
                if (!empty($imageUrls)) {
                    $mReview->mCreateReviewImages($reviewId, $imageUrls);
                }
            }
        }

        // Mark booking as rated
        $mBooking->mMarkBookingAsRated($bookingId, $userId);

        return [
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công!'
        ];
    }

    // Xử lý upload ảnh review
    // Validate file type (jpg, jpeg, png, webp), size (max 5MB), tối đa 5 ảnh
    // array $images - Dữ liệu $_FILES['images']
    // return array - ['success' => bool, 'urls' => array, 'message' => string]
    private function processReviewImages($images) {
        $uploadDir = __DIR__ . '/../public/uploads/reviews/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return ['success' => false, 'message' => 'Không thể tạo thư mục upload'];
            }
        }

        $fileCount = count($images['name']);
        $maxFiles = 5; // Maximum 5 images
        $maxFileSize = 5 * 1024 * 1024; // 5MB per file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if ($fileCount > $maxFiles) {
            return ['success' => false, 'message' => "Chỉ được upload tối đa $maxFiles ảnh"];
        }

        $imageUrls = [];

        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $images['tmp_name'][$i];
            $fileName = $images['name'][$i];
            $fileSize = $images['size'][$i];
            $fileError = $images['error'][$i];
            
            if (empty($tmpName)) continue;

            // Check for upload errors
            if ($fileError !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => "Lỗi upload ảnh: $fileName"];
            }

            // Validate file size
            if ($fileSize > $maxFileSize) {
                return ['success' => false, 'message' => "Ảnh $fileName quá lớn (tối đa 5MB)"];
            }

            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return ['success' => false, 'message' => "Ảnh $fileName không đúng định dạng (chỉ chấp nhận JPG, PNG, GIF, WEBP)"];
            }

            // Generate unique filename
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'review_' . time() . '_' . $i . '_' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            // Move uploaded file
            if (!move_uploaded_file($tmpName, $destination)) {
                return ['success' => false, 'message' => "Không thể lưu ảnh $fileName"];
            }

            $imageUrls[] = '/public/uploads/reviews/' . $newFileName;
        }

        return ['success' => true, 'urls' => $imageUrls];
    }
}
?>
