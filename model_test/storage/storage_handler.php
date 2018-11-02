<?php

interface storage_handler
{
  // 該当のストレージに接続する
  public function connect();

  // 該当のストレージからデータを取得する
  public function get();

  // 該当のストレージにデータを保存する
  public function set($mode);
}

