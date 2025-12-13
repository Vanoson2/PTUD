<?php
include_once(__DIR__ . '/mConnect.php');

class mBooking {
    
    /**
     * Count total bookings of a user (for first booking check)
     * @param int $userId User ID
     * @return int Number of confirmed/completed bookings
     */
    public function mCountUserBookings($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return 0;
        
        $userId = intval($userId);
        $sql = "SELECT COUNT(*) as total 
                FROM bookings 
                WHERE user_id = $userId 
                AND status IN ('confirmed', 'completed')";
        
        $result = $conn->query($sql);
        $count = 0;
        
        if ($result) {
            $row = $result->fetch_assoc();
            $count = (int)$row['total'];
        }
        
        $p->mDongKetNoi($conn);
        return $count;
    }
    
    // Kiểm tra user có đơn đặt nào khác trùng ngày không
    public function mCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId = null){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            $checkIn = $conn->real_escape_string($checkIn);
            $checkOut = $conn->real_escape_string($checkOut);
            
            // Kiểm tra user có booking nào conflict không (trừ listing hiện tại nếu có)
            // Logic: Hai khoảng thời gian KHÔNG overlap chỉ khi:
            // - check_out của booking cũ < check_in mới HOẶC
            // - check_in của booking cũ > check_out mới
            // Ngược lại = overlap = conflict
            $strSelect = "SELECT b.booking_id, b.code, b.check_in, b.check_out, l.title as listing_title
                         FROM bookings b
                         INNER JOIN listing l ON b.listing_id = l.listing_id
                         WHERE b.user_id = $userId 
                         AND (b.status = 'confirmed' OR b.status = 'pending')
                         AND NOT (
                            b.check_out < '$checkIn' OR b.check_in > '$checkOut'
                         )";
            
            // Nếu có excludeListingId, loại trừ listing đó ra (cho phép đặt cùng chỗ nhiều lần)
            if ($excludeListingId) {
                $excludeListingId = intval($excludeListingId);
                $strSelect .= " AND b.listing_id != $excludeListingId";
            }
            
            $strSelect .= " LIMIT 1";
            
            $result = $conn->query($strSelect);
            return $result; // Return mysqli_result, nếu có row = conflict
        }else{
            return false;
        }
    }
    
    // Kiểm tra listing có còn trống trong khoảng ngày không
    public function mCheckListingAvailability($listingId, $checkIn, $checkOut){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $checkIn = $conn->real_escape_string($checkIn);
            $checkOut = $conn->real_escape_string($checkOut);
            
            // Kiểm tra có booking nào ở listing này conflict không
            // Logic: Hai khoảng thời gian KHÔNG overlap (cho phép đặt) chỉ khi:
            // - check_out của booking cũ < check_in mới (ví dụ: cũ 7-8, mới 9-10 OK)
            // - check_in của booking cũ > check_out mới (ví dụ: cũ 10-11, mới 7-8 OK)
            // Ngược lại = overlap = không cho đặt
            // LƯU Ý: Nếu booking cũ 8-9 và mới 9-10 thì check_out(9) = check_in(9) -> CONFLICT
            $strSelect = "SELECT booking_id 
                         FROM bookings 
                         WHERE listing_id = $listingId 
                         AND (status = 'confirmed' OR status = 'pending')
                         AND NOT (
                            check_out < '$checkIn' OR check_in > '$checkOut'
                         )
                         LIMIT 1";
            
            $result = $conn->query($strSelect);
            return $result; // Return mysqli_result, nếu có row = đã được đặt
        }else{
            return false;
        }
    }
    
    // Tạo mã booking code unique
    private function generateBookingCode(){
        return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
    
    // Tạo booking mới
    public function mCreateBooking($userId, $listingId, $checkIn, $checkOut, $guests, $totalAmount, $note = null){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            $listingId = intval($listingId);
            $checkIn = $conn->real_escape_string($checkIn);
            $checkOut = $conn->real_escape_string($checkOut);
            $guests = intval($guests);
            $totalAmount = floatval($totalAmount);
            $note = $note ? "'" . $conn->real_escape_string($note) . "'" : "NULL";
            
            $code = $this->generateBookingCode();
            
            // ⚠️ Tạo booking với status='pending' cho đến khi thanh toán thành công
            $strInsert = "INSERT INTO bookings 
                         (code, user_id, listing_id, check_in, check_out, guests, total_amount, note, status)
                         VALUES 
                         ('$code', $userId, $listingId, '$checkIn', '$checkOut', $guests, $totalAmount, $note, 'pending')";
            
            if($conn->query($strInsert)){
                // Return booking_id vừa tạo
                return $conn->insert_id;
            }
            return false;
        }else{
            return false;
        }
    }
    
    // Thêm services vào booking
    public function mAddBookingServices($bookingId, $services){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn && is_array($services) && count($services) > 0){
            $bookingId = intval($bookingId);
            
            // Tạo JSON string của services
            $servicesJson = json_encode($services, JSON_UNESCAPED_UNICODE);
            $servicesJson = $conn->real_escape_string($servicesJson);
            
            // Update note field với thông tin services
            $strUpdate = "UPDATE bookings 
                         SET note = CONCAT(IFNULL(note, ''), '\nServices: ', '$servicesJson')
                         WHERE booking_id = $bookingId";
            
            return $conn->query($strUpdate);
        }
        return true; // Return true nếu không có services
    }
    
    // Lấy thông tin booking theo ID
    public function mGetBookingById($bookingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $bookingId = intval($bookingId);
            $strSelect = "SELECT b.*, l.title as listing_title, l.address, l.price as listing_price, l.ward_code, l.capacity,
                         u.full_name as user_name, u.email as user_email,
                         (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id ORDER BY is_cover DESC, sort_order ASC LIMIT 1) as image_url,
                         (SELECT w.full_name FROM wards w WHERE w.code = l.ward_code LIMIT 1) as ward_name,
                         (SELECT p.name FROM wards w INNER JOIN provinces p ON w.province_code = p.code WHERE w.code = l.ward_code LIMIT 1) as province_name,
                         (SELECT ROUND(AVG(rating), 2) FROM review WHERE listing_id = l.listing_id) as avg_rating,
                         (SELECT COUNT(*) FROM review WHERE listing_id = l.listing_id) as review_count
                         FROM bookings b
                         INNER JOIN listing l ON b.listing_id = l.listing_id
                         INNER JOIN user u ON b.user_id = u.user_id
                         WHERE b.booking_id = $bookingId";
            
            $result = $conn->query($strSelect);
            return $result; // Return mysqli_result
        }else{
            return false;
        }
    }
    
    // Lấy services của một booking từ note field
    public function mGetBookingServices($bookingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $bookingId = intval($bookingId);
            // Lấy note từ bookings table
            $strSelect = "SELECT note FROM bookings WHERE booking_id = $bookingId";
            $result = $conn->query($strSelect);
            
            if($result && $result->num_rows > 0){
                $row = $result->fetch_assoc();
                $note = $row['note'];
                
                // Parse JSON từ note
                if(preg_match('/Services: (.+)$/s', $note, $matches)){
                    $servicesData = json_decode($matches[1], true);
                    if($servicesData && is_array($servicesData)){
                        // Convert array thành mysqli_result format (fake result)
                        // Return array trực tiếp, view sẽ xử lý
                        return $servicesData;
                    }
                }
            }
            return []; // Return empty array nếu không có services
        }else{
            return false;
        }
    }
    
    // Lấy danh sách bookings của user theo status
    public function mGetUserBookings($userId, $status = 'upcoming'){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            $today = date('Y-m-d');
            
            if($status == 'upcoming'){
                // Đơn sắp tới: (status = 'pending' HOẶC 'confirmed') VÀ check_out >= hôm nay
                $strSelect = "SELECT b.*, l.title as listing_title, l.address,
                             b.payment_status, b.payment_method, b.payment_id, b.paid_at,
                             (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id ORDER BY is_cover DESC, sort_order ASC LIMIT 1) as image_url
                             FROM bookings b
                             INNER JOIN listing l ON b.listing_id = l.listing_id
                             WHERE b.user_id = $userId 
                             AND (b.status = 'pending' OR b.status = 'confirmed')
                             AND b.check_out >= '$today'
                             ORDER BY 
                               CASE WHEN b.payment_status = 'unpaid' THEN 0 
                                    WHEN b.payment_status = 'pending' THEN 1 
                                    ELSE 2 END,
                               b.check_in ASC";
            } else {
                // Đơn đã hoàn thành: status = 'completed' HOẶC (status = 'confirmed' VÀ check_out < hôm nay VÀ payment_status = 'paid')
                $strSelect = "SELECT b.*, l.title as listing_title, l.address, l.listing_id,
                             b.payment_status, b.payment_method, b.payment_id, b.paid_at,
                             (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id ORDER BY is_cover DESC, sort_order ASC LIMIT 1) as image_url,
                             b.is_rated as user_reviewed
                             FROM bookings b
                             INNER JOIN listing l ON b.listing_id = l.listing_id
                             WHERE b.user_id = $userId 
                             AND (b.status = 'completed' OR (b.status = 'confirmed' AND b.check_out < '$today' AND b.payment_status = 'paid'))
                             ORDER BY b.check_out DESC";
            }
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Cập nhật is_rated khi user đã đánh giá
    public function mMarkBookingAsRated($bookingId, $userId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $bookingId = intval($bookingId);
            $userId = intval($userId);
            
            // Đảm bảo chỉ update booking của user này
            $strUpdate = "UPDATE bookings 
                         SET is_rated = TRUE 
                         WHERE booking_id = $bookingId 
                         AND user_id = $userId";
            
            return $conn->query($strUpdate);
        }else{
            return false;
        }
    }
    
    // Hủy đơn đặt phòng
    public function mCancelBooking($bookingId, $userId, $cancelReason = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $bookingId = intval($bookingId);
            $userId = intval($userId);
            $cancelReason = $cancelReason ? $conn->real_escape_string($cancelReason) : null;
            
            // Kiểm tra booking có thuộc về user này không và status = 'confirmed'
            $strCheck = "SELECT booking_id, status, check_in 
                        FROM bookings 
                        WHERE booking_id = $bookingId 
                        AND user_id = $userId 
                        AND status = 'confirmed'";
            
            $checkResult = $conn->query($strCheck);
            if (!$checkResult || $checkResult->num_rows === 0) {
                return ['success' => false, 'message' => 'Không tìm thấy booking hoặc không thể hủy'];
            }
            
            $booking = $checkResult->fetch_assoc();
            
            // Update status sang cancelled
            $strUpdate = "UPDATE bookings 
                         SET status = 'cancelled',
                             cancelled_at = NOW(),
                             cancelled_by = 'user'";
            
            if ($cancelReason) {
                $strUpdate .= ", cancel_reason = '$cancelReason'";
            }
            
            $strUpdate .= " WHERE booking_id = $bookingId AND user_id = $userId";
            
            $success = $conn->query($strUpdate);
            
            if ($success) {
                return [
                    'success' => true, 
                    'message' => 'Hủy booking thành công',
                    'booking' => $booking
                ];
            }
            return ['success' => false, 'message' => 'Không thể hủy booking'];
        }else{
            return ['success' => false, 'message' => 'Lỗi kết nối database'];
        }
    }
}
?>
