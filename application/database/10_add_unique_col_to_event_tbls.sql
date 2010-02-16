ALTER TABLE `socks_hour_event` ADD `unique` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `event_type`;
ALTER TABLE `socks_day_event` ADD `unique` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `event_type`;