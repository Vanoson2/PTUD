<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Get search parameters
$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? 1;

// Calculate number of nights
$nights = 0;
if ($checkin && $checkout) {
  $checkinDate = strtotime($checkin);
  $checkoutDate = strtotime($checkout);
  $nights = round(($checkoutDate - $checkinDate) / (60 * 60 * 24));
}

// TODO: Fetch listings from database based on search criteria
// For now, using sample data
$listings = [
  [
    'id' => 1,
    'title' => 'Superior Family Room',
    'type' => 'Hotel room',
    'location' => 'Đà Nẵng',
    'image' => './public/img/home/DaNang.jpg',
    'guests' => 6,
    'bedrooms' => 4,
    'beds' => 1,
    'bathrooms' => 1,
    'amenities' => ['Kitchen', 'Wifi', 'Air conditioning'],
    'rating' => 4.84,
    'reviews' => 534,
    'price' => 150000
  ],
  [
    'id' => 2,
    'title' => 'Rainbow Plantation',
    'type' => 'Apartment',
    'location' => 'Đà Nẵng',
    'image' => './public/img/home/NhaTrang.jpg',
    'guests' => 3,
    'bedrooms' => 1,
    'beds' => 1,
    'bathrooms' => 1,
    'amenities' => ['Kitchen', 'Wifi', 'Air conditioning'],
    'rating' => 4.77,
    'reviews' => 838,
    'price' => 328000
  ],
  [
    'id' => 3,
    'title' => 'Junior Suite',
    'type' => 'Hotel room',
    'location' => 'Đà Nẵng',
    'image' => './public/img/home/Hue.jpg',
    'guests' => 3,
    'bedrooms' => 1,
    'beds' => 1,
    'bathrooms' => 1,
    'amenities' => ['Wifi', 'Air conditioning'],
    'rating' => 4.75,
    'reviews' => 463,
    'price' => 356000
  ],
  [
    'id' => 4,
    'title' => 'Solitude Pointe',
    'type' => 'Hotel room',
    'location' => 'Đà Nẵng',
    'image' => './public/img/home/Hanoi.jpg',
    'guests' => 2,
    'bedrooms' => 2,
    'beds' => 1,
    'bathrooms' => 1,
    'amenities' => ['Kitchen', 'Wifi', 'Washer', 'Air conditioning'],
    'rating' => 4.88,
    'reviews' => 147,
    'price' => 466000
  ],
  [
    'id' => 5,
    'title' => 'Harley Connection',
    'type' => 'Apartment',
    'location' => 'Đà Nẵng',
    'image' => './public/img/home/DaNang.jpg',
    'guests' => 4,
    'bedrooms' => 2,
    'beds' => 1,
    'bathrooms' => 1,
    'amenities' => ['Kitchen', 'Wifi', 'Washer', 'Air conditioning'],
    'rating' => 4.84,
    'reviews' => 534,
    'price' => 250000
  ],
];

$totalResults = count($listings);
?>

<?php include __DIR__ . '/view/partials/header.php'; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="./view/css/listListing.css?v=<?php echo time(); ?>">

