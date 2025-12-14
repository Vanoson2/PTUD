<?php
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cHost.php';
require_once __DIR__ . '/../../../model/mListing.php';

// Yêu cầu đăng nhập và là host
requireLogin();
requireHost();

$userId = getCurrentUserId();
$cHost = new cHost();
$mListing = new mListing();

// Lấy host_id
$hostInfo = $cHost->cGetHostByUserId($userId);
if (!$hostInfo) {
    header('Location: /view/user/host/become-host.php');
    exit;
}
$hostId = $hostInfo['host_id'];

// Lấy listing_id từ URL
$listingId = intval($_GET['id'] ?? 0);
if (!$listingId) {
    header('Location: /view/user/host/my-listings.php');
    exit;
}

// Lấy thông tin listing và verify ownership
$listing = $mListing->mGetListingById($listingId);
if (!$listing || $listing['host_id'] != $hostId) {
    header('Location: /view/user/host/my-listings.php');
    exit;
}

// Lấy dữ liệu cho form
$placeTypes = $cHost->cGetAllPlaceTypes();
$amenities = $cHost->cGetAllAmenities();
$services = $cHost->cGetAllServices();
$provinces = $cHost->cGetAllProvinces();

// Lấy ảnh hiện tại
$existingImages = $mListing->mGetListingImages($listingId);

// Lấy amenities hiện tại
$currentAmenities = $mListing->mGetListingAmenities($listingId);
$currentAmenityIds = [];
if (is_array($currentAmenities)) {
    foreach ($currentAmenities as $amenity) {
        $currentAmenityIds[] = $amenity['amenity_id'];
    }
}

// Lấy services hiện tại
$currentServices = $mListing->mGetListingServices($listingId);
$currentServicePrices = [];
if ($currentServices && $currentServices->num_rows > 0) {
    while ($service = $currentServices->fetch_assoc()) {
        $currentServicePrices[$service['service_id']] = $service['price'];
    }
}

// Lấy ward_code từ listing
$currentWardCode = $listing['ward_code'] ?? '';

// Lấy province_code từ ward_code
$currentProvinceCode = '';
if (!empty($currentWardCode)) {
    $wardInfo = $mListing->mGetWardInfo($currentWardCode);
    if ($wardInfo) {
        $currentProvinceCode = $wardInfo['province_code'];
    }
}

$successMessage = '';
$errorMessage = '';

// Xử lý form submit - CONTROLLER HANDLES ALL VALIDATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $placeTypeId = intval($_POST['place_type_id'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $wardCode = trim($_POST['ward_code'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $capacity = intval($_POST['capacity'] ?? 0);
    $selectedAmenities = $_POST['amenities'] ?? [];
    $status = $_POST['status'] ?? $listing['status'];
    
    // Call Controller with validation
    $result = $cHost->cUpdateListing(
        $listingId, 
        $hostId, 
        $title, 
        $description, 
        $address, 
        $wardCode, 
        $placeTypeId, 
        $price, 
        $capacity, 
        $status, 
        $selectedAmenities
    );
    
    if ($result['success']) {
      // Cập nhật amenities
      if (!empty($selectedAmenities)) {
        $cHost->cSaveListingAmenities($listingId, $selectedAmenities);
      } else {
        // If no amenities selected, clear all
        $cHost->cSaveListingAmenities($listingId, []);
      }
      
        // Cập nhật services
        if (isset($_POST['services'])) {
            $cHost->cSaveListingServices($listingId, $_POST['services']);
        }
        
        // Xử lý upload ảnh mới (nếu có)
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadDir = __DIR__ . '/../../../public/uploads/listings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $coverIndex = intval($_POST['cover_index'] ?? 0);
            $imageCounter = count($existingImages) + 1;
            
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                if (empty($tmpName)) continue;
                
                $fileName = $_FILES['images']['name'][$index];
                $fileSize = $_FILES['images']['size'][$index];
                $fileMimeType = $_FILES['images']['type'][$index];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file type
                $allowedTypes = ['jpg', 'jpeg', 'png'];
                $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                
                if (!in_array($fileType, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes)) {
                    continue;
                }
                
                // Validate file size (tối đa 5MB)
                $maxSize = 5 * 1024 * 1024;
                if ($fileSize > $maxSize) {
                    continue;
                }
                
                // Generate filename
                $imageNumber = str_pad($imageCounter, 2, '0', STR_PAD_LEFT);
                $newFileName = $userId . '_img' . $imageNumber . '_' . time() . '.' . $fileType;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $fileUrl = 'public/uploads/listings/' . $newFileName;
                    $isCover = ($index === $coverIndex) && count($existingImages) === 0;
                    $displayOrder = count($existingImages) + $index;
                    $cHost->cUploadListingImage($listingId, $fileUrl, $isCover, $displayOrder);
                    $imageCounter++;
                }
            }
        }
        
        // Xử lý xóa ảnh cũ (nếu có)
        if (isset($_POST['delete_images'])) {
            $deleteImages = $_POST['delete_images'];
            foreach ($deleteImages as $imageId) {
                $mListing->mDeleteListingImage(intval($imageId), $listingId);
            }
        }
        
        // Xử lý set cover image mới
        if (isset($_POST['new_cover_image'])) {
            $newCoverId = intval($_POST['new_cover_image']);
            $mListing->mSetCoverImage($listingId, $newCoverId);
        }
        
        $successMessage = $result['message'];
        
        // Reload listing data
        $listing = $mListing->mGetListingById($listingId);
        $existingImages = $mListing->mGetListingImages($listingId);
        
        // Redirect sau 1.5 giây
        echo "<script>
            setTimeout(function() {
                window.location.href = './listing-detail.php?id=" . $listingId . "';
            }, 1500);
        </script>";
    } else {
        $errorMessage = $result['message'];
    }
}

