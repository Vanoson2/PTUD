<?php
include_once(__DIR__ . '/mConnect.php');

class mReport {
    
    // ========== HOST REPORTS ==========
    
    // Lấy doanh thu theo tháng của host (12 tháng gần nhất)
    public function mGetHostRevenueByMonth($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            
            $strSelect = "SELECT 
                DATE_FORMAT(b.created_at, '%Y-%m') as month,
                DATE_FORMAT(b.created_at, '%m/%Y') as month_label,
                COUNT(b.booking_id) as total_bookings,
                SUM(b.total_amount) as total_revenue
            FROM bookings b
            INNER JOIN listing l ON b.listing_id = l.listing_id
            INNER JOIN host h ON l.host_id = h.host_id
            WHERE h.user_id = $userId
            AND b.status IN ('confirmed', 'completed')
            AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
            ORDER BY month ASC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Lấy top listings của host theo doanh thu
    public function mGetHostTopListings($userId, $limit = 5) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            $limit = intval($limit);
            
            $strSelect = "SELECT 
                l.listing_id,
                l.title,
                COUNT(b.booking_id) as total_bookings,
                SUM(b.total_amount) as total_revenue,
                AVG(r.rating) as avg_rating,
                COUNT(DISTINCT r.review_id) as total_reviews
            FROM listing l
            INNER JOIN host h ON l.host_id = h.host_id
            LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                AND b.status IN ('confirmed', 'completed')
            LEFT JOIN review r ON l.listing_id = r.listing_id
            WHERE h.user_id = $userId
            GROUP BY l.listing_id
            ORDER BY total_revenue DESC
            LIMIT $limit";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Lấy thống kê bookings theo trạng thái của host
    public function mGetHostBookingsByStatus($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            
            $strSelect = "SELECT 
                b.status,
                COUNT(b.booking_id) as count
            FROM bookings b
            INNER JOIN listing l ON b.listing_id = l.listing_id
            INNER JOIN host h ON l.host_id = h.host_id
            WHERE h.user_id = $userId
            GROUP BY b.status";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Lấy thống kê ratings của host
    public function mGetHostRatingsDistribution($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $userId = intval($userId);
            
            $strSelect = "SELECT 
                r.rating,
                COUNT(r.review_id) as count
            FROM review r
            INNER JOIN listing l ON r.listing_id = l.listing_id
            INNER JOIN host h ON l.host_id = h.host_id
            WHERE h.user_id = $userId
            GROUP BY r.rating
            ORDER BY r.rating DESC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // ========== ADMIN REPORTS ==========
    
    // Tổng quan hệ thống
    public function mGetSystemOverview() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT 
                (SELECT COUNT(*) FROM user) as total_users,
                (SELECT COUNT(*) FROM host WHERE status = 'approved') as total_hosts,
                (SELECT COUNT(*) FROM listing WHERE status = 'active') as active_listings,
                (SELECT COUNT(*) FROM listing WHERE status = 'pending') as pending_listings,
                (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') as confirmed_bookings,
                (SELECT COUNT(*) FROM bookings WHERE status = 'completed') as completed_bookings,
                (SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status IN ('confirmed', 'completed')) as total_revenue,
                (SELECT COUNT(*) FROM support_ticket WHERE status = 'open') as open_tickets";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Doanh thu theo tháng toàn hệ thống
    public function mGetSystemRevenueByMonth() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%m/%Y') as month_label,
                COUNT(booking_id) as total_bookings,
                SUM(total_amount) as total_revenue
            FROM bookings
            WHERE status IN ('confirmed', 'completed')
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Top hosts theo doanh thu
    public function mGetTopHosts($limit = 10) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $limit = intval($limit);
            
            $strSelect = "SELECT 
                u.user_id,
                u.full_name as fullname,
                u.email,
                COUNT(DISTINCT l.listing_id) as total_listings,
                COUNT(b.booking_id) as total_bookings,
                COALESCE(SUM(b.total_amount), 0) as total_revenue,
                AVG(r.rating) as avg_rating
            FROM user u
            INNER JOIN host h ON u.user_id = h.user_id
            LEFT JOIN listing l ON h.host_id = l.host_id
            LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                AND b.status IN ('confirmed', 'completed')
            LEFT JOIN review r ON l.listing_id = r.listing_id
            WHERE h.status = 'approved'
            GROUP BY u.user_id
            HAVING COUNT(DISTINCT l.listing_id) > 0
            ORDER BY total_revenue DESC
            LIMIT $limit";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Listings mới theo tháng
    public function mGetNewListingsByMonth() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%m/%Y') as month_label,
                COUNT(listing_id) as count
            FROM listing
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Users mới theo tháng
    public function mGetNewUsersByMonth() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%m/%Y') as month_label,
                COUNT(user_id) as count
            FROM user
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Thống kê listings theo tỉnh thành
    public function mGetListingsByProvince($limit = 10) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $limit = intval($limit);
            
            $strSelect = "SELECT 
                p.name as province_name,
                COUNT(l.listing_id) as total_listings,
                COUNT(b.booking_id) as total_bookings,
                SUM(b.total_amount) as total_revenue
            FROM listing l
            INNER JOIN wards w ON l.ward_code = w.code
            INNER JOIN provinces p ON w.province_code = p.code
            LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                AND b.status IN ('confirmed', 'completed')
            WHERE l.status = 'active'
            GROUP BY p.code
            ORDER BY total_listings DESC
            LIMIT $limit";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
}
?>
