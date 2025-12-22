<?php
// Include Authentication Helper and Controller
require_once __DIR__ . '/../../../helper/auth.php';
require_once __DIR__ . '/../../../controller/cUser.php';

// Use helper for authentication
requireLogin();

$userId = getCurrentUserId();
$currentPage = 'trust-score'; // For sidebar active state
$rootPath = '../../';
$showVerifyButton = false;

// Use Controller to get user data
$cUser = new cUser();
$user = $cUser->cGetUserProfile($userId);

if (!$user) {
  logoutUser();
  header('Location: ./login.php');
  exit;
}

// Lấy thông tin điểm tín nhiệm
$scoreResult = $cUser->cGetUserScore($userId);
$historyResult = $cUser->cGetScoreHistory($userId, 10);
$suggestionsResult = $cUser->cGetImprovementSuggestions($userId);

$scoreData = $scoreResult['success'] ? $scoreResult['data'] : null;
$history = $historyResult['success'] ? $historyResult['data'] : [];
$suggestions = $suggestionsResult['success'] ? $suggestionsResult['data'] : [];

// Set default values if scoreData is null
if (!$scoreData) {
  $scoreData = [
    'trust_score' => 0,
    'level' => [
      'badge' => '🆕',
      'name' => 'Người dùng mới',
      'description' => 'Chưa có điểm tín nhiệm',
      'color' => '#999999'
    ],
    'verified_phone' => false,
    'verified_id' => false,
    'verified_full' => false
  ];
}
?>

<?php include __DIR__ . '/../../partials/header.php'; ?>

<link rel="stylesheet" href="../../css/traveller-profile.css?v=<?php echo time(); ?>">
<style>
.trust-score-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.score-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.score-main {
  text-align: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.score-number {
  font-size: 72px;
  font-weight: bold;
  color: #333;
  line-height: 1;
}

.score-label {
  font-size: 18px;
  color: #666;
  margin-top: 10px;
}

.score-level {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 500;
  margin-top: 15px;
}

.progress-bar-container {
  width: 100%;
  max-width: 400px;
  height: 12px;
  background: #f0f0f0;
  border-radius: 6px;
  margin: 20px auto;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.3s ease;
}

.verification-status {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.verify-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 8px;
}

.verify-item.verified {
  background: #d4edda;
  color: #155724;
}

.verify-icon {
  font-size: 24px;
}

.suggestions-list {
  list-style: none;
  padding: 0;
}

.suggestion-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 15px;
  background: #fff9e6;
  border-left: 4px solid #ffc107;
  border-radius: 4px;
  margin-bottom: 10px;
}

.suggestion-item .icon {
  font-size: 20px;
  color: #ffc107;
}

.suggestion-item .text {
  flex: 1;
}

.suggestion-item .points {
  font-weight: bold;
  color: #28a745;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
}

.history-table th,
.history-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.history-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.score-change {
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 4px;
}

.score-change.positive {
  color: #28a745;
  background: #d4edda;
}

.score-change.negative {
  color: #dc3545;
  background: #f8d7da;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #999;
}
</style>

<?php include __DIR__ . '/../partials/profile-layout-start.php'; ?>

<!-- Page Content -->
<div class="trust-score-container">
  <div class="profile-header">
    <h1>Điểm Tín Nhiệm</h1>
    <p>Quản lý và cải thiện uy tín của bạn trên nền tảng</p>
  </div>

  <?php if ($scoreData): ?>
    <!-- Main Score Card -->
    <div class="score-card">
      <div class="score-main">
        <div class="score-number"><?php echo $scoreData['trust_score'] ?? 0; ?></div>
        <div class="score-label">/ 150 điểm</div>
        
        <div class="progress-bar-container">
          <div class="progress-bar-fill" 
               style="width: <?php echo min(100, ($scoreData['trust_score'] ?? 0) / 1.5); ?>%; background: <?php echo $scoreData['level']['color'] ?? '#999999'; ?>">
          </div>
        </div>
        
        <div class="score-level" style="background: <?php echo ($scoreData['level']['color'] ?? '#999999') . '20'; ?>; color: <?php echo $scoreData['level']['color'] ?? '#999999'; ?>">
          <span class="level-badge"><?php echo $scoreData['level']['badge'] ?? '🆕'; ?></span>
          <span><?php echo $scoreData['level']['name'] ?? 'Người dùng bình thường'; ?></span>
        </div>
        
        <p style="margin-top: 15px; color: #666;">
          <?php echo $scoreData['level']['description'] ?? 'Chưa có mô tả'; ?>
        </p>
      </div>
    </div>

    <!-- Suggestions -->
    <?php if (!empty($suggestions)): ?>
      <div class="score-card">
        <h3 style="margin-bottom: 15px;">💡 Cách cải thiện điểm</h3>
        <ul class="suggestions-list">
          <?php foreach ($suggestions as $suggestion): ?>
            <li class="suggestion-item">
              <span class="icon"><?php echo $suggestion['icon'] ?? '⭐'; ?></span>
              <span class="text"><?php echo htmlspecialchars($suggestion['action'] ?? 'Không có mô tả'); ?></span>
              <span class="points"><?php echo $suggestion['points'] ?? '+0'; ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- History -->
    <div class="score-card">
      <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-chart-line"></i> Lịch sử thay đổi điểm</h3>
      
      <?php if (!empty($history)): ?>
        <div style="overflow-x: auto;">
          <table class="history-table">
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Thay đổi</th>
                <th>Điểm cũ</th>
                <th>Điểm mới</th>
                <th>Lý do</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history as $item): ?>
                <tr>
                  <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                  <td>
                    <span class="score-change <?php echo $item['score_change'] > 0 ? 'positive' : 'negative'; ?>">
                      <?php echo $item['score_change'] > 0 ? '+' : ''; ?><?php echo $item['score_change']; ?>
                    </span>
                  </td>
                  <td><?php echo $item['old_score']; ?></td>
                  <td><?php echo $item['new_score']; ?></td>
                  <td><?php echo htmlspecialchars($item['reason']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>Chưa có lịch sử thay đổi điểm</p>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="alert alert-danger">
      Không thể tải thông tin điểm tín nhiệm
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/profile-layout-end.php'; ?>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
