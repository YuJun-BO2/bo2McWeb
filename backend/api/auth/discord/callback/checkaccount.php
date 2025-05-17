<?php
// checkaccount.php

header('Content-Type: application/json');

// 載入資料庫設定
$config = require __DIR__ . '/db.env.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫連線失敗']);
    exit;
}

// 檢查 GET 是否有傳入 discordID
if (!isset($_GET['discordID'])) {
    http_response_code(400);
    echo json_encode(['error' => '缺少 discordID']);
    exit;
}

$discordID = $_GET['discordID'];

// 查詢是否存在該 Discord ID
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Accounts WHERE discordID = ?");
$stmt->execute([$discordID]);
$count = $stmt->fetchColumn();

// 回傳 JSON 結果
echo json_encode(['exists' => $count > 0]);