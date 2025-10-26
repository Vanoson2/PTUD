<!-- Search Form Component -->
<?php
// Get default values
$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? date('Y-m-d', strtotime('+1 day'));
$checkout = $_GET['checkout'] ?? date('Y-m-d', strtotime('+2 days'));
$guests = $_GET['guests'] ?? ($_SESSION['guests'] ?? 1);

// Form action - có thể customize từ bên ngoài
if (!isset($formAction)) {
  $formAction = './view/user/traveller/listListings.php';
}

// Form wrapper class - có thể customize
if (!isset($formWrapperClass)) {
  $formWrapperClass = 'search-form-wrapper';
}
?>

<div class="<?php echo $formWrapperClass; ?>">
  <form action="<?php echo $formAction; ?>" method="GET" class="search-form" id="searchForm">
    <!-- Địa điểm -->
    <div class="search-field location">
      <label>Địa điểm</label>
      <input type="text" 
             name="location" 
             id="locationInput" 
             placeholder="Bạn muốn đi đâu?" 
             value="<?php echo htmlspecialchars($location); ?>" 
             autocomplete="off" 
             required />
    </div>
    
    <!-- Check in -->
    <div class="search-field date">
      <label>Check in</label>
      <input type="text" 
             name="checkin" 
             id="checkin" 
             placeholder="dd/mm/yyyy" 
             value="<?php echo $checkin; ?>" 
             readonly 
             required />
    </div>
    
    <!-- Check out -->
    <div class="search-field date">
      <label>Check out</label>
      <input type="text" 
             name="checkout" 
             id="checkout" 
             placeholder="dd/mm/yyyy" 
             value="<?php echo $checkout; ?>" 
             readonly 
             required />
    </div>
    
    <!-- Số khách -->
    <div class="search-field guests">
      <label>Số khách</label>
      <div class="guest-counter" id="guestCounter">
        <?php $g = (int)$guests; ?>
        <button type="button" 
                class="btn-guest minus" 
                aria-label="Giảm" 
                <?php echo $g <= 1 ? 'disabled' : ''; ?>>−</button>
        <input type="number" 
               name="guests" 
               id="guestsInput" 
               class="guest-input" 
               value="<?php echo $g; ?>" 
               min="1" 
               max="10" 
               readonly />
        <button type="button" 
                class="btn-guest plus" 
                aria-label="Tăng" 
                <?php echo $g >= 10 ? 'disabled' : ''; ?>>+</button>
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
