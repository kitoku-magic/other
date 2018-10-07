<?php

interface storage_handler
{
  // 該当のストレージからデータを取得する
  public function get();

  // 該当のストレージにデータを保存する
  public function set();
}

