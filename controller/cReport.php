<?php
include_once(__DIR__ . '/../model/mReport.php');

class cReport {
    
    // ========== HOST REPORTS ==========
    
    public function cGetHostRevenueByMonth($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostRevenueByMonth($userId);
    }
    
    public function cGetHostTopListings($userId, $limit = 5) {
        $mReport = new mReport();
        return $mReport->mGetHostTopListings($userId, $limit);
    }
    
    public function cGetHostBookingsByStatus($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostBookingsByStatus($userId);
    }
    
    public function cGetHostRatingsDistribution($userId) {
        $mReport = new mReport();
        return $mReport->mGetHostRatingsDistribution($userId);
    }
    
    // ========== ADMIN REPORTS ==========
    
    public function cGetSystemOverview() {
        $mReport = new mReport();
        return $mReport->mGetSystemOverview();
    }
    
    public function cGetSystemRevenueByMonth() {
        $mReport = new mReport();
        return $mReport->mGetSystemRevenueByMonth();
    }
    
    public function cGetTopHosts($limit = 10) {
        $mReport = new mReport();
        return $mReport->mGetTopHosts($limit);
    }
    
    public function cGetNewListingsByMonth() {
        $mReport = new mReport();
        return $mReport->mGetNewListingsByMonth();
    }
    
    public function cGetNewUsersByMonth() {
        $mReport = new mReport();
        return $mReport->mGetNewUsersByMonth();
    }
    
    public function cGetListingsByProvince($limit = 10) {
        $mReport = new mReport();
        return $mReport->mGetListingsByProvince($limit);
    }
}
?>
