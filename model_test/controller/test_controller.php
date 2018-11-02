<?php

/**
 * PHP5.2.17 https://museum.php.net/php5/php-5.2.17.tar.gz 2011-Jan-06
 * MySQL5.1.54 https://downloads.mysql.com/archives/get/file/mysql-5.1.54.tar.gz 2010-Nov-29
 * PostgreSQL8.4.6 https://ftp.postgresql.org/pub/source/v8.4.6/postgresql-8.4.6.tar.gz 2010-Dec-14
 * で動かしています
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../storage/impl/db_manager.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../service/test_service.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../model/repository/impl/order_repository_impl.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../model/repository/impl/order_item_repository_impl.php');

class test_controller
{
  public function exec()
  {
    // 「db_manager::get_storage_handlers()」を２回行って、DBリポジトリにそれぞれ別のインスタンスを渡すと、
    // トランザクション時に、整合性が取れなくなるので注意
    $storage_handlers = db_manager::get_storage_handlers();

    $service = new test_service(
      new order_repository_impl(
        $storage_handlers,
        new order_item_repository_impl($storage_handlers)
      )
    );

    $service->exec();
  }
}
