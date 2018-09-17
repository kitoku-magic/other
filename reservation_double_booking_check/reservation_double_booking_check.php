<?php

/**
 * 会議室の部屋を予約するプログラム
 *
 * 仕様：
 *
 * １．部屋と時間が重複した場合には、基本的に先に予約をした方を優先する
 * ２．予約追加後、ダブルブッキングが起きていない様にする
 * ３．予約変更時にダブルブッキングする場合には、何も行わない
 * ４．程々のパフォーマンスは確保する(MySQLはREPEATABLE READ、PostgreSQLはSERIALIZABLEで検証）
 *
 * 注意：
 *
 * １．使用する場合には、他の仕様等の絡みもありますので、自己責任でお願いします
 * ２．ダブルブッキング防止は出来ているかと思いますが、予約追加は正常なデータでも、100%追加されない場合がありますので、ご注意下さい（リトライ回数を増やす事で、成功率が上がります）
 */

/**
 * PHP7.2.10 MySQL5.7.23 PostgreSQL9.6.10で検証
 */

/**
 *
 * 実行例：
 *
 * sh reservation_double_booking_check.sh mysql insert
 */

// タイムスタンプ値は、こちら https://url-c.com/tc/ で算出
$execute_timestamp = 1537171461;

$retry_count = 5;

$pid = getmypid();

$db_system = $argv[1];
$conference_room_id = $argv[2];
$reservation_date = $argv[3];
$reservation_time_start = $argv[4];
$reservation_time_end = $argv[5];

$old_conference_room_id = null;
$old_reservation_date = null;
$old_reservation_time_start = null;
$old_reservation_time_end = null;
if (true === isset($argv[6]))
{
  $old_conference_room_id = $argv[6];
}
if (true === isset($argv[7]))
{
  $old_reservation_date = $argv[7];
}
if (true === isset($argv[8]))
{
  $old_reservation_time_start = $argv[8];
}
if (true === isset($argv[9]))
{
  $old_reservation_time_end = $argv[9];
}

if ('mysql' === $db_system)
{
  $db_host = 'MySQLサーバーホスト名';
  $db_name = 'MySQLデータベース名';
  $db_user = 'MySQLユーザー名';
  $db_password = 'MySQLパスワード';
  $db_connect_string = 'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8mb4';
}
else if ('postgresql' === $db_system)
{
  $db_host = 'PostgreSQLサーバーホスト名';
  $db_name = 'PostgreSQLデータベース名';
  $db_user = 'PostgreSQLユーザー名';
  $db_password = 'PostgreSQLパスワード';
  $db_connect_string = 'pgsql:host=' . $db_host . ';dbname=' . $db_name;
}

try
{
  $pdo = new PDO(
    $db_connect_string,
    $db_user,
    $db_password,
    array(
      PDO::ATTR_EMULATE_PREPARES => false,
      // エラーモードを指定しないと、エラー時に例外にならないので必ず指定
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    )
  );
}
catch (PDOException $e)
{
  exit('データベース接続失敗。' . $e->getMessage());
}

// 同時に並列実行させる為の処理
$time = time();
while ($execute_timestamp > $time)
{
  $time = time();
}

$start = microtime(true);

