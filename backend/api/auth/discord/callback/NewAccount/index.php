<?php
// /NewAccount/index.php

header('Content-Type: application/json');

$config = require __DIR__ . '/../db.env.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫連線失敗', 'details' => $e->getMessage()]);
    exit;
}

// 檢查 GET 資料
$required = ['id', 'username'];
foreach ($required as $field) {
    if (!isset($_GET[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "缺少必要欄位: $field"]);
        exit;
    }
}

$discordID = $_GET['id'];
$discordName = $_GET['username'];
$mccName = $discordName;
$discordAvatar = $_GET['avatar'] ?? null;

// 產生 UUID
$uuid = bin2hex(random_bytes(16));
$uuid_formatted = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));

// 用戶 IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

try {
    $stmt = $pdo->prepare("
        INSERT INTO Accounts (
            uuid, discordID, discordName, mccName, discordAvatar, banned, last_IP, created_at, last_login
        ) VALUES (
            :uuid, :discordID, :discordName, :mccName, :discordAvatar, 0, :last_IP, NOW(), NOW()
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

    // 回傳成功與所有建立的欄位
    echo json_encode([
        'success' => true,
        'uuid' => $uuid_formatted,
        'discordID' => $discordID,
        'discordName' => $discordName,
        'discordAvatar' => $discordAvatar,
        'mccName' => $mccName,
        'last_IP' => $ip
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '建立帳號失敗', 'details' => $e->getMessage()]);
}