<?php
include_once(__DIR__ . "/../model/mHostBooking.php");

class cHostBooking {
    
    public function cGetHostBookings($hostId, $status = 'all') {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetHostBookings($hostId, $status);
    }
    
    public function cGetBookingDetail($bookingId, $hostId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetBookingDetail($bookingId, $hostId);
    }
    
    public function cGetBookingServices($bookingId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mGetBookingServices($bookingId);
    }
    
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
    
    public function cCountBookingsByStatus($hostId) {
        $mHostBooking = new mHostBooking();
        return $mHostBooking->mCountBookingsByStatus($hostId);
    }
}
?>
