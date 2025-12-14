<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
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
  header('Location: ./become-host.php');
  exit;
}

// Lấy host_id
$hostInfo = $cHost->cGetHostByUserId($userId);
if (!$hostInfo) {
  header('Location: ./become-host.php');
  exit;
}
$hostId = $hostInfo['host_id'];

// Get filter status
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : null;
$validStatuses = ['draft', 'pending', 'active', 'inactive', 'rejected'];
if ($filterStatus && !in_array($filterStatus, $validStatuses)) {
  $filterStatus = null;
}

// Lấy danh sách listings
$listings = $cHost->cGetHostListings($hostId, $filterStatus);

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $listingId = intval($_POST['listing_id'] ?? 0);
  if ($listingId > 0) {
    $cHost->cDeleteListing($listingId, $hostId);
    header('Location: ./my-listings.php?status=' . ($filterStatus ?? ''));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý phòng - WeGo Host</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../view/css/host-my-listings.css">
</head>
<body>
  <div class="host-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1><i class="fa-solid fa-house"></i> Quản lý phòng của bạn</h1>
          <p>Xin chào, <?php echo htmlspecialchars($hostInfo['full_name']); ?></p>
        </div>
        <div class="col-md-6">
          <div class="header-actions justify-content-end">
            <a href="./host-dashboard.php" class="btn-back">← Dashboard HOST</a>
            <a href="./create-listing.php" class="btn-create-listing">+ Đăng phòng mới</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <!-- Statistics -->
    <?php
    $totalListings = $cHost->cGetHostListings($hostId);
    $draftListings = $cHost->cGetHostListings($hostId, 'draft');
    $pendingListings = $cHost->cGetHostListings($hostId, 'pending');
    $activeListings = $cHost->cGetHostListings($hostId, 'active');
    ?>
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-number"><?php echo is_array($totalListings) ? count($totalListings) : 0; ?></div>
        <div class="stat-label">Tổng phòng</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?php echo is_array($draftListings) ? count($draftListings) : 0; ?></div>
        <div class="stat-label">Bản nháp</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?php echo is_array($pendingListings) ? count($pendingListings) : 0; ?></div>
        <div class="stat-label">Chờ duyệt</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?php echo is_array($activeListings) ? count($activeListings) : 0; ?></div>
        <div class="stat-label">Hoạt động</div>
      </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-tabs">
      <a href="./my-listings.php" class="filter-btn <?php echo $filterStatus === null ? 'active' : ''; ?>">
        <i class="fa-solid fa-clipboard-list"></i> Tất cả
      </a>
      <a href="./my-listings.php?status=draft" class="filter-btn <?php echo $filterStatus === 'draft' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-alt"></i> Bản nháp
      </a>
      <a href="./my-listings.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt
      </a>
      <a href="./my-listings.php?status=active" class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>">
        <i class="fa-solid fa-check-circle"></i> Hoạt động
      </a>
      <a href="./my-listings.php?status=inactive" class="filter-btn <?php echo $filterStatus === 'inactive' ? 'active' : ''; ?>">
        ⏸️ Tạm dừng
      </a>
      <a href="./my-listings.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        <i class="fa-solid fa-times-circle"></i> Bị từ chối
      </a>
    </div>
    
    <!-- Listings Grid -->
    <?php if (empty($listings)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="fa-solid fa-house"></i></div>
        <h3>Chưa có phòng nào</h3>
        <p>
          <?php if ($filterStatus): ?>
            Không có phòng với trạng thái "<?php echo htmlspecialchars($filterStatus); ?>"
          <?php else: ?>
            Bạn chưa đăng phòng nào. Hãy bắt đầu chia sẻ không gian của bạn!
          <?php endif; ?>
        </p>
        <a href="./create-listing.php" class="btn-empty-action">+ Đăng phòng đầu tiên</a>
      </div>
    <?php else: ?>
      <div class="listings-grid">
        <?php foreach ($listings as $listing): ?>
          <div class="listing-card <?php echo $listing['status'] === 'inactive' ? 'inactive-card' : ''; ?>">
            <a href="./listing-detail.php?id=<?php echo $listing['listing_id']; ?>" class="listing-image-link">
              <div class="listing-image">
                <?php if (!empty($listing['image_url'])): ?>
                  <?php
                  // Determine correct image path
                  $imagePath = $listing['image_url'];
                  if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
                    // Local path - add root path
                    $imagePath = '../../../' . $imagePath;
                  }
                  ?>
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                <?php else: ?>
                  <div class="no-image-placeholder">
                    <i class="fa-solid fa-image fa-3x"></i>
                  </div>
                <?php endif; ?>
              </div>
            </a>
            
            <div class="listing-content">
              <a href="./listing-detail.php?id=<?php echo $listing['listing_id']; ?>" class="listing-title-link">
                <div class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></div>
              </a>
              
              <div class="listing-info">
                <?php if ($listing['place_type_name']): ?>
                  <?php echo htmlspecialchars($listing['place_type_name']); ?> •
                <?php endif; ?>
                <?php echo $listing['image_count']; ?> ảnh
              </div>
              
              <div class="listing-price">
                <?php echo number_format($listing['price'], 0, ',', '.'); ?>₫<span>/đêm</span>
              </div>
              
              <div>
                <?php
                $statusClass = 'status-' . $listing['status'];
                $statusText = '';
                switch ($listing['status']) {
                  case 'draft': $statusText = '<i class="fa-solid fa-file-alt"></i> Bản nháp'; break;
                  case 'pending': $statusText = '<i class="fa-solid fa-hourglass-half"></i> Chờ duyệt'; break;
                  case 'active': $statusText = '<i class="fa-solid fa-check-circle"></i> Hoạt động'; break;
                  case 'inactive': $statusText = '⏸️ Tạm dừng'; break;
                  case 'rejected': $statusText = '<i class="fa-solid fa-times-circle"></i> Bị từ chối'; break;
                }
                ?>
                <span class="listing-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
              </div>
              
              <div class="listing-actions">
                <a href="./listing-detail.php?id=<?php echo $listing['listing_id']; ?>" class="btn-action btn-view">
                  👁️ Xem
                </a>
                
                <a href="./edit-listing.php?id=<?php echo $listing['listing_id']; ?>" class="btn-action btn-edit">
                  <i class="fa-solid fa-edit"></i> Sửa
                </a>
                
                <?php if ($listing['status'] === 'active' || $listing['status'] === 'inactive'): ?>
                  <button type="button" 
                          class="btn-action btn-toggle-status" 
                          data-listing-id="<?php echo $listing['listing_id']; ?>"
                          data-current-status="<?php echo $listing['status']; ?>">
                    <?php echo $listing['status'] === 'active' ? '👁️‍🗨️ Ẩn' : '👁️ Hiện'; ?>
                  </button>
                <?php endif; ?>
                
                <?php if ($listing['status'] === 'draft'): ?>
                  <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                    <button type="submit" class="btn-action btn-delete">
                      <i class="fa-solid fa-trash"></i> Xóa
                    </button>
                  </form>
                <?php endif; ?>
              </div>
              
              <?php if ($listing['status'] === 'rejected' && $listing['rejection_reason']): ?>
                <div class="rejection-reason">
                  <strong>Lý do:</strong> <?php echo htmlspecialchars($listing['rejection_reason']); ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Handle toggle listing status
    document.querySelectorAll('.btn-toggle-status').forEach(button => {
      button.addEventListener('click', async function() {
        const listingId = this.getAttribute('data-listing-id');
        const currentStatus = this.getAttribute('data-current-status');
        const actionText = currentStatus === 'active' ? 'ẩn' : 'hiện';
        
        if (!confirm(`Bạn có chắc muốn ${actionText} phòng này?`)) {
          return;
        }
        
        // Disable button and show loading
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> Đang xử lý...';
        
        try {
          const formData = new FormData();
          formData.append('listing_id', listingId);
          
          const response = await fetch('./toggle-listing-status.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Show success message
            alert(result.message);
            
            // Reload page to update UI
            window.location.reload();
          } else {
            alert('Lỗi: ' + result.message);
            this.disabled = false;
            this.innerHTML = originalText;
          }
        } catch (error) {
          console.error('Error:', error);
          alert('Có lỗi xảy ra. Vui lòng thử lại.');
          this.disabled = false;
          this.innerHTML = originalText;
        }
      });
    });
  </script>
</body>
</html>
