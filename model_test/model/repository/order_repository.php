<?php

interface order_repository
{
  // 商品を注文する
  public function order();

  // 商品のステータスを変更する（発送済みにしたり、キャンセルしたり）
  public function change_order_status($order_id);
}
