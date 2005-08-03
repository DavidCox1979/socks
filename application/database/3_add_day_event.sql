CREATE TABLE IF NOT EXISTS `day_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type_id` int(11) NOT NULL,
  `count` int(5) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;