<?php

interface order_repository
{
  // 商品を注文する
  public function order();

  // 注文のステータスを取得する
  public function get_order_status($order_id);

  // 注文のステータスを変更する（発送済みにしたり、キャンセルしたり）
  public function change_order_status($order_id, $order_status);

  // 注文情報を取得する
  public function get_order($order_id);

  // 注文を削除する
  public function delete_order($order_id);
}
