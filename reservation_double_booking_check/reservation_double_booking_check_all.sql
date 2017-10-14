DROP DATABASE IF EXISTS `reservation_double_booking_check`;
CREATE DATABASE `reservation_double_booking_check` DEFAULT CHARACTER SET utf8mb4;

use `reservation_double_booking_check`;

CREATE TABLE `conference_room` (
  `conference_room_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conference_room_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`conference_room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `conference_room`(`conference_room_name`) VALUES
('第一会議室'),
('第二会議室');

CREATE TABLE `conference_room_reservation` (
  `conference_room_id` bigint unsigned NOT NULL DEFAULT '0',
  `reservation_date` date NOT NULL DEFAULT '0000-00-00',
  `reservation_time_start` time NOT NULL DEFAULT '00:00:00',
  `reservation_time_end` time NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (`conference_room_id`, `reservation_date`, `reservation_time_start`, `reservation_time_end`),
  CONSTRAINT `fk_conference_room_reservation_conference_room_id` FOREIGN KEY (`conference_room_id`) REFERENCES `conference_room` (`conference_room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `conference_room_reservation`(`conference_room_id`, `reservation_date`, `reservation_time_start`, `reservation_time_end`) VALUES
('1','2017-10-10','15:00:00','16:30:00');
