<?php
// /NewAccount/index.php

header('Content-Type: application/json');

// 載入設定
$config = require __DIR__ . '/../db.env.php';
$auth = require __DIR__ . '/../auth.env.php';
$secret = $auth['ACCOUNT_SIGN_SECRET'] ?? '';

// 驗證必要欄位
$required = ['id', 'username', 'ts', 'sig'];
foreach ($required as $field) {
    if (!isset($_GET[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "缺少必要欄位: $field"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 抽出參數
$discordID = $_GET['id'];
$discordName = $_GET['username'];
$discordAvatar = $_GET['avatar'] ?? null;
$timestamp = $_GET['ts'];
$provided_sig = $_GET['sig'];

// 驗證簽章（固定格式：id|ts）
$expected_sig = hash_hmac('sha256', "$discordID|$timestamp", $secret);

if (!hash_equals($expected_sig, $provided_sig)) {
    http_response_code(403);
    echo json_encode(['error' => '簽章驗證失敗'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 驗證時間是否過期（例如 5 秒內有效）
$allowed_delay = 5; // 單位：秒

if (abs(time() - (int)$timestamp) > $allowed_delay) {
    http_response_code(403);
    echo json_encode(['error' => '簽章已過期'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 設定其他欄位
$mccName = $discordName;
$uuid = bin2hex(random_bytes(16));
$uuid_formatted = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// 建立資料庫連線
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫連線失敗', 'details' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// 寫入資料
try {
    $stmt = $pdo->prepare("
        INSERT INTO Accounts (
            uuid, discordID, discordName, mccName, discordAvatar,
            banned, last_IP, created_at, last_login
        ) VALUES (
            :uuid, :discordID, :discordName, :mccName, :discordAvatar,
            0, :last_IP, NOW(), NOW()
        )
    ");

    $stmt->execute([
        'uuid' => $uuid_formatted,
        'discordID' => $discordID,
        'discordName' => $discordName,
        'mccName' => $mccName,
        'discordAvatar' => $discordAvatar,
        'last_IP' => $ip
    ]);

    echo json_encode([
        'success' => true,
        'uuid' => $uuid_formatted,
        'discordID' => $discordID,
        'discordName' => $discordName,
        'discordAvatar' => $discordAvatar,
        'mccName' => $mccName,
        'last_IP' => $ip
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => '建立帳號失敗',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
