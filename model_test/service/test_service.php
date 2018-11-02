<?php

class test_service
{
  private $order_repository;

  public function __construct(order_repository $order_repository)
  {
    $this->order_repository = $order_repository;
  }

  public function exec()
  {
    /*
    // 単純なSELECT
    $order_id = 1;
    $result = $this->order_repository->get_order_status($order_id);
    var_dump($result);
    exit();
    */


    /*
    // 関連テーブル側のSELECT
    $order_id = 1;
    $result = $this->order_repository->get_order_item_repository()->get_order_item_by_order_id($order_id);
    var_dump($result);
    exit();
    */


    /*
    // JOINするSELECT
    $order_id = 1;
    $result = $this->order_repository->get_order($order_id);
    var_dump($result);
    exit();
    */


    /*
    // トランザクション有りのINSERT
    $result = 0;
    $this->order_repository->begin();

    try
    {
      $result = $this->order_repository->order();
      if (0 === $result)
      {
        throw new Exception('注文に失敗しました');
      }

      //$this->order_repository->rollback();
      $this->order_repository->commit();
    }
    catch (Exception $e)
    {
      $this->order_repository->rollback();
      var_dump($e);
    }
    var_dump($result);
    exit();
    */


    /*
    // トランザクション無しのUPDATE
    $order_id = 1;
    $order_status = 2;
    $result = $this->order_repository->change_order_status($order_id, $order_status);
    var_dump($result);
    exit();
    */


    /*
    // トランザクション有りのDELETE
    $order_id = 1;
    $result = 0;
    $this->order_repository->begin();

    try
    {
      $result = $this->order_repository->delete_order($order_id);
      if (0 === $result)
      {
        throw new Exception('注文の削除に失敗しました');
      }

      //$this->order_repository->rollback();
      $this->order_repository->commit();
    }
    catch (Exception $e)
    {
      $this->order_repository->rollback();
      var_dump($e);
    }
    var_dump($result);
    exit();
    */
  }
}
