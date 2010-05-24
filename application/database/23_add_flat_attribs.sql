ALTER TABLE `socks_day_event` ADD `attribute_keys` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_keys` ) ;


ALTER TABLE `socks_month_event` ADD `attribute_keys` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `attribute_keys` ) ;