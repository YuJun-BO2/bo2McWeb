<?php
// callback.php
$config = require __DIR__ . '/auth.env.php';
$db = require __DIR__ . '/db.env.php';

$client_id = $config['DISCORD_CLIENT_ID'];
$client_secret = $config['DISCORD_CLIENT_SECRET'];
$redirect_uri = $config['DISCORD_REDIRECT_URI'];

if (!isset($_GET['code'])) {
    exit('無法取得授權碼');
}

// 1. 用 code 換 access token
$code = $_GET['code'];
$token_url = 'https://discord.com/api/oauth2/token';

$data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'scope' => 'identify'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/x-www-form-urlencoded",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    exit('獲取 access token 失敗');
}

// 2. 取得使用者資料
$access_token = $token['access_token'];
$user_info_url = 'https://discord.com/api/users/@me';

$opts = [
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
];
$context = stream_context_create($opts);
$user_info = file_get_contents($user_info_url, false, $context);
$user = json_decode($user_info, true);

// 3. 查詢資料庫中是否已經存在這個 Discord 使用者
try {
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Accounts WHERE discordID = ?");
    $stmt->execute([$user['id']]);
    $account_exists = $stmt->fetchColumn() > 0;

    $user['account_exists'] = $account_exists;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫查詢失敗', 'details' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. 如果帳號不存在，導向建立帳號頁面
if (!$user['account_exists']) {
    $secret = $config['ACCOUNT_SIGN_SECRET'];
    $timestamp = time();

    $accountData = [
        'id' => $user['id'],
        'username' => $user['username'],
        'avatar' => $user['avatar'] ?? null,
        'ts' => $timestamp
    ];
    $signature = hash_hmac('sha256', "{$accountData['id']}|$timestamp", $secret);
    $accountData['sig'] = $signature;

    $params = http_build_query($accountData);
    header("Location: NewAccount/index.php?$params");
    exit;
}

//  5. 使用者存在，回傳 JSON（可改為跳轉前端頁面）
header('Content-Type: application/json');
echo json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
