<?php

interface order_item_repository
{
  // 注文IDから注文した商品を取得する
  public function get_order_item_by_order_id($order_id);

  // 注文した全ての商品を取得する（自分の注文に限定した方が良いでしょうが）
  public function get_all_order_item();
}
