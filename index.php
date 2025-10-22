<?php
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
?>

<?php include __DIR__ . '/view/partials/header.php'; ?>

<section class="container mt-4">
  <!-- Hero Section with Search -->
  <div class="hero-section">
    <!-- Background Video -->
    <div class="hero-video">
      <iframe 
        src="https://www.youtube.com/embed/k8m0SaGQ_1c?autoplay=1&mute=1&controls=0&loop=1&playlist=k8m0SaGQ_1c&modestbranding=1&showinfo=0&rel=0&iv_load_policy=3&playsinline=1" 
        title="Background Video"
        frameborder="0"
        allow="autoplay; encrypted-media; picture-in-picture"
        allowfullscreen
      ></iframe>
    </div>
    <div class="overlay"></div>
    
    <!-- Search Form -->
    <div class="search-form-wrapper">
  <form action="index.php" method="GET" class="search-form">
        <!-- Địa điểm -->
        <div class="search-field location">
          <label>Địa điểm</label>
          <input type="text" name="location" placeholder="Bạn muốn đi đâu?" value="" />
        </div>
        <!-- Check in -->
        <div class="search-field date">
          <label>Check in</label>
          <input type="date" name="checkin" id="checkin" value="<?php echo $tomorrow; ?>" min="<?php echo $today; ?>" />
        </div>
        <!-- Check out -->
        <div class="search-field date">
          <label>Check out</label>
          <input type="date" name="checkout" id="checkout" value="<?php echo $dayAfterTomorrow; ?>" min="<?php echo $today; ?>" />
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
    $places = [
      ['title'=>'ĐÀ NẴNG','img'=>'./public/img/home/DaNang.jpg','count'=>'2,345 properties','dist'=>'7.6 miles away'],
      ['title'=>'NHA TRANG','img'=>'./public/img/home/NhaTrang.jpg','count'=>'4,158 properties','dist'=>'3.2 miles away'],
      ['title'=>'HUẾ','img'=>'./public/img/home/Hue.jpg','count'=>'4,567 properties','dist'=>'8.1 miles away'],
      ['title'=>'HÀ NỘI','img'=>'./public/img/home/Hanoi.jpg','count'=>'6,279 properties','dist'=>'6.0 miles away'],
    ];
    foreach($places as $p){
      echo "<div class='place-card'>";
      echo "<img src='{$p['img']}' alt='{$p['title']}'>";
      echo "<div class='place-card-content'>";
      echo "<div class='place-card-title'>{$p['title']}</div>";
      echo "<div class='place-card-info'>{$p['count']} · {$p['dist']}</div>";
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
        <button class="promo-btn">Đăng ký thành hoạt ngày!</button>
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
