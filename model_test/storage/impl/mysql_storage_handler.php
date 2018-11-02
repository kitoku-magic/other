<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../rdbms_storage_handler.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../config/config.php');

// MySQL版
class mysql_storage_handler extends rdbms_storage_handler
{
  const SQL_ESCAPE_CHARACTER = '`';

  private static $BIND_TYPE_MAPPINGS = array(
    parent::PARAM_INT => 'i',
    parent::PARAM_STR => 's',
  );

  private $statement;

  protected function set_statement($statement)
  {
    $this->statement = $statement;
  }

  protected function get_statement()
  {
    return $this->statement;
  }

  public function connect()
  {
    // DB接続処理
    if (null === $this->get_connection())
    {
      // DB接続
      $connection = new mysqli($this->get_host_name(), $this->get_user_name(), $this->get_password(), $this->get_database_name(), $this->get_port_number());
      if (null !== $connection->connect_error)
      {
        // DB接続失敗
        $this->set_error_message('DB接続に失敗しました。 ' . $connection->connect_error);

        return false;
      }

      $this->set_connection($connection);

      // 文字コード設定
      $this->get_connection()->set_charset(DB_CHARACTER_SET);
    }

    return true;
  }

  public function fetch($mode)
  {
    $result = null;

    $result_set = $this->get_result_set();

    if (0 < count($result_set))
    {
      $statement = $this->get_statement();
      $entity_class_name = $this->get_entity_class_name();
    }

    if (parent::FETCH_ONE === $mode)
    {
      $entity = null;

      if (true === isset($statement))
      {
        if ($statement->fetch())
        {
          $entity = new $entity_class_name;
          foreach ($result_set['data'] as $column_name => $column_value)
          {
            if (true === $entity->is_property_exists($column_name))
            {
              $entity->set_entity_data($column_name, $column_value);
            }
          }
        }
      }

      $result = $entity;
    }
    else if (parent::FETCH_ALL === $mode)
    {
      $entities = array();

      if (true === isset($statement))
      {
        while ($statement->fetch())
        {
          $entity = new $entity_class_name;
          foreach ($result_set['data'] as $column_name => $column_value)
          {
            if (true === $entity->is_property_exists($column_name))
            {
              $entity->set_entity_data($column_name, $column_value);
            }
          }
          $entities[] = $entity;
        }
      }

      $result = $entities;
    }
    else
    {
      throw new Exception('フェッチのモードが不明です');
    }

    if (true === isset($statement))
    {
      $statement->close();
    }

    return $result;
  }

  public function fetch_all_associated_entity($unique_primary_key_data)
  {
    $result_set = $this->get_result_set();

    if (0 < count($result_set))
    {
      $statement = $this->get_statement();
      $entity_class_name = $this->get_entity_class_name();
    }

    if (true === isset($statement))
    {
      $associated_entities = $this->get_associated_entities();
      $associated_tables = $this->get_associated_tables();
      $main_table_meta_data = $this->get_table_meta_data($this->get_table_name());
      while ($statement->fetch())
      {
        $this->set_all_entities(
          $unique_primary_key_data,
          $result_set['data'],
          $main_table_meta_data,
          $entity_class_name,
          $associated_entities,
          $associated_tables
        );
      }
    }

    return $this->get_entities();
  }

  public function get_last_insert_id()
  {
    $result = null;

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $result = $statement->insert_id;
    }

    return $result;
  }

  public function begin()
  {
    return $this->get_connection()->autocommit(false);
  }

  public function commit()
  {
    return $this->get_connection()->commit();
  }

  public function rollback()
  {
    return $this->get_connection()->rollback();
  }

  protected function execute_sql($sql)
  {
    $connection = $this->get_connection();
    $statement = $connection->prepare($sql);

    if (false === $statement)
    {
      throw new Exception('SQL文のプリペアに失敗しました');
    }
    $this->set_statement($statement);

    // パラメータのデータ型
    $bind_array = $this->get_converted_bind_types();

    $bind_params = $this->get_bind_params();
    if (null !== $bind_params)
    {
      foreach ($bind_params as $key => $val)
      {
        // パラメータの値
        $bind_array[] = &$bind_params[$key];
      }
    }

    $statement = $this->get_statement();
    if (0 < count($bind_array))
    {
      $result = call_user_func_array(array($statement, 'bind_param'), $bind_array);

      if (false === $result)
      {
        throw new Exception('SQL文のパラメータのバインドに失敗しました');
      }
    }

    $result = $statement->execute();

    return $result;
  }

  protected function get_table_meta_data($table_name)
  {
    // MySQLの場合、結果セットに関するメタデータを取得している
    $result = array();

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $mysqli_result = $statement->result_metadata();
      if (false !== $mysqli_result)
      {
        $result = $mysqli_result->fetch_fields();
      }
    }

    return $result;
  }

  protected function get_cast_value(array $table_meta_data, $column_name, $column_value)
  {
    // MySQLは不要なので何もやらない
    return $column_value;
  }

  protected function get_affected_rows()
  {
    $result = null;

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $result = $statement->affected_rows;
    }

    return $result;
  }

  protected function get_converted_bind_types()
  {
    $result = array();

    $bind_types = $this->get_bind_types();
    if (null !== $bind_types)
    {
      foreach ($bind_types as $bind_type)
      {
        $result[] = self::$BIND_TYPE_MAPPINGS[$bind_type];
      }
    }

    if (0 < count($result))
    {
      $result = array(implode('', $result));
    }

    return $result;
  }

  protected function get_result_set()
  {
    $result = array();

    $statement = $this->get_statement();

    if (null !== $statement)
    {
      $res = $statement->store_result();
      if (false === $res)
      {
        throw new Exception('結果セットのバッファへの格納に失敗しました。');
      }

      if (false === ($meta = $statement->result_metadata()))
      {
        throw new Exception('結果セットのメタデータの取得に失敗しました。');
      }

      $data = array();
      $columns = array();
      while ($field = $meta->fetch_field())
      {
        $columns[] = &$data[$field->name];
      }

      $res = call_user_func_array(array($statement, 'bind_result'), $columns);
      if (false === $res)
      {
        throw new Exception('結果セットのバインドに失敗しました。');
      }

      $this->read_entity_class($this->get_entity_class_name());

      $result['data'] = $data;
    }

    return $result;
  }
}
