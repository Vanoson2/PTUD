<?php
/**
 * Controller: Revenue Management
 * Xử lý logic và validation cho thống kê doanh thu
 */

require_once(__DIR__ . '/../model/mRevenue.php');

class cRevenue {
    private $model;
    
    public function __construct() {
        $this->model = new mRevenue();
    }
    
    /**
     * Lấy tổng doanh thu của host
     * @param int $hostId
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function cGetHostTotalRevenue($hostId, $startDate = null, $endDate = null) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        // Validate dates if provided
        if ($startDate && $endDate) {
            if (strtotime($startDate) === false || strtotime($endDate) === false) {
                return ['success' => false, 'message' => 'Định dạng ngày không hợp lệ'];
            }
            
            if (strtotime($startDate) > strtotime($endDate)) {
                return ['success' => false, 'message' => 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc'];
            }
        }
        
        $data = $this->model->mGetHostTotalRevenue($hostId, $startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Lấy doanh thu theo từng phòng của host
     * @param int $hostId
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function cGetRevenueByListing($hostId, $startDate = null, $endDate = null) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        $data = $this->model->mGetRevenueByListing($hostId, $startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Lấy doanh thu theo tháng (cho biểu đồ)
     * @param int $hostId
     * @param int $year
     * @return array
     */
    public function cGetMonthlyRevenue($hostId, $year = null) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        // Default to current year
        if (!$year) {
            $year = date('Y');
        }
        
        if (!is_numeric($year) || $year < 2020 || $year > 2100) {
            return ['success' => false, 'message' => 'Năm không hợp lệ'];
        }
        
        $data = $this->model->mGetMonthlyRevenue($hostId, $year);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Lấy thống kê booking của host
     * @param int $hostId
     * @return array
     */
    public function cGetBookingStatistics($hostId) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        $data = $this->model->mGetBookingStatistics($hostId);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * ADMIN: Lấy tổng doanh thu toàn hệ thống
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function cGetSystemTotalRevenue($startDate = null, $endDate = null) {
        $data = $this->model->mGetSystemTotalRevenue($startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * ADMIN: Lấy doanh thu theo từng host
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @param int $limit
     * @return array
     */
    public function cGetRevenueByHost($startDate = null, $endDate = null, $limit = 10) {
        if (!is_numeric($limit) || $limit <= 0 || $limit > 100) {
            $limit = 10;
        }
        
        $data = $this->model->mGetRevenueByHost($startDate, $endDate, $limit);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * ADMIN: Lấy doanh thu theo tháng (toàn hệ thống)
     * @param int $year
     * @return array
     */
    public function cGetSystemMonthlyRevenue($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        if (!is_numeric($year) || $year < 2020 || $year > 2100) {
            return ['success' => false, 'message' => 'Năm không hợp lệ'];
        }
        
        $data = $this->model->mGetSystemMonthlyRevenue($year);
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Format tiền VNĐ
     * @param float $amount
     * @return string
     */
    public static function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
    
    /**
     * Format số với dấu phẩy
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, ',', '.');
    }
}
?>
