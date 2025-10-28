<?php
include_once(__DIR__ . "/mConnect.php");

class mHostBooking {
    
    /**
     * Lấy danh sách booking của host (theo listing_id thuộc host)
     */
    public function mGetHostBookings($hostId, $status = 'all') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $hostId = (int)$hostId;
        
        // Build WHERE clause cho status
        $statusCondition = "";
        if ($status === 'upcoming') {
            $statusCondition = "AND b.status = 'confirmed' AND b.check_in >= CURDATE()";
        } elseif ($status === 'ongoing') {
            $statusCondition = "AND b.status = 'confirmed' AND b.check_in <= CURDATE() AND b.check_out > CURDATE()";
        } elseif ($status === 'completed') {
            $statusCondition = "AND b.status = 'completed'";
        } elseif ($status === 'cancelled') {
            $statusCondition = "AND b.status = 'cancelled'";
        }
        
        $sql = "SELECT 
                    b.booking_id,
                    b.code,
                    b.check_in,
                    b.check_out,
                    b.guests,
                    b.status,
                    b.total_amount,
                    b.created_at,
                    b.note,
                    l.listing_id,
                    l.title AS listing_title,
                    l.address,
                    w.name AS ward_name,
                    p.name AS province_name,
                    u.user_id,
                    u.full_name AS guest_name,
                    u.email AS guest_email,
                    u.phone AS guest_phone,
                    (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) AS listing_image
                FROM bookings b
                INNER JOIN listing l ON b.listing_id = l.listing_id
                INNER JOIN host h ON l.host_id = h.host_id
                INNER JOIN user u ON b.user_id = u.user_id
                LEFT JOIN wards w ON l.ward_code = w.code
                LEFT JOIN provinces p ON w.province_code = p.code
                WHERE h.host_id = $hostId
                $statusCondition
                ORDER BY b.created_at DESC";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result;
    }
    
    /**
     * Lấy chi tiết booking
     */
    public function mGetBookingDetail($bookingId, $hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $bookingId = (int)$bookingId;
        $hostId = (int)$hostId;
        
        $sql = "SELECT 
                    b.*,
                    l.listing_id,
                    l.title AS listing_title,
                    l.address,
                    l.capacity,
                    w.name AS ward_name,
                    p.name AS province_name,
                    u.user_id,
                    u.full_name AS guest_name,
                    u.email AS guest_email,
                    u.phone AS guest_phone,
                    (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) AS listing_image,
                    (SELECT AVG(rating) FROM review WHERE listing_id = l.listing_id) AS avg_rating,
                    (SELECT COUNT(*) FROM review WHERE listing_id = l.listing_id) AS review_count
                FROM bookings b
                INNER JOIN listing l ON b.listing_id = l.listing_id
                INNER JOIN host h ON l.host_id = h.host_id
                INNER JOIN user u ON b.user_id = u.user_id
                LEFT JOIN wards w ON l.ward_code = w.code
                LEFT JOIN provinces p ON w.province_code = p.code
                WHERE b.booking_id = $bookingId 
                AND h.host_id = $hostId
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $booking;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    /**
     * Lấy services của booking
     */
    public function mGetBookingServices($bookingId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $bookingId = (int)$bookingId;
        
        $sql = "SELECT 
                    ls.service_id,
                    s.name,
                    ls.price
                FROM listing_service ls
                INNER JOIN service s ON ls.service_id = s.service_id
                INNER JOIN bookings b ON ls.listing_id = b.listing_id
                WHERE b.booking_id = $bookingId";
        
        $result = $conn->query($sql);
        $services = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $services;
    }
    
    /**
     * Cập nhật trạng thái booking
     */
    public function mUpdateBookingStatus($bookingId, $hostId, $newStatus) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $bookingId = (int)$bookingId;
        $hostId = (int)$hostId;
        $newStatus = $conn->real_escape_string($newStatus);
        
        // Kiểm tra booking có thuộc host không
        $checkSql = "SELECT b.booking_id, b.status, b.check_out 
                     FROM bookings b
                     INNER JOIN listing l ON b.listing_id = l.listing_id
                     INNER JOIN host h ON l.host_id = h.host_id
                     WHERE b.booking_id = $bookingId AND h.host_id = $hostId
                     LIMIT 1";
        
        $checkResult = $conn->query($checkSql);
        
        if (!$checkResult || $checkResult->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Không tìm thấy booking hoặc bạn không có quyền'
            ];
        }
        
        $booking = $checkResult->fetch_assoc();
        
        // Validate status transition
        if ($newStatus === 'completed') {
            // Chỉ có thể chuyển sang completed nếu:
            // 1. Status hiện tại là confirmed
            // 2. Ngày check_out đã qua (hoặc là hôm nay)
            if ($booking['status'] !== 'confirmed') {
                $p->mDongKetNoi($conn);
                return [
                    'success' => false,
                    'message' => 'Chỉ có thể đánh dấu hoàn thành cho booking đang confirmed'
                ];
            }
            
            // Có thể bỏ qua check date nếu host muốn đánh dấu sớm
            // if (strtotime($booking['check_out']) > time()) {
            //     $p->mDongKetNoi($conn);
            //     return [
            //         'success' => false,
            //         'message' => 'Chưa đến ngày check-out'
            //     ];
            // }
        }
        
        // Update status
        $updateSql = "UPDATE bookings 
                      SET status = '$newStatus',
                          updated_at = CURRENT_TIMESTAMP
                      WHERE booking_id = $bookingId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Có lỗi khi cập nhật: ' . $conn->error
        ];
    }
    
    /**
     * Đếm số booking theo trạng thái
     */
    public function mCountBookingsByStatus($hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'upcoming' => 0,
                'ongoing' => 0,
                'completed' => 0,
                'total' => 0
            ];
        }
        
        $hostId = (int)$hostId;
        
        $sql = "SELECT 
                    COUNT(CASE WHEN b.status = 'confirmed' AND b.check_in >= CURDATE() THEN 1 END) AS upcoming,
                    COUNT(CASE WHEN b.status = 'confirmed' AND b.check_in <= CURDATE() AND b.check_out > CURDATE() THEN 1 END) AS ongoing,
                    COUNT(CASE WHEN b.status = 'completed' THEN 1 END) AS completed,
                    COUNT(*) AS total
                FROM bookings b
                INNER JOIN listing l ON b.listing_id = l.listing_id
                INNER JOIN host h ON l.host_id = h.host_id
                WHERE h.host_id = $hostId";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $counts = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $counts;
        }
        
        $p->mDongKetNoi($conn);
        return [
            'upcoming' => 0,
            'ongoing' => 0,
            'completed' => 0,
            'total' => 0
        ];
    }
}
?>
