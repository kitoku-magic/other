<?php

// PHP7.4.15にて検証

// 改善案は、基本的には以下が良い（データ型によって異なる）
// $is_numeric_valueが、元々is_numeric()の引数に渡していた変数
// int型（負の数値も許容する場合） → 「is_int($is_numeric_value)」
// int型（0以上のみ許容する場合） → 「$is_numeric_value >= 0」
// float型（負の数値も許容する場合）（ただ、INFもNANもtrueになる） → 「is_float($is_numeric_value)」
// float型（0以上のみ許容する場合）（ただ、INFはtrueになる） → 「$is_numeric_value >= 0」
// string型 → いずれのチェック方法も欠点がそれなりにあるので、値に応じてintval()かfloatval()でキャストしてから、チェックした方が良い

class foo
{
}

try
{
  // is_numeric()に渡した結果、結果がtrueになるのは以下の値
  // int型とfloat型の値は全て（INFとNANも）true
  // '-1'
  // '0'
  // '1'
  // '+0'
  // '+1'
  // '-1.1'
  // '-1.0'
  // '-0.1'
  // '0.0'
  // '0.1'
  // '1.0'
  // '1.1'
  // '+0.0'
  // '+0.1'
  // '+1.0'
  // '+1.1'
  // '00'
  // '01'
  // '.0'
  // '.1'
  // '0.'
  // '1.'
  // '-9223372036854775809'
  // '-9223372036854775808'
  // '9223372036854775807'
  // '9223372036854775808'
  // (-1 * PHP_FLOAT_MAX) - 1の文字列
  // (-1 * PHP_FLOAT_MAX)の文字列
  // PHP_FLOAT_MAXの文字列
  // PHP_FLOAT_MAX + 1の文字列
  // ' 1'
  // ' 0'

  // 検証用の値を全て含んだ配列
  $is_numeric_values = [
    // bool
    true,
    false,

    // int
    PHP_INT_MIN - 1,
    PHP_INT_MIN,
    -1,
    0,
    1,
    +0,
    +1,
    PHP_INT_MAX,
    PHP_INT_MAX + 1,
    00,
    01,

    // float
    // PHP_FLOAT_MINは、正の値の中での最小値なので0になる
    PHP_FLOAT_MIN - 0.1,
    PHP_FLOAT_MIN,
    (-1 * PHP_FLOAT_MAX) - 0.1,
    (-1 * PHP_FLOAT_MAX),
    -1.1,
    -1.0,
    -0.1,
    0.0,
    0.1,
    1.0,
    1.1,
    +0.0,
    +0.1,
    +1.0,
    +1.1,
    PHP_FLOAT_MAX,
    PHP_FLOAT_MAX + 0.1,
    00.0,
    01.0,
    INF,
    NAN,

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
    '０',
    '１',
    '+0',
    '+1',
    '-1.1',
    '-1.0',
    '-0.1',
    '0.0',
    '0.1',
    '1.0',
    '1.1',
    '+0.0',
    '+0.1',
    '+1.0',
    '+1.1',
    '[]',
    'array()',
    'array(null)',
    'new foo()',
    "fopen('/tmp/test', 'x')",
    'null',
    'NULL',
    '',
    '00',
    '01',
    '.0',
    '.1',
    '0.',
    '1.',
    // PHP_INT_MIN - 1
    '-9223372036854775809',
    // PHP_INT_MIN
    '-9223372036854775808',
    // PHP_INT_MAX
    '9223372036854775807',
    // PHP_INT_MAX + 1
    '9223372036854775808',
    // (-1 * PHP_FLOAT_MAX) - 1
    '-179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858369',
    // (-1 * PHP_FLOAT_MAX)
    '-179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368',
    // PHP_FLOAT_MAX
    '179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368',
    // PHP_FLOAT_MAX + 1
    '179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858369',
    '1 ',
    ' 1',
    ' 1 ',
    '1　',
    "1\t",
    '0 ',
    ' 0',
    ' 0 ',
    '0　',
    "0\t",
  ];

  echo "--------------------改善前--------------------\n";

  // is_numeric関数での判定結果を出力
  foreach ($is_numeric_values as $is_numeric_value)
  {
    echo var_export($is_numeric_value, true) . ' is ' . var_export(is_numeric($is_numeric_value), true) . "\n";
  }

  // 以下２つは、var_exportで出力出来ないので別枠
  echo 'リソース型 is ' . var_export(is_numeric(fopen('/tmp/test', 'x')), true) . "\n";
  unlink('/tmp/test');

  echo "--------------------改善後--------------------\n";

  // 以下からが改善案（元々の結果に疑問が多いので、よくありがちな数値チェックにしています）
  foreach ($is_numeric_values as $is_numeric_value)
  {
    // int
    if (is_int($is_numeric_value) === true)
    {
      echo "int:\n";

      // 負の数値も許容するならis_intだけで良い
      echo var_export($is_numeric_value, true) . ' is ' . var_export(true, true) . "\n";

      // 0以上のみ許容する場合（基本、これで良いと思われる）
      echo var_export($is_numeric_value, true) . ' is ' . var_export($is_numeric_value >= 0, true) . "\n";

      // 正規表現で、0以上のみ許容する場合
      echo var_export($is_numeric_value, true) . ' is ' . var_export(preg_match('/\A([1-9]\d*|0)\z/', $is_numeric_value) === 1, true) . "\n";

      // filter_varで、0以上のみ許容する場合
      $options = array(
        'options' => array(
          'default' => -1,
          'min_range' => 0,
          'max_range' => PHP_INT_MAX,
        ),
      );
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var($is_numeric_value, FILTER_VALIDATE_INT, $options) >= 0, true) . "\n";
    }
    // float
    else if (is_float($is_numeric_value) === true)
    {
      echo "float:\n";

      // 負の数値も許容するならis_floatだけで良い
      // ただ、INFもNANもtrueになる
      echo var_export($is_numeric_value, true) . ' is ' . var_export(true, true) . "\n";

      // 0以上のみ許容する場合（基本、これで良いと思われる）
      // ただ、INFはtrueになる
      echo var_export($is_numeric_value, true) . ' is ' . var_export($is_numeric_value >= 0, true) . "\n";

      // 正規表現で、0以上のみ許容する場合
      // INFはfalseになる
      // 指数表記が原因か、小数は正規表現ではINT_MAXより大きい数値がチェックできない？ので、number_formatでstringにキャストしている
      // 小数部の値を取得して、桁数を調べる
      $integer = floor($is_numeric_value);
      $decimal = $is_numeric_value - $integer;
      $decimal_number_of_digits = strlen(substr(strrchr($decimal, '.'), 1));
      echo var_export($is_numeric_value, true) . ' is ' . var_export(preg_match('/\A([1-9]\d*|0)(\.\d+)?\z/', number_format($is_numeric_value, $decimal_number_of_digits, '.', '')) === 1, true) . "\n";

      // filter_varで、0以上のみ許容する場合
      // INFはfalseになる
      $options = array(
        'options' => array(
          'default' => -1,
          'min_range' => 0,
          'max_range' => PHP_FLOAT_MAX,
        ),
      );
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var($is_numeric_value, FILTER_VALIDATE_FLOAT, $options) >= 0, true) . "\n";

      /*
      // 以下関数群も、上手くチェック出来ない？
      echo var_export($is_numeric_value, true) . ' is ' . var_export(bccomp($is_numeric_value, '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(bccomp(strval($is_numeric_value), '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(gmp_cmp($is_numeric_value, '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(gmp_cmp(strval($is_numeric_value), '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(gmp_cmp(gmp_init(strval($is_numeric_value)), '0') >= 0, true) . "\n";
      */
    }
    // string
    else if (is_string($is_numeric_value) === true)
    {
      echo "string:\n";

      // 0.0など小数はチェックできない。また、'00'もtrueになる
      // 負の数値もチェック出来ない
      echo var_export($is_numeric_value, true) . ' is ' . var_export(ctype_digit($is_numeric_value), true) . "\n";

      // 小数点任意で、小数チェック可能。'00'もfalseになる
      // コメントアウトの方では、負の数値もチェック可能
      // １０進数の小数のみなら、これが良いか？
      echo var_export($is_numeric_value, true) . ' is ' . var_export(preg_match('/\A([1-9]\d*|0)(\.\d+)?\z/', $is_numeric_value) === 1, true) . "\n";
      //echo var_export($is_numeric_value, true) . ' is ' . var_export(preg_match('/\A(-)?([1-9]\d*|0)(\.\d+)?\z/', $is_numeric_value) === 1, true) . "\n";

      // 空白を置換しないと「1 」などもtrueになる
      // また、「+1」などもtrueになるので、適時置換する
      $replace_chars = [
        " ",
        "　",
        "\t",
        '+',
      ];
      /*
      $options = array(
        'options' => array(
          'default' => null,
          'min_range' => 0,
          'max_range' => PHP_FLOAT_MAX,
        ),
        'flags' => FILTER_FLAG_ALLOW_THOUSAND,
      );
      // 0以上の小数チェック
      // '00'と'.0'もtrueになる
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var(str_replace($replace_chars, 'a', $is_numeric_value), FILTER_VALIDATE_FLOAT, $options) !== null, true) . "\n";
      */

      $options = array(
        'options' => array(
          'default' => null,
          'min_range' => 0,
          'max_range' => PHP_INT_MAX,
        ),
      );
      // 0以上の整数チェック
      // '00'はfalseになる
      // PHP_INT_MAXより大きい数値もfalseになる
      // １０進数の正の整数のみなら、これが良いか？
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var(str_replace($replace_chars, 'a', $is_numeric_value), FILTER_VALIDATE_INT, $options) !== null, true) . "\n";

      /*
      $options = array(
        'options' => array(
          'default' => null,
          'min_range' => (-1 * PHP_FLOAT_MAX),
          'max_range' => PHP_FLOAT_MAX,
        ),
        'flags' => FILTER_FLAG_ALLOW_THOUSAND,
      );
      // 負も含めた小数チェック
      // '00'と'.0'もtrueになる
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var(str_replace($replace_chars, 'a', $is_numeric_value), FILTER_VALIDATE_FLOAT, $options) !== null, true) . "\n";

      $options = array(
        'options' => array(
          'default' => null,
          'min_range' => PHP_INT_MIN,
          'max_range' => PHP_INT_MAX,
        ),
      );
      // 負も含めた整数チェック
      // '00'はfalseになる
      // PHP_INT_MAXより大きい数値もfalseになる
      // PHP_INT_MINより小さい数値もfalseになる
      echo var_export($is_numeric_value, true) . ' is ' . var_export(filter_var(str_replace($replace_chars, 'a', $is_numeric_value), FILTER_VALIDATE_INT, $options) !== null, true) . "\n";
      */

      // 空文字もtrueになるのでNG
      //echo var_export($is_numeric_value, true) . ' is ' . var_export($is_numeric_value >= 0, true) . "\n";

      // 空文字もtrueになるのでNG
      //echo var_export($is_numeric_value, true) . ' is ' . var_export(floatval($is_numeric_value) >= 0, true) . "\n";

      // 空文字もtrueになるのでNG
      //echo var_export($is_numeric_value, true) . ' is ' . var_export(is_float(floatval($is_numeric_value)), true) . "\n";

      /*
      // 以下関数群も、上手くチェック出来ない？
      echo var_export($is_numeric_value, true) . ' is ' . var_export(bccomp($is_numeric_value, '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(gmp_cmp($is_numeric_value, '0') >= 0, true) . "\n";
      echo var_export($is_numeric_value, true) . ' is ' . var_export(gmp_cmp(gmp_init($is_numeric_value), '0') >= 0, true) . "\n";
       */
    }
    // 上記以外の型は、全てfalse扱い
    // 型を見ないでチェックすると、bool型のtrue等も、trueに判定されてしまう時があるので
    else
    {
      echo var_export($is_numeric_value, true) . ' is ' . var_export(false, true) . "\n";
    }
  }

  // リソース型は常にfalse扱い
  echo 'リソース型 is ' . var_export(false, true) . "\n";

  ini_set('memory_limit', -1);
  while (count($is_numeric_values) <= 10000000)
  {
    $is_numeric_values = array_merge($is_numeric_values, $is_numeric_values);
  }

  echo "--------------------「is_numeric」の速度調査--------------------\n";

  // is_numeric関数での判定時間
  $start = microtime(true);
  foreach ($is_numeric_values as $is_numeric_value)
  {
    if (is_numeric($is_numeric_value) === true)
    {
    }
  }
  // time: 1.2068140506744
  // 百万件だと、0.15437483787537
  echo 'time: ' . (microtime(true) - $start) . "\n";

  echo "--------------------「改善案1（比較演算子とctype_digit）」の速度調査--------------------\n";

  $start = microtime(true);
  foreach ($is_numeric_values as $is_numeric_value)
  {
    // int
    if (is_int($is_numeric_value) === true)
    {
      // 0以上のみ許容する場合
      if ($is_numeric_value >= 0)
      {
      }
    }
    // float
    else if (is_float($is_numeric_value) === true)
    {
      // 0以上のみ許容する場合
      if ($is_numeric_value >= 0)
      {
      }
    }
    // string
    else if (is_string($is_numeric_value) === true)
    {
      // 0.0など小数はチェックできない。また、'00'もtrueになる
      // 負の数値もチェック出来ない
      if (ctype_digit($is_numeric_value) === true)
      {
      }
    }
  }
  // time: 1.2588701248169
  // 百万件だと、0.15955901145935
  echo 'time: ' . (microtime(true) - $start) . "\n";

  echo "--------------------「改善案2（正規表現）」の速度調査--------------------\n";

  $start = microtime(true);
  foreach ($is_numeric_values as $is_numeric_value)
  {
    // int
    if (is_int($is_numeric_value) === true)
    {
      // 正規表現で、0以上のみ許容する場合
      if (preg_match('/\A([1-9]\d*|0)\z/', $is_numeric_value) === 1)
      {
      }
    }
    // float
    else if (is_float($is_numeric_value) === true)
    {
      // 正規表現で、0以上のみ許容する場合
      $integer = floor($is_numeric_value);
      $decimal = $is_numeric_value - $integer;
      $decimal_number_of_digits = strlen(substr(strrchr($decimal, '.'), 1));
      if (preg_match('/\A([1-9]\d*|0)(\.\d+)?\z/', number_format($is_numeric_value, $decimal_number_of_digits, '.', '')) === 1)
      {
      }
    }
    // string
    else if (is_string($is_numeric_value) === true)
    {
      // 正規表現で、0以上のみ許容する場合
      if (preg_match('/\A([1-9]\d*|0)(\.\d+)?\z/', $is_numeric_value) === 1)
      {
      }
    }
  }
  // time: 31.550559997559
  // 百万件だと、3.8809390068054
  echo 'time: ' . (microtime(true) - $start) . "\n";

  echo "--------------------「改善案3（filter_var）」の速度調査--------------------\n";

  $start = microtime(true);
  foreach ($is_numeric_values as $is_numeric_value)
  {
    // int
    if (is_int($is_numeric_value) === true)
    {
      // filter_varで、0以上のみ許容する場合
      $options = array(
        'options' => array(
          'default' => -1,
          'min_range' => 0,
          'max_range' => PHP_INT_MAX,
        ),
      );
      if (filter_var($is_numeric_value, FILTER_VALIDATE_INT, $options) >= 0)
      {
      }
    }
    // float
    else if (is_float($is_numeric_value) === true)
    {
      // filter_varで、0以上のみ許容する場合
      $options = array(
        'options' => array(
          'default' => -1,
          'min_range' => 0,
          'max_range' => PHP_FLOAT_MAX,
        ),
      );
      if (filter_var($is_numeric_value, FILTER_VALIDATE_FLOAT, $options) >= 0)
      {
      }
    }
    // string
    else if (is_string($is_numeric_value) === true)
    {
      // filter_varで、0以上のみ許容する場合
      // foreachの外で宣言しても良いが、特に変わらなかった
      $replace_chars = [
        " ",
        "　",
        "\t",
        '+',
      ];
      $options = array(
        'options' => array(
          'default' => null,
          'min_range' => 0,
          'max_range' => PHP_INT_MAX,
        ),
      );
      if (filter_var(str_replace($replace_chars, 'a', $is_numeric_value), FILTER_VALIDATE_INT, $options) !== null)
      {
      }
    }
  }
  // time: 7.8323838710785
  // 百万件だと、1.0369029045105
  echo 'time: ' . (microtime(true) - $start) . "\n";
}
finally
{
  if (file_exists('/tmp/test')) {
    unlink('/tmp/test');
  }
}

