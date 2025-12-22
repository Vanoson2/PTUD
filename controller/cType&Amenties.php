<?php
include_once(__DIR__ . "/../model/mType&Amenties.php"); 
class cTypeAndAmenties{
    // Lấy tất cả loại nơi ở (place types)
    // return mysqli_result|false - Danh sách types
    public function cGetAllTypes(){
        $mType = new mType();
        $tbl = $mType->mGetAllTypes();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    // Lấy tất cả tiện nghi (amenities)
    // return mysqli_result|false - Danh sách amenities
    public function cGetAllAmenities(){
        $mType = new mType();
        $tbl = $mType->mGetAllAmenities();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    // Lấy tất cả dịch vụ (services)
    // return mysqli_result|false - Danh sách services
    public function cGetAllServices(){
        $mType = new mType();
        $tbl = $mType->mGetAllServices();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    // ===== AMENITY FUNCTIONS =====
    
    // Lấy thông tin amenity theo ID
    // int $amenityId - ID amenity
    // return mysqli_result|false - Dữ liệu amenity
    public function cGetAmenityById($amenityId){
        // Validate
        if(!is_numeric($amenityId) || $amenityId <= 0){
            return false;
        }
        
        $mType = new mType();
        return $mType->mGetAmenityById($amenityId);
    }
    
    // Thêm amenity mới
    // Validate tên (max 120), group name (max 120)
    // string $name - Tên amenity (bắt buộc)
    // string $groupName - Tên nhóm
    // string $description - Mô tả
    // return array - ['success' => bool, 'message' => string]
    public function cInsertAmenity($name, $groupName, $description){
        // Validate input
        $errors = [];
        
        if(empty(trim($name))){
            $errors[] = "Tên tiện nghi không được để trống";
        }
        
        if(strlen($name) > 120){
            $errors[] = "Tên tiện nghi không được quá 120 ký tự";
        }
        
        if(!empty($groupName) && strlen($groupName) > 120){
            $errors[] = "Tên nhóm không được quá 120 ký tự";
        }
        
        if(!empty($description) && strlen($description) > 500){
            $errors[] = "Mô tả không được quá 500 ký tự";
        }
        
        if(!empty($errors)){
            return ['success' => false, 'errors' => $errors];
        }
        
        $mType = new mType();
        $result = $mType->mInsertAmenity($name, $groupName, $description);
        
        if($result){
            return ['success' => true, 'message' => 'Thêm tiện nghi thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi thêm tiện nghi']];
        }
    }
    
    // Cập nhật amenity
    // Validate ID, tên (max 120), group name (max 120), description (max 500)
    // int $amenityId - ID amenity
    // string $name - Tên amenity (bắt buộc)
    // string $groupName - Tên nhóm
    // string $description - Mô tả
    // return array - ['success' => bool, 'message' => string, 'errors' => array]
    public function cUpdateAmenity($amenityId, $name, $groupName, $description){
        // Validate
        if(!is_numeric($amenityId) || $amenityId <= 0){
            return ['success' => false, 'errors' => ['ID tiện nghi không hợp lệ']];
        }
        
        $errors = [];
        
        if(empty(trim($name))){
            $errors[] = "Tên tiện nghi không được để trống";
        }
        
        if(strlen($name) > 120){
            $errors[] = "Tên tiện nghi không được quá 120 ký tự";
        }
        
        if(!empty($groupName) && strlen($groupName) > 120){
            $errors[] = "Tên nhóm không được quá 120 ký tự";
        }
        
        if(!empty($description) && strlen($description) > 500){
            $errors[] = "Mô tả không được quá 500 ký tự";
        }
        
        if(!empty($errors)){
            return ['success' => false, 'errors' => $errors];
        }
        
        $mType = new mType();
        $result = $mType->mUpdateAmenity($amenityId, $name, $groupName, $description);
        
        if($result){
            return ['success' => true, 'message' => 'Cập nhật tiện nghi thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi cập nhật tiện nghi']];
        }
    }
    
    // Xóa amenity
    // int $amenityId - ID amenity
    // return array - ['success' => bool, 'message' => string, 'errors' => array]
    public function cDeleteAmenity($amenityId){
        // Validate
        if(!is_numeric($amenityId) || $amenityId <= 0){
            return ['success' => false, 'errors' => ['ID tiện nghi không hợp lệ']];
        }
        
        $mType = new mType();
        $result = $mType->mDeleteAmenity($amenityId);
        
        if($result){
            return ['success' => true, 'message' => 'Xóa tiện nghi thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi xóa tiện nghi']];
        }
    }
    
