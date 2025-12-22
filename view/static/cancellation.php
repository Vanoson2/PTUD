<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rootPath = '../../';
$currentPage = 'cancellation';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="../../view/css/static-page.css?v=<?php echo time(); ?>">

<div class="static-page-container">
  <div class="container py-5">
    <div class="static-content">
      <h1 class="page-title">
        <i class="fas fa-calendar-times"></i>
        Chính sách hủy đặt chỗ
      </h1>
      
      <div class="last-updated">
        Cập nhật lần cuối: 19 tháng 12, 2025
      </div>

      <div class="content-section">
        <h2>1. Tổng quan</h2>
        <p>
          WeGo hiểu rằng đôi khi kế hoạch du lịch có thể thay đổi. Chính sách hủy đặt chỗ của chúng tôi 
          được thiết kế để cân bằng giữa sự linh hoạt cho khách hàng và bảo vệ quyền lợi của chủ nhà.
        </p>
      </div>

      <div class="content-section">
        <h2>2. Các loại chính sách hủy</h2>
        
        <div class="policy-box">
          <h3><i class="fas fa-check-circle text-success"></i> Hủy linh hoạt</h3>
          <ul>
            <li><strong>Hủy trước 24 giờ check-in:</strong> Hoàn lại 100% số tiền</li>
            <li><strong>Hủy trong vòng 24 giờ trước check-in:</strong> Hoàn lại 50% số tiền</li>
            <li><strong>Không đến (No-show):</strong> Không hoàn tiền</li>
          </ul>
        </div>

        <div class="policy-box">
          <h3><i class="fas fa-calendar-check text-primary"></i> Hủy trung bình</h3>
          <ul>
            <li><strong>Hủy trước 48 giờ check-in:</strong> Hoàn lại 100% số tiền</li>
            <li><strong>Hủy trong vòng 48 giờ trước check-in:</strong> Hoàn lại 50% số tiền</li>
            <li><strong>Không đến (No-show):</strong> Không hoàn tiền</li>
          </ul>
        </div>

        <div class="policy-box">
          <h3><i class="fas fa-ban text-warning"></i> Hủy nghiêm ngặt</h3>
          <ul>
            <li><strong>Hủy trước 7 ngày check-in:</strong> Hoàn lại 50% số tiền</li>
            <li><strong>Hủy trong vòng 7 ngày trước check-in:</strong> Không hoàn tiền</li>
            <li><strong>Không đến (No-show):</strong> Không hoàn tiền</li>
          </ul>
        </div>

        <div class="policy-box">
          <h3><i class="fas fa-times-circle text-danger"></i> Không hoàn tiền</h3>
          <ul>
            <li>Không áp dụng hoàn tiền trong mọi trường hợp</li>
            <li>Thường áp dụng cho các ưu đãi đặc biệt hoặc giá khuyến mãi</li>
          </ul>
        </div>
      </div>

      <div class="content-section">
        <h2>3. Quy trình hủy đặt chỗ</h2>
        
        <div class="step-box">
          <div class="step-number">1</div>
          <div class="step-content">
            <h4>Đăng nhập tài khoản</h4>
            <p>Truy cập vào trang "Đặt chỗ của tôi" trong tài khoản của bạn</p>
          </div>
        </div>

        <div class="step-box">
          <div class="step-number">2</div>
          <div class="step-content">
            <h4>Chọn đơn đặt cần hủy</h4>
            <p>Tìm đơn đặt chỗ bạn muốn hủy và nhấn "Hủy đơn đặt"</p>
          </div>
        </div>

        <div class="step-box">
          <div class="step-number">3</div>
          <div class="step-content">
            <h4>Xác nhận hủy</h4>
            <p>Hệ thống sẽ hiển thị số tiền hoàn lại (nếu có) dựa trên chính sách hủy</p>
          </div>
        </div>

        <div class="step-box">
          <div class="step-number">4</div>
          <div class="step-content">
            <h4>Nhận hoàn tiền</h4>
            <p>Số tiền sẽ được hoàn lại vào tài khoản thanh toán của bạn trong vòng 5-7 ngày làm việc</p>
          </div>
        </div>
      </div>

      <div class="content-section">
        <h2>4. Trường hợp đặc biệt</h2>
        <ul>
          <li>
            <strong>Thiên tai, dịch bệnh:</strong> WeGo sẽ xem xét từng trường hợp cụ thể và có thể áp dụng 
            chính sách hoàn tiền linh hoạt hơn.
          </li>
          <li>
            <strong>Chủ nhà hủy đơn:</strong> Nếu chủ nhà hủy đơn đặt chỗ đã xác nhận, bạn sẽ được hoàn lại 
            100% số tiền và nhận voucher bồi thường 10% giá trị đơn hàng.
          </li>
          <li>
            <strong>Chỗ ở không đúng mô tả:</strong> Nếu chỗ ở không đúng như mô tả, vui lòng liên hệ 
            hỗ trợ khách hàng trong vòng 24 giờ sau check-in để được hỗ trợ.
          </li>
        </ul>
      </div>

      <div class="content-section">
        <h2>5. Thời gian hoàn tiền</h2>
        <ul>
          <li><strong>Thanh toán qua MoMo:</strong> 3-5 ngày làm việc</li>
          <li><strong>Thanh toán qua thẻ tín dụng:</strong> 7-14 ngày làm việc</li>
          <li><strong>Thanh toán qua chuyển khoản:</strong> 5-7 ngày làm việc</li>
        </ul>
        <p class="note">
          <i class="fas fa-info-circle"></i>
          <strong>Lưu ý:</strong> Thời gian hoàn tiền có thể thay đổi tùy thuộc vào ngân hàng và 
          phương thức thanh toán của bạn.
        </p>
      </div>

      <div class="content-section">
        <h2>6. Điểm tín nhiệm</h2>
        <p>
          Hành vi hủy đơn có thể ảnh hưởng đến điểm tín nhiệm của bạn:
        </p>
        <ul>
          <li><strong>Hủy trước 24 giờ check-in:</strong> Không bị trừ điểm</li>
          <li><strong>Hủy trong vòng 24 giờ trước check-in:</strong> Trừ 5 điểm</li>
          <li><strong>Không đến (No-show):</strong> Trừ 10 điểm</li>
        </ul>
        <p class="note">
          <i class="fas fa-exclamation-triangle text-warning"></i>
          Điểm tín nhiệm thấp có thể ảnh hưởng đến khả năng đặt chỗ trong tương lai.
        </p>
      </div>

      <div class="content-section">
        <h2>7. Liên hệ hỗ trợ</h2>
        <p>
          Nếu bạn gặp vấn đề hoặc có thắc mắc về chính sách hủy đặt chỗ, vui lòng liên hệ:
        </p>
        <ul>
          <li><i class="fas fa-envelope"></i> Email: support@wegotravel.online</li>
          <li><i class="fas fa-phone"></i> Hotline: 1900-xxxx (8:00 - 22:00 hàng ngày)</li>
          <li><i class="fas fa-comments"></i> Chat trực tuyến: Góc phải màn hình</li>
        </ul>
      </div>

      <div class="alert alert-info mt-4">
        <i class="fas fa-lightbulb"></i>
        <strong>Mẹo:</strong> Trước khi đặt chỗ, hãy kiểm tra kỹ chính sách hủy của chỗ ở để đảm bảo 
        phù hợp với kế hoạch của bạn.
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
