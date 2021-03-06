<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../entity.php');

class order_item_entity_base extends entity
{
  private $order_item_id;

  private $order_id;

  private $item_name;

  private $item_count;

  // order_item_id
  public function set_order_item_id($order_item_id) { $this->order_item_id = $order_item_id; }
  public function get_order_item_id() { return $this->order_item_id; }

  // order_id
  public function set_order_id($order_id) { $this->order_id = $order_id; }
  public function get_order_id() { return $this->order_id; }

  // item_name
  public function set_item_name($item_name) { $this->item_name = $item_name; }
  public function get_item_name() { return $this->item_name; }

  // item_count
  public function set_item_count($item_count) { $this->item_count = $item_count; }
  public function get_item_count() { return $this->item_count; }

  public function get_table_columns()
  {
    // get_object_vars()でも良いけど、ここは呼ばれる回数が多いですしね
    return array(
      'order_item_id' => true,
      'order_id' => true,
      'item_name' => true,
      'item_count' => true,
    );
  }
}
