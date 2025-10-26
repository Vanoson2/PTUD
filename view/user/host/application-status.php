<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../../../controller/cHost.php';

$userId = $_SESSION['user_id'];
$cHost = new cHost();
$application = $cHost->cGetUserHostApplication($userId);

// Định nghĩa trạng thái với icon và màu sắc
$statusConfig = [
    'pending' => [
        'label' => 'Đang chờ duyệt',
        'icon' => 'clock',
        'color' => 'warning',
        'description' => 'Đơn đăng ký của bạn đang được xem xét bởi quản trị viên.'
    ],
    'approved' => [
        'label' => 'Đã duyệt',
        'icon' => 'check-circle',
        'color' => 'success',
        'description' => 'Chúc mừng! Đơn đăng ký của bạn đã được phê duyệt.'
    ],
    'rejected' => [
        'label' => 'Từ chối',
        'icon' => 'x-circle',
        'color' => 'danger',
        'description' => 'Rất tiếc, đơn đăng ký của bạn chưa được phê duyệt.'
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trạng thái đơn đăng ký Host - We Go</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/application-status.css">
</head>
<body>
    <?php include __DIR__ . '/../../partials/header.php'; ?>

    <div class="container my-5">
        <div class="application-card">
            <h2 class="mb-4">
                <i class="bi bi-file-earmark-text"></i> Trạng thái đơn đăng ký Host
            </h2>

            <?php if ($application): ?>
                <?php 
                $status = $application['status'];
                $config = $statusConfig[$status];
                ?>
                
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-<?php echo $config['icon']; ?> status-icon text-<?php echo $config['color']; ?>"></i>
                        <h3 class="mt-3">
                            <span class="badge bg-<?php echo $config['color']; ?> status-badge">
                                <?php echo $config['label']; ?>
                            </span>
                        </h3>
                        <p class="text-muted mt-3 mb-0"><?php echo $config['description']; ?></p>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Thông tin chi tiết</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-4 info-label">Mã đơn:</div>
                                <div class="col-md-8">#<?php echo $application['host_application_id']; ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-4 info-label">Ngày nộp đơn:</div>
                                <div class="col-md-8">
                                    <?php echo date('d/m/Y H:i', strtotime($application['created_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-4 info-label">Tên doanh nghiệp:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($application['business_name'] ?? 'Không có'); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($application['tax_code'])): ?>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Mã số thuế:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($application['tax_code']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($status !== 'pending'): ?>
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Người duyệt:</div>
                                    <div class="col-md-8">
                                        <?php echo $application['reviewed_by_name'] ?? 'Không xác định'; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-4 info-label">Ngày xử lý:</div>
                                    <div class="col-md-8">
                                        <?php 
                                        if ($application['reviewed_at']) {
                                            echo date('d/m/Y H:i', strtotime($application['reviewed_at']));
                                        } else {
                                            echo 'Chưa xác định';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($application['rejection_reason'])): ?>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-md-4 info-label">Lý do từ chối:</div>
                                        <div class="col-md-8 text-danger">
                                            <?php echo nl2br(htmlspecialchars($application['rejection_reason'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($status === 'approved'): ?>
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Bước tiếp theo:</strong> Bạn có thể bắt đầu tạo các listing để cho thuê nhà của mình!
                        <a href="create-listing.php" class="btn btn-success btn-sm ms-3">
                            <i class="bi bi-plus-circle"></i> Tạo Listing
                        </a>
                    </div>
                <?php elseif ($status === 'rejected'): ?>
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Lưu ý:</strong> Bạn có thể nộp đơn đăng ký lại sau khi khắc phục các vấn đề được nêu trong lý do từ chối.
                        <a href="become-host.php" class="btn btn-primary btn-sm ms-3">
                            <i class="bi bi-arrow-repeat"></i> Đăng ký lại
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox status-icon text-muted"></i>
                        <h4 class="mt-3">Chưa có đơn đăng ký</h4>
                        <p class="text-muted">Bạn chưa nộp đơn đăng ký trở thành Host.</p>
                        <a href="become-host.php" class="btn btn-primary mt-3">
                            <i class="bi bi-person-plus"></i> Đăng ký ngay
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../../../index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
