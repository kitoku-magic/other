<?php

// PHP7.4.7

class foo
{
}

try
{
  // empty()に渡した結果、結果がtrueになるのは以下の値
  // false
  // 0
  // 0.0
  // array()
  // null
  // '0'
  // ''
  // 未定義の変数
  $empty_values = [
    // bool
    true,
    false,

    // int
    -1,
    0,
    1,

    // float
    -1.1,
    -1.0,
    -0.1,
    0.0,
    0.1,
    1.0,
    1.1,

    // array
    [],
    array(),
    array(null),

    // object
    new foo(),

    // null
    null,
    NULL,

    // string
    'true',
    'false',
    '-1',
    '0',
    '1',
    '-1.1',
    '-1.0',
    '-0.1',
    '0.0',
    '0.1',
    '1.0',
    '1.1',
    '[]',
    'array()',
    'array(null)',
    'new foo()',
    "fopen('/tmp/test', 'x')",
    'null',
    'NULL',
    '',
  ];

  // empty関数での判定結果を出力
  foreach ($empty_values as $empty_value)
  {
    echo var_export($empty_value, true) . ' is ' . var_export(empty($empty_value), true) . "\n";
  }

  // 以下２つは、var_exportで出力出来ないので別枠
  echo 'リソース型 is ' . var_export(empty(fopen('/tmp/test', 'x')), true) . "\n";
  unlink('/tmp/test');

  echo '存在しない変数 is ' . var_export(empty($not_exist), true) . "\n";

  // 以下からが改善案（結果は、上記と全く同じ）
  foreach ($empty_values as $empty_value)
  {
    // bool
    if (is_bool($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === false, true) . "\n";
    }
    // int
    else if (is_int($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === 0, true) . "\n";
    }
    // float
    else if (is_float($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === 0.0, true) . "\n";
    }
    // array
    else if (is_array($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export(count($empty_value) === 0, true) . "\n";
    }
    // object
    else if (is_object($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === null, true) . "\n";
    }
    // null
    else if (is_null($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === null, true) . "\n";
    }
    // string
    else if (is_string($empty_value) === true)
    {
      echo var_export($empty_value, true) . ' is ' . var_export($empty_value === '' || $empty_value === '0', true) . "\n";
    }
  }

  // fopenの失敗時の戻り値がfalseの為（なので、リソース型を扱う関数によって違う）
  echo 'リソース型 is ' . var_export(fopen('/tmp/test', 'x') === false, true) . "\n";

  echo '存在しない変数 is ' . var_export(isset($not_exist) === false, true) . "\n";

  // おまけ：「is_null」と「=== null」での比較調査（一千万件で以下なので、正直誤差レベルかと）
  ini_set('memory_limit', -1);
  $null_values = array_fill(0, 10000000, null);
  $start = microtime(true);
  foreach ($null_values as $null_value)
  {
    if (is_null($null_value) === true)
    {
    }
  }
  // time: 0.24362707138062（上記の、「 === true」を省略すると、time: 0.17196798324585）
  echo 'time: ' . (microtime(true) - $start) . "\n";
  $start = microtime(true);
  foreach ($null_values as $null_value)
  {
    if ($null_value === null)
    {
    }
  }
  // time: 0.19953608512878
  echo 'time: ' . (microtime(true) - $start) . "\n";
}
finally
{
  unlink('/tmp/test');
}

// 参考：上記の全ての結果
/*
true is false
false is true
-1 is false
0 is true
1 is false
-1.1 is false
-1.0 is false
-0.1 is false
0.0 is true
0.1 is false
1.0 is false
1.1 is false
array (
) is true
array (
) is true
array (
  0 => NULL,
) is false
foo::__set_state(array(
)) is false
NULL is true
NULL is true
'true' is false
'false' is false
'-1' is false
'0' is true
'1' is false
'-1.1' is false
'-1.0' is false
'-0.1' is false
'0.0' is false
'0.1' is false
'1.0' is false
'1.1' is false
'[]' is false
'array()' is false
'array(null)' is false
'new foo()' is false
'fopen(\'/tmp/test\', \'x\')' is false
'null' is false
'NULL' is false
'' is true
リソース型 is false
存在しない変数 is true
*/
