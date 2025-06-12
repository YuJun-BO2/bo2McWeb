<?php
// /api/playerdb/logout.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// 載入設定
$config = require __DIR__ . '/../db.env.php';
$secret = $config['api_secret'] ?? '';

// 取得輸入資料
$input = json_decode(file_get_contents('php://input'), true);

// 驗證 secret
if (!isset($input['secret']) || $input['secret'] !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid secret']);
    exit;
}

// 驗證欄位
if (!isset($input['uuid'], $input['timestamp'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// 連接資料庫
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 更新最新登入紀錄
try {
    $stmt = $pdo->prepare("
        UPDATE mc_OceanBlock2_sessions_25S1
        SET logout_time = FROM_UNIXTIME(:logout_ts),
            session_duration_seconds = TIMESTAMPDIFF(SECOND, login_time, FROM_UNIXTIME(:logout_ts))
        WHERE minecraftUUID = :uuid AND logout_time IS NULL
        ORDER BY login_time DESC
        LIMIT 1
    ");
    $stmt->execute([
        ':uuid' => $input['uuid'],
        ':logout_ts' => $input['timestamp']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'no-session-found']);
    }
} catch (PDOException $e) {
    http_response_code(response_code: 500);
    echo json_encode(['error' => 'Database update failed', 'details' => $e->getMessage()]);
}
