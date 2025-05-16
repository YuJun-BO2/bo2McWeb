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

// 3. 顯示使用者資料或建立 session
echo "登入成功，歡迎 " . htmlspecialchars($user['username']) . "#" . $user['discriminator'];
// 你可以在這裡設定 session 或 redirect 回首頁
?>
