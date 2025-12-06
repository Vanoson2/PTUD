<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect to login with return URL
  $returnUrl = urlencode($_SERVER['REQUEST_URI']);
  header("Location: ../login.php?returnUrl=$returnUrl");
  exit;
}

// Get parameters
$listingId = $_GET['listing_id'] ?? 0;
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? 1;

if (empty($listingId) || empty($checkin) || empty($checkout)) {
  header('Location: ../../index.php');
  exit;
}

// Include controllers
include_once(__DIR__ . '/../../../controller/cListing.php');
include_once(__DIR__ . '/../../../controller/cBooking.php');

// Get listing details
$cListing = new cListing();
$listing = $cListing->cGetListingDetail($listingId);

if (!$listing) {
  header('Location: ../../index.php');
  exit;
}

// Get listing rating and review count
include_once(__DIR__ . '/../../../model/mListing.php');
$mListing = new mListing();
$ratingInfo = $mListing->mGetListingRating($listingId);
$avgRating = $ratingInfo['avg_rating'] ?? 0;
$reviewCount = $ratingInfo['review_count'] ?? 0;

// Get user_id from session
$userId = $_SESSION['user_id'];

// Check 1: User có đơn đặt nào khác trùng ngày không?
$cBooking = new cBooking();
$userConflictResult = $cBooking->cCheckUserBookingConflict($userId, $checkin, $checkout, $listingId);
$hasUserConflict = false;
$conflictBooking = null;

if ($userConflictResult && $userConflictResult->num_rows > 0) {
  $hasUserConflict = true;
  $conflictBooking = $userConflictResult->fetch_assoc();
}

// Check 2: Listing còn trống không?
$listingAvailabilityResult = $cBooking->cCheckListingAvailability($listingId, $checkin, $checkout);
$isListingAvailable = true;
if ($listingAvailabilityResult && $listingAvailabilityResult->num_rows > 0) {
  $isListingAvailable = false;
}

// Tổng hợp: Chỉ cho đặt nếu cả 2 điều kiện đều OK
$canBook = !$hasUserConflict && $isListingAvailable;

// Get listing services
$servicesResult = $cListing->cGetListingServices($listingId);
$services = [];
if ($servicesResult && $servicesResult->num_rows > 0) {
  while ($serviceRow = $servicesResult->fetch_assoc()) {
    $services[] = $serviceRow;
  }
}

// Get first image
$imagesResult = $cListing->cGetListingImages($listingId);
$coverImage = null;
if ($imagesResult && is_array($imagesResult) && count($imagesResult) > 0) {
  $coverImage = $imagesResult[0]['file_url'];
}

// Calculate nights
$checkinDate = strtotime($checkin);
$checkoutDate = strtotime($checkout);
$nights = round(($checkoutDate - $checkinDate) / (60 * 60 * 24));

