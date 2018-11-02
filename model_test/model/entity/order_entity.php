<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'generated/order_entity_base.php');

class order_entity extends order_entity_base
{
  private $order_item_entities;

  // order_item_entities
  public function set_order_item_entities(array $order_item_entities) { $this->order_item_entities = $order_item_entities; }
  public function get_order_item_entities() { return $this->order_item_entities; }
  public function get_order_item_entity($index) { return $this->order_item_entities[$index]; }
  public function add_order_item_entity(order_item_entity_base $order_item_entity) { $this->order_item_entities[] = $order_item_entity; }
}
