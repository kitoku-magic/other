<?php

abstract class entity
{
  public function set_entity_data($column, $value)
  {
    $method_name = 'set_' . $column;
    $this->execute_any_setter($method_name, $value);
  }

  public function is_property_exists($field_name)
  {
    // 5.3以上なら、以下の書き方でいけるんですけどね（笑）
    // $thisには、{テーブル名}_entity_baseクラスのサブクラスが入っている前提
    //return property_exists(get_parent_class($this), $field_name);
    return array_key_exists($field_name, $this->get_table_columns());
  }

  public function execute_any_setter($method_name, $value)
  {
    if (true === method_exists($this, $method_name))
    {
      call_user_func_array(array($this, $method_name), array($value));
    }
  }

  abstract public function get_table_columns();
}
