<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
header('Content-Type: application/json');

include_once __DIR__ . '/../../../controller/cHost.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
  exit;
}

$userId = $_SESSION['user_id'];
$cHost = new cHost();

// Kiểm tra user có phải là host không
if (!$cHost->cIsUserHost($userId)) {
  echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
  exit;
}

// Lấy host_id
$hostInfo = $cHost->cGetHostByUserId($userId);
if (!$hostInfo) {
  echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin host']);
  exit;
}
$hostId = $hostInfo['host_id'];

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

// Lấy listing_id
$listingId = intval($_POST['listing_id'] ?? 0);

if ($listingId <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID phòng không hợp lệ']);
  exit;
}

// Toggle status
$newStatus = $cHost->cToggleListingStatus($listingId, $hostId);

if ($newStatus === false) {
  echo json_encode([
    'success' => false, 
    'message' => 'Không thể thay đổi trạng thái. Chỉ có thể ẩn/hiện phòng đang ở trạng thái active/inactive'
  ]);
} else {
  $statusText = $newStatus === 'active' ? 'hiển thị' : 'ẩn';
  echo json_encode([
    'success' => true, 
    'message' => 'Đã ' . $statusText . ' phòng thành công',
    'new_status' => $newStatus
  ]);
}
?>
