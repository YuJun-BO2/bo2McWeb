<?php
// filepath: c:\Users\hyuju\Documents\GitHub\bo2McWeb\backend\api\auth\discord\callback\login.php

// 支援 POST JSON 輸入
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        $_GET = array_merge($input, $_GET);
    }
}

$session_lifetime = 180 * 24 * 60 * 60; // 15552000 秒

session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', $session_lifetime);
session_start();

setcookie(session_name(), session_id(), [
    'expires' => time() + $session_lifetime,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

$config = require __DIR__ . '/db.env.php';
$auth = require __DIR__ . '/auth.env.php';
$secret = $auth['ACCOUNT_SIGN_SECRET'] ?? '';

// 判斷是否要 redirect（預設 true）
$redirect = true;
if (isset($_GET['redirect'])) {
    $redirect = filter_var($_GET['redirect'], FILTER_VALIDATE_BOOLEAN);
}

// Debug helper
function output_debug($msg, $extra = []) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['error' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$required = ['discordID', 'ts', 'sig'];
foreach ($required as $field) {
    if (!isset($_GET[$field])) {
        if ($redirect) {
            header("Location: /?error=missing_$field");
            exit;
        } else {
            output_debug("缺少必要欄位: $field", [
                'debug' => [
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'input' => file_get_contents('php://input'),
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
                    'method' => $_SERVER['REQUEST_METHOD']
                ]
            ]);
        }
    }
}

// 明確轉型
$discordID = strval($_GET['discordID']);
$timestamp = strval($_GET['ts']);
$sig = $_GET['sig'];
$allowed_delay = 60;

// 明確轉型產生簽章
$expected_sig = hash_hmac('sha256', $discordID . '|' . $timestamp, $secret);

if (!hash_equals($expected_sig, $sig)) {
    if ($redirect) {
        header("Location: /?error=invalid_sig");
        exit;
    } else {
        output_debug('簽章驗證失敗', [
            'debug' => [
                'discordID' => $discordID,
                'timestamp' => $timestamp,
                'sig' => $sig,
                'expected_sig' => $expected_sig,
                // 不再輸出 secret
                'GET' => $_GET,
                'POST' => $_POST,
                'input' => file_get_contents('php://input'),
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD']
            ]
        ]);
    }
}

if (abs(time() - (int)$timestamp) > $allowed_delay) {
    if ($redirect) {
        header("Location: /?error=sig_expired");
        exit;
    } else {
        output_debug('簽章已過期', [
            'debug' => [
                'now' => time(),
                'timestamp' => $timestamp,
                'allowed_delay' => $allowed_delay
            ]
        ]);
    }
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    if ($redirect) {
        header("Location: /?error=db");
        exit;
    } else {
        output_debug('資料庫連線失敗', [
            'debug' => ['details' => $e->getMessage()]
        ]);
    }
}

$stmt = $pdo->prepare("
    SELECT uuid, discordID, discordName, mccName, setup_status, minecraftUUID, banned
    FROM Accounts
    WHERE discordID = ?
    LIMIT 1
");
$stmt->execute([$discordID]);
$user = $stmt->fetch();

if (!$user) {
    if ($redirect) {
        header("Location: /?error=user_not_found");
        exit;
    } else {
        output_debug('帳號不存在');
    }
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$update = $pdo->prepare("UPDATE Accounts SET last_login = NOW(), last_IP = ? WHERE uuid = ?");
$update->execute([$ip, $user['uuid']]);

$_SESSION['uuid'] = $user['uuid'];
$_SESSION['discordID'] = $user['discordID'];
$_SESSION['discordName'] = $user['discordName'];
$_SESSION['mccName'] = $user['mccName'];
$_SESSION['setup_status'] = $user['setup_status'];
$_SESSION['minecraftUUID'] = $user['minecraftUUID'];
$_SESSION['banned'] = (bool)$user['banned'];

if ($redirect) {
    header("Location: /");
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'uuid' => $user['uuid'],
        'discordID' => $user['discordID'],
        'discordName' => $user['discordName'],
        'mccName' => $user['mccName'],
        'setup_status' => $user['setup_status'],
        'minecraftUUID' => $user['minecraftUUID'],
        'banned' => (bool)$user['banned']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}