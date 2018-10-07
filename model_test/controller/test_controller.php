<?php

/**
 * PHP5.2.17、MySQL5.1.54で動かしています
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../service/test_service.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../storage/impl/mysql_storage_handler.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../model/repository/impl/order_repository_impl.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../model/repository/impl/order_item_repository_impl.php');

class test_controller
{
  public function exec()
  {
    // 「new mysql_storage_handler()」を２回行って、DBリポジトリにそれぞれ別のインスタンスを渡すと、
    // トランザクション時に、整合性が取れなくなるので注意
    $db_storage_handler = new mysql_storage_handler();
    $service = new test_service(
      new order_repository_impl(
        $db_storage_handler,
        new order_item_repository_impl($db_storage_handler)
      )
    );
    $service->exec();
  }
}
