<?php
/**
 * Model: Revenue Management
 * Quản lý thống kê doanh thu từ bookings và invoices
 */

require_once(__DIR__ . '/mConnect.php');

class mRevenue extends mConnect {
    
    /**
     * Lấy tổng doanh thu của host
     * @param int $hostId
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function mGetHostTotalRevenue($hostId, $startDate = null, $endDate = null) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    COUNT(DISTINCT b.booking_id) as total_bookings,
                    SUM(i.total) as total_revenue,
                    SUM(i.service_fee) as total_commission,
                    SUM(i.total - i.service_fee) as net_revenue
                FROM bookings b
                INNER JOIN invoice i ON b.booking_id = i.booking_id
                INNER JOIN listing l ON b.listing_id = l.listing_id
                WHERE l.host_id = ?
                AND b.status IN ('confirmed', 'completed')
                AND i.status = 'issued'";
        
        $params = [$hostId];
        $types = "i";
        
        if ($startDate && $endDate) {
            $sql .= " AND b.created_at BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        return $data ?: [
            'total_bookings' => 0,
            'total_revenue' => 0,
            'total_commission' => 0,
            'net_revenue' => 0
        ];
    }
    
    /**
     * Lấy doanh thu theo từng phòng của host
     * @param int $hostId
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function mGetRevenueByListing($hostId, $startDate = null, $endDate = null) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    l.listing_id,
                    l.name as listing_name,
                    l.price_per_night,
                    COUNT(b.booking_id) as total_bookings,
                    SUM(DATEDIFF(b.check_out, b.check_in)) as total_nights,
                    SUM(i.total) as revenue,
                    SUM(i.service_fee) as commission,
                    SUM(i.total - i.service_fee) as net_revenue
                FROM listing l
                LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                    AND b.status IN ('confirmed', 'completed')
                LEFT JOIN invoice i ON b.booking_id = i.booking_id 
                    AND i.status = 'issued'
                WHERE l.host_id = ?";
        
        $params = [$hostId];
        $types = "i";
        
        if ($startDate && $endDate) {
            $sql .= " AND b.created_at BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY l.listing_id
                  ORDER BY revenue DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $data;
    }
    
    /**
     * Lấy doanh thu theo tháng (cho biểu đồ)
     * @param int $hostId
     * @param int $year
     * @return array
     */
    public function mGetMonthlyRevenue($hostId, $year) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    MONTH(b.created_at) as month,
                    COUNT(b.booking_id) as bookings,
                    SUM(i.total) as revenue,
                    SUM(i.service_fee) as commission,
                    SUM(i.total - i.service_fee) as net_revenue
                FROM bookings b
                INNER JOIN invoice i ON b.booking_id = i.booking_id
                INNER JOIN listing l ON b.listing_id = l.listing_id
                WHERE l.host_id = ?
                AND YEAR(b.created_at) = ?
                AND b.status IN ('confirmed', 'completed')
                AND i.status = 'issued'
                GROUP BY MONTH(b.created_at)
                ORDER BY month";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $hostId, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Initialize all 12 months with 0
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = [
                'month' => $i,
                'bookings' => 0,
                'revenue' => 0,
                'commission' => 0,
                'net_revenue' => 0
            ];
        }
        
        // Fill with actual data
        while ($row = $result->fetch_assoc()) {
            $data[$row['month']] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return array_values($data); // Convert to indexed array
    }
    
    /**
     * Lấy thống kê booking của host
     * @param int $hostId
     * @return array
     */
    public function mGetBookingStatistics($hostId) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
                    COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as completed_bookings,
                    COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_bookings,
                    AVG(DATEDIFF(b.check_out, b.check_in)) as avg_stay_duration,
                    AVG(i.total) as avg_booking_value
                FROM bookings b
                INNER JOIN listing l ON b.listing_id = l.listing_id
                LEFT JOIN invoice i ON b.booking_id = i.booking_id
                WHERE l.host_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $hostId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        return $data;
    }
    
    /**
     * ADMIN: Lấy tổng doanh thu toàn hệ thống
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function mGetSystemTotalRevenue($startDate = null, $endDate = null) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    COUNT(DISTINCT b.booking_id) as total_bookings,
                    COUNT(DISTINCT l.host_id) as total_hosts,
                    SUM(i.total) as total_revenue,
                    SUM(i.service_fee) as total_commission,
                    AVG(i.total) as avg_booking_value
                FROM bookings b
                INNER JOIN invoice i ON b.booking_id = i.booking_id
                INNER JOIN listing l ON b.listing_id = l.listing_id
                WHERE b.status IN ('confirmed', 'completed')
                AND i.status = 'issued'";
        
        $params = [];
        $types = "";
        
        if ($startDate && $endDate) {
            $sql .= " AND b.created_at BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        $data = $result->fetch_assoc();
        
        if (isset($stmt)) $stmt->close();
        $conn->close();
        
        return $data;
    }
    
    /**
     * ADMIN: Lấy doanh thu theo từng host
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @param int $limit
     * @return array
     */
    public function mGetRevenueByHost($startDate = null, $endDate = null, $limit = 10) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    h.host_id,
                    u.full_name as host_name,
                    u.email as host_email,
                    COUNT(DISTINCT l.listing_id) as total_listings,
                    COUNT(b.booking_id) as total_bookings,
                    SUM(i.total) as revenue,
                    SUM(i.service_fee) as commission,
                    SUM(i.total - i.service_fee) as net_revenue
                FROM host h
                INNER JOIN user u ON h.user_id = u.user_id
                LEFT JOIN listing l ON h.host_id = l.host_id
                LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                    AND b.status IN ('confirmed', 'completed')
                LEFT JOIN invoice i ON b.booking_id = i.booking_id 
                    AND i.status = 'issued'
                WHERE h.status = 'approved'";
        
        $params = [];
        $types = "";
        
        if ($startDate && $endDate) {
            $sql .= " AND b.created_at BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY h.host_id
                  ORDER BY revenue DESC
                  LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $data;
    }
    
    /**
     * ADMIN: Lấy doanh thu theo tháng (toàn hệ thống)
     * @param int $year
     * @return array
     */
    public function mGetSystemMonthlyRevenue($year) {
        $conn = $this->connect();
        
        $sql = "SELECT 
                    MONTH(b.created_at) as month,
                    COUNT(b.booking_id) as bookings,
                    SUM(i.total) as revenue,
                    SUM(i.service_fee) as commission
                FROM bookings b
                INNER JOIN invoice i ON b.booking_id = i.booking_id
                WHERE YEAR(b.created_at) = ?
                AND b.status IN ('confirmed', 'completed')
                AND i.status = 'issued'
                GROUP BY MONTH(b.created_at)
                ORDER BY month";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Initialize all 12 months
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = [
                'month' => $i,
                'bookings' => 0,
                'revenue' => 0,
                'commission' => 0
            ];
        }
        
        // Fill with actual data
        while ($row = $result->fetch_assoc()) {
            $data[$row['month']] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return array_values($data);
    }
}
?>
