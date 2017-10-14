<?php

/**
 * 会議室の部屋を予約するプログラム
 *
 * 仕様：
 *
 * １．部屋と時間が重複した場合には、基本的に先に予約をした方を優先する
 * ２．予約追加後、ダブルブッキングが起きていない様にする
 * ３．予約変更時にダブルブッキングする場合には、何も行わない
 * ４．程々のパフォーマンスは確保する(REPEATABLE READで検証）
 *
 * 注意：
 *
 * １．100%完全には、予約追加が正常に出来ないので、ご注意下さい（SERIALIZABLEじゃないと不可能？）
 */

/*
実行例：
php reservation_double_booking_check.php 1 2017-10-09 17:00:00 18:30:00 1 2017-10-09 18:30:00 20:00:00 &
php reservation_double_booking_check.php 1 2017-10-09 16:30:00 18:00:00 1 2017-10-09 20:00:00 21:30:00 &
php reservation_double_booking_check.php 1 2017-10-10 21:30:00 23:00:00 1 2017-10-10 20:00:00 21:30:00 &
php reservation_double_booking_check.php 1 2017-10-10 22:00:00 23:30:00 1 2017-10-10 18:30:00 20:00:00 &
php reservation_double_booking_check.php 2 2017-10-09 16:30:00 18:00:00 2 2017-10-09 18:30:00 20:00:00 &
php reservation_double_booking_check.php 2 2017-10-09 16:00:00 17:30:00 2 2017-10-09 20:00:00 21:30:00 &
*/

$conference_room_id = $argv[1];
$reservation_date = $argv[2];
$reservation_time_start = $argv[3];
$reservation_time_end = $argv[4];

$old_conference_room_id = null;
$old_reservation_date = null;
$old_reservation_time_start = null;
$old_reservation_time_end = null;
if (true === isset($argv[5]))
{
  $old_conference_room_id = $argv[5];
}
if (true === isset($argv[6]))
{
  $old_reservation_date = $argv[6];
}
if (true === isset($argv[7]))
{
  $old_reservation_time_start = $argv[7];
}
if (true === isset($argv[8]))
{
  $old_reservation_time_end = $argv[8];
}

$db_host = 'DBサーバーホスト名';
$db_name = 'DB名';
$db_user = 'DBユーザー名';
$db_password = 'DBユーザーのパスワード';

try
{
  $pdo = new PDO(
    'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8mb4',
    $db_user,
    $db_password,
    array(PDO::ATTR_EMULATE_PREPARES => false)
  );
}
catch (PDOException $e)
{
  exit('データベース接続失敗。' . $e->getMessage());
}

// 同時に並列実行させる為の処理
$time = time();
while (1507906410 > $time)
{
  $time = time();
}

//$start = microtime(true);

