<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../base_repository.php');

class base_repository_impl implements base_repository
{
  const ENTITY_CLASS_SUFFIX = '_entity';

  private $storage_handlers;

  private $table_name;

  private $primary_keys;

  private $associated_tables;

  private $associated_entities;

  private $server_type;

  public function __construct(array $storage_handlers, $table_name, array $primary_keys, array $associated_tables)
  {
    $this->storage_handlers = $storage_handlers;
    $this->table_name = $table_name;
    $this->primary_keys = $primary_keys;
    $this->associated_tables = $associated_tables;
    $this->associated_entities = array();
    foreach ($associated_tables as $associated_table)
    {
      $this->associated_entities[] = $associated_table . self::ENTITY_CLASS_SUFFIX;
    }
  }

  public function get_primary_keys()
  {
    return $this->primary_keys;
  }

  public function begin()
  {
    $this->set_server_type('master');

    return $this->get_storage_handler()->begin();
  }

  public function commit()
  {
    return $this->get_storage_handler()->commit();
  }

  public function rollback()
  {
    return $this->get_storage_handler()->rollback();
  }

  protected function get_storage_handler()
  {
    return $this->storage_handlers[$this->get_server_type()];
  }

  protected function get_table_name()
  {
    return $this->table_name;
  }

  protected function get_associated_tables()
  {
    return $this->associated_tables;
  }

  protected function get_associated_entities()
  {
    return $this->associated_entities;
  }

  protected function set_server_type($server_type)
  {
    if ('master' === $server_type || 'slave' === $server_type)
    {
      return $this->server_type = $server_type;
    }
    else
    {
      throw new Exception('サーバーの種別が誤っています');
    }
  }

  protected function get_server_type()
  {
    return $this->server_type;
  }

  protected function select()
  {
    return $this->get_storage_handler()->get();
  }

  protected function insert()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_INSERT);
  }

  protected function update()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_UPDATE);
  }

  protected function delete()
  {
    return $this->get_storage_handler()->set(rdbms_storage_handler::SQL_DELETE);
  }

  protected function fetch($mode)
  {
    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_table_name($this->get_table_name());
    $storage_handler->set_entity_class_name($this->get_table_name() . self::ENTITY_CLASS_SUFFIX);

    return $storage_handler->fetch($mode);
  }

  protected function fetch_all_associated_entity($unique_primary_key_data = true)
  {
    $storage_handler = $this->get_storage_handler();
    $storage_handler->set_primary_keys($this->get_primary_keys());
    $storage_handler->set_table_name($this->get_table_name());
    $storage_handler->set_entity_class_name($this->get_table_name() . self::ENTITY_CLASS_SUFFIX);
    $storage_handler->set_associated_tables($this->get_associated_tables());
    $storage_handler->set_associated_entities($this->get_associated_entities());

    // 引数にfalseを設定すると、主キーが同じレコードでも、別の配列の要素となる
    return $storage_handler->fetch_all_associated_entity($unique_primary_key_data);
  }

  protected function get_last_insert_id()
  {
    return $this->get_storage_handler()->get_last_insert_id();
  }
}
