<?php
// callback.php
$config = require __DIR__ . '/auth.env.php';

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
$discordID = $user['id'];  // 從 Discord API 拿到的 ID

// 呼叫平行目錄的 checkaccount.php
$check_url = "http://localhost/checkaccount.php?discordID=" . urlencode($discordID);
$check_response = file_get_contents($check_url);
$check_result = json_decode($check_response, true);

// 加入回應
$user['account_exists'] = $check_result['exists'] ?? false;

// 4. 輸出整合後的 JSON 回應
header('Content-Type: application/json');
echo json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
