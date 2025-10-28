<?php
include_once __DIR__ . '/../../controller/cAdmin.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
  header('Location: ./login.php');
  exit;
}

$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($applicationId <= 0) {
  header('Location: ./applications.php');
  exit;
}

$cAdmin = new cAdmin();
$application = $cAdmin->cGetHostApplicationDetail($applicationId);

if (!$application) {
  header('Location: ./applications.php');
  exit;
}

// Load documents
include_once __DIR__ . '/../../model/mHost.php';
$mHost = new mHost();
$documents = $mHost->mGetHostDocuments($applicationId);

// Organize documents by type
$cccdFront = '';
$cccdBack = '';
$businessLicense = '';
foreach ($documents as $doc) {
  if ($doc['doc_type'] === 'cccd_front') {
    $cccdFront = $doc['file_url'];
  } elseif ($doc['doc_type'] === 'cccd_back') {
    $cccdBack = $doc['file_url'];
  } elseif ($doc['doc_type'] === 'business_license') {
    $businessLicense = $doc['file_url'];
  }
}

$successMessage = '';
$errorMessage = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'approve') {
    $result = $cAdmin->cApproveHostApplication($applicationId, $_SESSION['admin_id']);
    if ($result['success']) {
      $successMessage = $result['message'];
      // Refresh data
      $application = $cAdmin->cGetHostApplicationDetail($applicationId);
    } else {
      $errorMessage = $result['message'];
    }
  } elseif ($_POST['action'] === 'reject') {
    $reason = trim($_POST['reason'] ?? '');
    $result = $cAdmin->cRejectHostApplication($applicationId, $_SESSION['admin_id'], $reason);
    if ($result['success']) {
      $successMessage = $result['message'];
      // Refresh data
      $application = $cAdmin->cGetHostApplicationDetail($applicationId);
    } else {
      $errorMessage = $result['message'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết đơn đăng ký #<?php echo $applicationId; ?> - WeGo Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/application-detail.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="admin-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1>📄 Chi tiết đơn đăng ký #<?php echo $applicationId; ?></h1>
        </div>
        <div class="col-md-6">
          <div class="admin-info">
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</span>
            <a href="./dashboard.php" class="btn-back">📊 Dashboard</a>
            <a href="./users.php" class="btn-back">👥 Người dùng</a>
            <a href="./hosts.php" class="btn-back">🏡 Chủ nhà</a>
            <a href="./applications.php" class="btn-back">📋 Đơn đăng ký</a>
            <a href="./listings.php" class="btn-back">🏠 Phòng</a>
            <a href="./amenities-services.php" class="btn-back">🛠️ Tiện nghi & DV</a>
            <a href="./logout.php" class="btn-back">🚪 Đăng xuất</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <?php if ($successMessage): ?>
      <div class="alert alert-success">
        <strong>Thành công!</strong> <?php echo htmlspecialchars($successMessage); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
      <div class="alert alert-danger">
        <strong>Lỗi!</strong> <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>
    
    <!-- Application Status -->
    <div class="detail-card">
      <h3>Trạng thái đơn</h3>
      <div class="text-center">
        <?php
        $statusClass = '';
        $statusText = '';
        $statusIcon = '';
        switch ($application['status']) {
          case 'pending':
            $statusClass = 'badge-pending';
            $statusText = 'Chờ duyệt';
            $statusIcon = '⏳';
            break;
          case 'approved':
            $statusClass = 'badge-approved';
            $statusText = 'Đã duyệt';
            $statusIcon = '✅';
            break;
          case 'rejected':
            $statusClass = 'badge-rejected';
            $statusText = 'Đã từ chối';
            $statusIcon = '❌';
            break;
        }
        ?>
        <span class="badge-status <?php echo $statusClass; ?>">
          <?php echo $statusIcon; ?> <?php echo $statusText; ?>
        </span>
        
        <?php if ($application['status'] !== 'pending'): ?>
          <div class="review-info">
            <strong>Người duyệt:</strong> <?php echo htmlspecialchars($application['reviewed_by_name']); ?>
            <br>
            <strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($application['reviewed_at'])); ?>
          </div>
        <?php endif; ?>
        
        <?php if ($application['status'] === 'rejected' && $application['rejection_reason']): ?>
          <div class="rejection-reason-box">
            <strong>Lý do từ chối:</strong>
            <p><?php echo nl2br(htmlspecialchars($application['rejection_reason'])); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- User Information -->
    <div class="detail-card">
      <h3>👤 Thông tin người đăng ký</h3>
      <div class="info-row">
        <div class="info-label">Họ tên:</div>
        <div class="info-value"><?php echo htmlspecialchars($application['full_name']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value"><?php echo htmlspecialchars($application['email']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Số điện thoại:</div>
        <div class="info-value"><?php echo htmlspecialchars($application['phone']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Ngày đăng ký:</div>
        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($application['created_at'])); ?></div>
      </div>
    </div>
    
    <!-- Business Information -->
    <div class="detail-card">
      <h3>🏢 Thông tin kinh doanh</h3>
      <div class="info-row">
        <div class="info-label">Tên doanh nghiệp:</div>
        <div class="info-value"><?php echo $application['business_name'] ? htmlspecialchars($application['business_name']) : '<span class="text-muted">Chưa cung cấp</span>'; ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Mã số thuế:</div>
        <div class="info-value"><?php echo $application['tax_code'] ? htmlspecialchars($application['tax_code']) : '<span class="text-muted">Chưa cung cấp</span>'; ?></div>
      </div>
    </div>
    
    <!-- Documents -->
    <div class="detail-card">
      <h3>📎 Tài liệu đính kèm</h3>
      
      <?php if (empty($documents)): ?>
        <div class="alert alert-warning">
          <strong>Chưa có tài liệu!</strong> Người dùng chưa upload tài liệu đính kèm.
        </div>
      <?php else: ?>
        <div class="document-grid">
          <?php if ($cccdFront): ?>
            <div class="document-item">
              <div class="document-label">CCCD/CMND (Mặt trước)</div>
              <img src="../../<?php echo htmlspecialchars($cccdFront); ?>" 
                   alt="CCCD Front" 
                   class="document-image"
                   onclick="openLightbox(this.src)">
            </div>
          <?php endif; ?>
          
          <?php if ($cccdBack): ?>
            <div class="document-item">
              <div class="document-label">CCCD/CMND (Mặt sau)</div>
              <img src="../../<?php echo htmlspecialchars($cccdBack); ?>" 
                   alt="CCCD Back" 
                   class="document-image"
                   onclick="openLightbox(this.src)">
            </div>
          <?php endif; ?>
          
          <?php if ($businessLicense): ?>
            <div class="document-item">
              <div class="document-label">Giấy phép kinh doanh</div>
              <img src="../../<?php echo htmlspecialchars($businessLicense); ?>" 
                   alt="Business License" 
                   class="document-image"
                   onclick="openLightbox(this.src)">
            </div>
          <?php endif; ?>
          
          <?php if (!$cccdFront && !$cccdBack && !$businessLicense): ?>
            <div class="alert alert-warning">
              <strong>Không có ảnh!</strong> Các tài liệu trong database chưa được phân loại đúng.
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Action Buttons (only for pending applications) -->
    <?php if ($application['status'] === 'pending'): ?>
      <div class="detail-card">
        <h3>⚡ Hành động</h3>
        <div class="action-buttons">
          <form method="POST" onsubmit="return confirm('Bạn có chắc muốn duyệt đơn này?');">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn-approve">
              ✅ Duyệt đơn
            </button>
          </form>
          
          <button type="button" class="btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
            ❌ Từ chối đơn
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">❌ Từ chối đơn đăng ký</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="reject">
            <div class="mb-3">
              <label for="reason" class="form-label fw-bold">Lý do từ chối: <span class="text-danger">*</span></label>
              <textarea 
                name="reason" 
                id="reason" 
                class="form-control" 
                rows="5" 
                required
                placeholder="Nhập lý do từ chối đơn đăng ký..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Lightbox -->
  <div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <img src="" alt="Document" id="lightbox-img">
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openLightbox(src) {
      document.getElementById('lightbox-img').src = src;
      document.getElementById('lightbox').classList.add('active');
    }
    
    function closeLightbox() {
      document.getElementById('lightbox').classList.remove('active');
    }
    
    // Close lightbox on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeLightbox();
      }
    });
  </script>
</body>
</html>