// 予約追加に失敗した時用のリトライの為のループ（ループ回数は要調整）
for ($j = 0; $j < 5; $j++)
{
  try
  {
    // 同時に実行されにくくする為（INSERTが上手くいきにくくなる）に、揺らぎを持たせる
    usleep(mt_rand(10000, 100000));

    $pdo->beginTransaction();

    $result = select_insert($pdo, $conference_room_id, $reservation_date, $reservation_time_start, $reservation_time_end, $old_conference_room_id, $old_reservation_date, $old_reservation_time_start, $old_reservation_time_end);

    if ($result['ret'] && 1 <= $result['row_count'])
    {
      //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::commit_before' . "\n";
      $pdo->commit();
      //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::commit_after' . "\n";
      // 予約追加が上手くいったので、リトライしないで終了させる
      break;
    }
    else
    {
      // 予約追加が上手くいかなかったので、ロールバックしてリトライする
      $pdo->rollback();
      //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::rollback' . "\n";
    }
  }
  catch (Exception $e)
  {
    $pdo->rollback();
    var_dump($conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::Exception');
    var_dump($e);
  }
}

//$end = microtime(true);

//echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::' . formatMicrotime($end - $start) . '秒' . "\n";

function select_insert($pdo, $conference_room_id, $reservation_date, $reservation_time_start, $reservation_time_end, $old_conference_room_id, $old_reservation_date, $old_reservation_time_start, $old_reservation_time_end)
{
  if (null !== $old_conference_room_id &&
      null !== $old_reservation_date &&
      null !== $old_reservation_time_start &&
      null !== $old_reservation_time_end)
  {
    // 予約変更の場合は、まず予約を削除する
    $sql =
      '
      DELETE FROM conference_room_reservation
      WHERE
        conference_room_id = :conference_room_id AND
        reservation_date = :reservation_date AND
        reservation_time_start = :reservation_time_start AND
        reservation_time_end = :reservation_time_end
      ;';

    $delete_stmt = $pdo->prepare($sql);
    $delete_stmt->bindValue(':conference_room_id', (int) $old_conference_room_id, PDO::PARAM_INT);
    $delete_stmt->bindValue(':reservation_date', $old_reservation_date, PDO::PARAM_STR);
    $delete_stmt->bindValue(':reservation_time_start', $old_reservation_time_start, PDO::PARAM_STR);
    $delete_stmt->bindValue(':reservation_time_end', $old_reservation_time_end, PDO::PARAM_STR);

    //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete1' . "\n";
    $ret = $delete_stmt->execute();
    //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete2' . "\n";
  }

  // ダブルブッキングしている予約が無いかを調べて、無ければ予約を追加する
  // 競合しないギャップロック同士を同時に実行させない為、一本のSQLにまとめる
  $sql =
    '
    INSERT INTO conference_room_reservation (conference_room_id, reservation_date, reservation_time_start, reservation_time_end)
    SELECT :conference_room_id_select_column, :reservation_date_select_column, :reservation_time_start_select_column, :reservation_time_end_select_column FROM dual
    WHERE NOT EXISTS (
        SELECT
          *
        FROM
          conference_room_reservation
        WHERE
          conference_room_id = :conference_room_id_where AND
          reservation_date = :reservation_date_where AND
          reservation_time_start < :reservation_time_end_where AND
          reservation_time_end > :reservation_time_start_where
        FOR UPDATE
    );
  ';
  $insert_stmt = $pdo->prepare($sql);
  $insert_stmt->bindValue(':conference_room_id_select_column', (int) $conference_room_id, PDO::PARAM_INT);
  $insert_stmt->bindValue(':reservation_date_select_column', $reservation_date, PDO::PARAM_STR);
  $insert_stmt->bindValue(':reservation_time_start_select_column', $reservation_time_start, PDO::PARAM_STR);
  $insert_stmt->bindValue(':reservation_time_end_select_column', $reservation_time_end, PDO::PARAM_STR);
  $insert_stmt->bindValue(':conference_room_id_where', (int) $conference_room_id, PDO::PARAM_INT);
  $insert_stmt->bindValue(':reservation_date_where', $reservation_date, PDO::PARAM_STR);
  $insert_stmt->bindValue(':reservation_time_end_where', $reservation_time_end, PDO::PARAM_STR);
  $insert_stmt->bindValue(':reservation_time_start_where', $reservation_time_start, PDO::PARAM_STR);

  //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::1' . "\n";
  $ret = $insert_stmt->execute();
  //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::2' . "\n";

  $row_count = $insert_stmt->rowCount();
  //echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::row_count::' . $row_count . "\n";

  return array('ret' => $ret, 'row_count' => $row_count);
}

function formatMicrotime($time, $format = null)
{
   if (is_string($format))
   {
       $sec  = (int) $time;
       $msec = (int) (($time - $sec) * 100000);
       $formated = date($format, $sec). '.' . $msec;
   }
   else
   {
       $formated = sprintf('%0.5f', $time);
   }

   return $formated;
}
