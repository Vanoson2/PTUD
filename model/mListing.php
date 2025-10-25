<?php 
include_once("mConnect.php");
class mListing{
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
    // Tìm kiếm listings theo location (tỉnh/thành phố)
    public function mSearchListingsByLocation($location){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            // Tách location thành các từ khóa (ví dụ: "Bà Nà Hills, Đà Nẵng" -> ["Bà Nà Hills", "Đà Nẵng"])
            $keywords = array_map('trim', explode(',', $location));
            
            // Xây dựng WHERE clause linh hoạt
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
            
            $strSelect = "SELECT 
                            l.listing_id,
                            l.title,
                            l.description,
                            l.price,
                            l.capacity,
                            l.address,
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
    // Tìm kiếm listings với filter: location, checkin, checkout, số khách
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
            $strSelect = "SELECT a.amenity_id
                         FROM listing_amenity la
                         INNER JOIN amenity a ON la.amenity_id = a.amenity_id
                         WHERE la.listing_id = $listingId";
            
            $result = $conn->query($strSelect);
            $amenities = [];
            if($result){
                while($row = $result->fetch_assoc()){
                    $amenities[] = $row['amenity_id'];
                }
            }
            return $amenities;
        }else{
            return [];
        }
    }
}
?>