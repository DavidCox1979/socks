DROP TABLE `event_type` ;
ALTER TABLE `event` CHANGE `event_type_id` `event_type` VARCHAR( 15 ) NOT NULL ;
ALTER TABLE `hour_event` CHANGE `event_type_id` `event_type` VARCHAR( 15 ) NOT NULL ;