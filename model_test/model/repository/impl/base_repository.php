<?php

// これもインタフェース実装しても良いけど。あとストレージはDBに固定している
class base_repository
{
  const ENTITY_CLASS_SUFFIX = '_entity';

  private $db_storage_handler;

  private $table_name;

  private $primary_keys;

  private $associated_entities;

  public function __construct(storage_handler $db_storage_handler, $table_name, $primary_keys, $associated_entities)
  {
    $this->db_storage_handler = $db_storage_handler;
    $this->table_name = $table_name;
    $this->primary_keys = $primary_keys;
    $this->associated_entities = $associated_entities;
  }

  public function get_db_storage_handler()
  {
    return $this->db_storage_handler;
  }

  public function get_primary_keys()
  {
    return $this->primary_keys;
  }

  public function get_associated_entities()
  {
    return $this->associated_entities;
  }

  // 継承先のクラスからしかアクセスさせない
  protected function select()
  {
    // SELECT文を実行する
    // get()の中に、SQLを実行する処理を書く
    return $this->db_storage_handler->get();
  }

  // 継承先のクラスからしかアクセスさせない
  protected function insert($table_name, $columns, $values, $params)
  {
    // 実際には、こんな書き方しませんが（笑）
    $sql =
    '
      INSERT INTO
    '
    . $table_name
    . $columns 
    .
    '
      VALUES
    ' . $values
    . ';';

    // INSERT文を実行する
    $handle_instance = $this->db_storage_handler->get_handle_instance();
    $stmt = $handle_instance->prepare($sql);
    foreach ($params as $param)
    {
      $stmt->bindValue($param['name'], $param['value'], $param['data_type']);
    }
    $result = $stmt->execute();
  }

  // 継承先のクラスからしかアクセスさせない
  protected function update()
  {
    // UPDATE文を実行する
  }

  // 継承先のクラスからしかアクセスさせない
  protected function delete()
  {
    // DELETE文を実行する
  }

  // 継承先のクラスからしかアクセスさせない
  protected function fetch()
  {
    $this->db_storage_handler->set_entity_class_name($this->table_name . self::ENTITY_CLASS_SUFFIX);
    return $this->db_storage_handler->fetch();
  }

  // 継承先のクラスからしかアクセスさせない
  protected function fetch_all()
  {
    $this->db_storage_handler->set_entity_class_name($this->table_name . self::ENTITY_CLASS_SUFFIX);
    return $this->db_storage_handler->fetch_all();
  }

  protected function fetch_all_associated_entity()
  {
    $this->db_storage_handler->set_repository_class($this);
    $this->db_storage_handler->set_entity_class_name($this->table_name . self::ENTITY_CLASS_SUFFIX);
    // 第２引数にfalseを設定すると、主キーが同じレコードでも、別の配列の要素となる
    //return $this->db_storage_handler->fetch_all_associated_entity($this->get_associated_entities(), false);
    return $this->db_storage_handler->fetch_all_associated_entity($this->get_associated_entities());
  }

  public function begin()
  {
    $this->db_storage_handler->begin();
  }

  public function commit()
  {
    $this->db_storage_handler->commit();
  }

  public function rollback()
  {
    $this->db_storage_handler->rollback();
  }
}
