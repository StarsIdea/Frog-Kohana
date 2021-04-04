--
-- Add import_id reference to event_results table
--
ALTER TABLE  `event_results` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id` , ADD INDEX (  `import_id` );
ALTER TABLE  `event_results` ADD FOREIGN KEY (  `import_id` ) REFERENCES  `era`.`imports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

-- add import_id to ride
ALTER TABLE  `rides` ADD  `import_id` INT NOT NULL DEFAULT  '1' AFTER  `id` ,ADD INDEX (  `import_id` )

-- fk ride to import
ALTER TABLE  `rides` ADD FOREIGN KEY (  `import_id` ) REFERENCES  `era`.`imports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
