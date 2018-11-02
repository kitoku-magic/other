<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base_repository_impl.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../order_item_repository.php');

// order_itemテーブルのデータ操作を行うリポジトリ
class order_item_repository_impl extends base_repository_impl implements order_item_repository
{
  public function __construct(array $storage_handlers)
  {
    parent::__construct($storage_handlers, 'order_item', array('order_item_id'), array());
  }

  // 注文IDから注文した商品を取得する
  public function get_order_item_by_order_id($order_id)
  {
    $entities = array();

    $this->set_server_type('slave');

    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_columns();
    $storage_handler->set_main_table_name($this->get_table_name());
    $storage_handler->set_where(array(
      array(
        'name' => 'order_id',
        'bracket' => '=',
        'value' => '?',
        'conjunction' => '',
      )
    ));
    $storage_handler->set_bind_params(array(
      $order_id,
    ));
    $storage_handler->set_bind_types(array(
      rdbms_storage_handler::PARAM_INT,
    ));

    $result = $this->select();

    if (true === $result)
    {
      $entities = $this->fetch(rdbms_storage_handler::FETCH_ALL);
    }

    return $entities;
  }

  // 注文した全ての商品を取得する（自分の注文に限定した方が良いでしょうが）
  public function get_all_order_item()
  {
    return $this->select();
  }
}
