ALTER TABLE `socks_event` ADD `hour` INT( 2 ) NOT NULL ,
ADD `day` INT( 2 ) NOT NULL ,
ADD `month` INT( 2 ) NOT NULL ,
ADD `year` INT( 4 ) NOT NULL ;

UPDATE `socks_event` SET
`hour` = HOUR( `datetime` ),
`day` = DAY( `datetime` ),
`month` = MONTH( `datetime` ),
`year` = YEAR( `datetime` );

ALTER TABLE `socks_event` DROP `datetime`;