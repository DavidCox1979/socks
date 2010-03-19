SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `socks_day_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(15) NOT NULL,
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_day_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) DEFAULT NULL,
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(15) NOT NULL,
  `host` varchar(16) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `socks_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) NOT NULL,
  PRIMARY KEY (`event_id`,`key`,`value`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_hour_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(15) NOT NULL,
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `hour` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_hour_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) DEFAULT NULL,
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_meta` (
  `hour` int(2) DEFAULT NULL,
  `day` int(2) DEFAULT NULL,
  `month` int(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `socks_event` ADD INDEX ( `datetime` ) ;
ALTER TABLE `socks_day_event` ADD INDEX ( `year` , `month` , `day` ) ;
ALTER TABLE `socks_hour_event` ADD INDEX ( `year` , `month` , `day` , `hour` ) ;

ALTER TABLE `socks_hour_event_attributes` ADD INDEX ( `value` ) ;
ALTER TABLE `socks_day_event_attributes` ADD INDEX ( `value` ) ;

ALTER TABLE `socks_event` CHANGE `event_type` `event_type` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;
ALTER TABLE `socks_hour_event` CHANGE `event_type` `event_type` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `socks_day_event` CHANGE `event_type` `event_type` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

ALTER TABLE `socks_event_attributes` ADD INDEX ( `key` ) ;
ALTER TABLE `socks_event` ADD INDEX ( `host` ) ;