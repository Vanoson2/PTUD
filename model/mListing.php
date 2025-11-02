<?php 
include_once("mConnect.php");

class mListing {
    
    // ============================================
    // TRAVELLER METHODS (Search & View Listings)
    // ============================================
    
    public function mCountListingByProvince($provinceName){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $provinceName = $conn->real_escape_string($provinceName);
            $strSelect = "SELECT COUNT(*) as total 
                         FROM listing l
                         INNER JOIN wards w ON l.ward_code = w.code
                         INNER JOIN provinces p ON w.province_code = p.code
                         WHERE (p.name LIKE '%$provinceName%' 
                            OR p.full_name LIKE '%$provinceName%')
                         AND l.status = 'active'";
            
            $result = $conn->query($strSelect);
            if($result && $row = $result->fetch_assoc()){
                return $row['total'];
            }
            return 0;
        }else{
            return false;
        }
    }
    
    // Tìm kiếm listings theo location (tỉnh/thành phố) - Dùng cho địa điểm nổi tiếng
    // CHỈ TÌM THEO TỈNH/THÀNH PHỐ - Không tìm trong ward, address, title
    public function mSearchListingsByLocation($location){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $location = $conn->real_escape_string($location);
            
            // CHỈ tìm trong province name và full_name (KHÔNG tìm ward, address, title)
            $strSelect = "SELECT 
                            l.listing_id,
                            l.title,
                            l.description,
                            l.price,
                            l.capacity,
                            l.address,
                            l.place_type_id,
                            pt.name as place_type_name,
                            p.name as province_name,
                            p.full_name as province_full_name,
                            w.name as ward_name,
                            w.full_name as ward_full_name,
                            li.file_url,
                            COALESCE(AVG(r.rating), 0) as avg_rating,
                            COUNT(DISTINCT r.review_id) as review_count
                         FROM listing l
                         LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                         LEFT JOIN wards w ON l.ward_code = w.code
                         LEFT JOIN provinces p ON w.province_code = p.code
                         LEFT JOIN listing_image li ON l.listing_id = li.listing_id AND li.is_cover = 1
                         LEFT JOIN review r ON l.listing_id = r.listing_id
                         WHERE l.status = 'active'
                         AND (p.name LIKE '%$location%' OR p.full_name LIKE '%$location%')
                         GROUP BY l.listing_id
                         ORDER BY 
                            CASE WHEN COUNT(DISTINCT r.review_id) > 0 THEN 0 ELSE 1 END,
                            avg_rating DESC,
                            l.created_at DESC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Tìm kiếm listings với filters (location, checkin, checkout, guests)
    public function mSearchListingsWithFilters($location, $checkin = null, $checkout = null, $guests = 1){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            // Tách location thành các từ khóa
            $keywords = array_map('trim', explode(',', $location));
            // Xây dựng WHERE clause cho location
            $whereConditions = [];
            foreach ($keywords as $keyword) {
                $keyword = $conn->real_escape_string($keyword);
                $whereConditions[] = "(
                    p.name LIKE '%$keyword%' 
                    OR p.full_name LIKE '%$keyword%'
                    OR w.name LIKE '%$keyword%'
                    OR w.full_name LIKE '%$keyword%'
                    OR l.address LIKE '%$keyword%'
                    OR l.title LIKE '%$keyword%'
                )";
            }
            
            $whereClause = implode(' OR ', $whereConditions);
            
            // Lọc theo sức chứa (capacity >= số khách)
            $guests = intval($guests);
            $capacityFilter = "AND l.capacity >= $guests";
            
            // Lọc theo ngày checkin/checkout (loại bỏ chỗ ở đã được đặt)
            $dateFilter = "";
            if (!empty($checkin) && !empty($checkout)) {
                $checkin = $conn->real_escape_string($checkin);
                $checkout = $conn->real_escape_string($checkout);
                // Loại bỏ các listing có booking trùng ngày
                // Booking trùng khi: (checkin_mới < checkout_cũ) AND (checkout_mới > checkin_cũ)
                $dateFilter = "AND l.listing_id NOT IN (
                    SELECT DISTINCT b.listing_id 
                    FROM bookings b 
                    WHERE b.status IN ('confirmed', 'pending')
                    AND (
                        (b.check_in < '$checkout' AND b.check_out > '$checkin')
                    )
                )";
            }
            
            $strSelect = "SELECT 
                            l.listing_id,
                            l.title,
                            l.description,
                            l.price,
                            l.capacity,
                            l.address,
                            l.place_type_id,
                            pt.name as place_type_name,
                            p.name as province_name,
                            p.full_name as province_full_name,
                            w.name as ward_name,
                            w.full_name as ward_full_name,
                            li.file_url,
                            COALESCE(AVG(r.rating), 0) as avg_rating,
                            COUNT(DISTINCT r.review_id) as review_count
                         FROM listing l
                         LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                         LEFT JOIN wards w ON l.ward_code = w.code
                         LEFT JOIN provinces p ON w.province_code = p.code
                         LEFT JOIN listing_image li ON l.listing_id = li.listing_id AND li.is_cover = 1
                         LEFT JOIN review r ON l.listing_id = r.listing_id
                         WHERE l.status = 'active'
                         AND ($whereClause)
                         $capacityFilter
                         $dateFilter
                         GROUP BY l.listing_id
                         ORDER BY 
                            CASE WHEN COUNT(DISTINCT r.review_id) > 0 THEN 0 ELSE 1 END,
                            avg_rating DESC,
                            l.created_at DESC";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // Lấy danh sách amenities của một listing
    public function mGetListingAmenities($listingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $strSelect = "SELECT a.amenity_id, a.name
                         FROM listing_amenity la
                         INNER JOIN amenity a ON la.amenity_id = a.amenity_id
                         WHERE la.listing_id = $listingId";
            
            $result = $conn->query($strSelect);
            return $result; // Return mysqli_result
        }else{
            return false;
        }
    }
    
    // Lấy chi tiết một listing theo ID
    public function mGetListingDetail($listingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $strSelect = "SELECT 
                            l.*,
                            pt.name as place_type_name,
                            p.name as province_name,
                            p.full_name as province_full_name,
                            w.name as ward_name,
                            w.full_name as ward_full_name,
                            COALESCE(AVG(r.rating), 0) as avg_rating,
                            COUNT(DISTINCT r.review_id) as review_count,
                            h.legal_name as host_name
                         FROM listing l
                         LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                         LEFT JOIN wards w ON l.ward_code = w.code
                         LEFT JOIN provinces p ON w.province_code = p.code
                         LEFT JOIN review r ON l.listing_id = r.listing_id
                         LEFT JOIN host h ON l.host_id = h.host_id
                         WHERE l.listing_id = $listingId
                         GROUP BY l.listing_id";
            
            $result = $conn->query($strSelect);
            if($result && $result->num_rows > 0){
                return $result->fetch_assoc();
            }
            return null;
        }else{
            return null;
        }
    }
    
    // Lấy tất cả ảnh của một listing
    public function mGetListingImages($listingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $strSelect = "SELECT * FROM listing_image 
                         WHERE listing_id = $listingId 
                         ORDER BY is_cover DESC, sort_order ASC";
            
            $result = $conn->query($strSelect);
            $images = [];
            if($result){
                while($row = $result->fetch_assoc()){
                    $images[] = $row;
                }
            }
            return $images;
        }else{
            return [];
        }
    }
    
    // Lấy reviews của một listing
    public function mGetListingReviews($listingId, $limit = 10){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $limit = intval($limit);
            $strSelect = "SELECT r.*, u.full_name as user_name
                         FROM review r
                         LEFT JOIN user u ON r.user_id = u.user_id
                         WHERE r.listing_id = $listingId
                         ORDER BY r.created_at DESC
                         LIMIT $limit";
            
            $result = $conn->query($strSelect);
            $reviews = [];
            if($result){
                while($row = $result->fetch_assoc()){
                    $reviews[] = $row;
                }
            }
            return $reviews;
        }else{
            return [];
        }
    }
    
    // Lấy các ngày đã được đặt của một listing
    public function mGetBookedDates($listingId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            // Chỉ lấy booking đang active (confirmed) và chưa hoàn thành
            $strSelect = "SELECT check_in, check_out 
                         FROM bookings 
                         WHERE listing_id = $listingId 
                         AND status = 'confirmed'
                         AND check_out >= CURDATE()
                         ORDER BY check_in ASC";
            
            $result = $conn->query($strSelect);
            $bookings = [];
            if($result){
                while($row = $result->fetch_assoc()){
                    $bookings[] = $row;
                }
            }
            return $bookings;
        }else{
            return [];
        }
    }
    
    // ============================================
    // HOST METHODS (Create & Manage Listings)
    // ============================================
    
    public function mCreateListing($hostId, $data) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $title = $conn->real_escape_string($data['title']);
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
        $address = $conn->real_escape_string($data['address']);
        $wardCode = isset($data['ward_code']) ? $conn->real_escape_string($data['ward_code']) : null;
        $placeTypeId = isset($data['place_type_id']) ? intval($data['place_type_id']) : null;
        $price = floatval($data['price']);
        $capacity = isset($data['capacity']) ? intval($data['capacity']) : 1;
        $latitude = isset($data['latitude']) ? floatval($data['latitude']) : null;
        $longitude = isset($data['longitude']) ? floatval($data['longitude']) : null;
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'draft';
        
        $wardCodeSql = $wardCode ? "'$wardCode'" : 'NULL';
        $placeTypeIdSql = $placeTypeId ? $placeTypeId : 'NULL';
        $latitudeSql = $latitude ? $latitude : 'NULL';
        $longitudeSql = $longitude ? $longitude : 'NULL';
        
        $sql = "INSERT INTO listing (
                  host_id, title, description, address, ward_code, 
                  place_type_id, price, capacity, latitude, longitude, status
                ) VALUES (
                  $hostId, '$title', '$description', '$address', $wardCodeSql,
                  $placeTypeIdSql, $price, $capacity, $latitudeSql, $longitudeSql, '$status'
                )";
        
        if ($conn->query($sql)) {
            return $conn->insert_id;
        }
        
        return false;
    }
    
    public function mGetHostListings($hostId, $status = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT l.*, 
                       l.price as price_per_night,
                       pt.name as place_type_name,
                       (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) as image_url,
                       (SELECT COUNT(*) FROM listing_image WHERE listing_id = l.listing_id) as image_count,
                       CONCAT(w.name, ', ', p.name) as location
                FROM listing l
                LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                LEFT JOIN wards w ON l.ward_code = w.code
                LEFT JOIN provinces p ON w.province_code = p.code
                WHERE l.host_id = $hostId";
        
        if ($status) {
            $status = $conn->real_escape_string($status);
            $sql .= " AND l.status = '$status'";
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        $result = $conn->query($sql);
        $listings = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $listings[] = $row;
            }
        }
        
        return $listings;
    }
    
    public function mGetListingById($listingId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return null;
        
        $sql = "SELECT l.*, 
                       pt.name as place_type_name,
                       u.full_name as host_name,
                       u.full_name as host_full_name,
                       u.email as host_email
                FROM listing l
                LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                LEFT JOIN host h ON l.host_id = h.host_id
                LEFT JOIN user u ON h.user_id = u.user_id
                WHERE l.listing_id = $listingId";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function mUpdateListing($listingId, $data) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $updates = [];
        
        if (isset($data['title'])) {
            $title = $conn->real_escape_string($data['title']);
            $updates[] = "title = '$title'";
        }
        
        if (isset($data['description'])) {
            $description = $conn->real_escape_string($data['description']);
            $updates[] = "description = '$description'";
        }
        
        if (isset($data['address'])) {
            $address = $conn->real_escape_string($data['address']);
            $updates[] = "address = '$address'";
        }
        
        if (isset($data['ward_code'])) {
            $wardCode = $data['ward_code'] ? "'" . $conn->real_escape_string($data['ward_code']) . "'" : 'NULL';
            $updates[] = "ward_code = $wardCode";
        }
        
        if (isset($data['place_type_id'])) {
            $placeTypeId = $data['place_type_id'] ? intval($data['place_type_id']) : 'NULL';
            $updates[] = "place_type_id = $placeTypeId";
        }
        
        if (isset($data['price'])) {
            $price = floatval($data['price']);
            $updates[] = "price = $price";
        }
        
        if (isset($data['capacity'])) {
            $capacity = intval($data['capacity']);
            $updates[] = "capacity = $capacity";
        }
        
        if (isset($data['status'])) {
            $status = $conn->real_escape_string($data['status']);
            $updates[] = "status = '$status'";
            
            if ($status === 'pending') {
                $updates[] = "submitted_at = NOW()";
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE listing SET " . implode(', ', $updates) . " WHERE listing_id = $listingId";
        return $conn->query($sql) ? true : false;
    }
    
    public function mDeleteListing($listingId, $hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $sql = "DELETE FROM listing WHERE listing_id = $listingId AND host_id = $hostId AND status = 'draft'";
        $result = $conn->query($sql);
        
        return $result && $conn->affected_rows > 0;
    }
    
    public function mUploadListingImage($listingId, $fileUrl, $isCover = false, $sortOrder = 0) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $fileUrl = $conn->real_escape_string($fileUrl);
        $isCover = $isCover ? 1 : 0;
        
        if ($isCover) {
            $conn->query("UPDATE listing_image SET is_cover = 0 WHERE listing_id = $listingId");
        }
        
        $sql = "INSERT INTO listing_image (listing_id, file_url, is_cover, sort_order) 
                VALUES ($listingId, '$fileUrl', $isCover, $sortOrder)";
        
        return $conn->query($sql) ? true : false;
    }
    
    public function mDeleteListingImage($imageId, $listingId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $sql = "DELETE FROM listing_image WHERE image_id = $imageId AND listing_id = $listingId";
        $result = $conn->query($sql);
        
        return $result && $conn->affected_rows > 0;
    }
    
    public function mGetAllPlaceTypes() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT * FROM place_type ORDER BY name ASC";
        $result = $conn->query($sql);
        $placeTypes = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $placeTypes[] = $row;
            }
        }
        
        return $placeTypes;
    }
    
    public function mGetAllAmenities() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT * FROM amenity ORDER BY group_name ASC, name ASC";
        $result = $conn->query($sql);
        $amenities = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $amenities[] = $row;
            }
        }
        
        return $amenities;
    }
    
    public function mGetAllServices() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT * FROM service ORDER BY name ASC";
        $result = $conn->query($sql);
        $services = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        return $services;
    }
    
    public function mSaveListingAmenities($listingId, $amenityIds) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $conn->query("DELETE FROM listing_amenity WHERE listing_id = $listingId");
        
        if (!empty($amenityIds)) {
            $values = [];
            foreach ($amenityIds as $amenityId) {
                $amenityId = intval($amenityId);
                $values[] = "($listingId, $amenityId)";
            }
            
            $sql = "INSERT INTO listing_amenity (listing_id, amenity_id) VALUES " . implode(', ', $values);
            return $conn->query($sql) ? true : false;
        }
        
        return true;
    }
    
    public function mSaveListingServices($listingId, $services) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        // Delete existing services
        $conn->query("DELETE FROM listing_service WHERE listing_id = $listingId");
        
        if (!empty($services)) {
            $values = [];
            foreach ($services as $serviceId => $price) {
                $serviceId = intval($serviceId);
                $price = floatval($price);
                if ($price > 0) {
                    $values[] = "($listingId, $serviceId, $price)";
                }
            }
            
            if (!empty($values)) {
                $sql = "INSERT INTO listing_service (listing_id, service_id, price) VALUES " . implode(', ', $values);
                return $conn->query($sql) ? true : false;
            }
        }
        
        return true;
    }
    
    public function mGetAllProvinces() {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT code, name, full_name FROM provinces ORDER BY name ASC";
        $result = $conn->query($sql);
        $provinces = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $provinces[] = $row;
            }
        }
        
        return $provinces;
    }
    
    public function mGetWardsByProvince($provinceCode) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $provinceCode = $conn->real_escape_string($provinceCode);
        $sql = "SELECT code, name, full_name FROM wards WHERE province_code = '$provinceCode' ORDER BY name ASC";
        $result = $conn->query($sql);
        $wards = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $wards[] = $row;
            }
        }
        
        return $wards;
    }
    
    public function mIsListingOwner($listingId, $hostId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $sql = "SELECT listing_id FROM listing WHERE listing_id = $listingId AND host_id = $hostId";
        $result = $conn->query($sql);
        
        return $result && $result->num_rows > 0;
    }
    
    public function mGetAllListings($status = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return [];
        
        $sql = "SELECT l.*, 
                       pt.name as place_type_name,
                       u.full_name as host_name,
                       u.full_name as user_name,
                       (SELECT file_url FROM listing_image WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM listing l
                LEFT JOIN place_type pt ON l.place_type_id = pt.place_type_id
                LEFT JOIN host h ON l.host_id = h.host_id
                LEFT JOIN user u ON h.user_id = u.user_id";
        
        // Build WHERE clause
        if ($status) {
            $status = $conn->real_escape_string($status);
            $sql .= " WHERE l.status = '$status'";
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            return [];
        }
        
        $listings = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $listings[] = $row;
            }
        }
        
        return $listings;
    }
    
    public function mUpdateListingStatus($listingId, $status, $rejectionReason = null) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        $status = $conn->real_escape_string($status);
        $listingId = intval($listingId);
        
        $sql = "UPDATE listing SET status = '$status'";
        
        if ($rejectionReason) {
            $rejectionReason = $conn->real_escape_string($rejectionReason);
            $sql .= ", rejection_reason = '$rejectionReason'";
        }
        
        $sql .= " WHERE listing_id = $listingId";
        
        return $conn->query($sql);
    }
    
    // Lấy danh sách services của một listing
    public function mGetListingServices($listingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $strSelect = "SELECT ls.*, s.name, s.description
                         FROM listing_service ls
                         INNER JOIN service s ON ls.service_id = s.service_id
                         WHERE ls.listing_id = $listingId
                         ORDER BY s.name ASC";
            
            $result = $conn->query($strSelect);
            return $result; // Return mysqli_result
        }else{
            return false;
        }
    }
    
    // Lấy rating trung bình và số lượng đánh giá của listing
    public function mGetListingRating($listingId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $strSelect = "SELECT 
                            AVG(rating) as avg_rating,
                            COUNT(*) as review_count
                         FROM review
                         WHERE listing_id = $listingId";
            
            $result = $conn->query($strSelect);
            if($result && $row = $result->fetch_assoc()){
                return $row;
            }
            return ['avg_rating' => 0, 'review_count' => 0];
        }else{
            return false;
        }
    }
    
    /**
     * Get top provinces by booking count
     * Returns provinces sorted by total bookings (most popular destinations)
     * 
     * @param int $limit Number of provinces to return (default: 4)
     * @return array Array of provinces with booking counts and listing counts
     */
    public function mGetTopProvincesByBookings($limit = 4) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            $limit = (int)$limit;
            
            $strSelect = "SELECT 
                            p.code as province_code,
                            p.name as province_name,
                            p.full_name as province_full_name,
                            COUNT(DISTINCT b.booking_id) as total_bookings,
                            COUNT(DISTINCT l.listing_id) as total_listings
                         FROM provinces p
                         INNER JOIN wards w ON p.code = w.province_code
                         INNER JOIN listing l ON w.code = l.ward_code
                         LEFT JOIN bookings b ON l.listing_id = b.listing_id 
                            AND b.status IN ('confirmed', 'completed')
                         WHERE l.status = 'active'
                         GROUP BY p.code, p.name, p.full_name
                         HAVING total_bookings > 0
                         ORDER BY total_bookings DESC, total_listings DESC
                         LIMIT $limit";
            
            $result = $conn->query($strSelect);
            
            if ($result && $result->num_rows > 0) {
                $provinces = [];
                while ($row = $result->fetch_assoc()) {
                    $provinces[] = $row;
                }
                return $provinces;
            }
            
            return []; // Return empty array if no results
        }
        
        return false;
    }
    
    public function mSetCoverImage($listingId, $imageId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if (!$conn) return false;
        
        // First, remove is_cover from all images of this listing
        $conn->query("UPDATE listing_image SET is_cover = 0 WHERE listing_id = $listingId");
        
        // Then set the new cover image
        $sql = "UPDATE listing_image SET is_cover = 1 
                WHERE image_id = $imageId AND listing_id = $listingId";
        
        return $conn->query($sql) ? true : false;
    }
}
?>