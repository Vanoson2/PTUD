<?php
session_start();
$rootPath = '../../../';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . $rootPath . 'view/user/login.php');
  exit();
}

// Check if user is an approved HOST
require_once __DIR__ . '/../../../controller/cHost.php';
$cHost = new cHost();
$isHost = $cHost->cIsUserHost($_SESSION['user_id']);

if (!$isHost) {
  header('Location: ' . $rootPath . 'view/user/host/become-host.php');
  exit();
}

// Get HOST info
$hostInfo = $cHost->cGetHostByUserId($_SESSION['user_id']);
if (!$hostInfo) {
  header('Location: ' . $rootPath . 'view/user/host/become-host.php');
  exit();
}

include_once(__DIR__ . "/../../../model/mListing.php");

$mListing = new mListing();
$hostId = $hostInfo['host_id'];

$listingId = intval($_GET['id'] ?? 0);

// Get listing details and verify ownership
$listing = $mListing->mGetListingById($listingId);

if (!$listing || $listing['host_id'] != $hostId) {
  header("Location: ./my-listings.php");
  exit();
}

// Get listing images
$images = $mListing->mGetListingImages($listingId);

// Get listing amenities
$amenities = $mListing->mGetListingAmenities($listingId);

// Get listing services
$services = $mListing->mGetListingServices($listingId);

include_once __DIR__ . '/../../partials/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết phòng - <?php echo htmlspecialchars($listing['title']); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $rootPath; ?>view/css/listing-detail-host.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="host-container">
  <!-- Main Content -->
  <main class="host-main">
    <div class="host-content">
        
        <!-- Header with back button -->
        <div class="page-header">
          <div class="header-left">
            <a href="./my-listings.php" class="btn-back">
              <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <div>
              <h1><?php echo htmlspecialchars($listing['title']); ?></h1>
              <p class="listing-id">ID: #<?php echo $listingId; ?></p>
            </div>
          </div>
          <div class="header-right">
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
              case 'inactive':
                $statusText = 'Tạm dừng';
                $statusIcon = '⏸️';
                break;
            }
            ?>
            <span class="status-badge status-<?php echo $statusClass; ?>">
              <?php echo $statusIcon . ' ' . $statusText; ?>
            </span>
          </div>
        </div>

        <!-- Quick Stats - Removed for now, will add later with proper booking stats -->

        <!-- Action Buttons -->
        <div class="action-buttons">
          <a href="./edit-listing.php?id=<?php echo $listingId; ?>" class="btn-action btn-edit">
            <i class="fas fa-edit"></i> Chỉnh sửa
          </a>
          <a href="../traveller/detailListing.php?id=<?php echo $listingId; ?>" class="btn-action btn-view" target="_blank">
            <i class="fas fa-eye"></i> Xem như khách
          </a>
        </div>

        <!-- Images -->
        <?php if (!empty($images)): ?>
        <div class="detail-section">
          <h2><i class="fas fa-images"></i> Hình ảnh (<?php echo count($images); ?>)</h2>
          <div class="images-grid">
            <?php foreach ($images as $image): ?>
              <?php
              // Determine correct image path
              $imagePath = $image['file_url'];
              if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
                $imagePath = $rootPath . $imagePath;
              }
              ?>
              <div class="image-item <?php echo $image['is_cover'] ? 'is-cover' : ''; ?>">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Listing image">
                <?php if ($image['is_cover']): ?>
                  <span class="cover-badge"><i class="fas fa-star"></i> Ảnh bìa</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Basic Info -->
        <div class="detail-section">
          <h2><i class="fas fa-info-circle"></i> Thông tin cơ bản</h2>
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label"><i class="fas fa-home"></i> Loại chỗ ở:</span>
              <span class="info-value"><?php echo htmlspecialchars($listing['place_type_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label"><i class="fas fa-users"></i> Sức chứa:</span>
              <span class="info-value"><?php echo $listing['capacity']; ?> người</span>
            </div>
            <div class="info-item">
              <span class="info-label"><i class="fas fa-tag"></i> Giá mỗi đêm:</span>
              <span class="info-value price-large"><?php echo number_format($listing['price'], 0, ',', '.'); ?>đ</span>
            </div>
            <div class="info-item">
              <span class="info-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</span>
              <span class="info-value"><?php echo htmlspecialchars($listing['address']); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label"><i class="fas fa-calendar-plus"></i> Ngày tạo:</span>
              <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($listing['created_at'])); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label"><i class="fas fa-sync-alt"></i> Cập nhật lần cuối:</span>
              <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($listing['updated_at'])); ?></span>
            </div>
          </div>
        </div>

        <!-- Description -->
        <?php if (!empty($listing['description'])): ?>
        <div class="detail-section">
          <h2><i class="fas fa-align-left"></i> Mô tả</h2>
          <div class="description-box">
            <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Amenities -->
        <?php if (!empty($amenities)): ?>
        <div class="detail-section">
          <h2><i class="fas fa-concierge-bell"></i> Tiện nghi</h2>
          <div class="amenities-grid">
            <?php foreach ($amenities as $amenity): ?>
              <div class="amenity-item">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($amenity['name']); ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Services -->
        <?php if (!empty($services) && $services->num_rows > 0): ?>
        <div class="detail-section">
          <h2><i class="fas fa-bell"></i> Dịch vụ thêm (<?php echo $services->num_rows; ?>)</h2>
          <div class="services-list">
            <?php while($service = $services->fetch_assoc()): ?>
              <div class="service-item">
                <div class="service-header">
                  <div class="service-name">
                    <i class="fas fa-star"></i>
                    <?php echo htmlspecialchars($service['name']); ?>
                  </div>
                  <div class="service-price">
                    <?php echo number_format($service['price'], 0, ',', '.'); ?>đ
                  </div>
                </div>
                <?php if (!empty($service['description'])): ?>
                  <div class="service-desc">
                    <?php echo htmlspecialchars($service['description']); ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Rejection Reason (if rejected) -->
        <?php if ($listing['status'] === 'rejected' && !empty($listing['rejection_reason'])): ?>
        <div class="detail-section rejection-box">
          <h2><i class="fas fa-exclamation-triangle"></i> Lý do từ chối</h2>
          <div class="rejection-content">
            <?php echo nl2br(htmlspecialchars($listing['rejection_reason'])); ?>
          </div>
          <div class="rejection-action">
            <a href="./edit-listing.php?id=<?php echo $listingId; ?>" class="btn-action btn-edit">
              <i class="fas fa-edit"></i> Chỉnh sửa để gửi lại
            </a>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </main>
</div>

<?php include_once __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
