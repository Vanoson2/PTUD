<?php
include_once(__DIR__ . '/mConnect.php');

class mBooking {
    
    // Kiểm tra user có đơn đặt nào khác trùng ngày không
    public function mCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId = null){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            $checkIn = $conn->real_escape_string($checkIn);
            $checkOut = $conn->real_escape_string($checkOut);
            
            // Kiểm tra user có booking nào conflict không (trừ listing hiện tại nếu có)
            $strSelect = "SELECT b.booking_id, b.code, b.check_in, b.check_out, l.title as listing_title
                         FROM bookings b
                         INNER JOIN listing l ON b.listing_id = l.listing_id
                         WHERE b.user_id = $userId 
                         AND b.status = 'confirmed'
                         AND (
                            (b.check_in <= '$checkIn' AND b.check_out > '$checkIn')
                            OR (b.check_in < '$checkOut' AND b.check_out >= '$checkOut')
                            OR (b.check_in >= '$checkIn' AND b.check_out <= '$checkOut')
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
            $strSelect = "SELECT booking_id 
                         FROM bookings 
                         WHERE listing_id = $listingId 
                         AND status = 'confirmed'
                         AND (
                            (check_in <= '$checkIn' AND check_out > '$checkIn')
                            OR (check_in < '$checkOut' AND check_out >= '$checkOut')
                            OR (check_in >= '$checkIn' AND check_out <= '$checkOut')
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
            
            $strInsert = "INSERT INTO bookings 
                         (code, user_id, listing_id, check_in, check_out, guests, total_amount, note, status)
                         VALUES 
                         ('$code', $userId, $listingId, '$checkIn', '$checkOut', $guests, $totalAmount, $note, 'confirmed')";
            
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
                // Đơn sắp tới: status = 'confirmed' VÀ check_out >= hôm nay
                $strSelect = "SELECT b.*, l.title as listing_title, l.address,
                             (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id ORDER BY is_cover DESC, sort_order ASC LIMIT 1) as image_url
                             FROM bookings b
                             INNER JOIN listing l ON b.listing_id = l.listing_id
                             WHERE b.user_id = $userId 
                             AND b.status = 'confirmed'
                             AND b.check_out >= '$today'
                             ORDER BY b.check_in ASC";
            } else {
                // Đơn đã hoàn thành: status = 'completed' HOẶC (status = 'confirmed' VÀ check_out < hôm nay)
                $strSelect = "SELECT b.*, l.title as listing_title, l.address, l.listing_id,
                             (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id ORDER BY is_cover DESC, sort_order ASC LIMIT 1) as image_url,
                             b.is_rated as user_reviewed
                             FROM bookings b
                             INNER JOIN listing l ON b.listing_id = l.listing_id
                             WHERE b.user_id = $userId 
                             AND (b.status = 'completed' OR (b.status = 'confirmed' AND b.check_out < '$today'))
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
}
?>
