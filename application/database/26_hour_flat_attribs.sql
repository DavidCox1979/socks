ALTER TABLE `socks_hour_event` ADD `attribute_keys` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_keys` ) ;

ALTER TABLE `socks_hour_event` ADD `attribute_values` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_values` ) ;

ALTER TABLE `socks_hour_event` ADD INDEX ( `event_type` , `year` , `month` , `day` , `hour`, `attribute_values` ) ;