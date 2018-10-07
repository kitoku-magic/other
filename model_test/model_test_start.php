<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controller/test_controller.php');

$controller = new test_controller();
$controller->exec();
