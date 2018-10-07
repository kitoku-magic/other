<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../storage_handler.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../rdbms_storage_handler.php');

// MySQL版
class mysql_storage_handler extends rdbms_storage_handler implements storage_handler
{
  const ENTITY_CLASS_DIRECTORY = '../../model/entity/';

  private $statement;

  private $columns;

  private $from;

  private $where;

  private $group_by;

  // 以下、having・order_byなどが続く

  private $bind_params;

  private $repository_class;

  private $entity_class_name;

  public function set_columns($columns)
  {
    $this->columns = $columns;
  }

  public function set_from($from)
  {
    $this->from = $from;
  }

  public function set_where($where)
  {
    $this->where = $where;
  }

  public function set_group_by($group_by)
  {
    $this->group_by = $group_by;
  }

  public function set_bind_params($bind_params)
  {
    $this->bind_params = $bind_params;
  }

  public function set_repository_class(base_repository $repository_class)
  {
    $this->repository_class = $repository_class;
  }

  public function set_entity_class_name($entity_class_name)
  {
    $this->entity_class_name = $entity_class_name;
  }

  public function get()
  {
    $sql = 'SELECT ' . $this->columns . ' FROM ' . $this->from;
    if ('' !== $this->where)
    {
      $sql .= ' WHERE ' . $this->where;
    }
    // 以下、GROUP BYなどが続く

    $handle_instance = $this->get_handle_instance();
    $this->statement = $handle_instance->prepare($sql);

    foreach ($this->bind_params as $bind_param)
    {
      $this->statement->bindValue($bind_param['name'], $bind_param['value'], $bind_param['data_type']);
    }

    $result = $this->statement->execute();

    return $result;
  }

  public function set()
  {
    // INSERT・UPDATE・DELETEを実行する
  }

  public function fetch()
  {
    // １件しか取得しないのが分かり切っている時はコッチ
  }

  public function fetch_all()
  {
    $entities = array();

    if (null !== $this->statement)
    {
      // TODO: 当然、存在チェックもする
      require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::ENTITY_CLASS_DIRECTORY . $this->entity_class_name . '.php');
      $entities = $this->statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->entity_class_name);
    }

    return $entities;
  }

  public function fetch_all_associated_entity($associated_entities, $unique_primary_key_data = true)
  {
    $entities = array();

    if (0 === count($associated_entities))
    {
      return $entities;
    }

    if (null !== $this->statement)
    {
      // TODO: 当然、存在チェックもする
      require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::ENTITY_CLASS_DIRECTORY . $this->entity_class_name . '.php');
      $main_entity = null;
      while (false !== ($row = $this->statement->fetch(PDO::FETCH_ASSOC)))
      {
        $entity_created = false;
        if (true === $unique_primary_key_data &&
            null !== $main_entity)
        {
          $row_values = array();
          $entity_values = array();
          $main_entity_index = array_search($main_entity, $entities, true);
          $primary_keys = $this->repository_class->get_primary_keys();
          foreach ($primary_keys as $primary_key)
          {
            if (true === isset($row[$primary_key]))
            {
              $row_values[] = $row[$primary_key];

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
          $main_entity = new $this->entity_class_name;
        }
        foreach ($associated_entities as $associated_entity_class_name => $value)
        {
          require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::ENTITY_CLASS_DIRECTORY . $associated_entity_class_name . '.php');
          $associated_entity_class = new $associated_entity_class_name;
          foreach ($row as $column_name => $column_value)
          {
            if (true === $associated_entity_class->is_property_exists($column_name))
            {
              $method_name = 'set_' . $column_name;
              call_user_func_array(array($associated_entity_class, $method_name), array($column_value));
            }
            if (false === $entity_created &&
                true === $main_entity->is_property_exists($column_name))
            {
              $method_name = 'set_' . $column_name;
              call_user_func_array(array($main_entity, $method_name), array($column_value));
            }
          }
          $method_name = 'add_' . $associated_entity_class_name;
          call_user_func_array(array($main_entity, $method_name), array($associated_entity_class));
        }
        if (false === $entity_created)
        {
          $entities[] = $main_entity;
        }
      }
    }

    return $entities;
  }

  public function begin()
  {
    $this->get_handle_instance()->beginTransaction();
  }

  public function commit()
  {
    $this->get_handle_instance()->commit();
  }

  public function rollback()
  {
    $this->get_handle_instance()->rollback();
  }
}
