DROP TABLE `imports`;

ALTER TABLE  `event_results` ADD  `rider_name` VARCHAR( 64 ) NULL AFTER  `event_type_id`;
ALTER TABLE  `event_results` ADD  `equine_name` VARCHAR( 64 ) NULL AFTER  `equine_id`;
ALTER TABLE  `event_results` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id`;
ALTER TABLE  `rides` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id`;

ALTER TABLE  `event_results` DROP `rider_name`;
ALTER TABLE  `event_results` DROP `equine_name`;
ALTER TABLE  `event_results` DROP `import_id`;
ALTER TABLE  `rides` DROP `import_id`;