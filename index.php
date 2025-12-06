<?php
include_once __DIR__ . '/controller/cListing.php';
include_once __DIR__ . '/model/mConnect.php';
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
<link rel="stylesheet" href="./view/css/pages-home.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10ns/vn.js"></script>
<script defer src="./public/js/autocomplete.js"></script>
<script defer src="./public/js/guestscounter.js"></script>
<script defer src="./public/js/date-validation.js"></script>
<script defer src="./public/js/date-picker.js?v=<?php echo time(); ?>"></script>

<script>
// Handle redirect after login
document.addEventListener('DOMContentLoaded', function() {
  // Check if user just logged in successfully
  <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true): ?>
    // Clear the flag
    <?php unset($_SESSION['login_success']); ?>
    
    // Check for validated return URL
    const returnUrl = sessionStorage.getItem('validatedReturnUrl');
    if (returnUrl) {
      // Clear session storage
      sessionStorage.removeItem('returnUrl');
      sessionStorage.removeItem('validatedReturnUrl');
      
      // Redirect to the original page
      window.location.href = returnUrl;
    }
    // If no return URL, stay on homepage (current behavior)
  <?php endif; ?>
});
</script>

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
    <?php if (!empty($errors)): ?>
      <div class="search-form-wrapper">
        <div class="alert alert-danger" style="margin-bottom: 10px; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 8px;">
          <?php foreach ($errors as $error): ?>
            <div><?php echo htmlspecialchars($error); ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    
    <?php 
    $formAction = './view/user/traveller/listListings.php';
    include __DIR__ . '/view/partials/search-form.php'; 
    ?>
    
    <!-- Hero Title -->
    <div class="hero-content">
      <h1>ĐI KHẮP MUÔN NƠI<br>CHƠI KHÔNG LO PHÍ</h1>
    </div>
    
  </div>

  <!-- Places Section -->
  <h2 class="section-title-home">CÁC ĐỊA ĐIỂM DU LỊCH NỔI TIẾNG</h2>
  <div class="places-grid">
    <?php
    // Lấy top 4 tỉnh có nhiều booking nhất từ database
    $cListing = new cListing();
    $topProvinces = $cListing->cGetTopProvincesByBookings(4);
    
    // Function tự động map tên tỉnh với file ảnh
    function getProvinceImage($provinceName) {
      // Map tên tỉnh database -> tên file ảnh
      $imageMap = [
        'An Giang' => 'AnGiang.jpg',
        'Bắc Ninh' => 'BacNinh.png',
        'Cà Mau' => 'CaMau.jpg',
        'Cần Thơ' => 'CanTho.png',
        'Cao Bằng' => 'CaoBang.jpg',
        'Đắk Lắk' => 'DakLak.jpg',
        'Đà Nẵng' => 'DaNang.jpg',
        'Điện Biên' => 'DienBien.jpg',
        'Đồng Nai' => 'DongNai.jpg',
        'Đồng Tháp' => 'DongThap.png',
        'Gia Lai' => 'GiaLai.jpg',
        'Hải Phòng' => 'HaiPhong.jpg',
        'Hà Nội' => 'Hanoi.jpg',
        'Hà Tĩnh' => 'HaTinh.jpg',
        'Thừa Thiên Huế' => 'Hue.jpg',
        'Huế' => 'Hue.jpg',
        'Hưng Yên' => 'HungYen.jpg',
        'Khánh Hòa' => 'KhanhHoa.jpg',
        'Lai Châu' => 'LaiChau.jpg',
        'Lâm Đồng' => 'LamDong.jpg',
        'Lạng Sơn' => 'LangSon.jpg',
        'Lào Cai' => 'LaoCai.jpg',
        'Nghệ An' => 'NgheAn.jpg',
        'Ninh Bình' => 'NinhBinh.jpg',
        'Phú Thọ' => 'PhuTho.jpg',
        'Quảng Ngãi' => 'QuangNgai.jpg',
        'Quảng Ninh' => 'QuangNinh.jpg',
        'Quảng Trị' => 'QuangTri.jpg',
        'Sơn La' => 'SonLa.jpg',
        'Tây Ninh' => 'TayNinh.jpeg',
        'Thái Nguyên' => 'ThaiNguyen.png',
        'Thanh Hóa' => 'ThanhHoa.jpg',
        'Thành phố Hồ Chí Minh' => 'TP.HCM.jpg',
        'Hồ Chí Minh' => 'TP.HCM.jpg',
        'Tuyên Quang' => 'TuyenQuang.jpg',
        'Vĩnh Long' => 'VinhLong.jpg',
      ];
      
      // Nếu tìm thấy mapping, trả về đường dẫn file
      if (isset($imageMap[$provinceName])) {
        return './public/img/home/' . $imageMap[$provinceName];
      }
      
      // Nếu không tìm thấy, dùng ảnh mặc định
      return './public/img/home/DaNang.jpg';
    }
    
    // Nếu không có booking nào, hiển thị các tỉnh mặc định
    if (empty($topProvinces)) {
      $topProvinces = [
        ['province_name' => 'Đà Nẵng', 'province_full_name' => 'Thành phố Đà Nẵng', 'total_bookings' => 0, 'total_listings' => 0],
        ['province_name' => 'Khánh Hòa', 'province_full_name' => 'Tỉnh Khánh Hòa', 'total_bookings' => 0, 'total_listings' => 0],
        ['province_name' => 'Huế', 'province_full_name' => 'Thành phố Huế', 'total_bookings' => 0, 'total_listings' => 0],
        ['province_name' => 'Hà Nội', 'province_full_name' => 'Thành phố Hà Nội', 'total_bookings' => 0, 'total_listings' => 0],
      ];
    }
    
    foreach($topProvinces as $province){
      $provinceName = $province['province_name'];
      $provinceFullName = $province['province_full_name'];
      $totalBookings = $province['total_bookings'];
      $totalListings = $province['total_listings'];
      
      // Tự động lấy ảnh tương ứng với tỉnh
      $img = getProvinceImage($provinceName);
      
      // Tạo title viết hoa
      $title = mb_strtoupper($provinceName, 'UTF-8');
      
      // Hiển thị: "X chỗ ở - Đã có hơn Y đơn đặt"
      $countText = number_format($totalListings) . ' chỗ ở - Đã có hơn ' . number_format($totalBookings) . ' đơn đặt';
      
      // Tạo URL với tham số location, sử dụng ngày mặc định, thêm source=featured
      $searchUrl = "./view/user/traveller/listListings.php?source=featured&location=" . urlencode($provinceName) . 
                   "&checkin=" . $tomorrow . 
                   "&checkout=" . $dayAfterTomorrow . 
                   "&guests=1";
      
      echo "<a href='{$searchUrl}' class='place-card' style='text-decoration: none; color: inherit;'>";
      echo "<img src='{$img}' alt='{$title}'>";
      echo "<div class='place-card-content'>";
      echo "<div class='place-card-title'>{$title}</div>";
      echo "<div class='place-card-info'>{$countText}</div>";
      echo "</div>";
      echo "</a>";
    }
    ?>
  </div>
  <!-- Feature Cards -->
  <div class="feature-cards-grid">
    <?php
    // Định nghĩa 3 categories với amenity IDs và ảnh tương ứng
    $featureCategories = [
      [
        'title' => 'HÒA MÌNH CÙNG THIÊN NHIÊN',
        'amenity_ids' => [11, 12], // Cảnh biển (11) và Cảnh núi (12)
        'badge' => 'ĐIỂM ĐẾN ĐỘC ĐÁO',
        'image' => './public/img/home/BeautyScenery.jpg'
      ],
      [
        'title' => 'BBQ NGOÀI TRỜI',
        'amenity_ids' => [10], // BBQ ngoài trời (10)
        'badge' => null,
        'image' => './public/img/home/BBQ Outside.jpg'
      ],
      [
        'title' => 'CHO PHÉP THÚ CƯNG',
        'amenity_ids' => [21], // Cho phép thú cưng (21)
        'badge' => null,
        'image' => './public/img/home/AllowDog.jpg'
      ]
    ];
    
    foreach($featureCategories as $category) {
      $amenityIdsStr = implode(',', $category['amenity_ids']);
      
      // Query tìm listing có tiện nghi này với giá thấp nhất
      $p = new mConnect();
      $conn = $p->mMoKetNoi();
      
      if($conn) {
        $strSelect = "SELECT 
                        l.listing_id,
                        l.title,
                        l.price,
                        l.address,
                        li.file_url,
                        p.name as province_name,
                        w.name as ward_name
                     FROM listing l
                     INNER JOIN listing_amenity la ON l.listing_id = la.listing_id
                     LEFT JOIN listing_image li ON l.listing_id = li.listing_id AND li.is_cover = 1
                     LEFT JOIN wards w ON l.ward_code = w.code
                     LEFT JOIN provinces p ON w.province_code = p.code
                     WHERE l.status = 'active'
                       AND la.amenity_id IN ($amenityIdsStr)
                     GROUP BY l.listing_id
                     ORDER BY l.price ASC
                     LIMIT 1";
        
        $result = $conn->query($strSelect);
        
        if($result && $result->num_rows > 0) {
          $listing = $result->fetch_assoc();
          
          // Format giá
          $price = number_format($listing['price'], 0, ',', '.') . ' đ';
          
          // Tạo URL tìm kiếm với filter amenity
          $searchUrl = "./view/user/traveller/listListings.php?amenity=" . urlencode($amenityIdsStr) . 
                       "&checkin=" . $tomorrow . 
                       "&checkout=" . $dayAfterTomorrow . 
                       "&guests=1";
          
          // Encode URL để xử lý khoảng trắng và ký tự đặc biệt
          $imageUrl = str_replace(' ', '%20', $category['image']);
          
          echo "<a href='{$searchUrl}' class='feature-card' style='text-decoration: none; color: inherit;'>";
          echo "<img src='{$imageUrl}' alt='{$category['title']}'>";
          echo "<div class='overlay'></div>";
          if($category['badge']) {
            echo "<div class='feature-badge'>{$category['badge']}</div>";
          }
          echo "<div class='feature-content'>";
          echo "<div class='feature-title'>{$category['title']}</div>";
          echo "<div class='feature-price'>từ {$price}</div>";
          echo "</div>";
          echo "</a>";
        } else {
          // Nếu không tìm thấy listing, hiển thị ảnh category với thông báo
          // Encode URL để xử lý khoảng trắng và ký tự đặc biệt
          $imageUrl = str_replace(' ', '%20', $category['image']);
          
          echo "<div class='feature-card'>";
          echo "<img src='{$imageUrl}' alt='{$category['title']}'>";
          echo "<div class='overlay'></div>";
          if($category['badge']) {
            echo "<div class='feature-badge'>{$category['badge']}</div>";
          }
          echo "<div class='feature-content'>";
          echo "<div class='feature-title'>{$category['title']}</div>";
          echo "<div class='feature-price'>Đang cập nhật</div>";
          echo "</div>";
          echo "</div>";
        }
      }
    }
    ?>
  </div>

  <!-- Help Section -->
  <h2 class="section-title-home">CÓ THỂ HỮU ÍCH ĐỐI VỚI BẠN</h2>
  <div class="promo-cards-grid">
    <!-- Card 1 -->
    <div class="promo-card">
      <img src="./public/img/home/host.jpg" alt="Promo 1">
      <div class="overlay"></div>
      <div class="promo-content">
        <h3 class="promo-title">NHIỀU KHÁCH BIẾT ĐẾN<br>CHỖ Ở BẠN HƠN?</h3>
        <a href="./view/user/host/become-host.php" class="promo-btn" style="display: inline-block; text-decoration: none;">Trở thành đối tác với chúng tôi ngay!</a>
      </div>
    </div>
    <!-- Card 2 -->
    <div class="promo-card">
      <img src="./public/img/home/support.jpg" alt="Promo 2">
      <div class="overlay"></div>
      <div class="promo-content">
        <h3 class="promo-title">GẶP KHÓ KHĂN KHI<br>SỬ DỤNG HỆ THỐNG?</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="./view/user/support/create-ticket.php" class="promo-btn" style="text-decoration: none; display: inline-block;">
            Hãy phản hồi cho chúng tôi biết
          </a>
        <?php else: ?>
          <a href="./view/user/traveller/login.php" class="promo-btn" style="text-decoration: none; display: inline-block;">
            Đăng nhập để gửi phản hồi
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</section>
<?php include __DIR__ . '/view/partials/footer.php'; ?>
