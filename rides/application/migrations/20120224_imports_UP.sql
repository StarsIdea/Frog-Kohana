CREATE TABLE `imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch` int(11) NOT NULL,
  `model` varchar(64) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Add initial import batch for existing event_results --

INSERT INTO `imports` (`id`, `batch`, `model`, `name`, `type`, `size`, `date_created`) VALUES
(1, 1, 'initial', NULL, NULL, NULL, '2012-03-18 03:56:30');

-- Ammend event_results and add import_id to event_results and rides --

ALTER TABLE  `event_results` ADD  `rider_name` VARCHAR( 64 ) NULL AFTER  `event_type_id`;
ALTER TABLE  `event_results` ADD  `equine_name` VARCHAR( 64 ) NULL AFTER  `equine_id`;
ALTER TABLE  `event_results` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id`;
ALTER TABLE  `rides` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id`;


-- Constraints --

ALTER TABLE  `event_results` ADD INDEX (  `import_id` );
ALTER TABLE  `event_results` ADD FOREIGN KEY (  `import_id` ) REFERENCES  `imports` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE  `rides` ADD INDEX (  `import_id` );
ALTER TABLE  `rides` ADD FOREIGN KEY (  `import_id` ) REFERENCES  `imports` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;

