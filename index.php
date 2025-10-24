<?php
include_once __DIR__ . '/controller/cListing.php';
// Start session for server-side state (PHP only)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Initialize guests in session
if (!isset($_SESSION['guests'])) {
  $_SESSION['guests'] = 1; // default
}
// Handle plus/minus actions for guests without JavaScript
if (isset($_GET['guests_action'])) {
  $action = $_GET['guests_action'];
  $val = (int)($_SESSION['guests'] ?? 1);
  if ($action === 'inc') {
    $val = min(10, $val + 1);
  } elseif ($action === 'dec') {
    $val = max(1, $val - 1);
  }
  $_SESSION['guests'] = $val;
  // PRG pattern to avoid resubmission and keep UI clean
  header('Location: index.php');
  exit;
}
// Calculate default dates
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
$maxDate = date('Y-m-d', strtotime('+3 months')); // Max 3 months from today
// Validation errors
$errors = [];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['checkin']) || isset($_GET['checkout']))) {
  $checkin = $_GET['checkin'] ?? '';
  $checkout = $_GET['checkout'] ?? '';
  $location = $_GET['location'] ?? '';
  // Validate check-in date
  if (empty($checkin)) {
    $errors[] = 'Vui lòng chọn ngày check-in';
  } else {
    $checkinDate = strtotime($checkin);
    $todayTime = strtotime($today);
    $maxDateTime = strtotime($maxDate);
    if ($checkinDate < $todayTime) {
      $errors[] = 'Ngày check-in không được trước ngày hôm nay';
    } elseif ($checkinDate > $maxDateTime) {
      $errors[] = 'Ngày check-in chỉ được đặt trong vòng 3 tháng kể từ hôm nay';
    }
  }
  
  // Validate check-out date
  if (empty($checkout)) {
    $errors[] = 'Vui lòng chọn ngày check-out';
  } else {
    $checkoutDate = strtotime($checkout);
    
    if (!empty($checkin)) {
      $checkinDate = strtotime($checkin);
      
      if ($checkoutDate <= $checkinDate) {
        $errors[] = 'Ngày check-out phải sau ngày check-in';
      } else {
        // Calculate number of nights
        $daysDiff = ($checkoutDate - $checkinDate) / (60 * 60 * 24);
        
        if ($daysDiff > 30) {
          $errors[] = 'Tổng số ngày lưu trú tối đa là 30 ngày';
        }
      }
    }
  }
  
  // Validate location
  if (empty($location)) {
    $errors[] = 'Vui lòng chọn địa điểm';
  }
  
  if (empty($errors)) {
    $_SESSION['search_success'] = 'Tìm kiếm thành công!';
  }
}
?>

<?php include __DIR__ . '/view/partials/header.php'; ?>

<!-- Page-specific CSS and JavaScript for Home page -->
<link rel="stylesheet" href="./view/css/home.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10ns/vn.js"></script>
<script defer src="./public/js/autocomplete.js"></script>
<script defer src="./public/js/guestscounter.js"></script>
<script defer src="./public/js/date-validation.js"></script>
<script defer src="./public/js/date-picker.js?v=<?php echo time(); ?>"></script>

