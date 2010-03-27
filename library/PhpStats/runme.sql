SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `socks_month_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(30) NOT NULL,
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `year` (`year`,`month`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `socks_month_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) DEFAULT NULL,
  KEY `event_id` (`event_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB;

CREATE TABLE `socks_day_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(30) NOT NULL,
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `year` (`year`,`month`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_day_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) DEFAULT NULL,
  KEY `event_id` (`event_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(30) NOT NULL,
  `host` varchar(16) DEFAULT NULL,
  `hour` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) NOT NULL,
  PRIMARY KEY (`event_id`,`key`,`value`),
  KEY `event_id` (`event_id`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_hour_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(30) NOT NULL,
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `hour` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `year` (`year`,`month`,`day`,`hour`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_hour_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) DEFAULT NULL,
  KEY `event_id` (`event_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `socks_meta` (
  `hour` int(2) DEFAULT NULL,
  `day` int(2) DEFAULT NULL,
  `month` int(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `socks_event` ADD INDEX ( `event_type` , `hour` , `day` , `month` , `year` ) ;
ALTER TABLE `socks_event_attributes` ADD FOREIGN KEY (`event_id`) REFERENCES `socks_event` (`id`) ON DELETE CASCADE ;