// Calculate total
$subtotal = $listing['price'] * $nights;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác nhận đơn đặt - WEGO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../../css/shared-style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../../css/components-header.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../../css/confirm-booking.css?v=<?php echo time(); ?>">
</head>
<body>
  <?php include(__DIR__ . '/../../partials/header.php'); ?>

  <div class="booking-confirm-container">
    <!-- Back button -->
    <a href="javascript:history.back()" class="btn btn-link px-0 mb-3">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>

    <h1 class="mb-4">XÁC NHẬN ĐƠN ĐẶT CỦA BẠN</h1>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <i class="fa-solid fa-exclamation-circle"></i>
      <strong>Lỗi!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
      <?php unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <?php if ($hasUserConflict): ?>
    <div class="alert alert-warning">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <strong>Bạn đã có đơn đặt khác trùng thời gian!</strong>
      <p class="mb-0 mt-2">
        Bạn có đơn đặt <strong><?php echo $conflictBooking['listing_title']; ?></strong> 
        từ <?php echo date('d/m/Y', strtotime($conflictBooking['check_in'])); ?> 
        đến <?php echo date('d/m/Y', strtotime($conflictBooking['check_out'])); ?> 
        (Mã: <?php echo $conflictBooking['code']; ?>).
        <br>Vui lòng chọn ngày khác hoặc hủy đơn cũ trước.
      </p>
    </div>
    <?php endif; ?>

    <?php if (!$isListingAvailable): ?>
    <div class="alert alert-danger">
      <i class="fa-solid fa-times-circle"></i>
      <strong>Chỗ ở này đã được đặt!</strong> 
      Vui lòng chọn ngày khác.
    </div>
    <?php endif; ?>

    <div class="row">
      <!-- Left column -->
      <div class="col-md-7">
        <h5>CHUYẾN ĐI CỦA BẠN</h5>
        
        <div class="mb-4">
          <div class="row">
            <div class="col-6">
              <label class="form-label fw-bold">NGÀY</label>
              <div class="border rounded p-2">
                <?php echo date('d/m/Y', $checkinDate); ?> - <?php echo date('d/m/Y', $checkoutDate); ?>
                <a href="javascript:history.back()" class="float-end text-decoration-none">
                  <i class="fa-solid fa-pen"></i>
                </a>
              </div>
            </div>
            <div class="col-6">
              <label class="form-label fw-bold">SỐ KHÁCH</label>
              <div class="border rounded p-2">
                <?php echo $guests; ?> khách
                <a href="javascript:history.back()" class="float-end text-decoration-none">
                  <i class="fa-solid fa-pen"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <?php if (count($services) > 0): ?>
        <h5 class="mt-4">DỊCH VỤ CÓ SẴN TẠI CHỖ Ở</h5>
        <p class="text-muted small">Chọn các dịch vụ bạn muốn sử dụng</p>
        
        <form id="bookingForm" method="POST" action="process-booking.php">
          <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
          <input type="hidden" name="checkin" value="<?php echo $checkin; ?>">
          <input type="hidden" name="checkout" value="<?php echo $checkout; ?>">
          <input type="hidden" name="guests" value="<?php echo $guests; ?>">
          <input type="hidden" name="nights" value="<?php echo $nights; ?>">
          <input type="hidden" name="listing_price" value="<?php echo $listing['price']; ?>">
          
          <?php foreach ($services as $service): ?>
          <div class="service-checkbox">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <input type="checkbox" 
                       class="me-3 service-item" 
                       name="services[]" 
                       value="<?php echo $service['service_id']; ?>"
                       data-price="<?php echo round($service['price']); ?>"
                       id="service_<?php echo $service['service_id']; ?>">
                <div>
                  <label for="service_<?php echo $service['service_id']; ?>" class="mb-0 fw-bold" style="cursor: pointer;">
                    <?php echo htmlspecialchars($service['name']); ?>
                  </label>
                  <?php if ($service['description']): ?>
                  <div class="small text-muted"><?php echo htmlspecialchars($service['description']); ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="fw-bold"><?php echo number_format($service['price'], 0, ',', '.'); ?> VND</div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
        <form id="bookingForm" method="POST" action="process-booking.php">
          <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
          <input type="hidden" name="checkin" value="<?php echo $checkin; ?>">
          <input type="hidden" name="checkout" value="<?php echo $checkout; ?>">
          <input type="hidden" name="guests" value="<?php echo $guests; ?>">
          <input type="hidden" name="nights" value="<?php echo $nights; ?>">
          <input type="hidden" name="listing_price" value="<?php echo $listing['price']; ?>">
        <?php endif; ?>

          <div class="form-check mb-3 mt-4">
            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
            <label class="form-check-label small" for="agreeTerms">
              Bằng cách nhấn nút bên dưới, tôi đồng ý với 
              <a href="/view/static/terms.php" target="_blank">Điều khoản & Điều kiện</a>, 
              <a href="/view/static/privacy.php" target="_blank">Chính sách bảo mật</a> và 
              <a href="/view/static/cancellation.php" target="_blank">Chính sách hủy đặt chỗ</a>.
            </label>
          </div>

          <button type="submit" 
                  class="btn btn-primary w-100 py-3 fw-bold"
                  <?php echo !$canBook ? 'disabled' : ''; ?>>
            <?php if ($hasUserConflict): ?>
              Bạn có đơn đặt trùng ngày
            <?php elseif (!$isListingAvailable): ?>
              Chỗ ở đã được đặt
            <?php else: ?>
              XÁC NHẬN
            <?php endif; ?>
          </button>
        </form>
      </div>

      <!-- Right column - Listing info -->
      <div class="col-md-5">
        <div class="listing-card">
          <div class="p-3">
            <div class="d-flex">
              <?php if ($coverImage): ?>
              <?php
              // Determine correct image path
              $imagePath = $coverImage;
              if (strpos($imagePath, 'http://') !== 0 && strpos($imagePath, 'https://') !== 0) {
                // Local path - add relative path
                $imagePath = '../../../' . $imagePath;
              }
              // else: Keep full URL as is (Pexels)
              ?>
              <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Listing" class="rounded me-3" style="width: 100px; height: 100px; object-fit: cover;">
              <?php else: ?>
              <img src="../../../public/img/placeholder_listing/placeholder1.jpg" alt="Listing" class="rounded me-3" style="width: 100px; height: 100px; object-fit: cover;">
              <?php endif; ?>
              <div class="flex-grow-1">
                <p class="text-muted small mb-1">Hotel room in Ueno</p>
                <h6 class="mb-2"><?php echo htmlspecialchars($listing['title']); ?></h6>
                <p class="small text-muted mb-2">
                  <?php echo $guests; ?> khách • <?php echo $nights; ?> đêm
                </p>
                <?php if ($avgRating > 0 && $reviewCount > 0): ?>
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-star text-warning"></i>
                  <span class="ms-1 fw-bold"><?php echo number_format($avgRating, 2); ?></span>
                  <span class="text-muted small ms-1">(<?php echo $reviewCount; ?> đánh giá)</span>
                </div>
                <?php else: ?>
                <div class="text-muted small">Chưa có đánh giá</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="price-breakdown">
              <h6>Chi tiết giá</h6>
              <div class="d-flex justify-content-between mb-2">
                <span id="pricePerNightText">
                  <?php echo number_format($listing['price'], 0, ',', '.'); ?> VND x <?php echo $nights; ?> đêm
                </span>
                <span id="subtotalAmount"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span>
              </div>
              <div id="servicesBreakdown"></div>
              <div class="d-flex justify-content-between mb-2 mt-3 pt-3 border-top">
                <strong>TỔNG TIỀN</strong>
                <span class="total-price" id="totalAmount"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span>
              </div>
            </div>
          </div>
        </div>

        <p class="small text-muted">
          Free cancellation until 3:00 PM on July 15, 2022. <a href="#">More info</a>
        </p>
      </div>
    </div>
  </div>

  <?php include(__DIR__ . '/../../partials/footer.php'); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../public/js/booking.js"></script>
  <script>
    // Pass data to JS
    const listingPrice = <?php echo $listing['price']; ?>;
    const nights = <?php echo $nights; ?>;
  </script>
</body>
</html>
