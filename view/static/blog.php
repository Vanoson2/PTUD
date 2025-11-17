<?php
session_start();
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../css/style.css">

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <h1 class="mb-4">Blog Du Lịch</h1>
      
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <img src="../../public/img/home/DaNang.jpg" class="card-img-top" alt="Đà Nẵng">
            <div class="card-body">
              <span class="badge bg-primary mb-2">Điểm đến</span>
              <h5 class="card-title">Top 10 Điểm Du Lịch Đà Nẵng Không Thể Bỏ Qua</h5>
              <p class="card-text text-muted">
                Khám phá những địa điểm tuyệt vời nhất tại thành phố đáng sống bậc nhất Việt Nam...
              </p>
              <small class="text-muted">15/10/2025 • 5 phút đọc</small>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <img src="../../public/img/home/Hanoi.jpg" class="card-img-top" alt="Hà Nội">
            <div class="card-body">
              <span class="badge bg-success mb-2">Ẩm thực</span>
              <h5 class="card-title">Khám Phá Ẩm Thực Phố Cổ Hà Nội</h5>
              <p class="card-text text-muted">
                Hành trình khám phá những món ăn đặc sản không thể bỏ lỡ khi đến Hà Nội...
              </p>
              <small class="text-muted">12/10/2025 • 7 phút đọc</small>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <img src="../../public/img/home/NhaTrang.jpg" class="card-img-top" alt="Nha Trang">
            <div class="card-body">
              <span class="badge bg-info mb-2">Kinh nghiệm</span>
              <h5 class="card-title">Du Lịch Nha Trang Tiết Kiệm Với 5 Triệu</h5>
              <p class="card-text text-muted">
                Hướng dẫn chi tiết cách tận hưởng kỳ nghỉ tuyệt vời tại Nha Trang với ngân sách hạn chế...
              </p>
              <small class="text-muted">08/10/2025 • 6 phút đọc</small>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <img src="../../public/img/home/Hue.jpg" class="card-img-top" alt="Huế">
            <div class="card-body">
              <span class="badge bg-warning mb-2">Văn hóa</span>
              <h5 class="card-title">Tìm Hiểu Văn Hóa Cố Đô Huế</h5>
              <p class="card-text text-muted">
                Khám phá lịch sử, văn hóa và kiến trúc độc đáo của kinh đô xưa...
              </p>
              <small class="text-muted">05/10/2025 • 8 phút đọc</small>
            </div>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-4">
        <button class="btn btn-outline-primary">Xem thêm bài viết</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>