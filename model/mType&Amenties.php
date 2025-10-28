<?php 
include_once("mConnect.php");
class mType{
    public function mGetAllTypes(){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT * FROM place_type ORDER BY name ASC";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mGetAllAmenities(){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT * FROM amenity ORDER BY group_name ASC, name ASC";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mGetAllServices(){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT * FROM service ORDER BY name ASC";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    // AMENITY METHODS
    public function mGetAmenityById($amenityId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $amenityId = intval($amenityId);
            $strSelect = "SELECT * FROM amenity WHERE amenity_id = $amenityId";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mInsertAmenity($name, $groupName, $description){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $name = $conn->real_escape_string($name);
            $groupName = $conn->real_escape_string($groupName);
            $description = $conn->real_escape_string($description);
            
            $strInsert = "INSERT INTO amenity (name, group_name, description) 
                         VALUES ('$name', '$groupName', '$description')";
            $result = $conn->query($strInsert);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mUpdateAmenity($amenityId, $name, $groupName, $description){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $amenityId = intval($amenityId);
            $name = $conn->real_escape_string($name);
            $groupName = $conn->real_escape_string($groupName);
            $description = $conn->real_escape_string($description);
            
            $strUpdate = "UPDATE amenity 
                         SET name = '$name', group_name = '$groupName', description = '$description'
                         WHERE amenity_id = $amenityId";
            $result = $conn->query($strUpdate);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mDeleteAmenity($amenityId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $amenityId = intval($amenityId);
            // Xóa các liên kết trong listing_amenity trước
            $conn->query("DELETE FROM listing_amenity WHERE amenity_id = $amenityId");
            // Xóa amenity
            $strDelete = "DELETE FROM amenity WHERE amenity_id = $amenityId";
            $result = $conn->query($strDelete);
            return $result;
        }else{
            return false;
        }
    }
    
    // SERVICE METHODS
    public function mGetServiceById($serviceId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $serviceId = intval($serviceId);
            $strSelect = "SELECT * FROM service WHERE service_id = $serviceId";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mInsertService($name, $description){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $name = $conn->real_escape_string($name);
            $description = $conn->real_escape_string($description);
            
            $strInsert = "INSERT INTO service (name, description) 
                         VALUES ('$name', '$description')";
            $result = $conn->query($strInsert);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mUpdateService($serviceId, $name, $description){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $serviceId = intval($serviceId);
            $name = $conn->real_escape_string($name);
            $description = $conn->real_escape_string($description);
            
            $strUpdate = "UPDATE service 
                         SET name = '$name', description = '$description'
                         WHERE service_id = $serviceId";
            $result = $conn->query($strUpdate);
            return $result;
        }else{
            return false;
        }
    }
    
    public function mDeleteService($serviceId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $serviceId = intval($serviceId);
            // Xóa các liên kết trong listing_service trước
            $conn->query("DELETE FROM listing_service WHERE service_id = $serviceId");
            // Xóa service
            $strDelete = "DELETE FROM service WHERE service_id = $serviceId";
            $result = $conn->query($strDelete);
            return $result;
        }else{
            return false;
        }
    }
}
?>