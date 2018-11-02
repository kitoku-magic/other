<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../config/config.php');

// DBハンドル管理クラス
class db_manager
{
  public static function get_storage_handlers()
  {
    $db_handlers = array();

    // マスターへのDBハンドルを取得
    $db_handlers['master'] = self::get_storage_handle(DB_HOST_MASTER);

    // スレーブへのDBハンドルを取得（複数あるスレーブサーバーの内、一つをランダムで選択）
    $slave_number = mt_rand(1, DB_HOST_SLAVE_COUNT);
    $db_host_slave = 'DB_HOST_SLAVE' . $slave_number;
    if (true === defined($db_host_slave))
    {
      $db_handlers['slave'] = self::get_storage_handle(constant($db_host_slave));
    }

    return $db_handlers;
  }

  private static function get_storage_handle($db_host_name)
  {
    // DBハンドルクラスインスタンス
    $dbh = null;

    // DBMSの検索
    $db_handle_class_file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . DB_TYPE . '_storage_handler.php';
    $db_handle_class_name = DB_TYPE . '_storage_handler';
    if (true === file_exists($db_handle_class_file_path) &&
        true === is_readable($db_handle_class_file_path))
    {
      require_once($db_handle_class_file_path);
      $dbh = new $db_handle_class_name();
    }
    else
    {
      throw new Exception('DBが見つかりませんでした');
    }

    // DB接続情報を設定
    $dbh->set_user_name(DB_USER);
    $dbh->set_password(DB_PASSWORD);
    $dbh->set_database_name(DB_NAME);
    $dbh->set_host_name($db_host_name);
    $dbh->set_port_number(DB_PORT);

    // DB接続
    if (false === $dbh->connect())
    {
      // 接続失敗
      throw new Exception($dbh->get_error_message());
    }

    // パスワード情報の消去
    $dbh->set_password('????????????????????');

    return $dbh;
  }
}
