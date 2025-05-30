<?php
// APIからサーバーリスト取得
$json_data = file_get_contents('https://api.zpw.jp/connect/v2/serverlist.php');
$data = json_decode($json_data, true);

// サーバー名の定義（カッコは正規化される）
$main_server_name = 'here'; // ここにサーバー名入力
$sub_server_base = 'here'; // こっちはReadmeまたはwikiを参照して下さい
 

$main_server_up = false;
$sub_server_up = false;

// サーバー情報の初期化
$server_info = [];

// 半角・全角を同じ扱いにするための関数
function normalize_name($name) {
    return mb_convert_kana($name, 'asKV'); // 半角→全角/全角→半角 変換
}

if (isset($data['status']) && $data['status'] == 'ok') {
    foreach ($data['servers'] as $server_group) {
        foreach ($server_group as $server_details) {
            $normalized_name = normalize_name($server_details['server_name']);
            if (normalize_name($main_server_name) === $normalized_name && isset($server_details['mcinfo'])) {
                $main_server_up = true;
                $server_info = [
                    'name' => $server_details['server_name'],
                    'version' => $server_details['mcinfo']['version'],
                    'motd' => $server_details['mcinfo']['motd'],
                    'online' => $server_details['mcinfo']['players']['online'],
                    'maxplayers' => $server_details['mcinfo']['players']['maxplayers'],
                    'favicon' => $server_details['mcinfo']['favicon'],
                ];
            }

            // サブサーバーの検出カッコ内の文字でサブサーバーが動いてるか確認
            if (strpos($normalized_name, normalize_name($sub_server_base)) === 0 &&
                preg_match('/\(.*test.*\)/u', $server_details['server_name']) &&
                isset($server_details['mcinfo'])) {
                $sub_server_up = true;
            }
        }
    }
}

// ★メインサーバーステータス表示
echo "<h1 style='text-align: center; font-size: 48px; margin-top: 30px;'>";
if ($main_server_up) {
    echo "✅️サーバーは現在起動しています";
    echo "<br><span style='font-size: 24px; color: green; margin-top: 10px;'>さぁ！参加しましょう！</span>";
} else {
    echo "❌️サーバーは現在起動していません";
}
echo "</h1>";

// ★サブサーバーのステータスを表示
echo "<div style='text-align: center; margin-top: 10px; font-size: 20px;'>";
echo "サブサーバー: " . ($sub_server_up ? "✅️" : "❌️");
echo "</div>";
// ↑これコメントにするとサブの表記が消える
// ★そのあとに詳しいメインサーバー情報またはメッセージを表示
echo "<div style='text-align: center; margin-top: 20px;'>";
if ($main_server_up) {
    echo "<h2>" . htmlspecialchars($server_info['name']) . "</h2>";
    echo "<p>Version: " . htmlspecialchars($server_info['version']) . "</p>";
    echo "<p>MOTD: " . htmlspecialchars($server_info['motd']) . "</p>";
    echo "<p>Players: " . $server_info['online'] . " / " . $server_info['maxplayers'] . "</p>";
    echo "<img src='" . htmlspecialchars($server_info['favicon']) . "' alt='Server Favicon' style='margin-top: 10px;'>";
} else {
    echo "<p style='font-size: 24px; margin-top: 20px;'>大変申し訳ございませんが、もうしばらくお待ちください。</p>";
}
echo "</div>";
?>
