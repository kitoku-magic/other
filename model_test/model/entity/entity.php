<?php

abstract class entity
{
  public function is_property_exists($field_name)
  {
    // 5.3以上なら、以下の書き方でいけるんですけどね（笑）
    // $thisには、{テーブル名}_entity_baseクラスのサブクラスが入っている前提
    //return property_exists(get_parent_class($this), $field_name);
    return array_key_exists($field_name, $this->get_table_columns());
  }

  abstract public function get_table_columns();
}
