<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
  header("Location: ./login.php");
  exit();
}

include_once(__DIR__ . "/../../controller/cAdmin.php");
include_once(__DIR__ . "/../../model/mListing.php");

$cAdmin = new cAdmin();
$mListing = new mListing();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

$listingId = intval($_GET['id'] ?? 0);
$successMessage = '';
$errorMessage = '';

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'approve') {
    $result = $cAdmin->cApproveListing($listingId, $adminId);
    if ($result['success']) {
      $successMessage = $result['message'];
    } else {
      $errorMessage = $result['message'];
    }
  } elseif ($action === 'reject') {
    $reason = trim($_POST['reason'] ?? '');
    $result = $cAdmin->cRejectListing($listingId, $adminId, $reason);
    if ($result['success']) {
      $successMessage = $result['message'];
    } else {
      $errorMessage = $result['message'];
    }
  }
}

// Get listing details
$listing = $mListing->mGetListingById($listingId);

if (!$listing) {
  header("Location: ./listings.php");
  exit();
}

// Get listing images
$images = $mListing->mGetListingImages($listingId);

// Get listing amenities
$amenities = $mListing->mGetListingAmenities($listingId);

// Get listing services
$services = $mListing->mGetListingServices($listingId);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết phòng #<?php echo $listingId; ?> - WeGo Admin</title>
  <link rel="stylesheet" href="../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin-listings.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-shield-alt"></i>
      <h2>Quản trị</h2>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Tổng quan</span>
      </a>
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <a href="listings.php" class="active">
        <i class="fas fa-building"></i>
        <span>Quản lý Phòng</span>
      </a>
      <a href="support.php">
        <i class="fas fa-headset"></i>
        <span>Hỗ trợ khách hàng</span>
      </a>
      <a href="amenities-services.php">
        <i class="fas fa-cog"></i>
        <span>Tiện nghi & Dịch vụ</span>
      </a>
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="page-title">
      <h1>
        <i class="fas fa-building"></i>
        Chi tiết phòng #<?php echo $listingId; ?>
      </h1>
      <a href="listings.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
      </a>
    </div>
    
    <div class="container mt-5">

    <!-- Messages -->
    <?php if ($successMessage): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Listing Detail -->
    <div class="detail-container">
      <div class="detail-header">
        <div>
          <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
          <p class="listing-id">Listing ID: #<?php echo $listingId; ?></p>
        </div>
        <?php
        $statusClass = $listing['status'];
        $statusText = '';
        $statusIcon = '';
        switch ($listing['status']) {
          case 'pending':
            $statusText = 'Chờ duyệt';
            $statusIcon = '⏳';
            break;
          case 'active':
            $statusText = 'Hoạt động';
            $statusIcon = '✅';
            break;
          case 'rejected':
            $statusText = 'Từ chối';
            $statusIcon = '❌';
            break;
          case 'draft':
            $statusText = 'Bản nháp';
            $statusIcon = '📝';
            break;
        }
        ?>
        <span class="status-badge-large <?php echo $statusClass; ?>">
          <?php echo $statusIcon . ' ' . $statusText; ?>
        </span>
      </div>

      <!-- Images -->
      <?php if (!empty($images)): ?>
        <div class="detail-section">
          <h3>📷 Hình ảnh</h3>
          <div class="images-grid">
            <?php foreach ($images as $image): ?>
              <?php
              // Determine correct image path
              $imagePath = $image['file_url'];
              if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
                // Local path - add relative path from admin folder
                $imagePath = '../../' . $imagePath;
              }
              // else: Keep full URL as is (Pexels)
              ?>
              <div class="image-item <?php echo $image['is_cover'] ? 'is-cover' : ''; ?>">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Listing image">
                <?php if ($image['is_cover']): ?>
                  <span class="cover-badge">⭐ Ảnh bìa</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Basic Info -->
      <div class="detail-section">
        <h3>📝 Thông tin cơ bản</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Loại chỗ ở:</span>
            <span class="info-value"><?php echo htmlspecialchars($listing['place_type_name'] ?? 'N/A'); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Sức chứa:</span>
            <span class="info-value"><?php echo $listing['capacity']; ?> người</span>
          </div>
          <div class="info-item">
            <span class="info-label">Giá mỗi đêm:</span>
            <span class="info-value price-large"><?php echo number_format($listing['price'], 0, ',', '.'); ?> đ</span>
          </div>
          <div class="info-item">
            <span class="info-label">Địa chỉ:</span>
            <span class="info-value"><?php echo htmlspecialchars($listing['address']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Ngày tạo:</span>
            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($listing['created_at'])); ?></span>
          </div>
        </div>
      </div>

      <!-- Description -->
      <?php if (!empty($listing['description'])): ?>
        <div class="detail-section">
          <h3>📄 Mô tả</h3>
          <p class="description"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
        </div>
      <?php endif; ?>

      <!-- Amenities -->
      <?php if (!empty($amenities)): ?>
        <div class="detail-section">
          <h3>✨ Tiện nghi</h3>
          <div class="amenities-list">
            <?php foreach ($amenities as $amenity): ?>
              <span class="amenity-tag">
                • <?php echo htmlspecialchars($amenity['name']); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Services -->
      <?php if (!empty($services) && $services->num_rows > 0): ?>
        <div class="detail-section">
          <h3>🛎️ Dịch vụ thêm</h3>
          <div class="services-list">
            <?php while($service = $services->fetch_assoc()): ?>
              <div class="service-item-admin">
                <div class="service-name-admin">
                  <i class="fa-solid fa-concierge-bell"></i>
                  <?php echo htmlspecialchars($service['name']); ?>
                </div>
                <?php if (!empty($service['description'])): ?>
                  <div class="service-desc-admin">
                    <?php echo htmlspecialchars($service['description']); ?>
                  </div>
                <?php endif; ?>
                <div class="service-price-admin">
                  <?php echo number_format($service['price'], 0, ',', '.'); ?>đ
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Rejection Reason (if rejected) -->
      <?php if ($listing['status'] === 'rejected' && !empty($listing['rejection_reason'])): ?>
        <div class="detail-section rejection-reason">
          <h3>❌ Lý do từ chối</h3>
          <p><?php echo nl2br(htmlspecialchars($listing['rejection_reason'])); ?></p>
        </div>
      <?php endif; ?>

      <!-- Action Buttons -->
      <?php if ($listing['status'] === 'pending'): ?>
        <div class="detail-section action-section">
          <h3>⚙️ Thao tác</h3>
          <div class="action-buttons">
            <form method="POST" style="display: inline-block;">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn-approve" onclick="return confirm('Bạn có chắc muốn phê duyệt phòng này?')">
                ✅ Phê duyệt
              </button>
            </form>
            
            <button type="button" class="btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
              ❌ Từ chối
            </button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">❌ Từ chối phòng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="reject">
            <label for="reason" class="form-label">Lý do từ chối: <span style="color: red;">*</span></label>
            <textarea class="form-control" id="reason" name="reason" rows="4" 
                      placeholder="Nhập lý do từ chối..." required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  </main>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
