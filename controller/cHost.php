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
        
        // Description không bắt buộc, chỉ validate nếu có
        if (!empty($data['description']) && strlen($data['description']) < 20) {
            $errors['description'] = 'Mô tả phải có ít nhất 20 ký tự (hoặc để trống)';
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
    
    /**
     * Update listing with validation
     * @param int $listingId Listing ID
     * @param int $hostId Host ID (for authorization)
     * @param string $title Listing title
     * @param string $description Description
     * @param string $address Address
     * @param string $wardCode Ward code
     * @param int $placeTypeId Place type ID
     * @param float $price Price per night
     * @param int $capacity Number of guests
     * @param string $status Listing status
     * @param array $amenities Array of amenity IDs
     * @return array ['success' => bool, 'message' => string]
     */
    public function cUpdateListing($listingId, $hostId, $title, $description, $address, $wardCode, $placeTypeId, $price, $capacity, $status, $amenities = []) {
        // Validate listing ID
        if (empty($listingId) || $listingId <= 0) {
            return ['success' => false, 'message' => 'ID listing không hợp lệ'];
        }
        
        // Validate title
        if (empty($title)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tiêu đề'];
        }
        $title = trim($title);
        if (strlen($title) < 10) {
            return ['success' => false, 'message' => 'Tiêu đề phải có ít nhất 10 ký tự'];
        }
        if (strlen($title) > 200) {
            return ['success' => false, 'message' => 'Tiêu đề quá dài (tối đa 200 ký tự)'];
        }
        
        // Validate description (optional)
        if (!empty($description)) {
            $description = trim($description);
            if (strlen($description) > 5000) {
                return ['success' => false, 'message' => 'Mô tả quá dài (tối đa 5000 ký tự)'];
            }
        }
        
        // Validate address
        if (empty($address)) {
            return ['success' => false, 'message' => 'Vui lòng nhập địa chỉ'];
        }
        $address = trim($address);
        if (strlen($address) > 500) {
            return ['success' => false, 'message' => 'Địa chỉ quá dài (tối đa 500 ký tự)'];
        }
        
        // Validate price
        $price = floatval($price);
        if ($price <= 0) {
            return ['success' => false, 'message' => 'Giá phải lớn hơn 0'];
        }
        if ($price > 100000000) { // 100 million VND
            return ['success' => false, 'message' => 'Giá quá cao (tối đa 100.000.000 VNĐ)'];
        }
        
        // Validate capacity
        $capacity = intval($capacity);
        if ($capacity <= 0) {
            return ['success' => false, 'message' => 'Sức chứa phải lớn hơn 0'];
        }
        if ($capacity > 50) {
            return ['success' => false, 'message' => 'Sức chứa tối đa 50 người'];
        }
        
        // Validate status
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            $status = 'active';
        }
        
        // Sanitize inputs
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
        
        // Prepare data array for Model
        $data = [
            'title' => $title,
            'description' => $description,
            'address' => $address,
            'ward_code' => $wardCode,
            'place_type_id' => $placeTypeId,
            'price' => $price,
            'capacity' => $capacity,
            'status' => $status
        ];
        
        // Call Model to update
        $mListing = new mListing();
        $result = $mListing->mUpdateListing($listingId, $data);
        
        if ($result) {
            return ['success' => true, 'message' => 'Cập nhật listing thành công!'];
        } else {
            return ['success' => false, 'message' => 'Không thể cập nhật listing. Vui lòng thử lại.'];
        }
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

    /**
     * Validate and process listing creation with images
     * @param int $hostId
     * @param array $postData POST data from form
     * @param array $filesData FILES data (images)
     * @return array ['success' => bool, 'message' => string, 'listing_id' => int|null, 'errors' => array]
     */
    public function cProcessCreateListing($hostId, $postData, $filesData) {
        // Extract and sanitize input
        $title = trim($postData['title'] ?? '');
        $description = trim($postData['description'] ?? '');
        $placeTypeId = intval($postData['place_type_id'] ?? 0);
        $address = trim($postData['address'] ?? '');
        $provinceCode = trim($postData['province_code'] ?? '');
        $wardCode = trim($postData['ward_code'] ?? '');
        $price = floatval($postData['price'] ?? 0);
        $capacity = intval($postData['capacity'] ?? 0);
        $selectedAmenities = $postData['amenities'] ?? [];
        $selectedServices = $postData['services'] ?? [];
        $status = $postData['status'] ?? 'draft';
        $coverIndex = intval($postData['cover_index'] ?? 0);
        
        // Validation
        $errors = [];
        
        // Validate host ID
        if (!is_numeric($hostId) || $hostId <= 0) {
            return [
                'success' => false,
                'message' => 'Host ID không hợp lệ',
                'listing_id' => null,
                'errors' => ['host_id' => 'Host ID không hợp lệ']
            ];
        }
        
        // Validate title
        if (empty($title)) {
            $errors[] = 'Tiêu đề phòng không được để trống';
        } elseif (strlen($title) < 10) {
            $errors[] = 'Tiêu đề phải có ít nhất 10 ký tự';
        } elseif (strlen($title) > 100) {
            $errors[] = 'Tiêu đề không được vượt quá 100 ký tự';
        }
        
        // Validate description
        if (!empty($description) && strlen($description) < 20) {
            $errors[] = 'Mô tả phải có ít nhất 20 ký tự (hoặc để trống)';
        }
        
        // Validate place type
        if (empty($placeTypeId)) {
            $errors[] = 'Vui lòng chọn loại phòng';
        }
        
        // Validate address
        if (empty($address)) {
            $errors[] = 'Địa chỉ không được để trống';
        } elseif (strlen($address) < 10) {
            $errors[] = 'Địa chỉ phải có ít nhất 10 ký tự';
        }
        
        // Validate province
        if (empty($provinceCode)) {
            $errors[] = 'Vui lòng chọn Tỉnh/Thành phố';
        }
        
        // Validate ward
        if (empty($wardCode)) {
            $errors[] = 'Vui lòng chọn Phường/Xã';
        }
        
        // Validate price
        if ($price <= 0) {
            $errors[] = 'Giá thuê phải lớn hơn 0';
        } elseif ($price < 50000) {
            $errors[] = 'Giá thuê tối thiểu là 50,000đ/đêm';
        }
        
        // Validate capacity
        if ($capacity <= 0) {
            $errors[] = 'Sức chứa phải lớn hơn 0';
        } elseif ($capacity > 50) {
            $errors[] = 'Sức chứa tối đa là 50 người';
        }
        
        // Validate images
        if (!isset($filesData['images']) || empty($filesData['images']['name'][0])) {
            $errors[] = 'Vui lòng upload ít nhất 3 ảnh cho phòng';
        } else {
            $imageCount = count(array_filter($filesData['images']['name']));
            if ($imageCount < 3) {
                $errors[] = 'Vui lòng upload ít nhất 3 ảnh cho phòng';
            } elseif ($imageCount > 5) {
                $errors[] = 'Chỉ được upload tối đa 5 ảnh';
            }
        }
        
        // Return errors if any
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin',
                'listing_id' => null,
                'errors' => $errors
            ];
        }
        
        // Sanitize data
        $title = htmlspecialchars($title);
        $description = htmlspecialchars($description);
        $address = htmlspecialchars($address);
        
        // Prepare listing data
        $listingData = [
            'title' => $title,
            'description' => $description,
            'address' => $address,
            'ward_code' => $wardCode ?: null,
            'place_type_id' => $placeTypeId ?: null,
            'price' => $price,
            'capacity' => $capacity,
            'status' => in_array($status, ['draft', 'pending']) ? $status : 'draft'
        ];
        
        // Create listing
        $mListing = new mListing();
        $listingId = $this->cCreateListing($hostId, $listingData);
        
        if (!$listingId) {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo phòng. Vui lòng thử lại.',
                'listing_id' => null,
                'errors' => []
            ];
        }
        
        // Save amenities
        if (!empty($selectedAmenities)) {
            $this->cSaveListingAmenities($listingId, $selectedAmenities);
        }
        
        // Save services
        if (!empty($selectedServices)) {
            $this->cSaveListingServices($listingId, $selectedServices);
        }
        
        // Process image uploads
        $uploadResult = $this->processImageUploads($listingId, $filesData, $coverIndex, $postData['user_id'] ?? 0);
        
        // Prepare success message
        $message = ($status === 'draft') 
            ? 'Tạo phòng thành công! Bạn có thể chỉnh sửa hoặc gửi duyệt sau.'
            : 'Tạo phòng và gửi duyệt thành công! Chúng tôi sẽ xem xét trong vòng 24-48h.';
        
        return [
            'success' => true,
            'message' => $message,
            'listing_id' => $listingId,
            'errors' => []
        ];
    }
    
    /**
     * Process image uploads for listing
     * @param int $listingId
     * @param array $filesData
     * @param int $coverIndex
     * @param int $userId
     * @return array
     */
    private function processImageUploads($listingId, $filesData, $coverIndex, $userId) {
        if (!isset($filesData['images']) || empty($filesData['images']['name'][0])) {
            return ['success' => false, 'uploaded' => 0];
        }
        
        $uploadDir = __DIR__ . '/../public/uploads/listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadedCount = 0;
        $imageCounter = 1;
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        foreach ($filesData['images']['tmp_name'] as $index => $tmpName) {
            if (empty($tmpName)) continue;
            
            $fileName = $filesData['images']['name'][$index];
            $fileSize = $filesData['images']['size'][$index];
            $fileMimeType = $filesData['images']['type'][$index];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileType, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes)) {
                continue;
            }
            
            // Validate file size
            if ($fileSize > $maxSize) {
                continue;
            }
            
            // Generate filename
            $imageNumber = str_pad($imageCounter, 2, '0', STR_PAD_LEFT);
            $newFileName = $userId . '_img' . $imageNumber . '.' . $fileType;
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $fileUrl = 'public/uploads/listings/' . $newFileName;
                $isCover = ($index === $coverIndex);
                $this->cUploadListingImage($listingId, $fileUrl, $isCover, $index);
                $uploadedCount++;
                $imageCounter++;
            }
        }
        
        return ['success' => true, 'uploaded' => $uploadedCount];
    }

    /**
     * Register user as host with full validation
     * @param int $userId User ID
     * @param string $idNumber CMND/CCCD number
     * @param string $address Full address
     * @param string $phone Phone number
     * @param string $bankAccount Bank account number
     * @param string $bankName Bank name
     * @param string $taxCode Tax code (MST)
     * @param array $filesData $_FILES data for ID card images
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function cRegisterHost($userId, $idNumber, $address, $phone, $bankAccount, $bankName, $taxCode, $filesData = []) {
        $errors = [];
        
        // Validate CMND/CCCD
        if (empty($idNumber)) {
            $errors['id_number'] = 'Vui lòng nhập số CMND/CCCD';
        } else {
            $idNumber = trim($idNumber);
            if (strlen($idNumber) < 9 || strlen($idNumber) > 12) {
                $errors['id_number'] = 'Số CMND/CCCD không hợp lệ (9-12 chữ số)';
            } elseif (!preg_match('/^[0-9]{9,12}$/', $idNumber)) {
                $errors['id_number'] = 'Số CMND/CCCD chỉ được chứa chữ số';
            }
        }
        
        // Validate address
        if (empty($address)) {
            $errors['address'] = 'Vui lòng nhập địa chỉ';
        } else {
            $address = trim($address);
            if (strlen($address) > 500) {
                $errors['address'] = 'Địa chỉ quá dài (tối đa 500 ký tự)';
            }
        }
        
        // Validate phone
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10-11 chữ số)';
        }
        
        // Validate bank account
        if (empty($bankAccount)) {
            $errors['bank_account'] = 'Vui lòng nhập số tài khoản ngân hàng';
        } else {
            $bankAccount = trim($bankAccount);
            if (strlen($bankAccount) > 50) {
                $errors['bank_account'] = 'Số tài khoản quá dài (tối đa 50 ký tự)';
            }
        }
        
        // Validate bank name
        if (empty($bankName)) {
            $errors['bank_name'] = 'Vui lòng nhập tên ngân hàng';
        } else {
            $bankName = trim($bankName);
            if (strlen($bankName) > 100) {
                $errors['bank_name'] = 'Tên ngân hàng quá dài (tối đa 100 ký tự)';
            }
        }
        
        // Validate tax code
        if (empty($taxCode)) {
            $errors['tax_code'] = 'Vui lòng nhập mã số thuế';
        } else {
            $taxCode = trim($taxCode);
            if (!preg_match('/^[0-9]{10,13}$/', $taxCode)) {
                $errors['tax_code'] = 'Mã số thuế không hợp lệ (10-13 chữ số)';
            }
        }
        
        // Check ID card images
        if (empty($filesData['id_card_front']['name']) || empty($filesData['id_card_back']['name'])) {
            $errors['id_card'] = 'Vui lòng tải lên ảnh mặt trước và mặt sau CMND/CCCD';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors
            ];
        }
        
        // Process ID card images
        $idCardImages = $this->processIdCardImages($userId, $filesData);
        if (!$idCardImages['success']) {
            return $idCardImages;
        }
        
        // Sanitize inputs
        $idNumber = htmlspecialchars($idNumber, ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
        $bankAccount = htmlspecialchars($bankAccount, ENT_QUOTES, 'UTF-8');
        $bankName = htmlspecialchars($bankName, ENT_QUOTES, 'UTF-8');
        $taxCode = htmlspecialchars($taxCode, ENT_QUOTES, 'UTF-8');
        
        // Store data for View to process (View will call Model)
        // Controller only validates, View handles Model calls
        return [
            'success' => true,
            'message' => 'Validation passed',
            'data' => [
                'id_number' => $idNumber,
                'address' => $address,
                'phone' => $phone,
                'bank_account' => $bankAccount,
                'bank_name' => $bankName,
                'tax_code' => $taxCode,
                'id_card_images' => $idCardImages
            ]
        ];
    }

    /**
     * Process ID card image uploads
     * @param int $userId User ID
     * @param array $filesData $_FILES data
     * @return array ['success' => bool, 'front' => string, 'back' => string, 'message' => string]
     */
    private function processIdCardImages($userId, $filesData) {
        $uploadDir = __DIR__ . '/../public/uploads/id_cards/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return ['success' => false, 'message' => 'Không thể tạo thư mục upload'];
            }
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $images = [];
        
        foreach (['id_card_front' => 'front', 'id_card_back' => 'back'] as $fileKey => $side) {
            $file = $filesData[$fileKey];
            
            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => "Lỗi upload ảnh " . ($side === 'front' ? 'mặt trước' : 'mặt sau')];
            }
            
            if ($file['size'] > $maxFileSize) {
                return ['success' => false, 'message' => "Ảnh " . ($side === 'front' ? 'mặt trước' : 'mặt sau') . " quá lớn (tối đa 5MB)"];
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return ['success' => false, 'message' => "Ảnh " . ($side === 'front' ? 'mặt trước' : 'mặt sau') . " không đúng định dạng (chỉ JPG, PNG)"];
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'idcard_' . $userId . '_' . $side . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFileName;
            
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                return ['success' => false, 'message' => "Không thể lưu ảnh " . ($side === 'front' ? 'mặt trước' : 'mặt sau')];
            }
            
            $images[$side] = '/public/uploads/id_cards/' . $newFileName;
        }
        
        return ['success' => true, 'front' => $images['front'], 'back' => $images['back']];
    }
}

// API endpoint handler
if (isset($_GET['action'])) {
    $cHost = new cHost();
    
    if ($_GET['action'] === 'getWardsByProvince' && isset($_GET['province_code'])) {
        $provinceCode = $_GET['province_code'];
        $wards = $cHost->cGetWardsByProvince($provinceCode);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'wards' => $wards
        ]);
        exit();
    }
}
?>
