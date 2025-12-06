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
      <h1 class="mb-4">Trung Tâm Trợ Giúp</h1>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Câu Hỏi Thường Gặp</h3>
          
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                  Làm thế nào để đặt chỗ?
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  <p>Để đặt chỗ, bạn có thể làm theo các bước sau:</p>
                  <ol>
                    <li>Tìm kiếm địa điểm bạn muốn đến</li>
                    <li>Chọn ngày nhận phòng và trả phòng</li>
                    <li>Chọn số lượng khách</li>
                    <li>Xem danh sách chỗ ở phù hợp</li>
                    <li>Chọn chỗ ở bạn thích và nhấn "Đặt ngay"</li>
                    <li>Điền thông tin và xác nhận đặt chỗ</li>
                  </ol>
                </div>
              </div>
            </div>
            
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                  Chính sách hủy đặt chỗ như thế nào?
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  <p>Chính sách hủy phụ thuộc vào từng chỗ ở:</p>
                  <ul>
                    <li><strong>Hủy miễn phí:</strong> Hủy trước 48 giờ nhận phòng được hoàn tiền 100%</li>
                    <li><strong>Hủy linh hoạt:</strong> Hủy trước 24 giờ nhận phòng được hoàn tiền 50%</li>
                    <li><strong>Không hoàn tiền:</strong> Giá ưu đãi nhưng không được hoàn lại khi hủy</li>
                  </ul>
                  <p>Vui lòng kiểm tra chính sách cụ thể của từng chỗ ở trước khi đặt.</p>
                </div>
              </div>
            </div>
            
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                  Tôi có thể thanh toán bằng phương thức nào?
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  <p>Chúng tôi chấp nhận các phương thức thanh toán sau:</p>
                  <ul>
                    <li>Thẻ tín dụng/ghi nợ (Visa, Mastercard, JCB)</li>
                    <li>Chuyển khoản ngân hàng</li>
                    <li>Ví điện tử (MoMo, ZaloPay, VNPay)</li>
                    <li>Thanh toán khi nhận phòng (tùy chỗ ở)</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                  Làm thế nào để trở thành chủ nhà?
                </button>
              </h2>
              <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  <p>Để trở thành chủ nhà và cho thuê chỗ ở của bạn:</p>
                  <ol>
                    <li>Đăng ký tài khoản (nếu chưa có)</li>
                    <li>Chọn "Trở thành chủ nhà" trong menu</li>
                    <li>Điền thông tin cá nhân và xác thực</li>
                    <li>Đăng ký chỗ ở của bạn</li>
                    <li>Đợi phê duyệt từ quản trị viên (1-3 ngày)</li>
                    <li>Bắt đầu đón khách!</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Cần Hỗ Trợ Thêm?</h3>
          <p>Nếu bạn không tìm thấy câu trả lời cho câu hỏi của mình, vui lòng liên hệ với chúng tôi:</p>
          <ul>
            <li><strong>Email:</strong> support@wego.vn</li>
            <li><strong>Hotline:</strong> 1900-xxxx (8:00 - 22:00 hàng ngày)</li>
            <li><strong>Chat trực tuyến:</strong> Có sẵn trên website</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
