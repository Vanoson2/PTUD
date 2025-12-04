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
      <h1 class="mb-4">Thông Tin An Toàn</h1>
      
      <div class="alert alert-info">
        <i class="bi bi-shield-check"></i> An toàn của bạn là ưu tiên hàng đầu của chúng tôi
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">An Toàn Khi Đặt Chỗ</h3>
          <ul>
            <li><strong>Thanh toán an toàn:</strong> Tất cả giao dịch được mã hóa và bảo mật</li>
            <li><strong>Xác thực chủ nhà:</strong> Mọi chủ nhà đều được xác minh danh tính</li>
            <li><strong>Đánh giá thật:</strong> Chỉ khách đã ở mới có thể đánh giá</li>
            <li><strong>Hỗ trợ 24/7:</strong> Đội ngũ hỗ trợ sẵn sàng giúp đỡ bạn</li>
          </ul>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Lời Khuyên An Toàn Cho Khách</h3>
          <div class="mb-3">
            <h5><i class="bi bi-check-circle text-success"></i> Nên làm:</h5>
            <ul>
              <li>Đọc kỹ mô tả và đánh giá của chỗ ở</li>
              <li>Giao tiếp với chủ nhà trước khi đặt</li>
              <li>Kiểm tra chính sách hủy và hoàn tiền</li>
              <li>Chụp ảnh hiện trạng khi nhận phòng</li>
              <li>Giữ liên lạc qua nền tảng để được bảo vệ</li>
              <li>Báo cáo ngay nếu có vấn đề</li>
            </ul>
          </div>
          
          <div>
            <h5><i class="bi bi-x-circle text-danger"></i> Không nên:</h5>
            <ul>
              <li>Chuyển tiền trực tiếp cho chủ nhà ngoài hệ thống</li>
              <li>Chia sẻ thông tin cá nhân nhạy cảm không cần thiết</li>
              <li>Bỏ qua các dấu hiệu cảnh báo trong mô tả</li>
              <li>Đặt chỗ không có đánh giá hoặc ảnh rõ ràng</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Lời Khuyên An Toàn Cho Chủ Nhà</h3>
          <div class="mb-3">
            <h5><i class="bi bi-check-circle text-success"></i> Nên làm:</h5>
            <ul>
              <li>Xác thực danh tính khách trước khi nhận</li>
              <li>Có camera an ninh ở khu vực công cộng (báo trước cho khách)</li>
              <li>Cung cấp thông tin liên hệ khẩn cấp</li>
              <li>Kiểm tra nhà cửa trước và sau khi khách ở</li>
              <li>Có bảo hiểm cho tài sản</li>
              <li>Giữ liên lạc qua nền tảng</li>
            </ul>
          </div>
          
          <div>
            <h5><i class="bi bi-x-circle text-danger"></i> Không nên:</h5>
            <ul>
              <li>Nhận khách không qua nền tảng</li>
              <li>Nhận tiền mặt trực tiếp</li>
              <li>Để tài sản quý giá không khóa</li>
              <li>Bỏ qua các yêu cầu bất thường của khách</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">An Toàn Sức Khỏe</h3>
          <ul>
            <li>Vệ sinh sạch sẽ giữa các lượt khách</li>
            <li>Cung cấp đồ dùng vệ sinh cá nhân</li>
            <li>Thông gió tốt trong nhà</li>
            <li>Hướng dẫn thoát hiểm khẩn cấp</li>
            <li>Bộ sơ cứu có sẵn</li>
          </ul>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Báo Cáo Vấn Đề</h3>
          <p>Nếu bạn gặp vấn đề về an toàn hoặc phát hiện hành vi đáng ngờ:</p>
          <ul>
            <li><strong>Hotline khẩn cấp:</strong> 1900-xxxx</li>
            <li><strong>Email:</strong> safety@wego.vn</li>
            <li><strong>Trong trường hợp nguy hiểm:</strong> Gọi 113 (Công an) hoặc 115 (Cấp cứu)</li>
          </ul>
          <button class="btn btn-danger">Báo cáo ngay</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
