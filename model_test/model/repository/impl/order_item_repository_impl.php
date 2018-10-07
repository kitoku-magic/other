<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base_repository.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../order_item_repository.php');

// order_itemテーブルのデータ操作を行うリポジトリ
class order_item_repository_impl extends base_repository implements order_item_repository
{
  public function __construct(storage_handler $db_storage_handler)
  {
    parent::__construct($db_storage_handler, 'order_item', array('order_item_id'), array());
  }

  public function order_item()
  {
    $table_name = 'order_item';
    $columns = '(order_id, item_name)';
    $values = '(:order_id, :item_name)';
    $params = array(
      array(
        'name' => ':order_id',
        'value' => 1,
        'data_type' => PDO::PARAM_INT,
      ),
      array(
        'name' => ':item_name',
        'value' => 'order_item::item_nameテスト',
        'data_type' => PDO::PARAM_STR,
      ),
    );
    // order_itemテーブルにレコードを追加する
    $this->insert($table_name, $columns, $values, $params);
  }

  public function get_item_by_order_id($order_id)
  {
    // order_itemテーブルからorder_idに該当するレコードを取得する
    $this->select();
  }

  public function get_all_item()
  {
    // order_itemテーブルから全てのレコードを取得する（ユーザーIDを指定する方が良いでしょうが）
    $this->select();
  }
}
