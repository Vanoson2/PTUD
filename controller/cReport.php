<?php
include_once(__DIR__ . '/../model/mReport.php');

class cReport {
    
    // ===== BÁO CÁO HOST =====
    
    // Lấy doanh thu host theo tháng
    // int $userId - ID user/host
    // return array - Doanh thu theo từng tháng
    public function cGetHostRevenueByMonth($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostRevenueByMonth($userId);
    }
    
    // Lấy top listing của host theo doanh thu/booking
    // int $userId - ID user/host
    // int $limit - Số lượng listing (mặc định 5)
    // return array - Top listings
    public function cGetHostTopListings($userId, $limit = 5) {
        $mReport = new mReport();
        return $mReport->mGetHostTopListings($userId, $limit);
    }
    
    // Lấy thống kê booking host theo trạng thái
    // int $userId - ID user/host
    // return array - Số lượng booking theo status
    public function cGetHostBookingsByStatus($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostBookingsByStatus($userId);
    }
    
    // Lấy phân bố đánh giá của host (1-5 sao)
    // int $userId - ID user/host
    // return array - Số lượng đánh giá theo rating
    public function cGetHostRatingsDistribution($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostRatingsDistribution($userId);
    }
    
    // ===== BÁO CÁO ADMIN =====
    
    // Lấy tổng quan hệ thống (tổng user, host, listing, booking, revenue)
    // return array - Thống kê tổng quan
    public function cGetSystemOverview() {
        $mReport = new mReport();
        return $mReport->mGetSystemOverview();
    }
    
    // Lấy doanh thu hệ thống theo tháng
    // return array - Doanh thu theo từng tháng
    public function cGetSystemRevenueByMonth() {
        $mReport = new mReport();
        return $mReport->mGetSystemRevenueByMonth();
    }
    
    // Lấy top host theo doanh thu
    // int $limit - Số lượng host (mặc định 10)
    // return array - Top hosts
    public function cGetTopHosts($limit = 10) {
        $mReport = new mReport();
        return $mReport->mGetTopHosts($limit);
    }
    
    // Lấy số listing mới theo tháng
    // return array - Số listing mới theo từng tháng
    public function cGetNewListingsByMonth() {
        $mReport = new mReport();
        return $mReport->mGetNewListingsByMonth();
    }
    
    // Lấy số user mới theo tháng
    // return array - Số user mới theo từng tháng
    public function cGetNewUsersByMonth() {
        $mReport = new mReport();
        return $mReport->mGetNewUsersByMonth();
    }
    
    // Lấy số lượng listing theo tỉnh/thành
    // int $limit - Số lượng tỉnh (mặc định 10)
    // return array - Top provinces theo số listing
    public function cGetListingsByProvince($limit = 10) {
        $mReport = new mReport();
        return $mReport->mGetListingsByProvince($limit);
    }
}
?>
