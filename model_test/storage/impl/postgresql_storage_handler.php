<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../rdbms_storage_handler.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../config/config.php');

// PostgreSQL版
class postgresql_storage_handler extends rdbms_storage_handler
{
  const SQL_ESCAPE_CHARACTER = '"';

  // 他にも、まだデータ型あるし、マッピングも結構適当です
  private static $DATA_TYPE_MAPPINGS = array(
    // bigint、bigserial、
    'int8' => 'float',
    // boolean
    'bool' => 'bool',
    // character varing(varchar)
    'varchar' => 'str',
    // character(char)
    'bpchar' => 'str',
    // date
    'date' => 'str',
    // double_precision
    'float8' => 'float',
    // integer、serial
    'int4' => 'int',
    // numeric
    'numeric' => 'float',
    // real
    'float4' => 'float',
    // smallint
    'int2' => 'int',
    // text
    'text' => 'str',
    // time without time zone
    'time' => 'str',
    // time with time zone
    'timetz' => 'str',
    // timestamp without time zone
    'timestamp' => 'str',
    // timestamp with time zone
    'timestamptz' => 'str',
  );

  private $query_result;

  protected function set_query_result($query_result)
  {
    $this->query_result = $query_result;
  }

  protected function get_query_result()
  {
    return $this->query_result;
  }

  public function connect()
  {
    // DB接続処理
    if (null === $this->get_connection())
    {
      // DB接続文字列
      $conn_str = 'host=' . $this->get_host_name() . ' port=' . $this->get_port_number() . ' dbname=' . $this->get_database_name() . ' user=' . $this->get_user_name() .
      ' password=' . $this->get_password();
      $connection = pg_connect($conn_str);
      if (false === $connection)
      {
        // DB接続失敗
        $this->set_error_message('DB接続に失敗しました。');

        return false;
      }

      $this->set_connection($connection);

      // 文字コード設定
      pg_set_client_encoding($this->get_connection(), DB_CHARACTER_SET);
    }

    return true;
  }

  public function fetch($mode)
  {
    $result = null;

    $entity_class_name = $this->get_entity_class_name();
    $this->read_entity_class($entity_class_name);
    $table_meta_data = $this->get_table_meta_data($this->get_table_name());

    if (parent::FETCH_ONE === $mode)
    {
      $entity = null;

      if (false !== ($data = pg_fetch_assoc($this->get_query_result())))
      {
        $entity = new $entity_class_name;
        foreach ($data as $column => $value)
        {
          if (true === $entity->is_property_exists($column))
          {
            $value = $this->get_cast_value($table_meta_data, $column, $value);
            $entity->set_entity_data($column, $value);
          }
        }
      }

      $result = $entity;
    }
    else if (parent::FETCH_ALL === $mode)
    {
      $entities = array();

      while (false !== ($data = pg_fetch_assoc($this->get_query_result())))
      {
        $entity = new $entity_class_name;
        foreach ($data as $column => $value)
        {
          if (true === $entity->is_property_exists($column))
          {
            $value = $this->get_cast_value($table_meta_data, $column, $value);
            $entity->set_entity_data($column, $value);
          }
        }
        $entities[] = $entity;
      }

      $result = $entities;
    }
    else
    {
      throw new Exception('フェッチのモードが不明です');
    }

    return $result;
  }

  public function fetch_all_associated_entity($unique_primary_key_data)
  {
    $entity_class_name = $this->get_entity_class_name();
    $this->read_entity_class($entity_class_name);

    $associated_entities = $this->get_associated_entities();
    $associated_tables = $this->get_associated_tables();
    $main_table_meta_data = $this->get_table_meta_data($this->get_table_name());
    while (false !== ($data = pg_fetch_assoc($this->get_query_result())))
    {
      $this->set_all_entities(
        $unique_primary_key_data,
        $data,
        $main_table_meta_data,
        $entity_class_name,
        $associated_entities,
        $associated_tables
      );
    }

    return $this->get_entities();
  }

  public function get_last_insert_id()
  {
    $last_insert_id = null;

    // TODO: INSERT・・・RETURNINGという書き方もあるが、未検証
    $result = pg_query($this->get_connection(), 'SELECT LASTVAL()');

    if (false !== $result)
    {
      if (false !== ($row = pg_fetch_row($result)))
      {
        $last_insert_id = $row[0];
      }
    }

    return $last_insert_id;
  }

  public function begin()
  {
    return pg_query($this->get_connection(), 'begin');
  }

  public function commit()
  {
    return pg_query($this->get_connection(), 'commit');
  }

  public function rollback()
  {
    return pg_query($this->get_connection(), 'rollback');
  }

  protected function execute_sql($sql)
  {
    $connection = $this->get_connection();
    $result = pg_prepare($connection, '', $this->replace_place_holder($sql));

    if (false === $result)
    {
      throw new Exception('SQL文のプリペアに失敗しました');
    }

    $result = pg_execute($connection, '', $this->get_bind_params());

    if (false === $result)
    {
      return $result;
    }
    else
    {
      $this->set_query_result($result);

      return true;
    }
  }

  protected function get_table_meta_data($table_name)
  {
    return pg_meta_data($this->get_connection(), $table_name);
  }

  // PostgreSQLは、これをやらないと全部stringになるので
  protected function get_cast_value(array $table_meta_data, $column_name, $column_value)
  {
    $result = null;

    $data_type_name = '';

    if (true === isset($table_meta_data[$column_name]))
    {
      $data_type_name = $table_meta_data[$column_name]['type'];
    }

    if (true === isset(self::$DATA_TYPE_MAPPINGS[$data_type_name]))
    {
      $function_name = self::$DATA_TYPE_MAPPINGS[$data_type_name] . 'val';
      if (true === function_exists($function_name))
      {
        // TODO: boolは、't'や'f'の値になっている？
        $result = $function_name($column_value);
      }
      else
      {
        throw new Exception('存在しない型変換の関数です');
      }
    }

    if (null === $result)
    {
      throw new Exception('値をキャスト出来ませんでした');
    }

    return $result;
  }

  protected function get_affected_rows()
  {
    return pg_affected_rows($this->get_query_result());
  }

  // SQLパラメータの「?」を、「$1」などの数値に置換する(PostgreSQL専用)
  protected function replace_place_holder($sql)
  {
    $tmp_arr = explode('?', $sql);
    $sql = $tmp_arr[0];
    $tmp_arr_len = count($tmp_arr);
    for ($i = 1; $i < $tmp_arr_len; $i++)
    {
      $sql .= '$' . $i . $tmp_arr[$i];
    }

    return $sql;
  }
}
