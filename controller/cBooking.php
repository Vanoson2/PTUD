<?php
include_once(__DIR__ . '/../model/mBooking.php');

class cBooking {
    
    public function cCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId = null){
        $mBooking = new mBooking();
        return $mBooking->mCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId);
    }
    
    public function cCheckListingAvailability($listingId, $checkIn, $checkOut){
        $mBooking = new mBooking();
        return $mBooking->mCheckListingAvailability($listingId, $checkIn, $checkOut);
    }
    
    public function cCreateBooking($userId, $listingId, $checkIn, $checkOut, $guests, $totalAmount, $note = null){
        $mBooking = new mBooking();
        return $mBooking->mCreateBooking($userId, $listingId, $checkIn, $checkOut, $guests, $totalAmount, $note);
    }
    
    public function cAddBookingServices($bookingId, $services){
        $mBooking = new mBooking();
        return $mBooking->mAddBookingServices($bookingId, $services);
    }
    
    public function cGetBookingById($bookingId){
        $mBooking = new mBooking();
        return $mBooking->mGetBookingById($bookingId);
    }
    
    public function cGetBookingServices($bookingId){
        $mBooking = new mBooking();
        return $mBooking->mGetBookingServices($bookingId);
    }
    
    public function cGetUserBookings($userId, $status = 'upcoming'){
        $mBooking = new mBooking();
        return $mBooking->mGetUserBookings($userId, $status);
    }
}
?>
