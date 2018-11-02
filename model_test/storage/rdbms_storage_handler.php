<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage_handler.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../model/entity/entity.php');

// 汎用RDBMS
abstract class rdbms_storage_handler implements storage_handler
{
  const SQL_INSERT = 'insert';

  const SQL_UPDATE = 'update';

  const SQL_DELETE = 'delete';

  const PARAM_INT = 'int';

  const PARAM_STR = 'string';

  const FETCH_ONE = 1;

  const FETCH_ALL = 2;

  const ENTITY_CLASS_DIRECTORY = '../model/entity/';

  private $user_name;

  private $password;

  private $database_name;

  private $host_name;

  private $port_number;

  private $error_message;

  private $primary_keys;

  private $table_name;

  private $connection;

  private $columns;

  private $main_table_name;

  private $join;

  private $where;

  private $group_by;

  // 以下、having・order_byなどが続く

  private $values;

  private $bind_types;

  private $bind_params;

  private $entity_class_name;

  private $associated_tables;

  private $associated_entities;

  private $main_entity;

  private $entities;

  // データをフェッチする
  abstract public function fetch($mode);

  // 関連エンティティも含めて、全てのデータをフェッチする
  abstract public function fetch_all_associated_entity($unique_primary_key_data);

  // 最後に追加したレコードの一意なIDを取得する
  abstract public function get_last_insert_id();

  // トランザクションを開始する
  abstract public function begin();

  // トランザクションをコミットする
  abstract public function commit();

  // トランザクションをロールバックする
  abstract public function rollback();

  // 指定されたテーブルのメタデータを取得する
  abstract protected function get_table_meta_data($table_name);

  // エンティティクラスのフィールドに対して、適切な型のデータを設定する為にキャストする
  abstract protected function get_cast_value(array $table_meta_data, $column_name, $column_value);

  // 変更された行数を取得する
  abstract protected function get_affected_rows();

  public function __construct()
  {
    $this->entities = array();
  }

  public function set_user_name($user_name)
  {
    $this->user_name = $user_name;
  }

  public function get_user_name()
  {
    return $this->user_name;
  }

  public function set_password($password)
  {
    $this->password = $password;
  }

  public function get_password()
  {
    return $this->password;
  }

  public function set_database_name($database_name)
  {
    $this->database_name = $database_name;
  }

  public function get_database_name()
  {
    return $this->database_name;
  }

  public function set_host_name($host_name)
  {
    $this->host_name = $host_name;
  }

  public function get_host_name()
  {
    return $this->host_name;
  }

  public function set_port_number($port_number)
  {
    $this->port_number = $port_number;
  }

  public function get_port_number()
  {
    return $this->port_number;
  }

  public function set_error_message($error_message)
  {
    $this->error_message = $error_message;
  }

  public function get_error_message()
  {
    return $this->error_message;
  }

  public function set_primary_keys(array $primary_keys)
  {
    $this->primary_keys = $primary_keys;
  }

  protected function get_primary_keys()
  {
    return $this->primary_keys;
  }

  public function set_table_name($table_name)
  {
    $this->table_name = $table_name;
  }

  protected function get_table_name()
  {
    return $this->table_name;
  }

  protected function set_connection($connection)
  {
    $this->connection = $connection;
  }

  protected function get_connection()
  {
    return $this->connection;
  }

  public function set_columns($columns = '*')
  {
    $sql_escape_character = $this->get_sql_escape_character();
    if (true === is_array($columns))
    {
      $result = '';
      foreach ($columns as $column)
      {
        $result .= $sql_escape_character . $column . $sql_escape_character . ', ';
      }
      $this->columns = rtrim($result, ', ');
    }
    else
    {
      $this->columns = $columns;
    }
  }

  protected function get_columns()
  {
    return $this->columns;
  }

