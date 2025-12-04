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
      <h1 class="mb-4">Chính Sách Bảo Mật</h1>
      <p class="text-muted">Cập nhật lần cuối: 02/11/2025</p>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>1. Giới Thiệu</h3>
          <p>
            WeGo cam kết bảo vệ quyền riêng tư và thông tin cá nhân của bạn. Chính sách này giải thích 
            cách chúng tôi thu thập, sử dụng, chia sẻ và bảo vệ thông tin của bạn.
          </p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>2. Thông Tin Chúng Tôi Thu Thập</h3>
          <h5>2.1. Thông tin bạn cung cấp:</h5>
          <ul>
            <li>Họ tên, email, số điện thoại</li>
            <li>Thông tin tài khoản (tên đăng nhập, mật khẩu)</li>
            <li>Thông tin thanh toán</li>
            <li>Ảnh đại diện và giới thiệu cá nhân</li>
            <li>Thông tin đặt chỗ và giao tiếp</li>
          </ul>
          
          <h5>2.2. Thông tin tự động:</h5>
          <ul>
            <li>Địa chỉ IP, loại trình duyệt</li>
            <li>Thông tin thiết bị và hệ điều hành</li>
            <li>Lịch sử tìm kiếm và đặt chỗ</li>
            <li>Cookies và công nghệ tương tự</li>
          </ul>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>3. Cách Chúng Tôi Sử Dụng Thông Tin</h3>
          <ul>
            <li>Cung cấp và cải thiện dịch vụ</li>
            <li>Xử lý đặt chỗ và thanh toán</li>
            <li>Giao tiếp với bạn về dịch vụ</li>
            <li>Gửi thông tin khuyến mãi (nếu bạn đồng ý)</li>
            <li>Phòng chống gian lận</li>
            <li>Tuân thủ pháp luật</li>
          </ul>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>4. Chia Sẻ Thông Tin</h3>
          <p>Chúng tôi có thể chia sẻ thông tin với:</p>
          <ul>
            <li><strong>Chủ nhà/Khách:</strong> Thông tin cần thiết cho giao dịch</li>
            <li><strong>Đối tác thanh toán:</strong> Để xử lý giao dịch</li>
            <li><strong>Cơ quan pháp luật:</strong> Khi có yêu cầu hợp pháp</li>
            <li><strong>Dịch vụ phân tích:</strong> Dữ liệu ẩn danh để cải thiện dịch vụ</li>
          </ul>
          <p>Chúng tôi KHÔNG bán thông tin cá nhân của bạn.</p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>5. Bảo Mật Thông Tin</h3>
          <p>Chúng tôi áp dụng các biện pháp bảo mật:</p>
          <ul>
            <li>Mã hóa SSL/TLS cho tất cả giao dịch</li>
            <li>Mã hóa mật khẩu</li>
            <li>Kiểm soát truy cập nghiêm ngặt</li>
            <li>Giám sát và kiểm tra bảo mật thường xuyên</li>
            <li>Tuân thủ tiêu chuẩn PCI DSS cho thanh toán</li>
          </ul>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>6. Quyền Của Bạn</h3>
          <p>Bạn có quyền:</p>
          <ul>
            <li><strong>Truy cập:</strong> Xem thông tin cá nhân của bạn</li>
            <li><strong>Chỉnh sửa:</strong> Cập nhật thông tin không chính xác</li>
            <li><strong>Xóa:</strong> Yêu cầu xóa tài khoản và dữ liệu</li>
            <li><strong>Từ chối:</strong> Không nhận email marketing</li>
            <li><strong>Di chuyển:</strong> Tải xuống dữ liệu của bạn</li>
          </ul>
          <p>Liên hệ privacy@wego.vn để thực hiện các quyền này.</p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>7. Cookies</h3>
          <p>
            Chúng tôi sử dụng cookies để cải thiện trải nghiệm của bạn. Bạn có thể quản lý cookies 
            trong cài đặt trình duyệt. Lưu ý rằng tắt cookies có thể ảnh hưởng đến chức năng website.
          </p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3>8. Trẻ Em</h3>
          <p>
            Dịch vụ của chúng tôi không dành cho người dưới 18 tuổi. Chúng tôi không cố ý thu thập 
            thông tin từ trẻ em.
          </p>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h3>9. Liên Hệ</h3>
          <p>Nếu bạn có câu hỏi về chính sách này, vui lòng liên hệ:</p>
          <p>
            <strong>Email:</strong> privacy@wego.vn<br>
            <strong>Địa chỉ:</strong> Tầng 10, Tòa nhà ABC, 123 Đường Lê Lợi, Quận 1, TP.HCM
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
