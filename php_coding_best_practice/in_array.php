<?php

// PHP7.4.15にて検証

try
{
  ini_set('memory_limit', -1);

  //----------------------------------------
  // Part1. 値から一次元配列を検索するケース
  //----------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // in_array 第３引数有り
  // array_search 第３引数有り
  // array_diff
  echo "値から一次元配列を検索するケース\n";

  $from = 1;
  $to = 10000000;
  $exec_count = 20;

  $ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $ids[] = strval($i);
  }

  // for
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $id_count = count($ids);
    for ($j = 0; $j < $id_count; $j++) {
      if ($ids[$j] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.48803457021713
  echo 'for time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($id_count);

  // foreach
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $ids_inner) {
      if ($ids_inner === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.42709006071091
  echo 'foreach time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array($id, $ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.38878568410873
  echo 'in_array 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array($id, $ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.12029095888138
  echo 'in_array 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_search 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_search($id, $ids) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.4321351647377
  echo 'array_search 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_search 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_search($id, $ids, true) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.097854423522949
  echo 'array_search 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_keys 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_keys($ids, $id)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.81065909862518
  echo 'array_keys 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_keys 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_keys($ids, $id, true)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.19808239936829
  echo 'array_keys 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_key_exists array_flip使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_flip($ids);
    if (array_key_exists($id, $new_ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.67201002836227
  echo 'array_key_exists array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // isset array_flip使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_flip($ids);
    if (isset($new_ids[$id])) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.67326946258545
  echo 'isset array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_key_exists array_flip未使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = [];
    foreach ($ids as $new_id) {
      $new_ids[$new_id] = 1;
    }
    if (array_key_exists($id, $new_ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.470672416687
  echo 'array_key_exists array_flip未使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // isset array_flip未使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = [];
    foreach ($ids as $new_id) {
      $new_ids[$new_id] = 1;
    }
    if (isset($new_ids[$id])) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.5077633261681
  echo 'isset array_flip未使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_diff([$id], $ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.071302485466003
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_diff_key array_flip使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_flip($ids);
    if (count(array_diff_key([$id => 1], $new_ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.71137053966522
  echo 'array_diff_key array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";

  unset($id);
  unset($ids);
  unset($new_ids);

  //-----------------------------------------------------------------------------
  // Part2. 値から一次元配列を検索するケース（検索したい値がキーになっている場合）
  //-----------------------------------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_key_exists
  // isset
  // array_diff_key
  echo "値から一次元配列を検索するケース（検索したい値がキーになっている場合）\n";

  $from = 1;
  $to = 10000000;
  $exec_count = 20;

  $ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $ids[strval($i)] = strval($i);
  }

  // for
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $id_count = count($ids);
    for ($j = 1; $j <= $id_count; $j++) {
      if ($ids[$j] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.58931852579117
  echo 'for time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($id_count);

  // foreach（比較に値を使用）
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $ids_inner) {
      if ($ids_inner === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.4022576212883
  echo 'foreach （比較に値を使用） time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // foreach（比較にキーを使用）
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $idx => $ids_inner) {
      if ($idx === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.32323499917984
  echo 'foreach （比較にキーを使用） time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array($id, $ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.38206030130386
  echo 'in_array 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array($id, $ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.10250867605209
  echo 'in_array 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_search 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_search($id, $ids) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.41624916791916
  echo 'array_search 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_search 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_search($id, $ids, true) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.082406961917877
  echo 'array_search 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_keys 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_keys($ids, $id)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.81615974903107
  echo 'array_keys 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_keys 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_keys($ids, $id, true)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.20066704750061
  echo 'array_keys 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_key_exists($id, $ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.0000019431114196777
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (isset($ids[$id])) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.0000014424324035645
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_diff([$id], $ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.088145267963409
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_diff_key([$id => 1], $ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.0000021457672119141
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";

  unset($id);
  unset($ids);

  //----------------------------------------
  // Part3. 値から二次元配列を検索するケース
  //----------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // in_array 第一引数配列
  echo "値から二次元配列を検索するケース\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $ids[] = $inner_ids;
  }

  // for
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $id_count = count($ids);
    for ($j = 0; $j < $id_count; $j++) {
      if ($ids[$j]['id'] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.12671127319336
  echo 'for time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($id_count);

  // foreach
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $ids_inner) {
      if ($ids_inner['id'] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.30708212852478
  echo 'foreach time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (in_array($id, $new_ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16898664236069
  echo 'in_array 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // in_array 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (in_array($id, $new_ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.15193914175034
  echo 'in_array 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_search 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (array_search($id, $new_ids) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.17272605895996
  echo 'array_search 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_search 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (array_search($id, $new_ids, true) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.14840439558029
  echo 'array_search 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_keys 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_keys($new_ids, $id)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.21326947212219
  echo 'array_keys 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_keys 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_keys($new_ids, $id, true)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16089276075363
  echo 'array_keys 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id', 'id');
    if (array_key_exists($id, $new_ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16594141721725
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id', 'id');
    if (isset($new_ids[$id])) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16590288877487
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_diff([$id], $new_ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16469501256943
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id', 'id');
    if (count(array_diff_key([$id => 1], $new_ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.18004214763641
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // in_array 第一引数配列
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array(['id' => $id], $ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.047339403629303
  echo 'in_array 第一引数配列 time: ' . (array_sum($times) / count($times)) . "\n";

  unset($id);
  unset($ids);
  unset($new_ids);

  //-----------------------------------------------------------------------------
  // Part4. 値から二次元配列を検索するケース（検索したい値がキーになっている場合）
  //-----------------------------------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_key_exists
  // isset
  // array_diff_key
  echo "値から二次元配列を検索するケース（検索したい値がキーになっている場合）\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $ids[strval($i)] = $inner_ids;
  }

  // for
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $id_count = count($ids);
    for ($j = 1; $j <= $id_count; $j++) {
      if ($ids[$j]['id'] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.12822226285934
  echo 'for time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($id_count);

  // foreach（比較に値を使用）
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $ids_inner) {
      if ($ids_inner['id'] === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.29972810745239
  echo 'foreach （比較に値を使用） time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // foreach（比較にキーを使用）
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    foreach ($ids as $idx => $ids_inner) {
      if ($idx === $id) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.2160123705864
  echo 'foreach （比較にキーを使用） time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (in_array($id, $new_ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.18307688236237
  echo 'in_array 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // in_array 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (in_array($id, $new_ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.15441280603409
  echo 'in_array 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_search 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (array_search($id, $new_ids) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.18211812973022
  echo 'array_search 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_search 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (array_search($id, $new_ids, true) !== false) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.15816019773483
  echo 'array_search 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_keys 第３引数無し
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_keys($new_ids, $id)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.2180459856987
  echo 'array_keys 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_keys 第３引数有り
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_keys($new_ids, $id, true)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.165389752388
  echo 'array_keys 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (array_key_exists($id, $ids)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.000002.0265579223633
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (isset($ids[$id])) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.000001.2755393981934
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    $new_ids = array_column($ids, 'id');
    if (count(array_diff([$id], $new_ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.16686477661133
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);
  unset($new_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (count(array_diff_key([$id => 1], $ids)) === 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.000002.2411346435547
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($id);

  // in_array 第一引数配列
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $id = strval(mt_rand($from, $to));
    $start = microtime(true);
    if (in_array(['id' => $id], $ids, true)) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.051403450965881
  echo 'in_array 第一引数配列 time: ' . (array_sum($times) / count($times)) . "\n";

  unset($id);
  unset($ids);
  unset($new_ids);
  unset($inner_ids);

  //------------------------------------------------
  // Part5. 一次元配列から一次元配列を検索するケース
  //------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_key_exists array_flip使用
  // isset array_flip使用

  echo "一次元配列から一次元配列を検索するケース\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $from_ids[] = strval($i);
    $to_ids[] = strval($i);
  }

  // 以下、遅すぎるケースは、コメントアウトしています

//  // for
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    //$id = strval(mt_rand($from, $to));
//    $start = microtime(true);
//    $from_id_count = count($from_ids);
//    $to_id_count = count($to_ids);
//    for ($j = 0; $j < $from_id_count; $j++) {
//      for ($k = 0; $k < $to_id_count; $k++) {
//        if ($from_ids[$j] === $to_ids[$k]) {
//        }
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'for time: ' . (array_sum($times) / count($times)) . "\n";

//  // foreach
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    //$id = strval(mt_rand($from, $to));
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      foreach ($to_ids as $to_ids_inner) {
//        if ($from_ids_inner === $to_ids_inner) {
//        }
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'foreach time: ' . (array_sum($times) / count($times)) . "\n";

//  // in_array 第３引数無し
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (in_array($from_ids_inner, $to_ids)) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'in_array 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";

//  // in_array 第３引数有り
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (in_array($from_ids_inner, $to_ids, true)) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time: 0.11983201503754
//  echo 'in_array 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";

//  // array_search 第３引数無し
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (array_search($from_ids_inner, $to_ids) !== false) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'array_search 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";

//  // array_search 第３引数有り
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (array_search($from_ids_inner, $to_ids, true) !== false) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'array_search 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";

//  // array_keys 第３引数無し
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (count(array_keys($to_ids, $from_ids_inner)) > 0) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'array_keys 第３引数無し time: ' . (array_sum($times) / count($times)) . "\n";

//  // array_keys 第３引数有り
//  $times = [];
//  for ($i = 0; $i < $exec_count; $i++) {
//    $start = microtime(true);
//    foreach ($from_ids as $from_ids_inner) {
//      if (count(array_keys($to_ids, $from_ids_inner, true)) > 0) {
//      }
//    }
//    $times[] = microtime(true) - $start;
//  }
//  // time:
//  echo 'array_keys 第３引数有り time: ' . (array_sum($times) / count($times)) . "\n";

  // array_key_exists array_flip使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_ids = array_flip($to_ids);
    foreach ($from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $new_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.1274912238121
  echo 'array_key_exists array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_ids);

  // isset array_flip使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_ids = array_flip($to_ids);
    foreach ($from_ids as $from_ids_inner) {
      if (isset($new_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.12771881818771
  echo 'isset array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_ids);

  // array_key_exists array_flip未使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_ids = [];
    foreach ($to_ids as $new_id) {
      $new_ids[$new_id] = 1;
    }
    foreach ($from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $new_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.17932804822922
  echo 'array_key_exists array_flip未使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_ids);

  // isset array_flip未使用
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_ids = [];
    foreach ($to_ids as $new_id) {
      $new_ids[$new_id] = 1;
    }
    foreach ($from_ids as $from_ids_inner) {
      if (isset($new_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.17409352064133
  echo 'isset array_flip未使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_ids);

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    if (count(array_intersect($from_ids, $to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.3095527529716
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    if (count(array_diff($from_ids, $to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.174349629879
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_flip($from_ids);
    $new_to_ids = array_flip($to_ids);
    if (count(array_intersect_key($new_from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.21016070842743
  echo 'array_intersect_key array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_from_ids = array_flip($from_ids);
    $new_to_ids = array_flip($to_ids);
    if (count(array_diff_key($new_from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.15361819267273
  echo 'array_diff_key array_flip使用 time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  //-------------------------------------------------------------------------------------
  // Part6. 一次元配列から一次元配列を検索するケース（検索したい値がキーになっている場合）
  //-------------------------------------------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_diff_key
  // array_key_exists
  // isset

  echo "一次元配列から一次元配列を検索するケース（検索したい値がキーになっている場合）\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $from_ids[strval($i)] = strval($i);
    $to_ids[strval($i)] = strval($i);
  }

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    foreach ($from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $to_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.060429131984711
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    foreach ($from_ids as $from_ids_inner) {
      if (isset($to_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.061260414123535
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    if (count(array_intersect($from_ids, $to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.2900684118271
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    if (count(array_diff($from_ids, $to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.17477986812592
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    if (count(array_intersect_key($from_ids, $to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.14216200113297
  echo 'array_intersect_key time: ' . (array_sum($times) / count($times)) . "\n";

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    if (count(array_diff_key($from_ids, $to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.014595985412598
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  //------------------------------------------------
  // Part7. 一次元配列から二次元配列を検索するケース
  //------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_key_exists
  // isset
  // array_diff_key

  echo "一次元配列から二次元配列を検索するケース\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $from_ids[] = strval($i);
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $to_ids[] = $inner_ids;
  }

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $new_to_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.24965288639069
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($from_ids as $from_ids_inner) {
      if (isset($new_to_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.24954800605774
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_intersect($from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.4850440263748
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_diff($from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.34470274448395
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_to_ids);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_flip($from_ids);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_intersect_key($new_from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.34917887449265
  echo 'array_intersect_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_from_ids = array_flip($from_ids);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_diff_key($new_from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.27762771844864
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  //-------------------------------------------------------------------------------------
  // Part8. 一次元配列から二次元配列を検索するケース（検索したい値がキーになっている場合）
  //-------------------------------------------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_diff_key
  // array_key_exists
  // isset

  echo "一次元配列から二次元配列を検索するケース（検索したい値がキーになっている場合）\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $from_ids[strval($i)] = strval($i);
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $to_ids[strval($i)] = $inner_ids;
  }

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $new_to_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.25509669780731
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($from_ids as $from_ids_inner) {
      if (isset($new_to_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.25554367303848
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_intersect($from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.4582808852196
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_diff($from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.34679877758026
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_to_ids);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_intersect_key($from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.34460245370865
  echo 'array_intersect_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_to_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_diff_key($from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.20876059532166
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_to_ids);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  //------------------------------------------------
  // Part9. 二次元配列から二次元配列を検索するケース
  //------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_key_exists
  // isset
  // array_diff_key

  echo "二次元配列から二次元配列を検索するケース\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $from_ids[] = $inner_ids;
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $to_ids[] = $inner_ids;
  }

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($new_from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $new_to_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.47487887144089
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id', 'id');
    foreach ($new_from_ids as $from_ids_inner) {
      if (isset($new_to_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.47625352144241
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_intersect($new_from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.6631036043167
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_diff($new_from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.56159864664078
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id', 'id');
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_intersect_key($new_from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.60023413896561
  echo 'array_intersect_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_from_ids = array_column($from_ids, 'id', 'id');
    $new_to_ids = array_column($to_ids, 'id', 'id');
    if (count(array_diff_key($new_from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.46869833469391
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  //-------------------------------------------------------------------------------------
  // Part10. 二次元配列から二次元配列を検索するケース（検索したい値がキーになっている場合）
  //-------------------------------------------------------------------------------------
  // 以下のどれかが推奨（上から推奨順）
  // array_diff_key

  echo "二次元配列から二次元配列を検索するケース（検索したい値がキーになっている場合）\n";

  $from = 1;
  $to = 1000000;
  $exec_count = 20;

  $from_ids = [];
  $to_ids = [];
  for ($i = $from; $i <= $to; $i++) {
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $from_ids[strval($i)] = $inner_ids;
    $inner_ids = [];
    $inner_ids['id'] = strval($i);
    $to_ids[strval($i)] = $inner_ids;
  }

  // array_key_exists
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    foreach ($new_from_ids as $from_ids_inner) {
      if (array_key_exists($from_ids_inner, $to_ids)) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.27256939411163
  echo 'array_key_exists time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);

  // isset
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    foreach ($new_from_ids as $from_ids_inner) {
      if (isset($to_ids[$from_ids_inner])) {
      }
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.27317724227905
  echo 'isset time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);

  // array_intersect
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_intersect($new_from_ids, $new_to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 1.7256582736969
  echo 'array_intersect time: ' . (array_sum($times) / count($times)) . "\n";
  unset($new_from_ids);
  unset($new_to_ids);

  // array_diff
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    $new_from_ids = array_column($from_ids, 'id');
    $new_to_ids = array_column($to_ids, 'id');
    if (count(array_diff($new_from_ids, $new_to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.60451446771622
  echo 'array_diff time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);
  unset($new_from_ids);
  unset($new_to_ids);

  // array_intersect_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    if (count(array_intersect_key($from_ids, $to_ids)) > 0) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.43523551225662
  echo 'array_intersect_key time: ' . (array_sum($times) / count($times)) . "\n";

  // array_diff_key
  $times = [];
  for ($i = 0; $i < $exec_count; $i++) {
    $start = microtime(true);
    $from_ids_count = count($from_ids);
    if (count(array_diff_key($from_ids, $to_ids)) < $from_ids_count) {
    }
    $times[] = microtime(true) - $start;
  }
  // time: 0.0129274725914
  echo 'array_diff_key time: ' . (array_sum($times) / count($times)) . "\n";
  unset($from_ids_count);

  unset($from_ids);
  unset($to_ids);
  unset($new_ids);
  unset($from_ids_count);
}
finally
{
  // 以下、挙動調査
  //---------------------------
  // in_arrayの挙動
  //---------------------------
  $id = 1;

  $ids = [1];

  var_dump(in_array($id, $ids));
  var_dump(in_array($id, $ids, true));

  $id = '1';

  var_dump(in_array($id, $ids));
  var_dump(in_array($id, $ids, true));

  $id = '1';

  $ids = ['1'];

  var_dump(in_array($id, $ids));
  var_dump(in_array($id, $ids, true));

  $id = 1;

  var_dump(in_array($id, $ids));
  var_dump(in_array($id, $ids, true));

  //------------------------------
  // array_key_existsとissetの挙動
  //------------------------------
  // 以下は全てtrueになるので、キー値の型の差異は見ていない模様
  $id = '1';

  $ids = ['1' => true];

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  $id = 1;

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  $ids = [1 => true];

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  $id = '1';

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  // 以下もtrueになるので注意
  $id = null;
  $ids = ['' => true];

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  // issetは値がnullの時には、falseになるので注意
  $id = 1;
  $ids = [1 => null];

  var_dump(array_key_exists($id, $ids));
  var_dump(isset($ids[$id]));

  //---------------------------------
  // array_diffとarray_diff_keyの挙動
  //---------------------------------
  // 以下は全てtrueになるので、型の差異は見ていない模様
  $from_ids = [0 => 1, 1 => 2];
  $to_ids = [0 => 1, 1 => 2];

  var_dump(count(array_diff($from_ids, $to_ids)) === 0);
  var_dump(count(array_diff_key($from_ids, $to_ids)) === 0);

  $from_ids = [0 => 1, 1 => 2];
  $to_ids = ['0' => '1', '1' => '2'];

  var_dump(count(array_diff($from_ids, $to_ids)) === 0);
  var_dump(count(array_diff_key($from_ids, $to_ids)) === 0);
}