// Group amenities by group_name
$amenitiesByGroup = [];
foreach ($amenities as $amenity) {
    $group = $amenity['group_name'] ?: 'Khác';
    $amenitiesByGroup[$group][] = $amenity;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh sửa phòng - <?php echo htmlspecialchars($listing['title']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/host-create-listing.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../../css/host-edit-listing.css">
</head>
<body>
  <div class="container">
    <div class="form-container">
      <a href="./listing-detail.php?id=<?php echo $listingId; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại chi tiết phòng
      </a>
      
      <div class="form-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa phòng</h1>
        <p>Cập nhật thông tin cho phòng: <strong><?php echo htmlspecialchars($listing['title']); ?></strong></p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <strong><i class="fas fa-check-circle"></i> Thành công!</strong> <?php echo htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <strong><i class="fas fa-times-circle"></i> Lỗi!</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" id="listingForm">
        
        <!-- Ảnh hiện tại -->
        <?php if (!empty($existingImages)): ?>
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-camera"></i> Ảnh hiện tại</h3>
          <div class="existing-images">
            <?php foreach ($existingImages as $image): ?>
              <div class="existing-image-item <?php echo $image['is_cover'] ? 'is-cover' : ''; ?>" id="image-<?php echo $image['image_id']; ?>">
                <img src="../../../<?php echo htmlspecialchars($image['file_url']); ?>" alt="Listing image">
                <?php if ($image['is_cover']): ?>
                  <span class="cover-badge"><i class="fas fa-star"></i> Ảnh bìa</span>
                <?php endif; ?>
                <div class="image-actions">
                  <?php if (!$image['is_cover']): ?>
                    <button type="button" class="btn-set-cover" onclick="setCoverImage(<?php echo $image['image_id']; ?>)">
                      <i class="fas fa-star"></i>
                    </button>
                  <?php endif; ?>
                  <button type="button" class="btn-delete-image" onclick="deleteImage(<?php echo $image['image_id']; ?>)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="delete_images[]" id="deleteImagesInput" value="">
          <input type="hidden" name="new_cover_image" id="newCoverImageInput" value="">
        </div>
        <?php endif; ?>
        
        <!-- Thông tin cơ bản -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-edit"></i> Thông tin cơ bản</h3>
          
          <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề <span class="required">*</span></label>
            <input type="text" class="form-control" id="title" name="title" 
                   value="<?php echo htmlspecialchars($listing['title']); ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="5" 
                      placeholder="Mô tả chi tiết về chỗ ở của bạn..."><?php echo htmlspecialchars($listing['description'] ?? ''); ?></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="place_type_id" class="form-label">Loại chỗ ở</label>
              <select class="form-select" id="place_type_id" name="place_type_id">
                <option value="">-- Chọn loại chỗ ở --</option>
                <?php foreach ($placeTypes as $type): ?>
                  <option value="<?php echo $type['place_type_id']; ?>" 
                          <?php echo ($listing['place_type_id'] == $type['place_type_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="capacity" class="form-label">Sức chứa <span class="required">*</span></label>
              <input type="number" class="form-control" id="capacity" name="capacity" min="1" 
                     value="<?php echo $listing['capacity']; ?>" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="price" class="form-label">Giá mỗi đêm (VNĐ) <span class="required">*</span></label>
            <input type="number" class="form-control" id="price" name="price" min="0" step="1000" 
                   value="<?php echo $listing['price']; ?>" required>
          </div>
        </div>
        
        <!-- Địa chỉ -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ</h3>
          
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ cụ thể <span class="required">*</span></label>
            <input type="text" class="form-control" id="address" name="address" 
                   value="<?php echo htmlspecialchars($listing['address']); ?>" 
                   placeholder="VD: 123 Đường ABC, Phường XYZ" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="province_code" class="form-label">Tỉnh/Thành phố</label>
              <select class="form-select" id="province_code" name="province_code" onchange="loadWards(this.value)">
                <option value="">-- Chọn tỉnh/thành phố --</option>
                <?php foreach ($provinces as $province): ?>
                  <option value="<?php echo $province['code']; ?>" 
                          data-province-code="<?php echo $province['code']; ?>"
                          <?php echo ($currentProvinceCode === $province['code']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($province['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="ward_code" class="form-label">Phường/Xã</label>
              <select class="form-select" id="ward_code" name="ward_code">
                <option value="">-- Chọn phường/xã --</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Tiện nghi -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-sparkles"></i> Tiện nghi</h3>
          <?php foreach ($amenitiesByGroup as $groupName => $groupAmenities): ?>
            <div class="amenity-group">
              <h5><?php echo htmlspecialchars($groupName); ?></h5>
              <div class="amenity-list">
                <?php foreach ($groupAmenities as $amenity): ?>
                  <label class="amenity-item">
                    <input type="checkbox" name="amenities[]" value="<?php echo $amenity['amenity_id']; ?>"
                           <?php echo in_array($amenity['amenity_id'], $currentAmenityIds) ? 'checked' : ''; ?>>
                    <span><?php echo htmlspecialchars($amenity['name']); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Dịch vụ thêm -->
        <div class="form-section">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h3 class="section-title mb-1"><i class="fas fa-concierge-bell"></i> Dịch vụ thêm (tùy chọn)</h3>
              <p class="text-muted mb-0">Cập nhật các dịch vụ phụ phí mà khách có thể sử dụng</p>
            </div>
            <a href="suggest-service.php" class="btn btn-outline-primary btn-sm" target="_blank">
              <i class="fas fa-lightbulb"></i> Đề xuất dịch vụ mới
            </a>
          </div>
          
          <?php if (!empty($services)): ?>
            <div class="services-list">
              <?php foreach ($services as $service): ?>
                <?php 
                $isSelected = isset($currentServicePrices[$service['service_id']]);
                $currentPrice = $currentServicePrices[$service['service_id']] ?? '';
                ?>
                <div class="service-item-input">
                  <div class="service-checkbox">
                    <input type="checkbox" class="form-check-input service-toggle" 
                           id="service_<?php echo $service['service_id']; ?>"
                           data-service-id="<?php echo $service['service_id']; ?>"
                           <?php echo $isSelected ? 'checked' : ''; ?>>
                    <label class="service-label" for="service_<?php echo $service['service_id']; ?>">
                      <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                      <?php if ($service['description']): ?>
                        <small class="text-muted d-block"><?php echo htmlspecialchars($service['description']); ?></small>
                      <?php endif; ?>
                    </label>
                  </div>
                  <div class="service-price-input">
                    <div class="input-group">
                      <input type="number" class="form-control service-price" 
                             name="services[<?php echo $service['service_id']; ?>]" 
                             id="price_<?php echo $service['service_id']; ?>"
                             placeholder="Giá (VNĐ)" min="0" step="1000"
                             value="<?php echo $currentPrice; ?>"
                             <?php echo !$isSelected ? 'disabled' : ''; ?>>
                      <span class="input-group-text">đ</span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted">Không có dịch vụ nào.</p>
          <?php endif; ?>
        </div>
        
        <!-- Upload ảnh mới -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-images"></i> Thêm ảnh mới (tùy chọn)</h3>
          <p class="text-muted">Bạn có thể thêm thêm ảnh cho phòng. Chỉ chấp nhận file JPG, PNG. Tối đa 5MB/ảnh.</p>
          
          <div class="mb-3">
            <label for="images" class="form-label">Chọn ảnh</label>
            <input type="file" class="form-control" id="images" name="images[]" 
                   accept="image/png,image/jpeg,image/jpg" multiple>
            <div class="form-text">Bạn có thể chọn nhiều ảnh cùng lúc (tối đa 5 ảnh)</div>
          </div>
          
          <div id="imagePreview" class="row g-3"></div>
        </div>
        
        <!-- Trạng thái -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-clipboard-check"></i> Trạng thái</h3>
          <div class="mb-3">
            <label class="form-label">Chọn trạng thái</label>
            <div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="status" id="statusDraft" value="draft"
                       <?php echo ($listing['status'] === 'draft') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="statusDraft">
                  <i class="fas fa-file-alt"></i> Lưu nháp (chỉnh sửa sau)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending"
                       <?php echo ($listing['status'] === 'pending') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="statusPending">
                  <i class="fas fa-clock"></i> Gửi duyệt (chờ admin phê duyệt)
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Buttons -->
        <div class="form-actions">
          <a href="./listing-detail.php?id=<?php echo $listingId; ?>" class="btn btn-secondary">Hủy</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Cập nhật phòng
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Load wards when page loads (if province and ward exist)
    document.addEventListener('DOMContentLoaded', function() {
      const currentProvinceCode = '<?php echo $currentProvinceCode; ?>';
      const currentWardCode = '<?php echo $currentWardCode; ?>';
      
      if (currentProvinceCode) {
        loadWards(currentProvinceCode, currentWardCode);
      }
    });
    
    function loadWards(provinceCode, selectedWardCode = '') {
      const wardSelect = document.getElementById('ward_code');
      wardSelect.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
      
      if (!provinceCode) return;
      
      fetch(`../../../controller/cHost.php?action=getWardsByProvince&province_code=${provinceCode}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.wards) {
            data.wards.forEach(ward => {
              const option = document.createElement('option');
              option.value = ward.code;
              option.textContent = ward.name;
              if (selectedWardCode && ward.code === selectedWardCode) {
                option.selected = true;
              }
              wardSelect.appendChild(option);
            });
          }
        });
    }
    
    // Image preview for new uploads
    document.getElementById('images').addEventListener('change', function(e) {
      const preview = document.getElementById('imagePreview');
      preview.innerHTML = '';
      
      const files = Array.from(e.target.files);
      files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const col = document.createElement('div');
          col.className = 'col-md-3';
          col.innerHTML = `
            <div class="card">
              <img src="${e.target.result}" class="card-img-top" alt="Preview">
              <div class="card-body p-2">
                <small class="text-muted">Ảnh mới ${index + 1}</small>
              </div>
            </div>
          `;
          preview.appendChild(col);
        };
        reader.readAsDataURL(file);
      });
    });
    
    // Delete image functions
    const imagesToDelete = [];
    
    function deleteImage(imageId) {
      if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;
      
      imagesToDelete.push(imageId);
      document.getElementById('image-' + imageId).remove();
      
      // Update hidden input
      const deleteInput = document.getElementById('deleteImagesInput');
      const container = deleteInput.parentElement;
      deleteInput.remove();
      
      imagesToDelete.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_images[]';
        input.value = id;
        container.appendChild(input);
      });
    }
    
    function setCoverImage(imageId) {
      // Remove all is-cover classes and badges
      document.querySelectorAll('.existing-image-item').forEach(item => {
        item.classList.remove('is-cover');
        const badge = item.querySelector('.cover-badge');
        if (badge) badge.remove();
        const setCoverBtn = item.querySelector('.btn-set-cover');
        if (setCoverBtn) {
          setCoverBtn.style.display = 'block';
        }
      });
      
      // Add is-cover to selected image
      const selectedImage = document.getElementById('image-' + imageId);
      selectedImage.classList.add('is-cover');
      
      // Add cover badge
      const badge = document.createElement('span');
      badge.className = 'cover-badge';
      badge.innerHTML = '<i class="fas fa-star"></i> Ảnh bìa';
      selectedImage.insertBefore(badge, selectedImage.firstChild);
      
      // Hide set cover button for this image
      const setCoverBtn = selectedImage.querySelector('.btn-set-cover');
      if (setCoverBtn) {
        setCoverBtn.style.display = 'none';
      }
      
      // Update hidden input
      document.getElementById('newCoverImageInput').value = imageId;
    }
    
    // Service toggle functionality
    document.querySelectorAll('.service-toggle').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const serviceId = this.getAttribute('data-service-id');
        const priceInput = document.getElementById('price_' + serviceId);
        
        if (this.checked) {
          priceInput.disabled = false;
          priceInput.focus();
        } else {
          priceInput.disabled = true;
          priceInput.value = '';
        }
      });
    });
  </script>
</body>
</html>
