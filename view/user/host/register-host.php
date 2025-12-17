<?php
// Enable error reporting for debugging (REMOVE AFTER FIXING)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../logs/php_errors.log');

require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';
require_once __DIR__ . '/../../../controller/cHost.php';

requireLogin();
$userId = getCurrentUserId();

$cUser = new cUser();
$user = $cUser->cGetUserProfile($userId);

if (!$user) {
  logoutUser();
  header('Location: ../traveller/login.php');
  exit;
}

// Kiểm tra email đã xác thực chưa
if ($user['is_email_verified'] != 1) {
  header('Location: ../traveller/verify-code.php?user_id=' . $userId . '&email=' . urlencode($user['email']));
  exit;
}

$errors = [];
$successMessage = '';

// Load previous rejected application data to pre-fill form
$cHost = new cHost();
$previousApplication = $cHost->cGetUserHostApplication($userId);
$prefillData = [
  'tax_code' => '',
  'business_name' => $user['full_name'],
  'rejection_reason' => ''
];

if ($previousApplication && $previousApplication['status'] === 'rejected') {
  $prefillData['tax_code'] = $previousApplication['tax_code'] ?? '';
  $prefillData['business_name'] = $previousApplication['business_name'] ?? $user['full_name'];
  $prefillData['rejection_reason'] = $previousApplication['rejection_reason'] ?? '';
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idNumber = trim($_POST['id_number'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $phone = trim($_POST['phone'] ?? $user['phone']);
  $bankAccount = trim($_POST['bank_account'] ?? '');
  $bankName = trim($_POST['bank_name'] ?? '');
  $taxCode = trim($_POST['tax_code'] ?? '');
  $acceptTerms = isset($_POST['accept_terms']);
  
  // Prepare files data for Controller validation
  $filesData = [
    'id_card_front' => $_FILES['id_front'] ?? [],
    'id_card_back' => $_FILES['id_back'] ?? [],
    'business_license' => $_FILES['business_license'] ?? []
  ];
  
  // Validate accept terms first
  if (!$acceptTerms) {
    $errors['accept_terms'] = 'Bạn phải đồng ý với điều khoản và chính sách';
  }
  
  // Call Controller for validation
  if (empty($errors)) {
    $cHost = new cHost();
    $result = $cHost->cRegisterHost($userId, $idNumber, $address, $phone, $bankAccount, $bankName, $taxCode, $filesData);
    
    if ($result['success']) {
      // Get validated data
      $validatedData = $result['data'];
      $idCardImages = $validatedData['id_card_images'];
      
      // Upload business license
      $uploadDir = __DIR__ . '/../../../public/uploads/host/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }
      
      $businessLicenseImage = '';
      if (!empty($_FILES['business_license']['name'])) {
        $extension = pathinfo($_FILES['business_license']['name'], PATHINFO_EXTENSION);
        $businessLicenseImage = $userId . '_img03.' . $extension;
        move_uploaded_file($_FILES['business_license']['tmp_name'], $uploadDir . $businessLicenseImage);
      }
      
      // Create host application
      $fullName = $user['full_name'];
      $appResult = $cHost->cCreateHostApplication($userId, $fullName, $taxCode);
      
      if ($appResult['success']) {
        $applicationId = $appResult['application_id'];
        
        // Save ID card images to database
        if (isset($idCardImages['front'])) {
          $cHost->cSaveHostDocument($applicationId, 'cccd_front', $idCardImages['front'], 'image/jpeg', 0);
        }
        if (isset($idCardImages['back'])) {
          $cHost->cSaveHostDocument($applicationId, 'cccd_back', $idCardImages['back'], 'image/jpeg', 0);
        }
        
        // Save business license if uploaded
        if (!empty($businessLicenseImage)) {
          $businessLicensePath = '/public/uploads/host/' . $businessLicenseImage;
          $cHost->cSaveHostDocument($applicationId, 'business_license', $businessLicensePath, 'image/jpeg', 0);
        }
        
        // Successfully created host application
        // Do NOT create host record here - wait for admin approval
        $successMessage = 'Đăng ký host thành công! Đơn đăng ký của bạn đang chờ admin duyệt (24-48h). Chúng tôi sẽ thông báo qua email khi đơn được duyệt.';
        // Redirect to profile page
        $redirectUrl = '../traveller/profile.php';
        $redirectDelay = 3;
      } else {
        $errors['general'] = $appResult['message'];
      }
    } else {
      $errors = $result['errors'] ?? ['general' => $result['message']];
    }
  }
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<?php if (isset($redirectUrl) && isset($redirectDelay)): ?>
  <meta http-equiv="refresh" content="<?php echo $redirectDelay; ?>;url=<?php echo htmlspecialchars($redirectUrl); ?>">
<?php endif; ?>

<link rel="stylesheet" href="../../css/traveller-auth.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../css/host-register.css">

<div class="register-host-container">
  <div class="container">
    <div class="register-host-card">
      
      <div class="register-host-header">
        <h1><i class="fa-solid fa-house"></i> Đăng Ký Host</h1>
        <p>Hoàn thành thông tin để trở thành host</p>
      </div>
      
      <?php if ($successMessage): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
        
        <div class="success-cta">
          <a href="../../../index.php" class="btn btn-primary">Về Trang Chủ</a>
        </div>
      <?php else: ?>
        
        <?php if (!empty($prefillData['rejection_reason'])): ?>
        <div class="alert alert-warning">
          <i class="fa-solid fa-exclamation-triangle"></i>
          <strong>Đơn trước đó bị từ chối:</strong>
          <p><?php echo htmlspecialchars($prefillData['rejection_reason']); ?></p>
          <small>Vui lòng điền lại form và khắc phục các vấn đề trên.</small>
        </div>
        <?php endif; ?>
        
        <div class="info-card">
          <h4>
            <i class="fa-solid fa-circle-info"></i>
            Thông tin cần cung cấp:
          </h4>
          <ul>
            <li>Số CMND/CCCD để xác minh danh tính</li>
            <li><strong>Ảnh CMND/CCCD mặt trước và mặt sau</strong></li>
            <li><strong>Ảnh Giấy phép kinh doanh</strong> (hoặc giấy tờ chứng minh quyền sở hữu/cho thuê)</li>
            <li>Địa chỉ liên hệ chính xác</li>
            <li>Thông tin tài khoản ngân hàng để nhận thanh toán</li>
          </ul>
        </div>
        
        <?php if (isset($errors['general'])): ?>
          <div class="alert alert-danger">
            <i class="fa-solid fa-circle-xmark"></i>
            <span><?php echo htmlspecialchars($errors['general']); ?></span>
          </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form" enctype="multipart/form-data">
          
          <!-- Thông tin cá nhân -->
          <div class="form-section">
            <h3>Thông Tin Cá Nhân</h3>
            
            <div class="form-group">
              <label for="full_name">Họ và tên</label>
              <input 
                type="text" 
                id="full_name" 
                name="full_name" 
                class="form-control" 
                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                readonly
              >
            </div>
            
            <div class="form-group">
              <label for="email">Email</label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                value="<?php echo htmlspecialchars($user['email']); ?>"
                readonly
              >
            </div>
            
            <div class="form-group">
              <label for="phone">Số điện thoại *</label>
              <input 
                type="tel" 
                id="phone" 
                name="phone" 
                class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                value="<?php echo htmlspecialchars($user['phone']); ?>"
                placeholder="0912345678"
                readonly
                required
              >
              <?php if (isset($errors['phone'])): ?>
                <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Thông tin CMND/CCCD -->
          <div class="form-section">
            <h3>Thông Tin Định Danh</h3>
            
            <div class="form-group">
              <label for="id_type">Loại giấy tờ *</label>
              <select id="id_type" name="id_type" class="form-control" required>
                <option value="CMND">CMND</option>
                <option value="CCCD">CCCD</option>
                <option value="Passport">Hộ chiếu</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="id_number">Số CMND/CCCD *</label>
              <input 
                type="text" 
                id="id_number" 
                name="id_number" 
                class="form-control <?php echo isset($errors['id_number']) ? 'is-invalid' : ''; ?>" 
                placeholder="Nhập số CMND/CCCD"
                required
              >
              <?php if (isset($errors['id_number'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_number']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="address">Địa chỉ thường trú *</label>
              <textarea 
                id="address" 
                name="address" 
                class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                rows="3"
                placeholder="Nhập địa chỉ đầy đủ"
                required
              ></textarea>
              <?php if (isset($errors['address'])): ?>
                <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="id_front">Ảnh CMND/CCCD mặt trước *</label>
              <input 
                type="file" 
                id="id_front" 
                name="id_front" 
                class="form-control <?php echo isset($errors['id_front']) ? 'is-invalid' : ''; ?>" 
                accept="image/jpeg,image/jpg,image/png"
                required
              >
              <small class="form-text text-muted">Chỉ chấp nhận file JPG, JPEG, PNG. Tối đa 5MB</small>
              <?php if (isset($errors['id_front'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['id_front']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="id_back">Ảnh CMND/CCCD mặt sau *</label>
              <input 
                type="file" 
                id="id_back" 
                name="id_back" 
                class="form-control <?php echo isset($errors['id_back']) ? 'is-invalid' : ''; ?>" 
                accept="image/jpeg,image/jpg,image/png"
                required
              >
              <small class="form-text text-muted">Chỉ chấp nhận file JPG, JPEG, PNG. Tối đa 5MB</small>
              <?php if (isset($errors['id_back'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['id_back']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="business_license">Ảnh Giấy phép kinh doanh *</label>
              <input 
                type="file" 
                id="business_license" 
                name="business_license" 
                class="form-control <?php echo isset($errors['business_license']) ? 'is-invalid' : ''; ?>" 
                accept="image/jpeg,image/jpg,image/png"
                required
              >
              <small class="form-text text-muted">Giấy phép kinh doanh lưu trú hoặc giấy tờ tương đương. Tối đa 5MB</small>
              <?php if (isset($errors['business_license'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['business_license']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Thông tin thanh toán -->
          <div class="form-section">
            <h3>Thông Tin Thanh Toán</h3>
            
            <div class="form-group">
              <label for="bank_name">Tên ngân hàng *</label>
              <input 
                type="text" 
                id="bank_name" 
                name="bank_name" 
                class="form-control <?php echo isset($errors['bank_name']) ? 'is-invalid' : ''; ?>" 
                placeholder="Ví dụ: Vietcombank"
                required
              >
              <?php if (isset($errors['bank_name'])): ?>
                <div class="invalid-feedback"><?php echo $errors['bank_name']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="bank_account">Số tài khoản *</label>
              <input 
                type="text" 
                id="bank_account" 
                name="bank_account" 
                class="form-control <?php echo isset($errors['bank_account']) ? 'is-invalid' : ''; ?>" 
                placeholder="Nhập số tài khoản ngân hàng"
                required
              >
              <?php if (isset($errors['bank_account'])): ?>
                <div class="invalid-feedback"><?php echo $errors['bank_account']; ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label for="tax_code">Mã số thuế (không bắt buộc)</label>
              <input 
                type="text" 
                id="tax_code" 
                name="tax_code" 
                class="form-control <?php echo isset($errors['tax_code']) ? 'is-invalid' : ''; ?>" 
                placeholder="Nhập mã số thuế (10-13 số, nếu có)"
                pattern="[0-9]{10,13}"
                maxlength="13"
                value="<?php echo htmlspecialchars($prefillData['tax_code']); ?>"
              >
              <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i> Chỉ cần điền nếu bạn là doanh nghiệp hoặc đã đăng ký kinh doanh
              </small>
              <small class="form-text text-muted">Mã số thuế doanh nghiệp (10-13 chữ số)</small>
              <?php if (isset($errors['tax_code'])): ?>
                <div class="invalid-feedback"><?php echo $errors['tax_code']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Điều khoản -->
          <div class="terms-box">
            <label>
              <input 
                type="checkbox" 
                name="accept_terms"
                id="accept_terms" 
                required
              >
              <span>
                Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và 
                <a href="#">Chính sách bảo mật</a> của WeGo. 
                Tôi cam kết cung cấp thông tin chính xác và tuân thủ các quy định về cho thuê nhà.
              </span>
            </label>
            <?php if (isset($errors['accept_terms'])): ?>
              <div class="text-danger mt-2"><?php echo $errors['accept_terms']; ?></div>
            <?php endif; ?>
          </div>
          
          <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-circle-check"></i>
            Gửi Đăng Ký
          </button>
          
        </form>
      <?php endif; ?>
      
    </div>
  </div>
</div>

<script>
// Preview ảnh trước khi upload
function previewImage(input, previewId) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      let preview = document.getElementById(previewId);
      if (!preview) {
        preview = document.createElement('img');
        preview.id = previewId;
        preview.style.cssText = 'max-width: 200px; max-height: 200px; margin-top: 10px; border-radius: 8px; border: 2px solid #e5e7eb;';
        input.parentElement.appendChild(preview);
      }
      preview.src = e.target.result;
    }
    reader.readAsDataURL(file);
  }
}

// Attach event listeners
document.getElementById('id_front').addEventListener('change', function() {
  previewImage(this, 'preview_id_front');
});

document.getElementById('id_back').addEventListener('change', function() {
  previewImage(this, 'preview_id_back');
});

document.getElementById('business_license').addEventListener('change', function() {
  previewImage(this, 'preview_business_license');
});

// Validate file size
document.querySelectorAll('input[type="file"]').forEach(input => {
  input.addEventListener('change', function() {
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (this.files[0] && this.files[0].size > maxSize) {
      alert('Kích thước file không được vượt quá 5MB!');
      this.value = '';
    }
  });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
  const errors = [];
  
  // Validate ID Number (CCCD)
  const idNumber = document.getElementById('id_number').value.trim();
  if (!idNumber) {
    errors.push('Vui lòng nhập số CMND/CCCD');
  } else if (!/^[0-9]{9,12}$/.test(idNumber)) {
    errors.push('Số CMND/CCCD không hợp lệ (9-12 chữ số)');
  }
  
  // Validate Address
  const address = document.getElementById('address').value.trim();
  if (!address) {
    errors.push('Vui lòng nhập địa chỉ');
  } else if (address.length > 500) {
    errors.push('Địa chỉ quá dài (tối đa 500 ký tự)');
  }
  
  // Validate Phone
  const phone = document.getElementById('phone').value.trim();
  if (!phone) {
    errors.push('Vui lòng nhập số điện thoại');
  } else if (!/^[0-9]{10,11}$/.test(phone)) {
    errors.push('Số điện thoại không hợp lệ (10-11 chữ số)');
  }
  
  // Validate Bank Account
  const bankAccount = document.getElementById('bank_account').value.trim();
  if (!bankAccount) {
    errors.push('Vui lòng nhập số tài khoản ngân hàng');
  } else if (bankAccount.length > 50) {
    errors.push('Số tài khoản quá dài (tối đa 50 ký tự)');
  }
  
  // Validate Bank Name
  const bankName = document.getElementById('bank_name').value.trim();
  if (!bankName) {
    errors.push('Vui lòng nhập tên ngân hàng');
  } else if (bankName.length > 100) {
    errors.push('Tên ngân hàng quá dài (tối đa 100 ký tự)');
  }
  
  // Validate Tax Code
  const taxCode = document.getElementById('tax_code').value.trim();
  if (!taxCode) {
    errors.push('Vui lòng nhập mã số thuế');
  } else if (!/^[0-9]{10,13}$/.test(taxCode)) {
    errors.push('Mã số thuế không hợp lệ (10-13 chữ số)');
  }
  
  // Validate ID Card Images
  const idFront = document.getElementById('id_front').files.length;
  const idBack = document.getElementById('id_back').files.length;
  if (!idFront || !idBack) {
    errors.push('Vui lòng tải lên ảnh mặt trước và mặt sau CMND/CCCD');
  }
  
  // Validate Accept Terms
  const acceptTerms = document.getElementById('accept_terms').checked;
  if (!acceptTerms) {
    errors.push('Bạn phải đồng ý với điều khoản và chính sách');
  }
  
  // Show errors if any
  if (errors.length > 0) {
    e.preventDefault();
    let errorMessage = 'Vui lòng kiểm tra lại:\n\n';
    errors.forEach((error, index) => {
      errorMessage += `${index + 1}. ${error}\n`;
    });
    alert(errorMessage);
    return false;
  }
  
  return true;
});

// Real-time validation for ID Number
document.getElementById('id_number').addEventListener('input', function() {
  const value = this.value.trim();
  const feedback = this.nextElementSibling;
  
  if (value && !/^[0-9]{9,12}$/.test(value)) {
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
      const div = document.createElement('div');
      div.className = 'invalid-feedback';
      div.style.display = 'block';
      div.textContent = 'Số CMND/CCCD phải là 9-12 chữ số';
      this.parentNode.appendChild(div);
      this.classList.add('is-invalid');
    }
  } else {
    if (feedback && feedback.classList.contains('invalid-feedback')) {
      feedback.remove();
      this.classList.remove('is-invalid');
    }
  }
});

// Real-time validation for Phone
document.getElementById('phone').addEventListener('input', function() {
  const value = this.value.trim();
  const feedback = this.nextElementSibling;
  
  if (value && !/^[0-9]{10,11}$/.test(value)) {
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
      const div = document.createElement('div');
      div.className = 'invalid-feedback';
      div.style.display = 'block';
      div.textContent = 'Số điện thoại phải là 10-11 chữ số';
      this.parentNode.appendChild(div);
      this.classList.add('is-invalid');
    }
  } else {
    if (feedback && feedback.classList.contains('invalid-feedback')) {
      feedback.remove();
      this.classList.remove('is-invalid');
    }
  }
});

// Real-time validation for Tax Code
document.getElementById('tax_code').addEventListener('input', function() {
  const value = this.value.trim();
  const feedback = this.nextElementSibling;
  
  if (value && !/^[0-9]{10,13}$/.test(value)) {
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
      const div = document.createElement('div');
      div.className = 'invalid-feedback';
      div.style.display = 'block';
      div.textContent = 'Mã số thuế phải là 10-13 chữ số';
      this.parentNode.appendChild(div);
      this.classList.add('is-invalid');
    }
  } else {
    if (feedback && feedback.classList.contains('invalid-feedback')) {
      feedback.remove();
      this.classList.remove('is-invalid');
    }
  }
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>

