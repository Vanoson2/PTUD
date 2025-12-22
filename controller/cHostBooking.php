<?php
include_once(__DIR__ . "/../model/mHostBooking.php");

class cHostBooking {
    
    // Lấy danh sách booking của host
    // int $hostId - ID host
    // string $status - Trạng thái: 'all', 'confirmed', 'cancelled', 'completed', 'pending'
    // return mysqli_result|array - Danh sách bookings
    public function cGetHostBookings($hostId, $status = 'all') {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetHostBookings($hostId, $status);
    }
    
    // Lấy chi tiết booking (verify host ownership)
    // int $bookingId - ID booking
    // int $hostId - ID host (để verify)
    // return array|null - Dữ liệu booking hoặc null
    public function cGetBookingDetail($bookingId, $hostId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetBookingDetail($bookingId, $hostId);
    }
    
    // Lấy danh sách dịch vụ của booking
    // int $bookingId - ID booking
    // return mysqli_result|array - Danh sách services
    public function cGetBookingServices($bookingId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetBookingServices($bookingId);
    }
    
    // Cập nhật trạng thái booking (host confirm/cancel/complete)
    // Validate status: 'confirmed', 'cancelled', 'completed'
    // int $bookingId - ID booking
    // int $hostId - ID host (để verify ownership)
    // string $newStatus - Trạng thái mới
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateBookingStatus($bookingId, $hostId, $newStatus) {
        // Validate status
        $validStatuses = ['confirmed', 'cancelled', 'completed'];
        if (!in_array($newStatus, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Trạng thái không hợp lệ'
            ];
        }
        
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mUpdateBookingStatus($bookingId, $hostId, $newStatus);
    }
    
    // Đếm số booking theo trạng thái
    // int $hostId - ID host
    // return array - ['confirmed' => int, 'pending' => int, 'cancelled' => int, 'completed' => int]
    public function cCountBookingsByStatus($hostId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mCountBookingsByStatus($hostId);
    }
}
?>
