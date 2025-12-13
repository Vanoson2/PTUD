<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check admin login
if (!isset($_SESSION['admin_id'])) {
  header("Location: ./login.php");
  exit();
}

include_once(__DIR__ . "/../../../controller/cType&Amenties.php");

$cType = new cTypeAndAmenties();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Get admin role and set permissions
$adminRole = $_SESSION['admin_role'] ?? 'support';
$isSuperAdmin = ($adminRole === 'superadmin');
$isManager = ($adminRole === 'manager' || $isSuperAdmin);

$message = '';
$messageType = '';

// Xử lý các action (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  // AMENITY ACTIONS
  if ($action === 'add_amenity') {
    $name = trim($_POST['name'] ?? '');
    $groupName = trim($_POST['group_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cInsertAmenity($name, $groupName, $description);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  } 
  elseif ($action === 'edit_amenity') {
    $amenityId = $_POST['amenity_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $groupName = trim($_POST['group_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cUpdateAmenity($amenityId, $name, $groupName, $description);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  } 
  elseif ($action === 'delete_amenity') {
    $amenityId = $_POST['amenity_id'] ?? 0;
    
    $result = $cType->cDeleteAmenity($amenityId);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  }
  
  // SERVICE ACTIONS
  elseif ($action === 'add_service') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cInsertService($name, $description);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  } 
  elseif ($action === 'edit_service') {
    $serviceId = $_POST['service_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cUpdateService($serviceId, $name, $description);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  } 
  elseif ($action === 'delete_service') {
    $serviceId = $_POST['service_id'] ?? 0;
    
    $result = $cType->cDeleteService($serviceId);
    $_SESSION['message'] = $result['success'] ? $result['message'] : implode('<br>', $result['errors']);
    $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: amenities-services.php');
    exit();
  }
}

// Get message from session (after redirect)
if (isset($_SESSION['message'])) {
  $message = $_SESSION['message'];
  $messageType = $_SESSION['messageType'] ?? 'info';
  unset($_SESSION['message']);
  unset($_SESSION['messageType']);
}

// Get all amenities và services
$amenitiesResult = $cType->cGetAllAmenities();
$servicesResult = $cType->cGetAllServices();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Tiện nghi & Dịch vụ - WeGo Admin</title>
  <link rel="stylesheet" href="../../css/admin-layout.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/admin-amenities-services.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-shield-alt"></i>
      <h2>Quản trị</h2>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Tổng quan</span>
      </a>
      
      <?php if ($isManager): ?>
      <a href="users.php">
        <i class="fas fa-users"></i>
        <span>Quản lý Người dùng</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="hosts.php">
        <i class="fas fa-hotel"></i>
        <span>Quản lý Chủ nhà</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isManager): ?>
      <a href="applications.php">
        <i class="fas fa-file-alt"></i>
        <span>Đơn đăng ký Host</span>
      </a>
      <?php endif; ?>
      
      <a href="listings.php">
        <i class="fas fa-building"></i>
        <span>Quản lý Phòng</span>
      </a>
      <a href="support.php">
        <i class="fas fa-headset"></i>
        <span>Hỗ trợ khách hàng</span>
      </a>
      
      <?php if ($isManager): ?>
      <a href="amenities-services.php" class="active">
        <i class="fas fa-cog"></i>
        <span>Tiện nghi & Dịch vụ</span>
      </a>
      <?php endif; ?>
      
      <?php if ($isSuperAdmin): ?>
      <a href="admin-management.php">
        <i class="fas fa-user-shield"></i>
        <span>Quản lý Admin</span>
      </a>
      <?php endif; ?>
      
      <hr class="sidebar-divider">
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="page-title">
      <h1>
        <i class="fas fa-cog"></i>
        Quản lý Tiện nghi & Dịch vụ
      </h1>
    </div>
    
    <div class="container mt-5">
    <?php if ($message): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="mainTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="amenities-tab" data-bs-toggle="tab" data-bs-target="#amenities" type="button">
          🛋️ Tiện nghi (Amenities)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button">
          🔧 Dịch vụ (Services)
        </button>
      </li>
    </ul>

    <div class="tab-content" id="mainTabContent">
      <!-- AMENITIES TAB -->
      <div class="tab-pane fade show active" id="amenities" role="tabpanel">
        <div class="section-header">
          <h3>📋 Danh sách Tiện nghi</h3>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
            ➕ Thêm tiện nghi
          </button>
        </div>

        <div class="table-container">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên tiện nghi</th>
                <th>Nhóm</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              if ($amenitiesResult && $amenitiesResult->num_rows > 0) {
                while ($amenity = $amenitiesResult->fetch_assoc()): 
              ?>
                <tr>
                  <td><?php echo $amenity['amenity_id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($amenity['name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($amenity['group_name'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($amenity['description'] ?? '-'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" onclick="editAmenity(<?php echo $amenity['amenity_id']; ?>, '<?php echo htmlspecialchars(addslashes($amenity['name'])); ?>', '<?php echo htmlspecialchars(addslashes($amenity['group_name'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($amenity['description'] ?? '')); ?>')">
                      ✏️ Sửa
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa tiện nghi này?')">
                      <input type="hidden" name="action" value="delete_amenity">
                      <input type="hidden" name="amenity_id" value="<?php echo $amenity['amenity_id']; ?>">
                      <button type="submit" class="btn btn-sm btn-danger">🗑️ Xóa</button>
                    </form>
                  </td>
                </tr>
              <?php 
                endwhile;
              } else {
                echo '<tr><td colspan="5" class="text-center">Chưa có tiện nghi nào</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SERVICES TAB -->
      <div class="tab-pane fade" id="services" role="tabpanel">
        <div class="section-header">
          <h3>📋 Danh sách Dịch vụ</h3>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            ➕ Thêm dịch vụ
          </button>
        </div>

        <div class="table-container">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên dịch vụ</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              if ($servicesResult && $servicesResult->num_rows > 0) {
                while ($service = $servicesResult->fetch_assoc()): 
              ?>
                <tr>
                  <td><?php echo $service['service_id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($service['description'] ?? '-'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" onclick="editService(<?php echo $service['service_id']; ?>, '<?php echo htmlspecialchars(addslashes($service['name'])); ?>', '<?php echo htmlspecialchars(addslashes($service['description'] ?? '')); ?>')">
                      ✏️ Sửa
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?')">
                      <input type="hidden" name="action" value="delete_service">
                      <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">
                      <button type="submit" class="btn btn-sm btn-danger">🗑️ Xóa</button>
                    </form>
                  </td>
                </tr>
              <?php 
                endwhile;
              } else {
                echo '<tr><td colspan="4" class="text-center">Chưa có dịch vụ nào</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Add Amenity -->
  <div class="modal fade" id="addAmenityModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">➕ Thêm Tiện nghi mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_amenity">
            <div class="mb-3">
              <label class="form-label">Tên tiện nghi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" required maxlength="120">
            </div>
            <div class="mb-3">
              <label class="form-label">Nhóm tiện nghi</label>
              <input type="text" class="form-control" name="group_name" maxlength="120" placeholder="VD: Phòng tắm, Bếp, Giải trí...">
            </div>
            <div class="mb-3">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" name="description" rows="3" maxlength="500"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Thêm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Amenity -->
  <div class="modal fade" id="editAmenityModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">✏️ Sửa Tiện nghi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_amenity">
            <input type="hidden" name="amenity_id" id="edit_amenity_id">
            <div class="mb-3">
              <label class="form-label">Tên tiện nghi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" id="edit_amenity_name" required maxlength="120">
            </div>
            <div class="mb-3">
              <label class="form-label">Nhóm tiện nghi</label>
              <input type="text" class="form-control" name="group_name" id="edit_amenity_group" maxlength="120">
            </div>
            <div class="mb-3">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" name="description" id="edit_amenity_desc" rows="3" maxlength="500"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Add Service -->
  <div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">➕ Thêm Dịch vụ mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_service">
            <div class="mb-3">
              <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" required maxlength="120">
            </div>
            <div class="mb-3">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" name="description" rows="3" maxlength="500"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Thêm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Service -->
  <div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">✏️ Sửa Dịch vụ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_service">
            <input type="hidden" name="service_id" id="edit_service_id">
            <div class="mb-3">
              <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" id="edit_service_name" required maxlength="120">
            </div>
            <div class="mb-3">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" name="description" id="edit_service_desc" rows="3" maxlength="500"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function editAmenity(id, name, groupName, description) {
      document.getElementById('edit_amenity_id').value = id;
      document.getElementById('edit_amenity_name').value = name;
      document.getElementById('edit_amenity_group').value = groupName;
      document.getElementById('edit_amenity_desc').value = description;
      
      const modal = new bootstrap.Modal(document.getElementById('editAmenityModal'));
      modal.show();
    }

    function editService(id, name, description) {
      document.getElementById('edit_service_id').value = id;
      document.getElementById('edit_service_name').value = name;
      document.getElementById('edit_service_desc').value = description;
      
      const modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
      modal.show();
    }
  </script>
  
  </main>
</div>
</body>
</html>