// 予約追加に失敗した時用のリトライの為のループ（ループ回数は要調整）
for ($j = 0; $j < $retry_count; $j++)
{
  try
  {
    $is_commit = true;

    // 同時に実行されにくくする為（INSERTが上手くいきにくくなる）に、揺らぎを持たせる
    usleep(mt_rand(10000, 100000));

    $pdo->beginTransaction();

    if ('postgresql' === $db_system)
    {
      // BEGINの後に書かないと、有効にならない
      $pdo->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;');
    }

    if (null !== $old_conference_room_id &&
        null !== $old_reservation_date &&
        null !== $old_reservation_time_start &&
        null !== $old_reservation_time_end)
    {
      // 予約変更の場合は、まず現在の予約を取得して削除する（予約履歴テーブルにも登録する）
      $sql =
      '
        SELECT
          conference_room_reservation_id
        FROM
          conference_room_reservation
        WHERE
          conference_room_id = :conference_room_id AND
          reservation_date = :reservation_date AND
          reservation_time_start = :reservation_time_start AND
          reservation_time_end = :reservation_time_end
        FOR UPDATE
      ;';

      $select_stmt = $pdo->prepare($sql);
      $select_stmt->bindValue(':conference_room_id', (int) $old_conference_room_id, PDO::PARAM_INT);
      $select_stmt->bindValue(':reservation_date', $old_reservation_date, PDO::PARAM_STR);
      $select_stmt->bindValue(':reservation_time_start', $old_reservation_time_start, PDO::PARAM_STR);
      $select_stmt->bindValue(':reservation_time_end', $old_reservation_time_end, PDO::PARAM_STR);

      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete_select_before' . "\n";
      $ret = $select_stmt->execute();
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete_select_after' . "\n";

      if ($ret)
      {
        $result = $select_stmt->fetch(PDO::FETCH_ASSOC);
        if (false !== $result)
        {
          $sql =
          '
            DELETE FROM
              conference_room_reservation
            WHERE
              conference_room_reservation_id = :conference_room_reservation_id
          ;';

          $delete_stmt = $pdo->prepare($sql);
          $delete_stmt->bindValue(':conference_room_reservation_id', (int) $result['conference_room_reservation_id'], PDO::PARAM_INT);

          echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete_before' . "\n";
          $ret = $delete_stmt->execute();
          echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete_after' . "\n";

          $row_count = $delete_stmt->rowCount();
          echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::delete_count::' . $row_count . "\n";

          if ($ret && (1 === $row_count))
          {
            $sql =
            '
              INSERT INTO
                conference_room_reservation_history
                (conference_room_reservation_id, reservation_status)
              VALUES
                (:conference_room_reservation_id, :reservation_status)
            ;';

            $insert_stmt = $pdo->prepare($sql);
            $insert_stmt->bindValue(':conference_room_reservation_id', (int) $result['conference_room_reservation_id'], PDO::PARAM_INT);
            $insert_stmt->bindValue(':reservation_status', 1, PDO::PARAM_INT);

            echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::insert_history_before' . "\n";
            $ret = $insert_stmt->execute();
            echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::insert_history_after' . "\n";

            $row_count = $insert_stmt->rowCount();
            echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $old_conference_room_id . '_' . $old_reservation_date . '_' . $old_reservation_time_start . '_' . $old_reservation_time_end . '::insert_history_count::' . $row_count . "\n";

            if (false === $ret ||
                0 === $row_count)
            {
              $is_commit = false;
            }
          }
          else
          {
            $is_commit = false;
          }
        }
        else
        {
          $is_commit = false;
        }
      }
      else
      {
        $is_commit = false;
      }
    }

    if ($is_commit)
    {
      // ダブルブッキングしている予約が無いかを調べて、無ければ予約を追加する
      // 競合しないギャップロック同士を同時に実行させない為、一本のSQLにまとめる
      $sql =
      '
        INSERT INTO conference_room_reservation (conference_room_id, reservation_date, reservation_time_start, reservation_time_end)
        SELECT :conference_room_id_select_column, :reservation_date_select_column, :reservation_time_start_select_column, :reservation_time_end_select_column
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

      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::insert_before' . "\n";
      $ret = $insert_stmt->execute();
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::insert_after' . "\n";

      $row_count = $insert_stmt->rowCount();
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::insert_row_count::' . $row_count . "\n";

      if (false === $ret ||
          0 === $row_count)
      {
        $is_commit = false;
      }
    }

    if ($is_commit)
    {
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::commit_before' . "\n";
      $pdo->commit();
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::commit_after' . "\n";
      break;
    }
    else
    {
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::rollback_before' . "\n";
      $pdo->rollback();
      echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::rollback_after' . "\n";
      // ここのロールバックは、例外が発生せず、正常系でDB更新が出来なかった時なので、処理を終了させて良い
      break;
    }
  }
  catch (PDOException $e)
  {
    if ($pdo->inTransaction())
    {
      $pdo->rollback();
    }
    echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::PDOException' . "\n";
    var_dump($e->errorInfo);
    if ('mysql' === $db_system)
    {
      if ('40001' === $e->errorInfo[0] && 1213 === $e->errorInfo[1])
      {
        // デッドロックの時は、リトライする
      }
      else if ('23000' === $e->errorInfo[0] && 1062 === $e->errorInfo[1])
      {
        // ユニーク制約エラーの時は、すぐに終了する
        break;
      }
      else if ('HY000' === $e->errorInfo[0] && 1205 === $e->errorInfo[1])
      {
        // ロック取得のタイムアウトの時は、すぐに終了する（秒数の設定によっては、リトライでも良いが）
        break;
      }
      else
      {
        // 上記以外のエラーの時は、すぐに終了する
        break;
      }
    }
    else if ('postgresql' === $db_system)
    {
      if ('40001' === $e->errorInfo[0] && 7 === $e->errorInfo[1])
      {
        // シリアライズエラーの時は、リトライする
      }
      else if ('23505' === $e->errorInfo[0] && 7 === $e->errorInfo[1])
      {
        // ユニーク制約エラーの時は、すぐに終了する
        break;
      }
      else if ('55P03' === $e->errorInfo[0] && 7 === $e->errorInfo[1])
      {
        // ロック取得のタイムアウトの時は、すぐに終了する（秒数の設定によっては、リトライでも良いが）
        break;
      }
      else
      {
        // 上記以外のエラーの時は、すぐに終了する
        break;
      }
    }
  }
  catch (Exception $e)
  {
    if ($pdo->inTransaction())
    {
      $pdo->rollback();
    }
    echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::Exception' . "\n";
    var_dump($e);
    // エラー内容問わず、すぐに終了する
    break;
  }
}

$end = microtime(true);

echo formatMicrotime(microtime(true), 'Y-m-d H:i:s') . ' ' . $pid . ' ' . $conference_room_id . '_' . $reservation_date . '_' . $reservation_time_start . '_' . $reservation_time_end . '::' . formatMicrotime($end - $start) . '秒' . "\n";

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