// 参考：上記のis_numericの全ての結果
/*
true is false
false is false
-9.223372036854776E+18 is true
-9223372036854775807-1 is true
-1 is true
0 is true
1 is true
0 is true
1 is true
9223372036854775807 is true
9.223372036854776E+18 is true
0 is true
1 is true
-0.1 is true
2.2250738585072014E-308 is true
-1.7976931348623157E+308 is true
-1.7976931348623157E+308 is true
-1.1 is true
-1.0 is true
-0.1 is true
0.0 is true
0.1 is true
1.0 is true
1.1 is true
0.0 is true
0.1 is true
1.0 is true
1.1 is true
1.7976931348623157E+308 is true
1.7976931348623157E+308 is true
0.0 is true
1.0 is true
INF is true
NAN is true
array (
) is false
array (
) is false
array (
  0 => NULL,
) is false
foo::__set_state(array(
)) is false
NULL is false
NULL is false
'true' is false
'false' is false
'-1' is true
'0' is true
'1' is true
'０' is false
'１' is false
'+0' is true
'+1' is true
'-1.1' is true
'-1.0' is true
'-0.1' is true
'0.0' is true
'0.1' is true
'1.0' is true
'1.1' is true
'+0.0' is true
'+0.1' is true
'+1.0' is true
'+1.1' is true
'[]' is false
'array()' is false
'array(null)' is false
'new foo()' is false
'fopen(\'/tmp/test\', \'x\')' is false
'null' is false
'NULL' is false
'' is false
'00' is true
'01' is true
'.0' is true
'.1' is true
'0.' is true
'1.' is true
'-9223372036854775809' is true
'-9223372036854775808' is true
'9223372036854775807' is true
'9223372036854775808' is true
'-179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858369' is true
'-179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368' is true
'179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368' is true
'179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858369' is true
'1 ' is false
' 1' is true
' 1 ' is false
'1　' is false
'1	' is false
'0 ' is false
' 0' is true
' 0 ' is false
'0　' is false
'0	' is false
リソース型 is false
*/
