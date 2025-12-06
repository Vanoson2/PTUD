<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../css/shared-style.css">

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <h1 class="mb-4">Diễn Đàn Cộng Đồng</h1>
      
      <div class="card mb-4">
        <div class="card-body">
          <h5>Chào mừng đến với Diễn đàn WeGo!</h5>
          <p>Nơi chia sẻ kinh nghiệm du lịch, đặt câu hỏi và kết nối với cộng đồng yêu du lịch.</p>
          <button class="btn btn-primary">Đăng bài viết mới</button>
        </div>
      </div>
      
      <div class="list-group mb-4">
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Kinh nghiệm du lịch Phú Quốc tự túc</h5>
            <small class="text-muted">2 giờ trước</small>
          </div>
          <p class="mb-2">Mình vừa đi Phú Quốc về, chia sẻ một số kinh nghiệm cho các bạn đang có kế hoạch...</p>
          <div class="d-flex gap-3">
            <small><i class="bi bi-person-circle"></i> NguyenVanA</small>
            <small><i class="bi bi-chat-dots"></i> 15 bình luận</small>
            <small><i class="bi bi-eye"></i> 234 lượt xem</small>
          </div>
        </div>
        
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Hỏi về thuê xe máy ở Đà Lạt</h5>
            <small class="text-muted">5 giờ trước</small>
          </div>
          <p class="mb-2">Cho mình hỏi thuê xe máy ở Đà Lạt giá bao nhiêu và chỗ nào uy tín nhỉ?</p>
          <div class="d-flex gap-3">
            <small><i class="bi bi-person-circle"></i> TranThiB</small>
            <small><i class="bi bi-chat-dots"></i> 8 bình luận</small>
            <small><i class="bi bi-eye"></i> 156 lượt xem</small>
          </div>
        </div>
        
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Chia sẻ ảnh Sapa mùa lúa chín</h5>
            <small class="text-muted">1 ngày trước</small>
          </div>
          <p class="mb-2">Vừa đi Sapa về, chia sẻ mọi người mấy tấm hình mùa lúa chín đẹp tuyệt!</p>
          <div class="d-flex gap-3">
            <small><i class="bi bi-person-circle"></i> LeVanC</small>
            <small><i class="bi bi-chat-dots"></i> 32 bình luận</small>
            <small><i class="bi bi-eye"></i> 892 lượt xem</small>
          </div>
        </div>
        
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Tìm bạn đi du lịch Hà Giang tháng 12</h5>
            <small class="text-muted">2 ngày trước</small>
          </div>
          <p class="mb-2">Mình dự định đi Hà Giang tháng 12, tìm 2-3 bạn cùng đi share chi phí...</p>
          <div class="d-flex gap-3">
            <small><i class="bi bi-person-circle"></i> PhamThiD</small>
            <small><i class="bi bi-chat-dots"></i> 12 bình luận</small>
            <small><i class="bi bi-eye"></i> 445 lượt xem</small>
          </div>
        </div>
      </div>
      
      <nav>
        <ul class="pagination justify-content-center">
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item"><a class="page-link" href="#">Tiếp</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
