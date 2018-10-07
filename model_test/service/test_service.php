<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../storage/storage_handler.php');

class test_service
{
  private $order_repository;

  // private $order_item_repository;

  // public function __construct(order_repository $order_repository, order_item_repository $order_item_repository)
  public function __construct(order_repository $order_repository)
  {
    $this->order_repository = $order_repository;
    // $this->order_item_repository = $order_item_repository;
  }

  public function exec()
  {
    /*
    $order_id = 1;
    $result = $this->order_repository->get_order_status($order_id);
    var_dump($result);
    exit();
    */


    $order_id = 1;
    $result = $this->order_repository->get_order_item_name($order_id);
    var_dump($result);
    exit();


    /*
    $this->order_repository->begin();

    try
    {
      $this->order_repository->order();
      // $this->order_item_repository->order_item();

      //$this->order_repository->rollback();
      $this->order_repository->commit();
    }
    catch (Exception $e)
    {
      var_dump($e);
      $this->order_repository->rollback();
    }
    */
  }
}
