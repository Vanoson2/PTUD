<?php 
include_once(__DIR__ . "/../mConnect.php");
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
}
?>