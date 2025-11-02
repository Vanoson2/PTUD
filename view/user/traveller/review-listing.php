<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

$userId = $_SESSION['user_id'];
$listingId = $_GET['listing_id'] ?? 0;
$bookingId = $_GET['booking_id'] ?? 0;

if (empty($listingId) || empty($bookingId)) {
  header('Location: my-bookings.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cListing.php');
include_once(__DIR__ . '/../../../controller/cBooking.php');

$cListing = new cListing();
$cBooking = new cBooking();

// Get listing details
$listing = $cListing->cGetListingDetail($listingId);

if (!$listing) {
  header('Location: my-bookings.php');
  exit;
}

// Get booking details to verify ownership
$bookingResult = $cBooking->cGetBookingById($bookingId);
if (!$bookingResult || $bookingResult->num_rows == 0) {
  header('Location: my-bookings.php');
  exit;
}

$booking = $bookingResult->fetch_assoc();

// Verify booking belongs to user
if ($booking['user_id'] != $userId) {
  header('Location: my-bookings.php');
  exit;
}

// Check if already rated
if ($booking['is_rated']) {
  header('Location: my-bookings.php?tab=completed&error=already_rated');
  exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Viết review - WEGO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/review-listing.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="review-modal">
  <div class="modal-header-custom">
    <h2>Viết review</h2>
    <p>Chia sẻ trải nghiệm của bạn cùng những người khác</p>
  </div>
  
  <form id="reviewForm" method="POST" action="submit-review.php" enctype="multipart/form-data">
    <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
    
    <!-- Rating -->
    <div class="rating-section">
      <label class="rating-label">Số sao</label>
      <div class="star-rating">
        <input type="radio" name="rating" id="star1" value="1" required>
        <label for="star1">★</label>
        <input type="radio" name="rating" id="star2" value="2">
        <label for="star2">★</label>
        <input type="radio" name="rating" id="star3" value="3">
        <label for="star3">★</label>
        <input type="radio" name="rating" id="star4" value="4">
        <label for="star4">★</label>
        <input type="radio" name="rating" id="star5" value="5">
        <label for="star5">★</label>
      </div>
    </div>
    
    <!-- Comment -->
    <div class="comment-section">
      <label class="comment-label">Nhận xét</label>
      <textarea 
        name="comment" 
        class="comment-textarea"
      ></textarea>
    </div>
    
    <!-- Image Upload -->
    <div class="image-upload-section">
      <label class="upload-label">Ảnh (Không bắt buộc - Tối đa 5 ảnh - PNG/JPG/JPEG - Max 5MB/ảnh)</label>
      <div class="upload-area" onclick="document.getElementById('imageInput').click()">
        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <p class="upload-text">Upload ảnh (tuỳ chọn)</p>
      </div>
      <input 
        type="file" 
        id="imageInput" 
        name="images[]" 
        accept="image/png,image/jpg,image/jpeg" 
        multiple 
        max="5"
      >
      <div class="preview-container" id="previewContainer"></div>
      <p class="image-count" id="imageCount">0/5 ảnh đã chọn</p>
    </div>
    
    <!-- Actions -->
    <div class="modal-actions">
      <button type="button" class="btn-cancel" onclick="window.location.href='my-bookings.php?tab=completed'">
        Hủy
      </button>
      <button type="submit" class="btn-submit" id="submitBtn">
        Gửi đánh giá
      </button>
    </div>
  </form>
</div>

<!-- Success Modal -->
<div class="success-overlay" id="successOverlay">
  <div class="success-modal">
    <div class="success-icon">
      <svg width="48" height="48" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
      </svg>
    </div>
    <h3>ĐÁNH GIÁ THÀNH CÔNG</h3>
    <p>ĐÁNH GIÁ ĐÃ ĐƯỢC GỬI<br>CẢM ƠN BẠN ĐÃ CHIA SẺ TRẢI NGHIỆM CỦA MÌNH.</p>
    <button class="btn-ok" onclick="window.location.href='my-bookings.php?tab=completed'">OK</button>
  </div>
</div>

<script>
  let selectedFiles = [];
  const maxFiles = 5;
  
  // Star rating hover effect
  const starLabels = document.querySelectorAll('.star-rating label');
  const starInputs = document.querySelectorAll('.star-rating input[type="radio"]');
  
  starLabels.forEach((label, index) => {
    label.addEventListener('mouseenter', function() {
      highlightStars(index + 1);
    });
  });
  
  document.querySelector('.star-rating').addEventListener('mouseleave', function() {
    const checkedInput = document.querySelector('.star-rating input[type="radio"]:checked');
    if (checkedInput) {
      const checkedValue = parseInt(checkedInput.value);
      highlightStars(checkedValue);
    } else {
      highlightStars(0);
    }
  });
  
  starInputs.forEach(input => {
    input.addEventListener('change', function() {
      highlightStars(parseInt(this.value));
    });
  });
  
  function highlightStars(count) {
    starLabels.forEach((label, index) => {
      if (index < count) {
        label.style.color = '#fbbf24';
      } else {
        label.style.color = '#d1d5db';
      }
    });
  }
  
  document.getElementById('imageInput').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    
    // Validate file types and size
    files.forEach(file => {
      if (selectedFiles.length >= maxFiles) {
        alert(`Chỉ được upload tối đa ${maxFiles} ảnh!`);
        return;
      }
      
      // Check file type
      const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
      if (!allowedTypes.includes(file.type)) {
        alert(`File ${file.name} không đúng định dạng. Chỉ chấp nhận PNG, JPG, JPEG!`);
        return;
      }
      
      // Check file size (5MB)
      const maxSize = 5 * 1024 * 1024;
      if (file.size > maxSize) {
        alert(`File ${file.name} vượt quá 5MB!`);
        return;
      }
      
      selectedFiles.push(file);
    });
    
    updatePreview();
    updateSubmitButton();
  });
  
  function updatePreview() {
    const container = document.getElementById('previewContainer');
    const countLabel = document.getElementById('imageCount');
    
    container.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `
          <img src="${e.target.result}" alt="Preview">
          <button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>
        `;
        container.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
    
    countLabel.textContent = `${selectedFiles.length}/${maxFiles} ảnh đã chọn`;
  }
  
  function removeImage(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
    updateSubmitButton();
  }
  
  function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const rating = document.querySelector('input[name="rating"]:checked');
    const comment = document.querySelector('textarea[name="comment"]').value;
    
    submitBtn.disabled = !rating || !comment.trim();
  }
  
  // Update button state on rating change
  document.querySelectorAll('input[name="rating"]').forEach(input => {
    input.addEventListener('change', updateSubmitButton);
  });
  
  // Update button state on comment change
  document.querySelector('textarea[name="comment"]').addEventListener('input', updateSubmitButton);
  
  // Handle form submission
  document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Remove default file input and add selected files
    formData.delete('images[]');
    selectedFiles.forEach(file => {
      formData.append('images[]', file);
    });
    
    // Submit via AJAX
    fetch('submit-review.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById('successOverlay').style.display = 'flex';
      } else {
        alert('Có lỗi xảy ra: ' + (data.message || 'Vui lòng thử lại'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Có lỗi xảy ra. Vui lòng thử lại.');
    });
  });
  
  // Initialize button state
  updateSubmitButton();
</script>

</body>
</html>