  public function set_main_table_name($main_table_name)
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $this->main_table_name = $sql_escape_character . $main_table_name . $sql_escape_character;
  }

  protected function get_main_table_name()
  {
    return $this->main_table_name;
  }

  public function set_join(array $join)
  {
    $this->join = $join;
  }

  protected function get_join()
  {
    return $this->join;
  }

  protected function make_join()
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $result = '';
    $join = $this->get_join();
    if (null !== $join)
    {
      foreach ($join as $value)
      {
        $result .= ' ' . $value['join_type'] . ' JOIN ' . $sql_escape_character . $value['join_table'] . $sql_escape_character . ' ON ';
        foreach ($value['join_where'] as $join_where)
        {
          $result .= $sql_escape_character . $join_where['main_table'] . $sql_escape_character . '.' . $sql_escape_character . $join_where['main_column'] . $sql_escape_character . ' ' . $join_where['bracket'] .
            ' ' . $sql_escape_character . $join_where['relation_table'] . $sql_escape_character . '.' . $sql_escape_character . $join_where['relation_column'] . $sql_escape_character . ' ' . $join_where['conjunction'] . ' ';
        }
      }
    }

    return $result;
  }

  public function set_where(array $where)
  {
    $this->where = $where;
  }

  protected function get_where()
  {
    return $this->where;
  }

  protected function make_where($values)
  {
    $sql_escape_character = $this->get_sql_escape_character();
    $result = '';
    if (null !== $values)
    {
      foreach ($values as $value)
      {
        if (true === isset($value['table']))
        {
          $result .= $sql_escape_character . $value['table'] . $sql_escape_character . '.';
        }
        $result .= $sql_escape_character . $value['name'] . $sql_escape_character . ' ' . $value['bracket'] . ' ' . $value['value'] . ' ' . $value['conjunction'] . ' ';
      }
    }

    return $result;
  }

  public function set_group_by($group_by)
  {
    $this->group_by = $group_by;
  }

  protected function get_group_by()
  {
    return $this->group_by;
  }

  public function set_values(array $values)
  {
    $this->values = $values;
  }

  protected function get_values()
  {
    return $this->values;
  }

  protected function make_values()
  {
    $result = '';
    $values = $this->get_values();

    if (null !== $values)
    {
      $result = implode(', ', $values);
    }

    return $result;
  }

  public function set_bind_types($bind_types)
  {
    $this->bind_types = $bind_types;
  }

  protected function get_bind_types()
  {
    return $this->bind_types;
  }

  public function set_bind_params(array $bind_params)
  {
    $this->bind_params = $bind_params;
  }

  protected function get_bind_params()
  {
    return $this->bind_params;
  }

  public function set_entity_class_name($entity_class_name)
  {
    $this->entity_class_name = $entity_class_name;
  }

  protected function get_entity_class_name()
  {
    return $this->entity_class_name;
  }

  public function set_associated_tables(array $associated_tables)
  {
    $this->associated_tables = $associated_tables;
  }

  protected function get_associated_tables()
  {
    return $this->associated_tables;
  }

  public function set_associated_entities(array $associated_entities)
  {
    $this->associated_entities = $associated_entities;
  }

  protected function get_associated_entities()
  {
    return $this->associated_entities;
  }

  protected function set_main_entity(entity $main_entity)
  {
    $this->main_entity = $main_entity;
  }

  protected function get_main_entity()
  {
    return $this->main_entity;
  }

  protected function set_entities(array $entities)
  {
    $this->entities = $entities;
  }

  protected function get_entities()
  {
    return $this->entities;
  }

  protected function get_entity($entity_index)
  {
    return $this->entities[$entity_index];
  }

  protected function add_entities(entity $entity)
  {
    $this->entities[] = $entity;
  }

  protected function get_sql_escape_character()
  {
    return constant(get_class($this) . '::SQL_ESCAPE_CHARACTER');
  }

  public function get()
  {
    $sql = 'SELECT ' . $this->get_columns();

    // FROM句が無いSQLも有り得るので
    $main_table_name = $this->get_main_table_name();
    if (null !== $main_table_name)
    {
      $sql .= ' FROM ' . $main_table_name;
    }

    $join = $this->make_join();
    if ('' !== $join)
    {
      $sql .= $join;
    }

    $where = $this->make_where($this->get_where());
    if ('' !== $where)
    {
      $sql .= ' WHERE ' . $where;
    }
    // 以下、GROUP BYなどが続く

    return $this->execute_sql($sql);
  }

  public function set($mode)
  {
    $sql = '';

    // INSERT・UPDATE・DELETEを実行する
    if (self::SQL_INSERT === $mode)
    {
      $sql = 'INSERT INTO ' . $this->get_main_table_name();
      $columns = $this->get_columns();
      if (null !== $columns)
      {
        $sql .= '(' . $columns . ')';
      }
      $values = $this->make_values();
      if ('' !== $values)
      {
        $sql .= ' VALUES(' . $values . ')';
      }
    }
    else if (self::SQL_UPDATE === $mode)
    {
      $sql = 'UPDATE ' . $this->get_main_table_name();
      $values = $this->make_where($this->get_values());
      if ('' !== $values)
      {
        $sql .= ' SET ' . $values;
      }
      $where = $this->make_where($this->get_where());
      if ('' !== $where)
      {
        $sql .= ' WHERE ' . $where;
      }
    }
    else if (self::SQL_DELETE === $mode)
    {
      $sql = 'DELETE FROM ' . $this->get_main_table_name();
      $where = $this->make_where($this->get_where());
      if ('' !== $where)
      {
        $sql .= ' WHERE ' . $where;
      }
    }
    else
    {
      throw new Exception('SQLのモードが不明です');
    }

    $result = $this->execute_sql($sql);

    if (true === $result)
    {
      $result = $this->get_affected_rows();
    }
    else
    {
      throw new Exception('SQLの実行に失敗しました');
    }

    return $result;
  }

  protected function set_all_entities(
    $unique_primary_key_data,
    array $data,
    array $main_table_meta_data,
    $entity_class_name,
    array $associated_entities,
    array $associated_tables
  ) {
    $main_entity = $this->get_main_entity();
    $entities = $this->get_entities();

    $entity_created = false;
    if (true === $unique_primary_key_data &&
      null !== $main_entity)
    {
      // 既にエンティティが設定済みで、主キー毎に配列要素をまとめたい時
      $row_values = array();
      $entity_values = array();
      $main_entity_index = array_search($main_entity, $entities, true);
      $primary_keys = $this->get_primary_keys();
      // 主キーにデータが設定され、エンティティが関連付いているかどうか調べる
      foreach ($primary_keys as $primary_key)
      {
        if (true === isset($data[$primary_key]))
        {
          $value = $data[$primary_key];
          $row_values[] = $this->get_cast_value($main_table_meta_data, $primary_key, $value);

          if (false !== $main_entity_index)
          {
            $method_name = 'get_' . $primary_key;
            $entity_values[] = call_user_func_array(array($entities[$main_entity_index], $method_name), array());
          }
        }
      }
      // 取得したカラムに、全ての主キーが含まれている &&
      // 既に設定済みのエンティティの全ての主キーの値に、nullが含まれていない &&
      // 取得したカラムの主キーの値と、既に設定済みのエンティティの主キーの値が同じ
      if (count($primary_keys) === count($row_values) &&
        false === in_array(null, $entity_values, true) &&
        $entity_values === $row_values)
      {
        $entity_created = true;
      }
    }
    if (false === $entity_created)
    {
      $main_entity = new $entity_class_name;
    }
    // 関連しているエンティティへの、データの設定
    foreach ($associated_entities as $associated_idx => $associated_entity_class_name)
    {
      $this->read_entity_class($associated_entity_class_name);
      $associated_entity_class = new $associated_entity_class_name;
      $associated_table_meta_data = $this->get_table_meta_data($associated_tables[$associated_idx]);
      foreach ($data as $column_name => $column_value)
      {
        if (true === $associated_entity_class->is_property_exists($column_name))
        {
          $column_value = $this->get_cast_value($associated_table_meta_data, $column_name, $column_value);
          $associated_entity_class->set_entity_data($column_name, $column_value);
        }
        if (false === $entity_created &&
            true === $main_entity->is_property_exists($column_name))
        {
          $column_value = $this->get_cast_value($main_table_meta_data, $column_name, $column_value);
          $main_entity->set_entity_data($column_name, $column_value);
        }
      }
      // メインのエンティティに対する、関連しているエンティティの追加
      $method_name = 'add_' . $associated_entity_class_name;
      call_user_func_array(array($main_entity, $method_name), array($associated_entity_class));
    }
    if (false === $entity_created)
    {
      $this->add_entities($main_entity);
    }

    $this->set_main_entity($main_entity);
  }

  protected function read_entity_class($entity_class_name)
  {
    $entity_class_file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::ENTITY_CLASS_DIRECTORY . $entity_class_name . '.php';

    if (true === file_exists($entity_class_file_path) &&
        true === is_readable($entity_class_file_path))
    {
      require_once($entity_class_file_path);
    }
    else
    {
      throw new Exception('エンティティクラスが存在しません');
    }
  }
}

