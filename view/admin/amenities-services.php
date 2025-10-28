<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
  header("Location: ./login.php");
  exit();
}

include_once(__DIR__ . "/../../controller/cType&Amenties.php");

$cType = new cTypeAndAmenties();
$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

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
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  } 
  elseif ($action === 'edit_amenity') {
    $amenityId = $_POST['amenity_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $groupName = trim($_POST['group_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cUpdateAmenity($amenityId, $name, $groupName, $description);
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  } 
  elseif ($action === 'delete_amenity') {
    $amenityId = $_POST['amenity_id'] ?? 0;
    
    $result = $cType->cDeleteAmenity($amenityId);
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  }
  
  // SERVICE ACTIONS
  elseif ($action === 'add_service') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cInsertService($name, $description);
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  } 
  elseif ($action === 'edit_service') {
    $serviceId = $_POST['service_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $result = $cType->cUpdateService($serviceId, $name, $description);
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  } 
  elseif ($action === 'delete_service') {
    $serviceId = $_POST['service_id'] ?? 0;
    
    $result = $cType->cDeleteService($serviceId);
    if ($result['success']) {
      $message = $result['message'];
      $messageType = 'success';
    } else {
      $message = implode('<br>', $result['errors']);
      $messageType = 'danger';
    }
  }
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/amenities-services.css?v=<?php echo time(); ?>">
</head>
<body>
  <!-- Header -->
  <nav class="admin-navbar">
    <div class="container-fluid">
      <div class="navbar-brand">
        <h1>🛠️ Quản lý Tiện nghi & Dịch vụ</h1>
        <span class="admin-name">Xin chào, <?php echo htmlspecialchars($adminName); ?>!</span>
      </div>
      <div class="navbar-links">
        <a href="./dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="./users.php" class="nav-link">👥 Người dùng</a>
        <a href="./hosts.php" class="nav-link">🏡 Chủ nhà</a>
        <a href="./listings.php" class="nav-link">🏠 Chỗ ở</a>
        <a href="./applications.php" class="nav-link">📝 Đơn đăng ký</a>
        <a href="./logout.php" class="nav-link logout">🚪 Đăng xuất</a>
      </div>
    </div>
  </nav>

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
</body>
</html>
