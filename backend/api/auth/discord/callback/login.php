<?php
// login.php

// 設定 session 有效時間：180 天（秒）
$session_lifetime = 180 * 24 * 60 * 60; // 15552000 秒

// 設定 Cookie 的屬性（安全、持久）
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'secure' => true,       // 僅 HTTPS 才送 cookie
    'httponly' => true,     // 禁止 JavaScript 存取 cookie
    'samesite' => 'Lax'     // 防止跨站請求送 cookie（避免 CSRF）
]);

// 設定伺服器端 session 的保存時間
ini_set('session.gc_maxlifetime', $session_lifetime);

// 開啟 session
session_start();
header('Content-Type: application/json');

// 額外手動強制重新送 Cookie（確保瀏覽器寫入 180 天）
setcookie(session_name(), session_id(), [
    'expires' => time() + $session_lifetime,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// 載入設定
$config = require __DIR__ . '/db.env.php';
$auth = require __DIR__ . '/auth.env.php';
$secret = $auth['ACCOUNT_SIGN_SECRET'] ?? '';

// 驗證欄位
$required = ['discordID', 'ts', 'sig'];
foreach ($required as $field) {
    if (!isset($_GET[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "缺少必要欄位: $field"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$discordID = $_GET['discordID'];
$timestamp = $_GET['ts'];
$sig = $_GET['sig'];
$allowed_delay = 5;

// 產生預期簽章並驗證
$expected_sig = hash_hmac('sha256', "$discordID|$timestamp", $secret);
if (!hash_equals($expected_sig, $sig)) {
    http_response_code(403);
    echo json_encode(['error' => '簽章驗證失敗'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 驗證時間有效性
if (abs(time() - (int)$timestamp) > $allowed_delay) {
    http_response_code(403);
    echo json_encode(['error' => '簽章已過期'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線資料庫
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

// 查詢使用者資料
$stmt = $pdo->prepare("
    SELECT uuid, discordID, discordName, mccName, setup_status, minecraftUUID, banned
    FROM Accounts
    WHERE discordID = ?
    LIMIT 1
");
$stmt->execute([$discordID]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => '帳號不存在'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 建立 session（登入成功）
$_SESSION['uuid'] = $user['uuid'];
$_SESSION['discordID'] = $user['discordID'];
$_SESSION['discordName'] = $user['discordName'];
$_SESSION['mccName'] = $user['mccName'];
$_SESSION['setup_status'] = $user['setup_status'];
$_SESSION['minecraftUUID'] = $user['minecraftUUID'];
$_SESSION['banned'] = (bool)$user['banned'];

// 導向首頁（可改成 dashboard）
header("Location: /");
exit;
