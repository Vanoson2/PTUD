<?php
include_once __DIR__ . '/mConnect.php';

class mHost {
    
    public function mCreateHostApplication($userId, $businessName, $taxCode = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database',
                'application_id' => null
            ];
        }
        
        $businessName = $conn->real_escape_string($businessName);
        $taxCode = $conn->real_escape_string($taxCode);
        
        // Check xem user đã có application pending chưa
        $checkSql = "SELECT host_application_id, status 
                     FROM host_application 
                     WHERE user_id = $userId 
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $result = $conn->query($checkSql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] === 'pending') {
                $p->mDongKetNoi($conn);
                return [
                    'success' => false,
                    'message' => 'Bạn đã có đơn đăng ký đang chờ duyệt',
                    'application_id' => null
                ];
            }
        }
        
        // Tạo application mới
        $sql = "INSERT INTO host_application (user_id, business_name, tax_code, status, created_at) 
                VALUES ($userId, '$businessName', '$taxCode', 'pending', CURRENT_TIMESTAMP)";
        
        if ($conn->query($sql)) {
            $applicationId = $conn->insert_id;
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Tạo đơn đăng ký thành công',
                'application_id' => $applicationId
            ];
        }
        
        // Lấy error message trước khi đóng connection
        $errorMessage = $conn->error;
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Không thể tạo đơn đăng ký: ' . $errorMessage,
            'application_id' => null
        ];
    }
    
    /**
     * Create pending host record immediately after registration
     * @param int $userId User ID
     * @param string $businessName Business name
     * @param string $taxCode Tax code
     * @return bool
     */
    public function mCreatePendingHost($userId, $businessName, $taxCode = '') {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $businessName = $conn->real_escape_string($businessName);
        $taxCode = $conn->real_escape_string($taxCode);
        $taxCodeValue = !empty($taxCode) ? "'$taxCode'" : "NULL";
        
        // Create host with pending status
        $sql = "INSERT INTO host (user_id, legal_name, tax_code, status, created_at) 
                VALUES ($userId, '$businessName', $taxCodeValue, 'pending', CURRENT_TIMESTAMP)";
        
        $success = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $success ? true : false;
    }
    
    public function mSaveHostDocument($applicationId, $docType, $fileUrl, $mimeType, $fileSizeBytes) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $docType = $conn->real_escape_string($docType);
        $fileUrl = $conn->real_escape_string($fileUrl);
        $mimeType = $conn->real_escape_string($mimeType);
        
        // Xóa document cũ nếu có (do constraint UNIQUE)
        $deleteSql = "DELETE FROM host_document 
                      WHERE host_application_id = $applicationId 
                      AND doc_type = '$docType'";
        $conn->query($deleteSql);
        
        // Insert document mới
        $sql = "INSERT INTO host_document 
                (host_application_id, doc_type, file_url, mime_type, file_size_bytes, created_at) 
                VALUES 
                ($applicationId, '$docType', '$fileUrl', '$mimeType', $fileSizeBytes, CURRENT_TIMESTAMP)";
        
        $result = $conn->query($sql);
        $p->mDongKetNoi($conn);
        
        return $result ? true : false;
    }
    
    public function mGetHostApplicationByUser($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT ha.*, 
                       u.full_name, u.email, u.phone,
                       a.full_name as reviewed_by_name
                FROM host_application ha
                INNER JOIN user u ON ha.user_id = u.user_id
                LEFT JOIN admin a ON ha.reviewed_by_admin_id = a.admin_id
                WHERE ha.user_id = $userId
                ORDER BY ha.created_at DESC
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $p->mDongKetNoi($conn);
            return $data;
        }
        
        $p->mDongKetNoi($conn);
        return null;
    }
    
    public function mGetHostDocuments($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [];
        }
        
        $sql = "SELECT * FROM host_document 
                WHERE host_application_id = $applicationId 
                ORDER BY created_at ASC";
        
        $result = $conn->query($sql);
        $documents = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $documents[] = $row;
            }
        }
        
        $p->mDongKetNoi($conn);
        return $documents;
    }
    
    public function mIsUserHost($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        // Allow both pending and approved hosts to access host features
        $sql = "SELECT host_id FROM host WHERE user_id = $userId AND status IN ('pending', 'approved') LIMIT 1";
        $result = $conn->query($sql);
        
        $isHost = ($result && $result->num_rows > 0);
        $p->mDongKetNoi($conn);
        
        return $isHost;
    }
    
    public function mCreateHostFromApplication($applicationId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        // Lấy thông tin application
        $sql = "SELECT user_id, business_name, tax_code 
                FROM host_application 
                WHERE host_application_id = $applicationId 
                AND status = 'approved'";
        
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return false;
        }
        
        $app = $result->fetch_assoc();
        $userId = $app['user_id'];
        $businessName = $conn->real_escape_string($app['business_name']);
        $taxCode = $conn->real_escape_string($app['tax_code']);
        
        // Check xem đã là host chưa
        $checkSql = "SELECT host_id FROM host WHERE user_id = $userId LIMIT 1";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            // Đã là host rồi, chỉ update status
            $updateSql = "UPDATE host SET status = 'approved', legal_name = '$businessName', tax_code = '$taxCode' WHERE user_id = $userId";
            $success = $conn->query($updateSql);
        } else {
            // Tạo host mới - lưu tax_code nếu có
            $taxCodeValue = !empty($taxCode) ? "'$taxCode'" : "NULL";
            $insertSql = "INSERT INTO host (user_id, legal_name, tax_code, status, created_at) 
                          VALUES ($userId, '$businessName', $taxCodeValue, 'approved', CURRENT_TIMESTAMP)";
            $success = $conn->query($insertSql);
        }
        
        $p->mDongKetNoi($conn);
        return $success ? true : false;
    }
    
    public function mGetHostByUserId($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT h.*, u.full_name, u.email, u.phone
                FROM host h
                JOIN user u ON h.user_id = u.user_id
                WHERE h.user_id = $userId
                LIMIT 1";
        
        $result = $conn->query($sql);
        $host = null;
        
        if ($result && $result->num_rows > 0) {
            $host = $result->fetch_assoc();
        }
        
        $p->mDongKetNoi($conn);
        return $host;
    }
    
    public function mGetUserHostApplication($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return null;
        }
        
        $sql = "SELECT ha.*, 
                       a.full_name as reviewed_by_name
                FROM host_application ha
                LEFT JOIN admin a ON ha.reviewed_by_admin_id = a.admin_id
                WHERE ha.user_id = $userId
                ORDER BY ha.created_at DESC
                LIMIT 1";
        
        $result = $conn->query($sql);
        $application = null;
        
        if ($result && $result->num_rows > 0) {
            $application = $result->fetch_assoc();
        }
        
        $p->mDongKetNoi($conn);
        return $application;
    }
    
    public function mGetHostStatistics($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'total_listings' => 0,
                'total_bookings' => 0,
                'total_revenue' => 0,
                'average_rating' => 0,
                'total_reviews' => 0
            ];
        }
        
        $userId = intval($userId);
        $stats = [
            'total_listings' => 0,
            'total_bookings' => 0,
            'total_revenue' => 0,
            'average_rating' => 0,
            'total_reviews' => 0
        ];
        
        // Lấy host_id từ user_id
        $hostSql = "SELECT host_id FROM host WHERE user_id = $userId LIMIT 1";
        $hostResult = $conn->query($hostSql);
        
        if (!$hostResult || $hostResult->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return $stats; // User chưa là host
        }
        
        $hostRow = $hostResult->fetch_assoc();
        $hostId = $hostRow['host_id'];
        
        // Đếm số listings
        $listingSql = "SELECT COUNT(*) as total FROM listing WHERE host_id = $hostId AND status = 'active'";
        $result = $conn->query($listingSql);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_listings'] = (int)$row['total'];
        }
        
        // Đếm bookings và revenue
        $bookingSql = "SELECT COUNT(*) as total_bookings, 
                       COALESCE(SUM(total_amount), 0) as total_revenue 
                       FROM bookings b
                       INNER JOIN listing l ON b.listing_id = l.listing_id
                       WHERE l.host_id = $hostId AND b.status IN ('confirmed', 'completed')";
        $result = $conn->query($bookingSql);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_bookings'] = (int)$row['total_bookings'];
            $stats['total_revenue'] = (float)$row['total_revenue'];
        }
        
        // Đánh giá trung bình
        $reviewSql = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as total_reviews
                      FROM review r
                      INNER JOIN listing l ON r.listing_id = l.listing_id
                      WHERE l.host_id = $hostId";
        $result = $conn->query($reviewSql);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['average_rating'] = round((float)$row['avg_rating'], 1);
            $stats['total_reviews'] = (int)$row['total_reviews'];
        }
        
        $p->mDongKetNoi($conn);
        return $stats;
    }
    
    public function mCheckTaxCodeExists($taxCode) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return false;
        }
        
        $taxCode = $conn->real_escape_string($taxCode);
        
        // Kiểm tra trong host_application (không bao gồm các đơn bị từ chối)
        $sql = "SELECT COUNT(*) as count 
                FROM host_application 
                WHERE tax_code = '$taxCode' 
                AND status != 'rejected'";
        
        $result = $conn->query($sql);
        $exists = false;
        
        if ($result && $row = $result->fetch_assoc()) {
            $exists = ($row['count'] > 0);
        }
        
        $p->mDongKetNoi($conn);
        return $exists;
    }
}
?>
