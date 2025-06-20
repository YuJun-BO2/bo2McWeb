<?php
// filepath: frontend/pages/setup/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$config = require __DIR__ . '/db.env.php';
$db_host = $config['host'];
$db_user = $config['user'];
$db_pass = $config['password'];
$db_name = $config['dbname'];
$secret  = $config['setup_secret'];

$discord_id = $_SESSION['discordID'] ?? null;

$mccName = '';
$minecraftUUID = '';
$minecraftName = '';

if ($discord_id) {
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        die('資料庫連線失敗: ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare("SELECT mccName, minecraftUUID FROM Accounts WHERE discordID = ?");
    $stmt->bind_param("s", $discord_id);
    $stmt->execute();
    $stmt->bind_result($mccName, $minecraftUUID);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    // 如果已經有 UUID，去 Mojang API 查名稱
    if ($minecraftUUID) {
        $uuid_nodash = str_replace('-', '', $minecraftUUID);
        $api_url = "https://sessionserver.mojang.com/session/minecraft/profile/" . $uuid_nodash;
        $json = @file_get_contents($api_url);
        if ($json !== false) {
            $data = json_decode($json, true);
            if (isset($data['name'])) {
                $minecraftName = $data['name'];
            }
        }
    }
}

// 產生 timestamp 與簽名
$timestamp = time();
$signature = '';
if ($discord_id) {
    $signature = hash_hmac('sha256', $discord_id . $timestamp, $secret);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>帳號設定</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('/bg.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col font-sans text-gray-800">
  <main class="flex-grow flex flex-col items-center justify-center text-center p-4">
    <div class="w-full max-w-xl mx-auto bg-white/80 rounded-xl p-10 shadow-lg mt-10 backdrop-blur-lg">
      <h2 class="text-2xl font-bold mb-6">帳號設定</h2>
      <form action="save.php" method="post" class="flex flex-col gap-6">
        <div class="flex flex-col items-start">
          <label for="mccName" class="mb-2 font-semibold text-gray-700">站內暱稱</label>
          <input
            type="text"
            id="mccName"
            name="mccName"
            required
            value="<?php echo htmlspecialchars($mccName); ?>"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="請輸入您的站內暱稱"
          >
        </div>
        <div class="flex flex-col items-start">
          <label for="minecraftName" class="mb-2 font-semibold text-gray-700">Minecraft 玩家名稱</label>
          <input
            type="text"
            id="minecraftName"
            name="minecraftName"
            required
            value="<?php echo htmlspecialchars($minecraftName); ?>"
            <?php if ($minecraftUUID): ?>
              readonly
              disabled
              class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-200 text-gray-500 cursor-not-allowed"
            <?php else: ?>
              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="請輸入您的 Minecraft 玩家名稱"
            <?php endif; ?>
          >
          <?php if ($minecraftUUID): ?>
            <p class="text-sm text-gray-500 mt-2">已綁定 Minecraft 帳號 <?php echo htmlspecialchars($minecraftName); ?>，請聯絡管理員修改。</p>
          <?php endif; ?>
        </div>
        <input type="hidden" name="timestamp" value="<?php echo $timestamp; ?>">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
        <button
          type="submit"
          class="mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition"
        >
          儲存設定
        </button>
      </form>
    </div>
  </main>
</body>
</html>