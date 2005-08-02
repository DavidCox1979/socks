CREATE TABLE IF NOT EXISTS `hour_event_attributes` (
  `event_id` int(15) NOT NULL,
  `key` varchar(25) NOT NULL,
  `value` varchar(25) NOT NULL,
  PRIMARY KEY (`event_id`,`key`,`value`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDb;