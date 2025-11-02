<?php
include_once(__DIR__ . "/../model/mHost.php");
include_once(__DIR__ . "/../model/mListing.php");

class cHost {
    
    public function cIsUserHost($userId) {
        $mHost = new mHost();
        return $mHost->mIsUserHost($userId);
    }
    
    public function cGetHostByUserId($userId) {
        $mHost = new mHost();
        return $mHost->mGetHostByUserId($userId);
    }
    
    public function cGetHostListings($hostId, $status = null) {
        $mListing = new mListing();
        return $mListing->mGetHostListings($hostId, $status);
    }
    
    public function cDeleteListing($listingId, $hostId) {
        $mListing = new mListing();
        return $mListing->mDeleteListing($listingId, $hostId);
    }
    
    public function cCreateHostApplication($userId, $businessName, $taxCode = '') {
        // Validate input
        $errors = [];
        
        if (empty($businessName)) {
            $errors['business_name'] = 'Vui lòng nhập tên doanh nghiệp';
        } elseif (strlen($businessName) > 255) {
            $errors['business_name'] = 'Tên doanh nghiệp quá dài (tối đa 255 ký tự)';
        }
        
        if (!empty($taxCode) && strlen($taxCode) > 50) {
            $errors['tax_code'] = 'Mã số thuế quá dài (tối đa 50 ký tự)';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors,
                'application_id' => null
            ];
        }
        
        $mHost = new mHost();
        return $mHost->mCreateHostApplication($userId, $businessName, $taxCode);
    }
    
    public function cGetUserHostApplication($userId) {
        $mHost = new mHost();
        return $mHost->mGetUserHostApplication($userId);
    }
    
    public function cSaveHostDocument($applicationId, $docType, $fileUrl, $mimeType, $fileSizeBytes) {
        $mHost = new mHost();
        return $mHost->mSaveHostDocument($applicationId, $docType, $fileUrl, $mimeType, $fileSizeBytes);
    }
    
    public function cGetHostStatistics($userId) {
        $mHost = new mHost();
        return $mHost->mGetHostStatistics($userId);
    }
    
    public function cCreateListing($hostId, $data) {
        // Validate dữ liệu
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Vui lòng nhập tiêu đề';
        }
        
        if (empty($data['description'])) {
            $errors['description'] = 'Vui lòng nhập mô tả';
        }
        
        // Check both 'price' and 'price_per_night' for compatibility
        $price = $data['price'] ?? $data['price_per_night'] ?? 0;
        if (empty($price) || $price <= 0) {
            $errors['price'] = 'Vui lòng nhập giá hợp lệ';
        }
        
        if (empty($data['capacity']) || $data['capacity'] <= 0) {
            $errors['capacity'] = 'Vui lòng nhập sức chứa hợp lệ';
        }
        
        if (!empty($errors)) {
            return false;
        }
        
        $mListing = new mListing();
        return $mListing->mCreateListing($hostId, $data);
    }
    
    public function cUpdateListing($listingId, $data) {
        $mListing = new mListing();
        return $mListing->mUpdateListing($listingId, $data);
    }
    
    public function cGetListingById($listingId) {
        $mListing = new mListing();
        return $mListing->mGetListingById($listingId);
    }
    
    public function cIsListingOwner($listingId, $hostId) {
        $mListing = new mListing();
        return $mListing->mIsListingOwner($listingId, $hostId);
    }
    
    public function cUploadListingImage($listingId, $fileUrl, $isCover = false, $sortOrder = 0) {
        $mListing = new mListing();
        return $mListing->mUploadListingImage($listingId, $fileUrl, $isCover, $sortOrder);
    }
    
    public function cDeleteListingImage($imageId, $listingId) {
        $mListing = new mListing();
        return $mListing->mDeleteListingImage($imageId, $listingId);
    }
    
    public function cGetAllPlaceTypes() {
        $mListing = new mListing();
        return $mListing->mGetAllPlaceTypes();
    }
    
    public function cGetAllAmenities() {
        $mListing = new mListing();
        return $mListing->mGetAllAmenities();
    }
    
    public function cGetAllServices() {
        $mListing = new mListing();
        return $mListing->mGetAllServices();
    }
    
    public function cSaveListingAmenities($listingId, $amenityIds) {
        $mListing = new mListing();
        return $mListing->mSaveListingAmenities($listingId, $amenityIds);
    }
    
    public function cSaveListingServices($listingId, $services) {
        $mListing = new mListing();
        return $mListing->mSaveListingServices($listingId, $services);
    }
    
    public function cGetAllProvinces() {
        $mListing = new mListing();
        return $mListing->mGetAllProvinces();
    }
    
    public function cGetWardsByProvince($provinceCode) {
        $mListing = new mListing();
        return $mListing->mGetWardsByProvince($provinceCode);
    }
    
    public function cGetListingImages($listingId) {
        $mListing = new mListing();
        return $mListing->mGetListingImages($listingId);
    }
    
    public function cCheckTaxCodeExists($taxCode) {
        $mHost = new mHost();
        return $mHost->mCheckTaxCodeExists($taxCode);
    }
    
    public function cToggleListingStatus($listingId, $hostId) {
        $mListing = new mListing();
        return $mListing->mToggleListingStatus($listingId, $hostId);
    }
}
?>
