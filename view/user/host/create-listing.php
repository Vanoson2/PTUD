<?php
session_start();
include_once __DIR__ . '/../../../controller/cHost.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

$userId = $_SESSION['user_id'];
$cHost = new cHost();

// Kiểm tra user có phải là host không
if (!$cHost->cIsUserHost($userId)) {
  header('Location: ../host/become-host.php');
  exit;
}

// Lấy host_id
$hostInfo = $cHost->cGetHostByUserId($userId);
if (!$hostInfo) {
  header('Location: ./become-host.php');
  exit;
}
$hostId = $hostInfo['host_id'];

// Lấy dữ liệu cho form
$placeTypes = $cHost->cGetAllPlaceTypes();
$amenities = $cHost->cGetAllAmenities();
$provinces = $cHost->cGetAllProvinces();

$successMessage = '';
$errorMessage = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate inputs
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $placeTypeId = intval($_POST['place_type_id'] ?? 0);
  $address = trim($_POST['address'] ?? '');
  $provinceCode = trim($_POST['province_code'] ?? '');
  $wardCode = trim($_POST['ward_code'] ?? '');
  $price = floatval($_POST['price'] ?? 0);
  $capacity = intval($_POST['capacity'] ?? 0);
  $selectedAmenities = $_POST['amenities'] ?? [];
  $status = $_POST['status'] ?? 'draft'; // draft hoặc pending
  
  // Validation
  if (empty($title) || empty($address) || $price <= 0 || $capacity <= 0) {
    $errorMessage = 'Vui lòng điền đầy đủ thông tin bắt buộc (Tiêu đề, Địa chỉ, Giá, Sức chứa)';
  } else {
    // Tạo listing
    $listingData = [
      'title' => $title,
      'description' => $description,
      'address' => $address,
      'ward_code' => $wardCode ?: null,
      'place_type_id' => $placeTypeId ?: null,
      'price' => $price,
      'capacity' => $capacity,
      'status' => $status
    ];
    
    $listingId = $cHost->cCreateListing($hostId, $listingData);
    
    if ($listingId) {
      // Lưu amenities
      if (!empty($selectedAmenities)) {
        $cHost->cSaveListingAmenities($listingId, $selectedAmenities);
      }
      
      // Xử lý upload ảnh
      if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../../../public/uploads/listings/';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0755, true);
        }
        
        $coverIndex = intval($_POST['cover_index'] ?? 0);
        $uploadedCount = 0;
        
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
          if (empty($tmpName)) continue;
          
          $fileName = $_FILES['images']['name'][$index];
          $fileSize = $_FILES['images']['size'][$index];
          $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
          
          // Validate
          $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
          if (!in_array($fileType, $allowedTypes)) {
            continue;
          }
          
          if ($fileSize > 10 * 1024 * 1024) { // Max 10MB
            continue;
          }
          
          // Generate unique filename
          $newFileName = 'listing_' . $listingId . '_' . time() . '_' . $index . '.' . $fileType;
          $targetPath = $uploadDir . $newFileName;
          
          if (move_uploaded_file($tmpName, $targetPath)) {
            $fileUrl = 'public/uploads/listings/' . $newFileName;
            $isCover = ($index === $coverIndex);
            $cHost->cUploadListingImage($listingId, $fileUrl, $isCover, $index);
            $uploadedCount++;
          }
        }
      }
      
      if ($status === 'draft') {
        $successMessage = 'Tạo phòng thành công! Bạn có thể chỉnh sửa hoặc gửi duyệt sau.';
      } else {
        $successMessage = 'Tạo phòng và gửi duyệt thành công! Chúng tôi sẽ xem xét trong vòng 24-48h.';
      }
      
      // Redirect sau 2 giây
      echo "<script>
        setTimeout(function() {
          window.location.href = './my-listings.php';
        }, 2000);
      </script>";
    } else {
      $errorMessage = 'Có lỗi xảy ra khi tạo phòng. Vui lòng thử lại.';
    }
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
  <title>Đăng phòng mới - WeGo Host</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/create-listing.css?v=<?php echo time(); ?>">
