<?php
// \frontend\pages\setup\save.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$config = require __DIR__ . '/db.env.php';
$db_host = $config['host'];
$db_user = $config['user'];
$db_pass = $config['password'];
$db_name = $config['dbname'];
$setup_secret  = $config['setup_secret'];
$account_sign_secret = $config['ACCOUNT_SIGN_SECRET'] ?? null;

$discord_id = $_SESSION['discordID'] ?? null;
if (!$discord_id) {
    die('未登入');
}

// 驗證簽名與時效
$timestamp = $_POST['timestamp'] ?? '';
$signature = $_POST['signature'] ?? '';
if (!$timestamp || !$signature) {
    die('缺少簽名資訊');
}
if (abs(time() - intval($timestamp)) > 300) {
    die('簽名過期，請重新整理頁面');
}
$expected_signature = hash_hmac('sha256', strval($discord_id) . strval($timestamp), $setup_secret);
if (!hash_equals($expected_signature, $signature)) {
    die('簽名驗證失敗');
}

// 取得表單資料
$mccName = trim($_POST['mccName'] ?? '');
$minecraftName = trim($_POST['minecraftName'] ?? '');

if ($mccName === '') {
    $result = ['success' => false, 'msg' => '站內暱稱不得為空'];
} else {
    // 連線資料庫
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        $result = ['success' => false, 'msg' => '資料庫連線失敗: ' . $mysqli->connect_error];
    } else {
        // 檢查 mccName 是否重複（排除自己）
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM Accounts WHERE mccName = ? AND discordID != ?");
        $stmt->bind_param("ss", $mccName, $discord_id);
        $stmt->execute();
        $stmt->bind_result($name_count);
        $stmt->fetch();
        $stmt->close();

        if ($name_count > 0) {
            $result = ['success' => false, 'msg' => '站內暱稱已被使用，請換一個'];
        } else {
            // 查詢目前 UUID 狀態
            $stmt = $mysqli->prepare("SELECT minecraftUUID FROM Accounts WHERE discordID = ?");
            $stmt->bind_param("s", $discord_id);
            $stmt->execute();
            $stmt->bind_result($minecraftUUID);
            $stmt->fetch();
            $stmt->close();

            if ($minecraftUUID) {
                // 已綁定 UUID，只能改 mccName
                $stmt = $mysqli->prepare("UPDATE Accounts SET mccName = ? WHERE discordID = ?");
                $stmt->bind_param("ss", $mccName, $discord_id);
                $stmt->execute();
                $stmt->close();
                $result = ['success' => true, 'msg' => '暱稱已更新', 'mccName' => $mccName, 'minecraftUUID' => $minecraftUUID];
            } else {
                // 尚未綁定 UUID，需查 Mojang API
                if ($minecraftName === '') {
                    $result = ['success' => false, 'msg' => '請輸入 Minecraft 玩家名稱'];
                } else {
                    $api_url = "https://api.mojang.com/users/profiles/minecraft/" . urlencode($minecraftName);
                    $json = @file_get_contents($api_url);
                    if ($json === false) {
                        $result = ['success' => false, 'msg' => '查詢 Mojang API 失敗'];
                    } else {
                        $data = json_decode($json, true);
                        if (!isset($data['id'])) {
                            $result = ['success' => false, 'msg' => '找不到該玩家名稱'];
                        } else {
                            $uuid = $data['id'];
                            // 將 uuid 格式化為帶 dash（32字元轉36字元）
                            $uuid = preg_replace(
                                '/^(.{8})(.{4})(.{4})(.{4})(.{12})$/',
                                '$1-$2-$3-$4-$5',
                                $uuid
                            );
                            $stmt = $mysqli->prepare("UPDATE Accounts SET mccName = ?, minecraftUUID = ? WHERE discordID = ?");
                            $stmt->bind_param("sss", $mccName, $uuid, $discord_id);
                            $stmt->execute();
                            $stmt->close();
                            $result = ['success' => true, 'msg' => '綁定成功', 'mccName' => $mccName, 'minecraftUUID' => $uuid];
                        }
                    }
                }
            }
        }
        $mysqli->close();
    }
}

// receipt 頁面一開始就產生 login.php 參數
$login_ts = time();
$login_sig = '';
if ($account_sign_secret && $discord_id) {
    $login_sig = hash_hmac('sha256', strval($discord_id) . '|' . strval($login_ts), $account_sign_secret);
    $login_api = "/api/auth/discord/callback/login.php?redirect=0";
    $login_payload = [
        'discordID' => strval($discord_id),
        'ts' => strval($login_ts),
        'sig' => $login_sig
    ];
} else {
    $login_api = null;
    $login_payload = [];
}

// 安全初始化
$minecraftNameReceipt = '';

// 取得 Minecraft 玩家名稱（如果有 UUID）
if (!empty($result['minecraftUUID'])) {
    $uuid_nodash = str_replace('-', '', $result['minecraftUUID']);
    $api_url = "https://sessionserver.mojang.com/session/minecraft/profile/" . $uuid_nodash;
    $json = @file_get_contents($api_url);
    if ($json !== false) {
        $data = json_decode($json, true);
        if (isset($data['name'])) {
            $minecraftNameReceipt = $data['name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>設定結果</title>
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
      <h2 class="text-2xl font-bold mb-6">設定結果</h2>
      <?php if ($result['success']): ?>
        <div class="mb-6">
          <p class="text-green-700 font-semibold mb-2"><?php echo htmlspecialchars($result['msg']); ?></p>
          <div class="bg-gray-100 rounded-lg p-4 text-left text-gray-700">
            <div><span class="font-bold">站內暱稱：</span><?php echo htmlspecialchars($result['mccName'] ?? ''); ?></div>
            <div><span class="font-bold">Minecraft 玩家名稱：</span><?php echo htmlspecialchars($minecraftNameReceipt); ?></div>
          </div>
        </div>
      <?php else: ?>
        <div class="mb-6">
          <p class="text-red-700 font-semibold mb-2"><?php echo htmlspecialchars($result['msg']); ?></p>
        </div>
      <?php endif; ?>
      <div class="flex flex-col gap-4 mt-8">
        <a href="/" class="px-6 py-3 bg-gray-300 text-gray-800 rounded-lg font-bold hover:bg-gray-400 transition">回首頁</a>
      </div>
      <div id="loginStatus" class="mt-4 text-sm text-gray-500"></div>
    </div>
  </main>
  <?php if ($result['success'] && $account_sign_secret): ?>
  <script>
  window.addEventListener('DOMContentLoaded', function() {
    console.log("開始自動登入，payload：", <?php echo json_encode($login_payload); ?>);
    fetch("<?php echo $login_api; ?>", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(<?php echo json_encode($login_payload); ?>)
    })
    .then(r => r.json())
    .then(res => {
      console.log("login.php 回應：", res);
      if (res.success) {
        document.getElementById('loginStatus').innerText = "Session 已自動更新";
      } else {
        document.getElementById('loginStatus').innerText = "Session 更新失敗：" + (res.error || "未知錯誤");
        if (res.debug) {
          document.getElementById('loginStatus').innerText += "\nDebug: " + JSON.stringify(res.debug, null, 2);
          console.log("login.php debug info:", res.debug);
        }
      }
    })
    .catch(e => {
      document.getElementById('loginStatus').innerText = "Session 更新請求失敗";
      console.log("login.php fetch error:", e);
    });
  });
  </script>
  <?php endif; ?>