DROP TABLE `events`;

ALTER TABLE  `event_results` ADD  `ride_id` INT( 11 ) NULL AFTER  `id`;
ALTER TABLE  `event_results` ADD INDEX (  `ride_id` );
ALTER TABLE  `event_results` CHANGE  `event_id`  `event_type_id` INT( 11 ) NULL DEFAULT NULL;