</head>
<body>
</head>
<body>
  <div class="container">
    <div class="form-container">
      <a href="./my-listings.php" class="back-link">← Quay lại danh sách phòng</a>
      
      <div class="form-header">
        <h1>🏡 Đăng phòng mới</h1>
        <p>Chia sẻ không gian của bạn với du khách trên WeGo</p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <strong>✅ Thành công!</strong> <?php echo htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <strong>❌ Lỗi!</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" id="listingForm">
        <!-- Thông tin cơ bản -->
        <div class="form-section">
          <h3 class="section-title">📝 Thông tin cơ bản</h3>
          
          <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề <span class="required">*</span></label>
            <input type="text" class="form-control" id="title" name="title" 
                   placeholder="VD: Căn hộ 2 phòng ngủ view biển tại Đà Nẵng" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="5"
                      placeholder="Mô tả chi tiết về chỗ ở của bạn..."></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="place_type_id" class="form-label">Loại chỗ ở</label>
              <select class="form-select" id="place_type_id" name="place_type_id">
                <option value="">-- Chọn loại --</option>
                <?php foreach ($placeTypes as $pt): ?>
                  <option value="<?php echo $pt['place_type_id']; ?>">
                    <?php echo htmlspecialchars($pt['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-4 mb-3">
              <label for="capacity" class="form-label">Sức chứa (người) <span class="required">*</span></label>
              <input type="number" class="form-control" id="capacity" name="capacity" 
                     min="1" max="10" placeholder="2" required>
            </div>
            
            <div class="col-md-4 mb-3">
              <label for="price" class="form-label">Giá mỗi đêm (VND) <span class="required">*</span></label>
              <input type="number" class="form-control" id="price" name="price" 
                     min="0" step="1000" placeholder="500000" required>
            </div>
          </div>
        </div>
        
        <!-- Địa chỉ -->
        <div class="form-section">
          <h3 class="section-title">📍 Địa chỉ</h3>
          
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ chi tiết <span class="required">*</span></label>
            <input type="text" class="form-control" id="address" name="address" 
                   placeholder="Số nhà, tên đường..." required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="province_code" class="form-label">Tỉnh/Thành phố</label>
              <select class="form-select" id="province_code" name="province_code">
                <option value="">-- Chọn tỉnh/thành --</option>
                <?php foreach ($provinces as $province): ?>
                  <option value="<?php echo $province['code']; ?>">
                    <?php echo htmlspecialchars($province['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="ward_code" class="form-label">Quận/Huyện/Phường</label>
              <select class="form-select" id="ward_code" name="ward_code" disabled>
                <option value="">-- Chọn tỉnh trước --</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Tiện nghi -->
        <div class="form-section">
          <h3 class="section-title">✨ Tiện nghi</h3>
          <p class="text-muted">Chọn các tiện nghi có sẵn tại chỗ ở của bạn</p>
          
          <?php foreach ($amenitiesByGroup as $groupName => $groupAmenities): ?>
            <div class="amenity-group">
              <div class="amenity-group-title"><?php echo htmlspecialchars($groupName); ?></div>
              <div class="amenities-grid">
                <?php foreach ($groupAmenities as $amenity): ?>
                  <label class="amenity-item" for="amenity_<?php echo $amenity['amenity_id']; ?>">
                    <input class="form-check-input" type="checkbox" 
                           name="amenities[]" value="<?php echo $amenity['amenity_id']; ?>"
                           id="amenity_<?php echo $amenity['amenity_id']; ?>">
                    <span class="amenity-label">
                      <?php echo htmlspecialchars($amenity['name']); ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Ảnh -->
        <div class="form-section">
          <h3 class="section-title">📷 Hình ảnh</h3>
          <p class="text-muted">Tải lên ít nhất 3 ảnh (tối đa 10MB/ảnh, định dạng JPG/PNG/WEBP)</p>
          
          <div class="image-upload-area" onclick="document.getElementById('images').click()">
            <div class="upload-icon">📸</div>
            <p><strong>Click để chọn ảnh</strong></p>
            <p class="text-muted">Hoặc kéo thả ảnh vào đây</p>
          </div>
          
          <input type="file" id="images" name="images[]" multiple accept="image/*">
          <input type="hidden" id="cover_index" name="cover_index" value="0">
          
          <div id="imagePreviewGrid" class="image-preview-grid"></div>
        </div>
        
        <!-- Submit buttons -->
        <div class="row">
          <div class="col-md-6">
            <button type="submit" name="status" value="draft" class="btn-submit btn-draft">
              💾 Lưu nháp
            </button>
          </div>
          <div class="col-md-6">
            <button type="submit" name="status" value="pending" class="btn-submit">
              🚀 Gửi duyệt
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Load wards when province changes
    document.getElementById('province_code').addEventListener('change', async function() {
      const provinceCode = this.value;
      const wardSelect = document.getElementById('ward_code');
      
      wardSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
      wardSelect.disabled = true;
      
      if (!provinceCode) {
        wardSelect.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>';
        return;
      }
      
      try {
        const response = await fetch(`../../../controller/get-wards.php?province_code=${provinceCode}`);
        const wards = await response.json();
        
        wardSelect.innerHTML = '<option value="">-- Chọn quận/huyện/phường --</option>';
        wards.forEach(ward => {
          const option = document.createElement('option');
          option.value = ward.code;
          option.textContent = ward.name;
          wardSelect.appendChild(option);
        });
        
        wardSelect.disabled = false;
      } catch (error) {
        console.error('Error loading wards:', error);
        wardSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
      }
    });
    
    // Image preview
    const imagesInput = document.getElementById('images');
    const previewGrid = document.getElementById('imagePreviewGrid');
    let selectedFiles = [];
    
    imagesInput.addEventListener('change', function(e) {
      const files = Array.from(e.target.files);
      selectedFiles = [...selectedFiles, ...files];
      updatePreview();
    });
    
    function updatePreview() {
      previewGrid.innerHTML = '';
      
      selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          const div = document.createElement('div');
          div.className = 'image-preview-item';
          div.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            ${index === 0 ? '<div class="cover-badge">Ảnh bìa</div>' : ''}
            <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
          `;
          
          div.addEventListener('click', function(event) {
            if (!event.target.classList.contains('remove-btn')) {
              setCoverImage(index);
            }
          });
          
          previewGrid.appendChild(div);
        };
        
        reader.readAsDataURL(file);
      });
      
      // Update file input
      const dt = new DataTransfer();
      selectedFiles.forEach(file => dt.items.add(file));
      imagesInput.files = dt.files;
    }
    
    function removeImage(index) {
      selectedFiles.splice(index, 1);
      updatePreview();
    }
    
    function setCoverImage(index) {
      document.getElementById('cover_index').value = index;
      updatePreview();
    }
    
    // Drag and drop
    const uploadArea = document.querySelector('.image-upload-area');
    
    uploadArea.addEventListener('dragover', function(e) {
      e.preventDefault();
      this.style.borderColor = '#6366f1';
      this.style.background = '#f9fafb';
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
      e.preventDefault();
      this.style.borderColor = '#d1d5db';
      this.style.background = 'transparent';
    });
    
    uploadArea.addEventListener('drop', function(e) {
      e.preventDefault();
      this.style.borderColor = '#d1d5db';
      this.style.background = 'transparent';
      
      const files = Array.from(e.dataTransfer.files);
      selectedFiles = [...selectedFiles, ...files];
      updatePreview();
    });
  </script>
</body>
</html>
