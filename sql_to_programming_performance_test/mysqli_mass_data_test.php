<?php

// WHEREの絞り込みをFETCHの中で行うケース
// 大体、以下前後ぐらい
// time: 1.085854 memory: 46.003906 MB

error_reporting(-1);
ini_set('memory_limit', '128M');

// DB接続
$conn = new mysqli(
  'ホスト名',
  'ユーザー名',
  'パスワード',
  'データベース名',
  ポート番号,
  'ソケットファイルのフルパス'
);
if ($conn_error = mysqli_connect_error())
{
  // DB接続失敗
  var_dump($conn_error);
  exit();
}

// 文字コード設定
$conn->set_charset('utf8mb4');

$start = microtime(true);

$sql = 'SELECT * FROM `items`;';

$stmt = $conn->prepare($sql);

$stmt->execute();

// 以下は、bind_resultではなく、store_resultやget_resultを使用すると落ちる（メモリへの展開方法が違う？）
$row = [];
$params = [];
$meta = $stmt->result_metadata();
while ($field = $meta->fetch_field())
{
  $params[] = &$row[$field->name];
}

call_user_func_array(array($stmt, 'bind_result'), $params);

$meta->close();

unset($params);

$result = [];
while ($stmt->fetch())
{
  // ここで絞り込み
  if ($row['item_status'] === 7)
  {
    $tmp = [];
    foreach($row as $field => $value)
    {
      $tmp[$field] = $value;
    }
    $result[] = $tmp;
  }
}

var_dump(count($result));

$stmt->free_result();

$stmt->close();

$conn->close();

$time = microtime(true) - $start;
$memory = memory_get_peak_usage(true) / 1024 / 1024;

echo sprintf('time: %f memory: %f MB', $time, $memory) . "\n";

