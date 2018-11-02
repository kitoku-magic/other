<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base_repository_impl.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../order_repository.php');

// orderテーブルのデータ操作を行うリポジトリ
class order_repository_impl extends base_repository_impl implements order_repository
{
  private $order_item_repository;

  public function __construct(array $storage_handlers, order_item_repository $order_item_repository)
  {
    parent::__construct(
      $storage_handlers,
      'order',
      array('order_id'),
      array('order_item')
    );
    $this->order_item_repository = $order_item_repository;
  }

  public function get_order_item_repository()
  {
    return $this->order_item_repository;
  }

  // 商品を注文する
  public function order()
  {
    $order_status = 1;

    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_main_table_name($this->get_table_name());
    $storage_handler->set_columns(array('order_status'));
    $storage_handler->set_values(array('?'));
    $storage_handler->set_bind_params(array(
      $order_status,
    ));
    $storage_handler->set_bind_types(array(
      rdbms_storage_handler::PARAM_INT,
    ));

    // orderテーブルにレコードを追加する
    $affected_rows = $this->insert();

    if (0 < $affected_rows)
    {
      $order_id = $this->get_last_insert_id();
      $item_name = 'order_id' . $order_id . '::item_name1';
      $item_count = 6;

      $this->order_item_repository->set_server_type('master');

      $storage_handler = $this->order_item_repository->get_storage_handler();
      $storage_handler->set_main_table_name($this->order_item_repository->get_table_name());
      $storage_handler->set_columns(array('order_id', 'item_name', 'item_count'));
      $storage_handler->set_values(array('?', '?', '?'));
      $storage_handler->set_bind_params(array(
        $order_id,
        $item_name,
        $item_count
      ));
      $storage_handler->set_bind_types(array(
        rdbms_storage_handler::PARAM_INT,
        rdbms_storage_handler::PARAM_STR,
        rdbms_storage_handler::PARAM_INT
      ));

      // order_itemテーブルにレコードを追加する
      $affected_rows = $this->order_item_repository->insert();
    }

    return $affected_rows;
  }

  // 注文のステータスを取得する
  public function get_order_status($order_id)
  {
    $entity = null;

    $this->set_server_type('slave');

    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_columns(array('order_status'));
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
      $entity = $this->fetch(rdbms_storage_handler::FETCH_ONE);
    }

    return $entity;
  }

  // 注文のステータスを変更する（発送済みにしたり、キャンセルしたり）
  public function change_order_status($order_id, $order_status)
  {
    $this->set_server_type('master');

    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_main_table_name($this->get_table_name());
    $storage_handler->set_values(array(
      array(
        'name' => 'order_status',
        'bracket' => '=',
        'value' => '?',
        'conjunction' => '',
      )
    ));
    $storage_handler->set_where(array(
      array(
        'name' => 'order_id',
        'bracket' => '=',
        'value' => '?',
        'conjunction' => '',
      )
    ));
    $storage_handler->set_bind_params(array(
      $order_status,
      $order_id,
    ));
    $storage_handler->set_bind_types(array(
      rdbms_storage_handler::PARAM_INT,
      rdbms_storage_handler::PARAM_INT,
    ));

    $affected_rows = $this->update();

    return $affected_rows;
  }

  // 注文情報を取得する
  public function get_order($order_id)
  {
    $this->set_server_type('slave');

    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_columns('*');
    $storage_handler->set_main_table_name($this->get_table_name());
    $storage_handler->set_join(array(
      array(
        'join_type' => 'INNER',
        'join_table' => $this->get_order_item_repository()->get_table_name(),
        'join_where' => array(
          array(
            'main_table' => $this->get_table_name(),
            'main_column' => 'order_id',
            'bracket' => '=',
            'relation_table' => $this->get_order_item_repository()->get_table_name(),
            'relation_column' => 'order_id',
            'conjunction' => '',
          ),
        ),
      ),
    ));
    $storage_handler->set_where(array(
      array(
        'table' => $this->get_table_name(),
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

    $entities = array();
    if (true === $result)
    {
      $entities = $this->fetch_all_associated_entity();
    }

    return $entities;
  }

  // 注文を削除する
  public function delete_order($order_id)
  {
    $storage_handler = $this->get_storage_handler();
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

    // orderテーブルからレコードを削除する
    $affected_rows = $this->delete();

    if (0 < $affected_rows)
    {
      $this->order_item_repository->set_server_type('master');

      $storage_handler = $this->order_item_repository->get_storage_handler();
      $storage_handler->set_main_table_name($this->order_item_repository->get_table_name());
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

      // order_itemテーブルからレコードを削除する
      $affected_rows = $this->order_item_repository->delete();
    }

    return $affected_rows;
  }
}
