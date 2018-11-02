<?php

interface base_repository
{
  public function get_primary_keys();

  public function begin();

  public function commit();

  public function rollback();
}
