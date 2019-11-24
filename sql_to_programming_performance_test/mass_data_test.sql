drop database if exists `mass_data_test`;

CREATE DATABASE `mass_data_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

use `mass_data_test`;

drop table if exists `digit`;

CREATE TABLE `digit`(num integer);

INSERT INTO `digit` VALUES
(0),
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9);

drop table if exists `items`;

create table `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varbinary(128) DEFAULT '' NOT NULL,
  `item_price` mediumint unsigned DEFAULT '0' NOT NULL,
  `item_status` tinyint unsigned DEFAULT '0' NOT NULL,
  `created_at` bigint unsigned DEFAULT '0' NOT NULL,
  `updated_at` bigint unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB;


-- 100万件のデータを作成
begin;

INSERT INTO items(id)
SELECT
    (d1.num + d2.num*10 + d3.num*100 + d4.num*1000 + d5.num*10000 + d6.num*100000) + 1
FROM
    digit d1, digit d2, digit d3, digit d4, digit d5, digit d6
;

update
  items
set
  item_name = concat('アイテム', id),
  item_price = id / 10,
  item_status = mod(id, 10),
  created_at = UNIX_TIMESTAMP() + id,
  updated_at = UNIX_TIMESTAMP() + id
;

commit;

