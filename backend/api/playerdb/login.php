<?php
// /api/playerdb/login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// 載入設定
$config = require __DIR__ . '/../db.env.php';
$secret = $config['api_secret'] ?? '';

// 驗證 secret
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['secret']) || $input['secret'] !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid secret']);
    exit;
}

// 驗證必要欄位
if (!isset($input['uuid'], $input['name'], $input['timestamp'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// 連接資料庫
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 寫入登入紀錄
try {
    $stmt = $pdo->prepare("
        INSERT INTO mc_OceanBlock2_sessions_25S1 (minecraftUUID, player_name, login_time)
        VALUES (:uuid, :name, FROM_UNIXTIME(:timestamp))
    ");
    $stmt->execute([
        ':uuid' => $input['uuid'],
        ':name' => $input['name'],
        ':timestamp' => $input['timestamp']
    ]);
    echo json_encode(['status' => 'ok']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database insert failed', 'details' => $e->getMessage()]);
}