    // ===== SERVICE FUNCTIONS =====
    
    // Lấy thông tin service theo ID
    // int $serviceId - ID service
    // return mysqli_result|false - Dữ liệu service
    public function cGetServiceById($serviceId){
        // Validate
        if(!is_numeric($serviceId) || $serviceId <= 0){
            return false;
        }
        
        $mType = new mType();
        return $mType->mGetServiceById($serviceId);
    }
    
    // Thêm service mới
    // Validate tên (max 120), description (max 500)
    // string $name - Tên service (bắt buộc)
    // string $description - Mô tả
    // return array - ['success' => bool, 'message' => string, 'errors' => array]
    public function cInsertService($name, $description){
        // Validate input
        $errors = [];
        
        if(empty(trim($name))){
            $errors[] = "Tên dịch vụ không được để trống";
        }
        
        if(strlen($name) > 120){
            $errors[] = "Tên dịch vụ không được quá 120 ký tự";
        }
        
        if(!empty($description) && strlen($description) > 500){
            $errors[] = "Mô tả không được quá 500 ký tự";
        }
        
        if(!empty($errors)){
            return ['success' => false, 'errors' => $errors];
        }
        
        $mType = new mType();
        $result = $mType->mInsertService($name, $description);
        
        if($result){
            return ['success' => true, 'message' => 'Thêm dịch vụ thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi thêm dịch vụ']];
        }
    }
    
    // Cập nhật service
    // Validate ID, tên (max 120), description (max 500)
    // int $serviceId - ID service
    // string $name - Tên service (bắt buộc)
    // string $description - Mô tả
    // return array - ['success' => bool, 'message' => string, 'errors' => array]
    public function cUpdateService($serviceId, $name, $description){
        // Validate
        if(!is_numeric($serviceId) || $serviceId <= 0){
            return ['success' => false, 'errors' => ['ID dịch vụ không hợp lệ']];
        }
        
        $errors = [];
        
        if(empty(trim($name))){
            $errors[] = "Tên dịch vụ không được để trống";
        }
        
        if(strlen($name) > 120){
            $errors[] = "Tên dịch vụ không được quá 120 ký tự";
        }
        
        if(!empty($description) && strlen($description) > 500){
            $errors[] = "Mô tả không được quá 500 ký tự";
        }
        
        if(!empty($errors)){
            return ['success' => false, 'errors' => $errors];
        }
        
        $mType = new mType();
        $result = $mType->mUpdateService($serviceId, $name, $description);
        
        if($result){
            return ['success' => true, 'message' => 'Cập nhật dịch vụ thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi cập nhật dịch vụ']];
        }
    }
    
    // Xóa service
    // int $serviceId - ID service
    // return array - ['success' => bool, 'message' => string, 'errors' => array]
    public function cDeleteService($serviceId){
        // Validate
        if(!is_numeric($serviceId) || $serviceId <= 0){
            return ['success' => false, 'errors' => ['ID dịch vụ không hợp lệ']];
        }
        
        $mType = new mType();
        $result = $mType->mDeleteService($serviceId);
        
        if($result){
            return ['success' => true, 'message' => 'Xóa dịch vụ thành công'];
        }else{
            return ['success' => false, 'errors' => ['Có lỗi xảy ra khi xóa dịch vụ']];
        }
    }
}
?>