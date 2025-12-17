<?php
// Include Authentication Helper and Controller
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cHost.php';

// Use helper for authentication
requireLogin();
requireHost();

$userId = getCurrentUserId();
$cHost = new cHost();

// Get host_id
$hostInfo = $cHost->cGetHostByUserId($userId);
if (!$hostInfo) {
  header('Location: ./become-host.php');
  exit;
}
$hostId = $hostInfo['host_id'];

// Get data for form
$placeTypes = $cHost->cGetAllPlaceTypes();
$amenities = $cHost->cGetAllAmenities();
$services = $cHost->cGetAllServices();
$provinces = $cHost->cGetAllProvinces();

$successMessage = '';
$errorMessage = '';

// Handle form submission - Delegate to Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add user_id to POST data for image naming
  $_POST['user_id'] = $userId;
  
  // Process through Controller (handles ALL validation and business logic)
  $result = $cHost->cProcessCreateListing($hostId, $_POST, $_FILES);
  
  if ($result['success']) {
    $successMessage = $result['message'];
    
    // Redirect after 2 seconds
    echo "<script>
      setTimeout(function() {
        window.location.href = './my-listings.php';
      }, 2000);
    </script>";
  } else {
    // Display errors
    if (!empty($result['errors'])) {
      $errorMessage = '<ul class="mb-0">';
      foreach ($result['errors'] as $error) {
        $errorMessage .= '<li>' . htmlspecialchars($error) . '</li>';
      }
      $errorMessage .= '</ul>';
    } else {
      $errorMessage = $result['message'];
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
  <link rel="stylesheet" href="../../css/host-create-listing.css?v=<?php echo time(); ?>">
</head>
<body>
</head>
<body>
  <div class="container">
    <div class="form-container">
      <a href="./my-listings.php" class="back-link">← Quay lại danh sách phòng</a>
      
      <div class="form-header">
        <h1><i class="fas fa-home"></i> Đăng phòng mới</h1>
        <p>Chia sẻ không gian của bạn với du khách trên WeGo</p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <strong><i class="fas fa-check-circle"></i> Thành công!</strong> <?php echo htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
          <strong><i class="fas fa-times-circle"></i> Lỗi!</strong> <?php echo $errorMessage; ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" id="listingForm">
        <!-- Thông tin cơ bản -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-edit"></i> Thông tin cơ bản</h3>
          
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
              <label for="place_type_id" class="form-label">Loại chỗ ở <span class="required">*</span></label>
              <select class="form-select" id="place_type_id" name="place_type_id" required>
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
                     min="1" max="50" placeholder="2" required>
            </div>
            
            <div class="col-md-4 mb-3">
              <label for="price" class="form-label">Giá mỗi đêm (VND) <span class="required">*</span></label>
              <input type="number" class="form-control" id="price" name="price" 
                     min="50000" step="1000" placeholder="500000" required>
            </div>
          </div>
        </div>
        
        <!-- Địa chỉ -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ</h3>
          
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ chi tiết <span class="required">*</span></label>
            <input type="text" class="form-control" id="address" name="address" 
                   placeholder="Số nhà, tên đường..." required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="province_code" class="form-label">Tỉnh/Thành phố <span class="required">*</span></label>
              <select class="form-select" id="province_code" name="province_code" required>
                <option value="">-- Chọn tỉnh/thành --</option>
                <?php foreach ($provinces as $province): ?>
                  <option value="<?php echo $province['code']; ?>">
                    <?php echo htmlspecialchars($province['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="ward_code" class="form-label">Quận/Huyện/Phường <span class="required">*</span></label>
              <select class="form-select" id="ward_code" name="ward_code" disabled required>
                <option value="">-- Chọn tỉnh trước --</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Tiện nghi -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-sparkles"></i> Tiện nghi</h3>
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
        
        <!-- Dịch vụ thêm -->
        <div class="form-section">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h3 class="section-title mb-1"><i class="fas fa-concierge-bell"></i> Dịch vụ thêm (tùy chọn)</h3>
              <p class="text-muted mb-0">Thêm các dịch vụ phụ phí mà khách có thể sử dụng</p>
            </div>
            <a href="suggest-service.php" class="btn btn-outline-primary btn-sm" target="_blank">
              <i class="fas fa-lightbulb"></i> Đề xuất dịch vụ mới
            </a>
          </div>
          
          <?php if (!empty($services)): ?>
            <div class="services-list">
              <?php foreach ($services as $service): ?>
                <div class="service-item-input">
                  <div class="service-checkbox">
                    <input type="checkbox" class="form-check-input service-toggle" 
                           id="service_<?php echo $service['service_id']; ?>"
                           data-service-id="<?php echo $service['service_id']; ?>">
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
                             placeholder="Giá (VNĐ)" min="0" step="1000" disabled>
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
        
        <!-- Ảnh -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-camera"></i> Hình ảnh</h3>
          <p class="text-muted">Tải lên từ 3-5 ảnh (tối đa 5MB/ảnh, định dạng JPG/PNG/JPEG)</p>
          
          <div class="image-upload-area" onclick="document.getElementById('images').click()">
            <div class="upload-icon"><i class="fas fa-images fa-3x"></i></div>
            <p><strong>Click để chọn ảnh (3-5 ảnh)</strong></p>
            <p class="text-muted">Hoặc kéo thả ảnh vào đây</p>
          </div>
          
          <input type="file" id="images" name="images[]" multiple accept="image/png,image/jpg,image/jpeg" required>
          <input type="hidden" id="cover_index" name="cover_index" value="0">
          
          <div id="imagePreviewGrid" class="image-preview-grid"></div>
        </div>
        
        <!-- Submit buttons -->
        <div class="row">
          <div class="col-md-6">
            <button type="submit" name="status" value="draft" class="btn-submit btn-draft">
              <i class="fas fa-save"></i> Lưu nháp
            </button>
          </div>
          <div class="col-md-6">
            <button type="submit" name="status" value="pending" class="btn-submit">
              <i class="fas fa-paper-plane"></i> Gửi duyệt
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
      wardSelect.value = ''; // Clear current value
      
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
        
        wardSelect.disabled = false; // Enable sau khi load xong
      } catch (error) {
        wardSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
        console.error('Error loading wards:', error);
      }
    });
    
    // Image preview
    const imagesInput = document.getElementById('images');
    const previewGrid = document.getElementById('imagePreviewGrid');
    let selectedFiles = [];
    const maxFiles = 5;
    const minFiles = 3;
    
    imagesInput.addEventListener('change', function(e) {
      const files = Array.from(e.target.files);
      
      // Kiểm tra số lượng ảnh
      if (selectedFiles.length + files.length > maxFiles) {
        alert(`Chỉ được upload tối đa ${maxFiles} ảnh!`);
        const allowedCount = maxFiles - selectedFiles.length;
        selectedFiles = [...selectedFiles, ...files.slice(0, allowedCount)];
      } else {
        selectedFiles = [...selectedFiles, ...files];
      }
      
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
      
      // Kiểm tra số lượng ảnh
      if (selectedFiles.length + files.length > maxFiles) {
        alert(`Chỉ được upload tối đa ${maxFiles} ảnh!`);
        const allowedCount = maxFiles - selectedFiles.length;
        selectedFiles = [...selectedFiles, ...files.slice(0, allowedCount)];
      } else {
        selectedFiles = [...selectedFiles, ...files];
      }
      
      updatePreview();
    });
    
    // Service checkbox toggle
    document.querySelectorAll('.service-toggle').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const serviceId = this.dataset.serviceId;
        const priceInput = document.getElementById('price_' + serviceId);
        if (this.checked) {
          priceInput.disabled = false;
          priceInput.required = true;
          priceInput.focus();
        } else {
          priceInput.disabled = true;
          priceInput.required = false;
          priceInput.value = '';
        }
      });
    });
    
    // Validate form trước khi submit
    document.getElementById('listingForm').addEventListener('submit', function(e) {
      const errors = [];
      
      // Validate tiêu đề
      const title = document.getElementById('title').value.trim();
      if (!title) {
        errors.push('Tiêu đề phòng không được để trống');
      } else if (title.length < 10) {
        errors.push('Tiêu đề phải có ít nhất 10 ký tự');
      } else if (title.length > 100) {
        errors.push('Tiêu đề không được vượt quá 100 ký tự');
      }
      
      // Validate mô tả
      const description = document.getElementById('description').value.trim();
      if (description && description.length < 10) {
        errors.push('Mô tả phải có ít nhất 10 ký tự (hoặc để trống)');
      }
      
      // Validate loại phòng
      const placeType = document.getElementById('place_type_id').value;
      if (!placeType) {
        errors.push('Vui lòng chọn loại phòng');
      }
      
      // Validate địa chỉ
      const address = document.getElementById('address').value.trim();
      if (!address) {
        errors.push('Địa chỉ không được để trống');
      } else if (address.length < 10) {
        errors.push('Địa chỉ phải có ít nhất 10 ký tự');
      }
      
      // Validate tỉnh/thành phố
      const provinceCode = document.getElementById('province_code').value;
      if (!provinceCode) {
        errors.push('Vui lòng chọn Tỉnh/Thành phố');
      }
      
      // Validate phường/xã
      const wardCode = document.getElementById('ward_code').value;
      if (!wardCode) {
        errors.push('Vui lòng chọn Phường/Xã');
      }
      
      // Validate giá
      const price = parseFloat(document.getElementById('price').value);
      if (!price || price <= 0) {
        errors.push('Giá thuê phải lớn hơn 0');
      } else if (price < 50000) {
        errors.push('Giá thuê tối thiểu là 50,000đ/đêm');
      }
      
      // Validate sức chứa
      const capacity = parseInt(document.getElementById('capacity').value);
      if (!capacity || capacity <= 0) {
        errors.push('Sức chứa phải lớn hơn 0');
      } else if (capacity > 50) {
        errors.push('Sức chứa tối đa là 50 người');
      }
      
      // Validate ảnh
      if (selectedFiles.length < minFiles) {
        errors.push(`Vui lòng upload ít nhất ${minFiles} ảnh cho phòng`);
      } else if (selectedFiles.length > maxFiles) {
        errors.push(`Chỉ được upload tối đa ${maxFiles} ảnh`);
      }
      
      // Nếu có lỗi, hiển thị và ngăn submit
      if (errors.length > 0) {
        e.preventDefault();
        let errorMsg = 'Vui lòng kiểm tra lại:\n\n';
        errors.forEach((error, index) => {
          errorMsg += `${index + 1}. ${error}\n`;
        });
        alert(errorMsg);
        
        // Scroll đến trường đầu tiên bị lỗi
        if (!title) {
          document.getElementById('title').focus();
        } else if (!placeType) {
          document.getElementById('place_type_id').focus();
        } else if (!address) {
          document.getElementById('address').focus();
        } else if (!provinceCode) {
          document.getElementById('province_code').focus();
        } else if (!wardCode) {
          document.getElementById('ward_code').focus();
        } else if (!price || price <= 0) {
          document.getElementById('price').focus();
        } else if (!capacity || capacity <= 0) {
          document.getElementById('capacity').focus();
        }
        
        return false;
      }
      
      // Thêm loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
      }
    });
  </script>
</body>
</html>
