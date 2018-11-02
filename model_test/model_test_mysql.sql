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
  `item_count` mediumint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

insert into `order`(`order_status`) values(1),(2),(3);

insert into `order_item`(`order_id`, `item_name`, `item_count`) values
(1, 'order_id1::item_name1', 1),
(1, 'order_id1::item_name2', 2),
(2, 'order_id2::item_name1', 3),
(3, 'order_id3::item_name1', 4),
(3, 'order_id3::item_name2', 5)
;
