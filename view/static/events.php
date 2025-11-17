<?php
session_start();
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../css/style.css">

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <h1 class="mb-4">Sự Kiện Du Lịch</h1>
      
      <div class="card mb-4">
        <div class="card-body bg-primary text-white">
          <h3>Lễ Hội Hoa Đà Lạt 2025</h3>
          <p class="mb-0"><i class="bi bi-calendar3"></i> 20-25/12/2025 • <i class="bi bi-geo-alt"></i> Đà Lạt, Lâm Đồng</p>
        </div>
        <img src="../../public/img/home/LamDong.jpg" class="card-img" alt="Đà Lạt">
        <div class="card-body">
          <p>Lễ hội hoa lớn nhất miền Bắc với hàng triệu bông hoa rực rỡ, các hoạt động văn hóa nghệ thuật đặc sắc.</p>
          <button class="btn btn-outline-primary">Xem chi tiết</button>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <span class="badge bg-success mb-2">Đang diễn ra</span>
              <h5 class="card-title">Festival Biển Nha Trang</h5>
              <p><i class="bi bi-calendar3"></i> 01-05/11/2025</p>
              <p><i class="bi bi-geo-alt"></i> Nha Trang, Khánh Hòa</p>
              <p class="card-text">Lễ hội biển với các hoạt động thể thao dưới nước, trình diễn pháo hoa...</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <span class="badge bg-info mb-2">Sắp diễn ra</span>
              <h5 class="card-title">Festival Áo Dài Huế</h5>
              <p><i class="bi bi-calendar3"></i> 15-18/11/2025</p>
              <p><i class="bi bi-geo-alt"></i> Huế, Thừa Thiên Huế</p>
              <p class="card-text">Tôn vinh văn hóa áo dài truyền thống Việt Nam...</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <span class="badge bg-warning mb-2">Sắp diễn ra</span>
              <h5 class="card-title">Ngày Hội Du Lịch Hà Nội</h5>
              <p><i class="bi bi-calendar3"></i> 01-03/12/2025</p>
              <p><i class="bi bi-geo-alt"></i> Hà Nội</p>
              <p class="card-text">Quảng bá du lịch thủ đô với nhiều ưu đãi hấp dẫn...</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <span class="badge bg-secondary mb-2">Sắp diễn ra</span>
              <h5 class="card-title">Lễ Hội Cà Phê Buôn Ma Thuột</h5>
              <p><i class="bi bi-calendar3"></i> 10-12/12/2025</p>
              <p><i class="bi bi-geo-alt"></i> Buôn Ma Thuột, Đắk Lắk</p>
              <p class="card-text">Lễ hội cà phê lớn nhất Tây Nguyên...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>