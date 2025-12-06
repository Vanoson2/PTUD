  </main>
  <?php
  // Auto-detect root path based on current file location
  if (!isset($rootPath)) {
    $currentPath = $_SERVER['PHP_SELF'];
    if (strpos($currentPath, '/view/user/') !== false) {
      $rootPath = '../../../';
    } elseif (strpos($currentPath, '/view/static/') !== false) {
      $rootPath = '../../';
    } elseif (strpos($currentPath, '/view/') !== false) {
      $rootPath = '../../';
    } else {
      $rootPath = './';
    }
  }
  ?>
  <footer class="site-footer bg-light mt-5">
    <div class="container py-5">
      <div class="row g-5">
        <div class="col-md-3 col-sm-6">
          <h5 class="mb-3">Hỗ Trợ</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/help-center.php" class="text-decoration-none text-dark">Trung Tâm Trợ Giúp</a></li>
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/contact.php" class="text-decoration-none text-dark">Liên Hệ</a></li>
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/safety.php" class="text-decoration-none text-dark">Thông Tin An Toàn</a></li>
          </ul>
        </div>
        <div class="col-md-3 col-sm-6">
          <h5 class="mb-3">Cộng Đồng</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/blog.php" class="text-decoration-none text-dark">Blog</a></li>
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/forum.php" class="text-decoration-none text-dark">Diễn Đàn</a></li>
            <li class="mb-2"><a href="<?php echo $rootPath; ?>view/static/events.php" class="text-decoration-none text-dark">Sự Kiện</a></li>
          </ul>
        </div>
        <div class="col-md-3 col-sm-6">
          <h5 class="mb-3">Đăng Ký Nhận Tin</h5>
          <form action="#" method="post">
            <div class="input-group">
              <input class="form-control" type="email" placeholder="Email của bạn" required>
              <button class="btn btn-primary" type="submit">Đăng ký</button>
            </div>
          </form>
        </div>
        <div class="col-md-3 col-sm-6">
          <h5 class="mb-3">Theo dõi chúng tôi trên</h5>
          <ul class="list-unstyled">
            <li class="mb-2">
              <a href="https://facebook.com" target="_blank" class="text-decoration-none text-dark">
                <i class="fab fa-facebook"></i> Facebook
              </a>
            </li>
            <li class="mb-2">
              <a href="https://instagram.com" target="_blank" class="text-decoration-none text-dark">
                <i class="fab fa-instagram"></i> Instagram
              </a>
            </li>
            <li class="mb-2">
              <a href="https://tiktok.com" target="_blank" class="text-decoration-none text-dark">
                <i class="fab fa-tiktok"></i> TikTok
              </a>
            </li>
            <li class="mb-2">
              <a href="https://youtube.com" target="_blank" class="text-decoration-none text-dark">
                <i class="fab fa-youtube"></i> Youtube
              </a>
            </li>
            <li class="mb-2">
              <a href="https://telegram.org" target="_blank" class="text-decoration-none text-dark">
                <i class="fab fa-telegram"></i> Telegram
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="text-center mt-4">
        &copy; 2025 WeGo, Inc. · 
        <a href="<?php echo $rootPath; ?>view/static/privacy.php" class="text-decoration-none">Chính sách bảo mật</a> · 
        <a href="<?php echo $rootPath; ?>view/static/terms.php" class="text-decoration-none">Điều khoản</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
