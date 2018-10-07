<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base_repository.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../order_repository.php');

// orderテーブルのデータ操作を行うリポジトリ
class order_repository_impl extends base_repository implements order_repository
{
  private $order_item_repository;

  public function __construct(storage_handler $db_storage_handler, order_item_repository $order_item_repository)
  {
    parent::__construct($db_storage_handler, 'order', array('order_id'), array('order_item' . parent::ENTITY_CLASS_SUFFIX => null));
    $this->order_item_repository = $order_item_repository;
  }

  public function order()
  {
    $table_name = '`order`';
    $columns = '(order_status)';
    $values = '(:order_status)';
    $params = array(
      array(
        'name' => ':order_status',
        'value' => 1,
        'data_type' => PDO::PARAM_INT,
      ),
    );
    // orderテーブルにレコードを追加する
    $this->insert($table_name, $columns, $values, $params);

    $table_name = '`order_item`';
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
    $this->order_item_repository->insert($table_name, $columns, $values, $params);
  }

  public function change_order_status($order_id)
  {
    // orderテーブルのstatusを変更する
    $this->update();
  }

  public function get_order_status($order_id)
  {
    $db_storage_handler = $this->get_db_storage_handler();
    $db_storage_handler->set_columns('order_status');
    $db_storage_handler->set_from('`order`');
    $db_storage_handler->set_where('order_id = :order_id');
    $db_storage_handler->set_bind_params(array(
      array(
        'name' => ':order_id',
        'value' => $order_id,
        'data_type' => PDO::PARAM_INT,
      )
    ));
    $result = $this->select();

    $entities = array();
    if (true === $result)
    {
      $entities = $this->fetch_all();
    }

    return $entities;
  }

  public function get_order_item_name($order_id)
  {
    $db_storage_handler = $this->get_db_storage_handler();
    //$db_storage_handler->set_columns('oi.item_name');
    $db_storage_handler->set_columns('*');
    $db_storage_handler->set_from('`order` o INNER JOIN `order_item` oi ON o.order_id = oi.order_id');
    //$db_storage_handler->set_where('o.order_id = :order_id');
    $db_storage_handler->set_where('');
    $db_storage_handler->set_bind_params(array(
      /*array(
        'name' => ':order_id',
        'value' => $order_id,
        'data_type' => PDO::PARAM_INT,
      )*/
    ));
    $result = $this->select();

    $entities = array();
    if (true === $result)
    {
      $entities = $this->fetch_all_associated_entity();
    }

    return $entities;
  }
}
