<?php
// /api/session-check.php
header('Content-Type: application/json');

// 定義允許的前端來源
$allowed_origin = 'https://mcc.bo2.tw';

// 驗證 Referer
if (!isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], $allowed_origin) !== 0) {
    http_response_code(403);
    echo json_encode(['error' => '非法來源'], JSON_UNESCAPED_UNICODE);
    exit;
}

session_start();

echo json_encode([
    'loggedIn' => isset($_SESSION['discordID']),
    'session' => $_SESSION
], JSON_UNESCAPED_UNICODE);
