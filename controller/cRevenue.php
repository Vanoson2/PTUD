<?php
// Controller: Quản lý doanh thu
// Xử lý logic và validation cho thống kê doanh thu

require_once(__DIR__ . '/../model/mRevenue.php');

class cRevenue {
    private $model;
    
    public function __construct() {
        $this->model = new mRevenue();
    }
    
    // Lấy tổng doanh thu của host
    // Validate host ID và date range nếu có
    // int $hostId - ID host
    // string|null $startDate - Ngày bắt đầu (Y-m-d)
    // string|null $endDate - Ngày kết thúc (Y-m-d)
    // return array - ['success' => bool, 'data' => array, 'message' => string]
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
    
    // Lấy doanh thu theo từng phòng/listing của host
    // int $hostId - ID host
    // string|null $startDate - Ngày bắt đầu (Y-m-d)
    // string|null $endDate - Ngày kết thúc (Y-m-d)
    // return array - ['success' => bool, 'data' => array]
    public function cGetRevenueByListing($hostId, $startDate = null, $endDate = null) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        $data = $this->model->mGetRevenueByListing($hostId, $startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }
    
    // Lấy doanh thu theo tháng (cho biểu đồ)
    // int $hostId - ID host
    // int|null $year - Năm (mặc định năm hiện tại, phải từ 2020-2100)
    // return array - ['success' => bool, 'data' => array, 'message' => string]
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
    
    // Lấy thống kê booking của host (tổng, completed, cancelled, etc.)
    // int $hostId - ID host
    // return array - ['success' => bool, 'data' => array]
    public function cGetBookingStatistics($hostId) {
        if (!is_numeric($hostId) || $hostId <= 0) {
            return ['success' => false, 'message' => 'Host ID không hợp lệ'];
        }
        
        $data = $this->model->mGetBookingStatistics($hostId);
        return ['success' => true, 'data' => $data];
    }
    
    // ADMIN: Lấy tổng doanh thu toàn hệ thống
    // string|null $startDate - Ngày bắt đầu (Y-m-d)
    // string|null $endDate - Ngày kết thúc (Y-m-d)
    // return array - ['success' => bool, 'data' => array]
    public function cGetSystemTotalRevenue($startDate = null, $endDate = null) {
        $data = $this->model->mGetSystemTotalRevenue($startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }
    
    // ADMIN: Lấy doanh thu theo từng host (top hosts)
    // string|null $startDate - Ngày bắt đầu (Y-m-d)
    // string|null $endDate - Ngày kết thúc (Y-m-d)
    // int $limit - Số lượng host (1-100, mặc định 10)
    // return array - ['success' => bool, 'data' => array]
    public function cGetRevenueByHost($startDate = null, $endDate = null, $limit = 10) {
        if (!is_numeric($limit) || $limit <= 0 || $limit > 100) {
            $limit = 10;
        }
        
        $data = $this->model->mGetRevenueByHost($startDate, $endDate, $limit);
        return ['success' => true, 'data' => $data];
    }
    
    // ADMIN: Lấy doanh thu theo tháng toàn hệ thống (cho biểu đồ admin)
    // int|null $year - Năm (mặc định năm hiện tại, phải từ 2020-2100)
    // return array - ['success' => bool, 'data' => array, 'message' => string]
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
    
    // Format tiền VNĐ với dấu phẩy và ký hiệu đ
    // float $amount - Số tiền
    // return string - Số tiền đã format (vd: "1.000.000đ")
    public static function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
    
    // Format số với dấu phẩy phân cách hàng nghìn
    // float $number - Số cần format
    // int $decimals - Số chữ số thập phân (mặc định 0)
    // return string - Số đã format (vd: "1.000")
    public static function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, ',', '.');
    }
}
?>
