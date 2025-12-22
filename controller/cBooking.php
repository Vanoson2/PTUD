<?php
include_once(__DIR__ . '/../model/mBooking.php');

class cBooking {
    
    // Đếm số booking của user
    // int $userId - ID user
    // return int - Số lượng booking
    public function cCountUserBookings($userId) {
        $mBooking = new mBooking();
        return $mBooking->mCountUserBookings($userId);
    }
    
    // Kiểm tra user có booking trùng thời gian không
    // Tránh user đặt nhiều chỗ cùng lúc
    // int $userId - ID user
    // string $checkIn - Ngày check-in (Y-m-d)
    // string $checkOut - Ngày check-out (Y-m-d)
    // int|null $excludeListingId - Loại trừ listing này (khi edit booking)
    // return bool - true nếu bị trùng
    public function cCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId = null){
        $mBooking = new mBooking();
        return $mBooking->mCheckUserBookingConflict($userId, $checkIn, $checkOut, $excludeListingId);
    }
    
    // Kiểm tra listing có sẵn trong khoảng thời gian không
    // int $listingId - ID listing
    // string $checkIn - Ngày check-in (Y-m-d)
    // string $checkOut - Ngày check-out (Y-m-d)
    // return bool - true nếu available
    public function cCheckListingAvailability($listingId, $checkIn, $checkOut){
        $mBooking = new mBooking();
        return $mBooking->mCheckListingAvailability($listingId, $checkIn, $checkOut);
    }
    
    // Tạo booking mới
    // int $userId - ID user
    // int $listingId - ID listing
    // string $checkIn - Ngày check-in (Y-m-d)
    // string $checkOut - Ngày check-out (Y-m-d)
    // int $guests - Số khách
    // float $totalAmount - Tổng tiền
    // string|null $note - Ghi chú
    // return array - ['success' => bool, 'message' => string, 'booking_id' => int|null]
    public function cCreateBooking($userId, $listingId, $checkIn, $checkOut, $guests, $totalAmount, $note = null){
        $mBooking = new mBooking();
        return $mBooking->mCreateBooking($userId, $listingId, $checkIn, $checkOut, $guests, $totalAmount, $note);
    }
    
    // Thêm dịch vụ cho booking
    // int $bookingId - ID booking
    // array $services - Mảng dịch vụ [['service_id' => int, 'price' => float], ...]
    // return array - ['success' => bool, 'message' => string]
    public function cAddBookingServices($bookingId, $services){
        $mBooking = new mBooking();
        return $mBooking->mAddBookingServices($bookingId, $services);
    }
    
    // Lấy thông tin booking theo ID
    // int $bookingId - ID booking
    // return array|null - Dữ liệu booking hoặc null
    public function cGetBookingById($bookingId){
        $mBooking = new mBooking();
        return $mBooking->mGetBookingById($bookingId);
    }
    
    // Lấy danh sách dịch vụ của booking
    // int $bookingId - ID booking
    // return array - Danh sách services
    public function cGetBookingServices($bookingId){
        $mBooking = new mBooking();
        return $mBooking->mGetBookingServices($bookingId);
    }
    
    // Lấy danh sách booking của user
    // int $userId - ID user
    // string $status - Trạng thái: 'upcoming', 'completed', 'cancelled', 'all'
    // return array - Danh sách bookings
    public function cGetUserBookings($userId, $status = 'upcoming'){
        $mBooking = new mBooking();
        return $mBooking->mGetUserBookings($userId, $status);
    }
    
    // Hủy booking
    // Tự động trừ điểm tín nhiệm nếu hủy gần ngày check-in
    // int $bookingId - ID booking
    // int $userId - ID user (để verify ownership)
    // string|null $cancelReason - Lý do hủy
    // return array - ['success' => bool, 'message' => string, 'booking' => array]
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

    // Xử lý đặt phòng với đầy đủ validation và logic
    // Kiểm tra conflict, availability, tính tổng tiền, tạo booking và thêm dịch vụ
    // int $userId - ID user
    // int $listingId - ID listing
    // string $checkin - Ngày check-in (Y-m-d)
    // string $checkout - Ngày check-out (Y-m-d)
    // int $guests - Số khách
    // int $nights - Số đêm
    // float $listingPrice - Giá listing/đêm
    // array $selectedServices - Mảng ID dịch vụ đã chọn
    // return array - ['success' => bool, 'booking_id' => int|null, 'message' => string, 'redirect' => string|null]
    public function cProcessBooking($userId, $listingId, $checkin, $checkout, $guests, $nights, $listingPrice, $selectedServices = []) {
        // Validation
        if (empty($listingId) || empty($checkin) || empty($checkout)) {
            return [
                'success' => false,
                'message' => 'Thiếu thông tin đặt chỗ',
                'redirect' => '../../index.php'
            ];
        }

        // CLEANUP: Tự động hủy các booking pending hết hạn TRƯỚC KHI check conflict
        // Đảm bảo booking đã hủy MoMo không còn block user
        require_once(__DIR__ . '/../model/mBooking.php');
        $mBookingCleanup = new mBooking();
        $mBookingCleanup->mCancelExpiredBookings();

        // Check: User có booking nào trùng ngày không?
        $userConflictResult = $this->cCheckUserBookingConflict($userId, $checkin, $checkout, $listingId);
        if ($userConflictResult && $userConflictResult->num_rows > 0) {
            $conflict = $userConflictResult->fetch_assoc();
            return [
                'success' => false,
                'message' => 'Bạn đã có đơn đặt khác trong khoảng thời gian này: ' . $conflict['listing_title'] . ' (' . date('d/m/Y', strtotime($conflict['check_in'])) . ' - ' . date('d/m/Y', strtotime($conflict['check_out'])) . ')',
                'redirect' => "confirm-booking.php?listing_id=$listingId&checkin=$checkin&checkout=$checkout&guests=$guests"
            ];
        }
        
        // Check: Listing còn trống không?
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
