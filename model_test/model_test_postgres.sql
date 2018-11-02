DROP DATABASE IF EXISTS model_test;
CREATE DATABASE model_test ENCODING 'UTF8';

\c model_test;

CREATE TABLE "order" (
  order_id bigserial NOT NULL,
  order_status smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (order_id)
);

CREATE TABLE order_item (
  order_item_id bigserial NOT NULL,
  order_id bigint NOT NULL DEFAULT '0',
  item_name varchar(255) NOT NULL DEFAULT '',
  item_count int NOT NULL DEFAULT '0',
  PRIMARY KEY (order_item_id)
);

insert into "order"(order_status) values(1),(2),(3);

insert into order_item(order_id, item_name, item_count) values
(1, 'order_id1::item_name1', 1),
(1, 'order_id1::item_name2', 2),
(2, 'order_id2::item_name1', 3),
(3, 'order_id3::item_name1', 4),
(3, 'order_id3::item_name2', 5)
;

-- 以下は、実処理とは関係ないデータ型のテスト用
CREATE TABLE data_type_test (
  bigint_test bigint NOT NULL DEFAULT '0',
  bigserial_test bigserial NOT NULL,
  boolean_test boolean NOT NULL DEFAULT 'f',
  character_varying_test character varying(255) NOT NULL DEFAULT '',
  character_test character(255) NOT NULL DEFAULT '',
  date_test date NOT NULL DEFAULT CURRENT_DATE,
  double_precision_test double precision NOT NULL DEFAULT '0.0',
  integer_test integer NOT NULL DEFAULT '0',
  numeric_test numeric NOT NULL DEFAULT '0.0',
  real_test real NOT NULL DEFAULT '0.0',
  smallint_test smallint NOT NULL DEFAULT '0',
  serial_test serial NOT NULL,
  text_test text NOT NULL DEFAULT '',
  time_without_test time without time zone NOT NULL DEFAULT CURRENT_TIME,
  time_with_test time with time zone NOT NULL DEFAULT CURRENT_TIME,
  timestamp_without_test timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  timestamp_with_test timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
