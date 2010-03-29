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