<section class="container mt-4">
  <!-- Hero Section with Search -->
  <div class="hero-section">
    <!-- Background Video -->
    <div class="hero-video">
      <iframe 
        src="https://www.youtube-nocookie.com/embed/k8m0SaGQ_1c?autoplay=1&mute=1&controls=0&loop=1&playlist=k8m0SaGQ_1c&modestbranding=1&showinfo=0&rel=0&iv_load_policy=3&playsinline=1" 
        title="Background Video"
        frameborder="0"
        allow="autoplay; encrypted-media; picture-in-picture"
        allowfullscreen
      ></iframe>
    </div>
    <div class="overlay"></div>
    
    <!-- Search Form -->
    <div class="search-form-wrapper">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 10px; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 8px;">
          <?php foreach ($errors as $error): ?>
            <div><?php echo htmlspecialchars($error); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <form action="./view/user/traveller/listListings.php" method="GET" class="search-form" id="searchForm">
        <!-- Địa điểm -->
        <div class="search-field location">
          <label>Địa điểm</label>
          <input type="text" name="location" id="locationInput" placeholder="Bạn muốn đi đâu?" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" autocomplete="off" required />
        </div>
        <!-- Check in -->
        <div class="search-field date">
          <label>Check in</label>
          <input type="text" name="checkin" id="checkin" placeholder="dd/mm/yyyy" value="<?php echo $tomorrow; ?> readonly" required />
        </div>
        <!-- Check out -->
        <div class="search-field date">
          <label>Check out</label>
          <input type="text" name="checkout" id="checkout" placeholder="dd/mm/yyyy" value="<?php echo $dayAfterTomorrow; ?> readonly" required />
        </div>
        <!-- Số khách -->
        <div class="search-field guests">
          <label>Số khách</label>
          <div class="guest-counter" id="guestCounter">
            <?php $g = (int)($_SESSION['guests'] ?? 1); ?>
            <button type="button" class="btn-guest minus" aria-label="Giảm" <?php echo $g <= 1 ? 'disabled' : ''; ?>>−</button>
            <input type="number" name="guests" id="guestsInput" class="guest-input" value="<?php echo $g; ?>" min="1" max="10" readonly />
            <button type="button" class="btn-guest plus" aria-label="Tăng" <?php echo $g >= 10 ? 'disabled' : ''; ?>>+</button>
          </div>
        </div>
        <!-- Search Button -->
        <button type="submit" class="search-btn">
          <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
          </svg>
        </button>
      </form>
    </div>
    
    <!-- Hero Title -->
    <div class="hero-content">
      <h1>ĐI KHẮP MUÔN NƠI<br>CHƠI KHÔNG LO PHÍ</h1>
    </div>
    
  </div>

  <!-- Places Section -->
  <h2 class="section-title-home">CÁC ĐỊA ĐIỂM DU LỊCH NỔI TIẾNG</h2>
  <div class="places-grid">
    <?php
    // Sử dụng controller để đếm số lượng listing thực tế từ database
    $cListing = new cListing();
    
    $places = [
      [
        'title' => 'ĐÀ NẴNG',
        'img' => './public/img/home/DaNang.jpg',
        'province' => 'Đà Nẵng'
      ],
      [
        'title' => 'NHA TRANG',
        'img' => './public/img/home/NhaTrang.jpg',
        'province' => 'Khánh Hòa'
      ],
      [
        'title' => 'HUẾ',
        'img' => './public/img/home/Hue.jpg',
        'province' => 'Huế'
      ],
      [
        'title' => 'HÀ NỘI',
        'img' => './public/img/home/Hanoi.jpg',
        'province' => 'Hà Nội'
      ],
    ];
    
    foreach($places as $p){
      $count = $cListing->cCountListingByProvince($p['province']);
      $countText = number_format($count) . ' chỗ ở';
      
      echo "<div class='place-card'>";
      echo "<img src='{$p['img']}' alt='{$p['title']}'>";
      echo "<div class='place-card-content'>";
      echo "<div class='place-card-title'>{$p['title']}</div>";
      echo "<div class='place-card-info'>{$countText}</div>";
      echo "</div>";
      echo "</div>";
    }
    ?>
  </div>
  <!-- Feature Cards -->
  <div class="feature-cards-grid">
    <!-- Card 1 -->
    <div class="feature-card">
      <img src="./public/img/home/NhaTrang.jpg" alt="f1">
      <div class="overlay"></div>
      <div class="feature-content">
        <div class="feature-title">KỲ NGHỈ NGOÀI TRỜI</div>
        <div class="feature-price">từ 679$ đ</div>
      </div>
    </div>
    <!-- Card 2 -->
    <div class="feature-card">
      <img src="./public/img/home/Hue.jpg" alt="f2">
      <div class="overlay"></div>
      <div class="feature-badge">ĐIỂM ĐẾN ĐỘC ĐÁO</div>
      <div class="feature-content">
        <div class="feature-title">TOÀN BỘ CẢNH ĐẸP</div>
        <div class="feature-price">từ 888$ đ</div>
      </div>
    </div>
    <!-- Card 3 -->
    <div class="feature-card">
      <img src="./public/img/home/Hanoi.jpg" alt="f3">
      <div class="overlay"></div>
      <div class="feature-content">
        <div class="feature-title">CHỈ PHẢI THỎA CƯỜI</div>
        <div class="feature-price">từ 945$ đ</div>
      </div>
    </div>
  </div>

  <!-- Help Section -->
  <h2 class="section-title-home">CÓ THỂ HỮU ÍCH ĐỐI VỚI BẠN</h2>
  <div class="promo-cards-grid">
    <!-- Card 1 -->
    <div class="promo-card">
      <img src="./public/img/home/DaNang.jpg" alt="Promo 1">
      <div class="overlay"></div>
      <div class="promo-content">
        <h3 class="promo-title">NHIỀU KHÁCH BIẾT ĐẾN<br>CHỖ Ở BẠN HƠN?</h3>
        <button class="promo-btn">Trở thành đối tác với chúng tôi ngay!</button>
      </div>
    </div>
    <!-- Card 2 -->
    <div class="promo-card">
      <img src="./public/img/home/NhaTrang.jpg" alt="Promo 2">
      <div class="overlay"></div>
      <div class="promo-content">
        <h3 class="promo-title">GẶP KHÓ KHĂN KHI<br>SỬ DỤNG HỆ THỐNG?</h3>
        <button class="promo-btn">Hãy phản hồi cho chúng tôi biết</button>
      </div>
    </div>
  </div>

</section>
<?php include __DIR__ . '/view/partials/footer.php'; ?>
