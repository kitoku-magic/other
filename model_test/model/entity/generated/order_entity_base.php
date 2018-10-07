<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../entity.php');

class order_entity_base extends entity
{
  private $order_id;

  private $order_status;

  // order_id
  public function get_order_id() { return $this->order_id; }
  public function set_order_id($order_id) { $this->order_id = $order_id; }

  // order_status
  public function get_order_status() { return $this->order_status; }
  public function set_order_status($order_status) { $this->order_status = $order_status; }

  public function get_table_columns()
  {
    // get_object_vars()でも良いけど、ここは呼ばれる回数が多いですしね
    return array(
      'order_id' => null,
      'order_status' => null,
    );
  }
}
