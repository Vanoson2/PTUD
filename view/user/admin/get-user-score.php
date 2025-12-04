<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include_once(__DIR__ . "/../../../controller/cUser.php");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

$cUser = new cUser();

// Get score data
$scoreResult = $cUser->cGetUserScore($userId);
if (!$scoreResult['success']) {
    echo json_encode($scoreResult);
    exit();
}

// Get history
$historyResult = $cUser->cGetScoreHistory($userId, 10);
$history = $historyResult['success'] ? $historyResult['data'] : [];

// Prepare response
$response = [
    'success' => true,
    'data' => [
        'score' => $scoreResult['data'],
        'level' => $scoreResult['data']['level'],
        'history' => $history
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
