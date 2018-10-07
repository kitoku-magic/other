<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../config/config.php');

// 汎用RDBMS
abstract class rdbms_storage_handler
{
  private $handle_instance;

  public function __construct()
  {
    $this->handle_instance = new PDO(
      'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';unix_socket=' . DB_UNIX_SOCKET . ';charset=' . DB_CHARACTER_SET,
      DB_USER,
      DB_PASSWORD,
      array(
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      )
    );
  }

  public function get_handle_instance()
  {
    return $this->handle_instance;
  }

  // データを１行フェッチする
  abstract public function fetch();

  // データを全件フェッチする
  abstract public function fetch_all();

  // トランザクションを開始する
  abstract public function begin();

  // トランザクションをコミットする
  abstract public function commit();

  // トランザクションをロールバックする
  abstract public function rollback();
}

