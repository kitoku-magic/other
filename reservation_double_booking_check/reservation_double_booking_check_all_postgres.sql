DROP DATABASE IF EXISTS reservation_double_booking_check;
CREATE DATABASE reservation_double_booking_check ENCODING UTF8;

\c reservation_double_booking_check;

CREATE TABLE conference_room (
  conference_room_id serial NOT NULL,
  conference_room_name varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (conference_room_id)
);

INSERT INTO conference_room(conference_room_name) VALUES
('第一会議室'),
('第二会議室');

CREATE TABLE conference_room_reservation (
  conference_room_reservation_id serial NOT NULL,
  conference_room_id bigint NOT NULL DEFAULT '0',
  reservation_date date NOT NULL DEFAULT '0001-01-01',
  -- time型のデフォルト値は、これが適切か？
  reservation_time_start time without time zone NOT NULL DEFAULT '00:00:00',
  reservation_time_end time without time zone NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (conference_room_reservation_id),
  UNIQUE (conference_room_id, reservation_date, reservation_time_start, reservation_time_end),
  CONSTRAINT fk_conference_room_reservation_conference_room_id FOREIGN KEY (conference_room_id) REFERENCES conference_room (conference_room_id)
);

INSERT INTO conference_room_reservation(conference_room_id, reservation_date, reservation_time_start, reservation_time_end) VALUES
('1','2017-10-09','16:00:00','17:30:00'),
('1','2017-10-09','18:00:00','19:30:00'),
('1','2017-10-09','19:30:00','21:00:00'),
('1','2017-10-10','21:00:00','22:30:00'),
('2','2017-10-09','16:00:00','17:30:00'),
('2','2017-10-09','18:00:00','19:30:00'),
('2','2017-10-10','17:00:00','18:30:00');


CREATE TABLE conference_room_reservation_history (
  conference_room_reservation_id bigint NOT NULL DEFAULT '0',
  reservation_status smallint NOT NULL DEFAULT '0',
  create_datetime timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (conference_room_reservation_id, reservation_status)
);

