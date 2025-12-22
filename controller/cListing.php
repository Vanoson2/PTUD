<?php 
include_once(__DIR__ . "/../model/mListing.php");
 class cListing{
    // Đếm số listing theo tỉnh/thành
    // string $provinceName - Tên tỉnh/thành
    // return int - Số lượng listing
    public function cCountListingByProvince($provinceName){
        $mListing = new mListing();
        return $mListing->mCountListingByProvince($provinceName);
    }
    
    // Tìm kiếm listing theo địa điểm
    // string $location - Địa điểm tìm kiếm
    // return mysqli_result|array - Kết quả listing
    public function cSearchListingsByLocation($location){
        $mListing = new mListing();
        return $mListing->mSearchListingsByLocation($location);
    }
    
    // Tìm kiếm listing với filters (địa điểm, ngày, số khách)
    // string $location - Địa điểm
    // string|null $checkin - Ngày check-in (Y-m-d)
    // string|null $checkout - Ngày check-out (Y-m-d)
    // int $guests - Số khách (mặc định 1)
    // return mysqli_result|array - Kết quả listing available
    public function cSearchListingsWithFilters($location, $checkin = null, $checkout = null, $guests = 1){
        $mListing = new mListing();
        return $mListing->mSearchListingsWithFilters($location, $checkin, $checkout, $guests);
    }
    
    // Lấy danh sách tiện nghi của listing
    // int $listingId - ID listing
    // return mysqli_result|array - Danh sách amenities
    public function cGetListingAmenities($listingId){
        $mListing = new mListing();
        $amenitiesResult = $mListing->mGetListingAmenities($listingId);
        
        // Vẫn return mysqli_result nếu có, hoặc array nếu model đã xử lý
        if (is_array($amenitiesResult)) {
            return $amenitiesResult;
        }
        return $amenitiesResult;
    }
    
    // Lấy thông tin chi tiết listing
    // int $listingId - ID listing
    // return mysqli_result|array - Dữ liệu listing
    public function cGetListingDetail($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingDetail($listingId);
    }
    
    // Lấy tất cả ảnh của listing
    // int $listingId - ID listing
    // return mysqli_result|array - Danh sách images
    public function cGetListingImages($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingImages($listingId);
    }
    
    // Lấy danh sách đánh giá của listing
    // int $listingId - ID listing
    // int $limit - Số lượng reviews (mặc định 10)
    // return mysqli_result|array - Danh sách reviews
    public function cGetListingReviews($listingId, $limit = 10){
        $mListing = new mListing();
        return $mListing->mGetListingReviews($listingId, $limit);
    }
    
    // Lấy các ngày đã được booking của listing
    // Dùng để disable date picker
    // int $listingId - ID listing
    // return mysqli_result|array - Danh sách ngày đã book
    public function cGetBookedDates($listingId){
        $mListing = new mListing();
        return $mListing->mGetBookedDates($listingId);
    }
    
    // Lấy danh sách dịch vụ của listing
    // int $listingId - ID listing
    // return mysqli_result|array - Danh sách services với giá
    public function cGetListingServices($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingServices($listingId);
    }
    
    // Lấy top tỉnh/thành phố theo số lượng booking
    // Trả về các điểm đến phổ biến nhất dựa trên booking đã confirmed/completed
    // int $limit - Số lượng tỉnh/thành cần lấy (mặc định 4)
    // return array - Danh sách provinces với thống kê booking
    public function cGetTopProvincesByBookings($limit = 4) {
        $mListing = new mListing();
        $result = $mListing->mGetTopProvincesByBookings($limit);
        
        // Return result or empty array if failed
        return is_array($result) ? $result : [];
    }
    
    // Tìm kiếm listing theo tiện nghi
    // array $amenityIds - Mảng ID tiện nghi
    // string|null $checkin - Ngày check-in (Y-m-d)
    // string|null $checkout - Ngày check-out (Y-m-d)
    // int $guests - Số khách (mặc định 1)
    // return mysqli_result|array - Kết quả listing có amenities đó
    public function cSearchListingsByAmenity($amenityIds, $checkin = null, $checkout = null, $guests = 1){
        $mListing = new mListing();
        return $mListing->mSearchListingsByAmenity($amenityIds, $checkin, $checkout, $guests);
    }
 }
?>