<div class="list-container">
  <!-- Sidebar Filters -->
  <aside class="sidebar">
    <div class="filter-header">
      <button class="filter-toggle">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path d="M3 6a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
        </svg>
      </button>
    </div>

    <!-- Loại chỗ ở -->
    <div class="filter-section">
      <h3 class="filter-title">Loại chỗ ở</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="checkbox" name="type[]" value="khachsan">
          <span>Khách sạn</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="type[]" value="homestay">
          <span>Homestay</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="type[]" value="villa">
          <span>Villa</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="type[]" value="canho">
          <span>Căn hộ</span>
        </label>
      </div>
    </div>

    <!-- Khoảng giá -->
    <div class="filter-section">
      <h3 class="filter-title">Khoảng giá</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="checkbox" name="price[]" value="0-500000">
          <span>Dưới 500.000</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="price[]" value="500000-1000000">
          <span>500.000 - 1.000.000</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="price[]" value="1000000-1500000">
          <span>1.000.000 - 1.500.000</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="price[]" value="1500000+">
          <span>Trên 1.500.000</span>
        </label>
      </div>
    </div>

    <!-- Đánh giá -->
    <div class="filter-section">
      <h3 class="filter-title">Đánh giá</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="1">
          <span>1 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="2">
          <span>2 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="3">
          <span>3 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="4">
          <span>4 sao</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="rating[]" value="5">
          <span>5 sao</span>
        </label>
      </div>
    </div>

    <!-- Tiện nghi -->
    <div class="filter-section">
      <h3 class="filter-title">Tiện nghi</h3>
      <div class="filter-options">
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="wifi">
          <span>Wifi</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="pool">
          <span>Bể bơi</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="ac">
          <span>Hồ bơi</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="bbq">
          <span>Lò nướng BBQ</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="hottub">
          <span>Bồn tắm nước nóng</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="parking">
          <span>Chỗ đậu xe cùng</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="amenity[]" value="breakfast">
          <span>Sân ngoài trời</span>
        </label>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Search Header -->
    <div class="search-header">
      <div class="search-info">
        <h1 class="search-title"><?php echo $totalResults; ?>+ chỗ ở tại <?php echo htmlspecialchars($location); ?></h1>
        <div class="search-params">
          <span><?php echo date('d/m', strtotime($checkin)); ?> - <?php echo date('d/m', strtotime($checkout)); ?></span>
          <span>•</span>
          <span><?php echo $guests; ?> khách</span>
          <button class="btn-search-modify">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Listings Grid -->
    <div class="listings-grid">
      <?php foreach ($listings as $listing): ?>
        <article class="listing-card">
          <div class="listing-image">
            <img src="<?php echo $listing['image']; ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
            <button class="btn-favorite">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
              </svg>
            </button>
          </div>
          
          <div class="listing-content">
            <div class="listing-header">
              <span class="listing-type"><?php echo $listing['type']; ?> in <?php echo $listing['location']; ?></span>
              <div class="listing-rating">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span><?php echo $listing['rating']; ?></span>
                <span class="rating-count">(<?php echo $listing['reviews']; ?> reviews)</span>
              </div>
            </div>
            
            <h2 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h2>
            
            <div class="listing-details">
              <span><?php echo $listing['guests']; ?> guests</span>
              <span>•</span>
              <span><?php echo $listing['bedrooms']; ?> bedroom<?php echo $listing['bedrooms'] > 1 ? 's' : ''; ?></span>
              <span>•</span>
              <span><?php echo $listing['beds']; ?> bed<?php echo $listing['beds'] > 1 ? 's' : ''; ?></span>
              <span>•</span>
              <span><?php echo $listing['bathrooms']; ?> bath<?php echo $listing['bathrooms'] > 1 ? 's' : ''; ?></span>
            </div>
            
            <div class="listing-amenities">
              <?php foreach (array_slice($listing['amenities'], 0, 3) as $amenity): ?>
                <span><?php echo $amenity; ?></span>
              <?php endforeach; ?>
            </div>
            
            <div class="listing-footer">
              <div class="listing-price">
                <span class="price"><?php echo number_format($listing['price']); ?>,000₫</span>
                <span class="price-unit">/đêm</span>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button class="btn-page" disabled>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
      </button>
      
      <button class="btn-page active">1</button>
      <button class="btn-page">2</button>
      <button class="btn-page">3</button>
      <button class="btn-page">4</button>
      <button class="btn-page">5</button>
      <span class="page-dots">...</span>
      <button class="btn-page">30</button>
      
      <button class="btn-page">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>
  </main>
</div>

<?php include __DIR__ . '/view/partials/footer.php'; ?>
