DROP DATABASE IF EXISTS `model_test`;
CREATE DATABASE `model_test` DEFAULT CHARACTER SET utf8;

use `model_test`;

CREATE TABLE `order` (
  `order_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_status` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_item` (
  `order_item_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL DEFAULT '0',
  `item_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

