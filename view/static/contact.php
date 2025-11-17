<?php
session_start();
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../css/style.css">

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <h1 class="mb-4">Liên Hệ Với Chúng Tôi</h1>
      
      <div class="row mb-5">
        <div class="col-md-4 mb-3">
          <div class="card text-center h-100">
            <div class="card-body">
              <i class="bi bi-envelope-fill fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Email</h5>
              <p class="card-text">support@wego.vn</p>
              <p class="card-text text-muted">Phản hồi trong 24h</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 mb-3">
          <div class="card text-center h-100">
            <div class="card-body">
              <i class="bi bi-telephone-fill fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Hotline</h5>
              <p class="card-text">1900-xxxx</p>
              <p class="card-text text-muted">8:00 - 22:00 hàng ngày</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 mb-3">
          <div class="card text-center h-100">
            <div class="card-body">
              <i class="bi bi-chat-dots-fill fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Live Chat</h5>
              <p class="card-text">Chat trực tuyến</p>
              <p class="card-text text-muted">Phản hồi ngay lập tức</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h3 class="card-title mb-4">Gửi Tin Nhắn Cho Chúng Tôi</h3>
          <form>
            <div class="mb-3">
              <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" required>
            </div>
            
            <div class="mb-3">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" required>
            </div>
            
            <div class="mb-3">
              <label for="phone" class="form-label">Số điện thoại</label>
              <input type="tel" class="form-control" id="phone">
            </div>
            
            <div class="mb-3">
              <label for="subject" class="form-label">Chủ đề <span class="text-danger">*</span></label>
              <select class="form-select" id="subject" required>
                <option value="">Chọn chủ đề...</option>
                <option value="booking">Vấn đề về đặt chỗ</option>
                <option value="payment">Vấn đề thanh toán</option>
                <option value="host">Trở thành chủ nhà</option>
                <option value="technical">Lỗi kỹ thuật</option>
                <option value="feedback">Góp ý</option>
                <option value="other">Khác</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="message" class="form-label">Nội dung <span class="text-danger">*</span></label>
              <textarea class="form-control" id="message" rows="5" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
          </form>
        </div>
      </div>
      
      <div class="card mt-4">
        <div class="card-body">
          <h3 class="card-title">Văn Phòng</h3>
          <p><strong>Trụ sở chính:</strong></p>
          <p>
            Tầng 10, Tòa nhà ABC<br>
            123 Đường Lê Lợi, Quận 1<br>
            Thành phố Hồ Chí Minh<br>
            Việt Nam
          </p>
          <p><strong>Giờ làm việc:</strong> Thứ 2 - Thứ 6: 8:00 - 18:00</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>