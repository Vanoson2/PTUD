<?php
include_once(__DIR__ . '/../model/mBooking.php');

class cBooking {
    
    public function cCountUserBookings($userId) {
        $mBooking = new mBooking();
        return $mBooking->mCountUserBookings($userId);
    }
    
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
    
    public function cCancelBooking($bookingId, $userId, $cancelReason = null){
        $mBooking = new mBooking();
        $result = $mBooking->mCancelBooking($bookingId, $userId, $cancelReason);
        
        // Auto-deduct score when cancel booking
        if ($result['success'] && isset($result['booking'])) {
            include_once(__DIR__ . "/cUser.php");
            $cUser = new cUser();
            
            $booking = $result['booking'];
            $checkInTime = strtotime($booking['check_in']);
            $now = time();
            $hoursUntilCheckIn = ($checkInTime - $now) / 3600;
            
            if ($hoursUntilCheckIn < 24) {
                // Late cancellation - bigger penalty
                $cUser->cAddScoreByAction($userId, 'late_cancel_booking', 'booking', $bookingId);
            } else {
                // Normal cancellation
                $cUser->cAddScoreByAction($userId, 'cancel_booking', 'booking', $bookingId);
            }
        }
        
        return $result;
    }

    /**
     * Process booking with all validations and business logic
     * @param int $userId
     * @param int $listingId
     * @param string $checkin
     * @param string $checkout
     * @param int $guests
     * @param int $nights
     * @param float $listingPrice
     * @param array $selectedServices
     * @return array ['success' => bool, 'booking_id' => int|null, 'message' => string, 'redirect' => string|null]
     */
    public function cProcessBooking($userId, $listingId, $checkin, $checkout, $guests, $nights, $listingPrice, $selectedServices = []) {
        // Validation
        if (empty($listingId) || empty($checkin) || empty($checkout)) {
            return [
                'success' => false,
                'message' => 'Thiếu thông tin đặt chỗ',
                'redirect' => '../../index.php'
            ];
        }

        // Check 1: User có đơn đặt nào khác trùng ngày không?
        $userConflictResult = $this->cCheckUserBookingConflict($userId, $checkin, $checkout, $listingId);
        if ($userConflictResult && $userConflictResult->num_rows > 0) {
            $conflict = $userConflictResult->fetch_assoc();
            return [
                'success' => false,
                'message' => "Bạn đã có đơn đặt '{$conflict['listing_title']}' trùng thời gian (Mã: {$conflict['code']})",
                'redirect' => "confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests"
            ];
        }

        // Check 2: Listing còn trống không?
        $listingAvailabilityResult = $this->cCheckListingAvailability($listingId, $checkin, $checkout);
        if ($listingAvailabilityResult && $listingAvailabilityResult->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Chỗ ở này đã được đặt trong khoảng thời gian bạn chọn',
                'redirect' => "confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests"
            ];
        }

        // Calculate total amount
        $subtotal = $listingPrice * $nights;
        $servicesTotal = 0;
        $servicesData = [];

        if (count($selectedServices) > 0) {
            // Get services info
            include_once(__DIR__ . '/cListing.php');
            $cListing = new cListing();
            $servicesResult = $cListing->cGetListingServices($listingId);
            
            if ($servicesResult && $servicesResult->num_rows > 0) {
                while ($serviceRow = $servicesResult->fetch_assoc()) {
                    if (in_array($serviceRow['service_id'], $selectedServices)) {
                        $servicesData[] = [
                            'service_id' => $serviceRow['service_id'],
                            'name' => $serviceRow['name'],
                            'price' => $serviceRow['price']
                        ];
                        $servicesTotal += $serviceRow['price'];
                    }
                }
            }
        }

        $totalAmount = $subtotal + $servicesTotal;

        // Create booking với status pending payment
        $bookingId = $this->cCreateBooking($userId, $listingId, $checkin, $checkout, $guests, $totalAmount);

        if (!$bookingId) {
            return [
                'success' => false,
                'message' => 'Không thể tạo đơn đặt chỗ. Vui lòng thử lại.',
                'redirect' => "confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests"
            ];
        }

        // Add services to booking
        if (!empty($servicesData)) {
            $this->cAddBookingServices($bookingId, $servicesData);
        }

        return [
            'success' => true,
            'booking_id' => $bookingId,
            'message' => 'Đơn đặt chỗ đã được tạo thành công',
            'redirect' => null
        ];
    }
}
?>
