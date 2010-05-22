ALTER TABLE `socks_day_event_attributes` ADD INDEX ( `key` ) ;
ALTER TABLE `socks_day_event_attributes` ADD INDEX ( `key` , `value` ) ;

ALTER TABLE `socks_hour_event_attributes` ADD INDEX ( `key` ) ;
ALTER TABLE `socks_hour_event_attributes` ADD INDEX ( `key` , `value` ) ;

ALTER TABLE `socks_event_attributes` ADD INDEX ( `key` , `value` ) ;
