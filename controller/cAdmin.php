<?php
include_once(__DIR__ . "/../model/mAdmin.php");
include_once(__DIR__ . "/../model/mHost.php");
include_once(__DIR__ . "/../model/mListing.php");

class cAdmin {
    
    public function cAdminLogin($username, $password) {
        // Validate input
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors,
                'admin' => null
            ];
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mAdminLogin($username, $password);
    }
    
    public function cGetDashboardStats() {
        $mAdmin = new mAdmin();
        return $mAdmin->mGetDashboardStats();
    }
    
    public function cGetAllHostApplications($status = null) {
        // Validate status
        if ($status !== null) {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetAllHostApplications($status);
    }
    
    public function cGetHostApplicationDetail($applicationId) {
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return null;
        }
        
        $mAdmin = new mAdmin();
        return $mAdmin->mGetHostApplicationDetail($applicationId);
    }
    
    public function cApproveHostApplication($applicationId, $adminId) {
        // Validate input
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return [
                'success' => false,
                'message' => 'Application ID không hợp lệ'
            ];
        }
        
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [
                'success' => false,
                'message' => 'Admin ID không hợp lệ'
            ];
        }
        
        // Kiểm tra application tồn tại và đang pending
        $mAdmin = new mAdmin();
        $application = $mAdmin->mGetHostApplicationDetail($applicationId);
        
        if (!$application) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn đăng ký'
            ];
        }
        
        if ($application['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Đơn này đã được xử lý rồi'
            ];
        }
        
        // Approve application
        $result = $mAdmin->mApproveHostApplication($applicationId, $adminId);
        
        if ($result) {
            // Tạo host record
            $mHost = new mHost();
            $hostCreated = $mHost->mCreateHostFromApplication($applicationId);
            
            if ($hostCreated) {
                return [
                    'success' => true,
                    'message' => 'Đã duyệt đơn thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Đã duyệt đơn nhưng không thể tạo host record'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt đơn'
            ];
        }
    }
    
    public function cRejectHostApplication($applicationId, $adminId, $reason) {
        // Validate input
        if (!is_numeric($applicationId) || $applicationId <= 0) {
            return [
                'success' => false,
                'message' => 'Application ID không hợp lệ'
            ];
        }
        
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [
                'success' => false,
                'message' => 'Admin ID không hợp lệ'
            ];
        }
        
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập lý do từ chối'
            ];
        }
        
        if (strlen($reason) > 500) {
            return [
                'success' => false,
                'message' => 'Lý do từ chối quá dài (tối đa 500 ký tự)'
            ];
        }
        
        // Kiểm tra application tồn tại và đang pending
        $mAdmin = new mAdmin();
        $application = $mAdmin->mGetHostApplicationDetail($applicationId);
        
        if (!$application) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn đăng ký'
            ];
        }
        
        if ($application['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Đơn này đã được xử lý rồi'
            ];
        }
        
        // Reject application
        $result = $mAdmin->mRejectHostApplication($applicationId, $adminId, $reason);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã từ chối đơn thành công'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối đơn'
            ];
        }
    }
    
    // Listing management methods
    public function cGetAllListings($status = null) {
        // Validate status
        if ($status !== null) {
            $validStatuses = ['draft', 'pending', 'active', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                $status = null;
            }
        }
        
        $mListing = new mListing();
        return $mListing->mGetAllListings($status);
    }
    
    public function cApproveListing($listingId, $adminId) {
        // Validate input
        if (!is_numeric($listingId) || $listingId <= 0) {
            return [
                'success' => false,
                'message' => 'Listing ID không hợp lệ'
            ];
        }
        
        $mListing = new mListing();
        $result = $mListing->mUpdateListingStatus($listingId, 'active');
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã phê duyệt phòng thành công'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi phê duyệt phòng'
            ];
        }
    }
    
    public function cRejectListing($listingId, $adminId, $reason) {
        // Validate input
        if (!is_numeric($listingId) || $listingId <= 0) {
            return [
                'success' => false,
                'message' => 'Listing ID không hợp lệ'
            ];
        }
        
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập lý do từ chối'
            ];
        }
        
        $mListing = new mListing();
        $result = $mListing->mUpdateListingStatus($listingId, 'rejected', $reason);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã từ chối phòng'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối phòng'
            ];
        }
    }
}
?>
