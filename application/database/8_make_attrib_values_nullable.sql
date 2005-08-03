ALTER TABLE `hour_event_attributes` DROP PRIMARY KEY;
ALTER TABLE `hour_event_attributes` CHANGE `value` `value` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ;

ALTER TABLE `day_event_attributes` DROP PRIMARY KEY;
ALTER TABLE `day_event_attributes` CHANGE `value` `value` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ;