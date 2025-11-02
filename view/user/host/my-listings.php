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
  <link rel="stylesheet" href="../../../view/css/my-listings.css">
</head>
<body>
  <div class="host-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1>🏡 Quản lý phòng của bạn</h1>
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
        📋 Tất cả
      </a>
      <a href="./my-listings.php?status=draft" class="filter-btn <?php echo $filterStatus === 'draft' ? 'active' : ''; ?>">
        📝 Bản nháp
      </a>
      <a href="./my-listings.php?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
        ⏳ Chờ duyệt
      </a>
      <a href="./my-listings.php?status=active" class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>">
        ✅ Hoạt động
      </a>
      <a href="./my-listings.php?status=inactive" class="filter-btn <?php echo $filterStatus === 'inactive' ? 'active' : ''; ?>">
        ⏸️ Tạm dừng
      </a>
      <a href="./my-listings.php?status=rejected" class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">
        ❌ Bị từ chối
      </a>
    </div>
    
    <!-- Listings Grid -->
    <?php if (empty($listings)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🏠</div>
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
          <div class="listing-card">
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
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
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
                  case 'draft': $statusText = '📝 Bản nháp'; break;
                  case 'pending': $statusText = '⏳ Chờ duyệt'; break;
                  case 'active': $statusText = '✅ Hoạt động'; break;
                  case 'inactive': $statusText = '⏸️ Tạm dừng'; break;
                  case 'rejected': $statusText = '❌ Bị từ chối'; break;
                }
                ?>
                <span class="listing-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
              </div>
              
              <div class="listing-actions">
                <a href="./edit-listing.php?id=<?php echo $listing['listing_id']; ?>" class="btn-action btn-edit">
                  ✏️ Sửa
                </a>
                
                <?php if ($listing['status'] === 'draft'): ?>
                  <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                    <button type="submit" class="btn-action btn-delete">
                      🗑️ Xóa
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
</body>
</html>
