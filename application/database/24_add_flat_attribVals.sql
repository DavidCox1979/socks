ALTER TABLE `socks_day_event` ADD `attribute_values` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_values` ) ;
ALTER TABLE `socks_day_event` ADD INDEX ( `event_type` , `year` , `month` , `day` , `attribute_values` ) ;

ALTER TABLE `socks_month_event` ADD `attribute_values` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_values` ) ;
ALTER TABLE `socks_month_event` ADD INDEX ( `event_type` , `year` , `month`,  `attribute_values` ) ;