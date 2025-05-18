<?php
// /api/skip-setup.php

session_start();
header('Content-Type: application/json');

// 載入資料庫設定
$config = require __DIR__ . '/db.env.php';

// 檢查是否登入
if (!isset($_SESSION['uuid'])) {
    http_response_code(403);
    echo json_encode(['error' => '未登入，無法執行操作'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 執行 UPDATE
    $stmt = $pdo->prepare("UPDATE Accounts SET setup_status = 'skip' WHERE uuid = ?");
    $stmt->execute([$_SESSION['uuid']]);

    // 更新 session 中的狀態
    $_SESSION['setup_status'] = 'skip';

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => '資料庫錯誤',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
