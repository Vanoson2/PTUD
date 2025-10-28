<?php
include_once(__DIR__ . "/../model/mType&Amenties.php"); 
class cTypeAndAmenties{
    public function cGetAllTypes(){
        $mType = new mType();
        $tbl = $mType->mGetAllTypes();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    public function cGetAllAmenities(){
        $mType = new mType();
        $tbl = $mType->mGetAllAmenities();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    public function cGetAllServices(){
        $mType = new mType();
        $tbl = $mType->mGetAllServices();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    
    // AMENITY METHODS
    public function cGetAmenityById($amenityId){
        // Validate
        if(!is_numeric($amenityId) || $amenityId <= 0){
            return false;
        }
        
        $mType = new mType();
        return $mType->mGetAmenityById($amenityId);
    }
    
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
    
    // SERVICE METHODS
    public function cGetServiceById($serviceId){
        // Validate
        if(!is_numeric($serviceId) || $serviceId <= 0){
            return false;
        }
        
        $mType = new mType();
        return $mType->mGetServiceById($serviceId);
    }
    
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