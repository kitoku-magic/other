<?php

// WHEREの絞り込みをSQL内で行うケース
// 大体、以下前後ぐらい
// time: 0.718613 memory: 46.003906 MB

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

$sql = 'SELECT * FROM `items` WHERE `item_status` = ?;';

$stmt = $conn->prepare($sql);
$itemStatus = 7;
$stmt->bind_param('i', $itemStatus);
$stmt->execute();

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
  $tmp = [];
  foreach($row as $field => $value)
  {
    $tmp[$field] = $value;
  }
  $result[] = $tmp;
}

var_dump(count($result));

$stmt->free_result();

$stmt->close();

$conn->close();

$time = microtime(true) - $start;
$memory = memory_get_peak_usage(true) / 1024 / 1024;

echo sprintf('time: %f memory: %f MB', $time, $memory) . "\n";

