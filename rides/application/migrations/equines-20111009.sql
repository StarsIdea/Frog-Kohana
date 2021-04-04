ALTER TABLE `equines` DROP `old_equineid`;
ALTER TABLE `equines` DROP `card`;
ALTER TABLE `equines` CHANGE `registration_number` `breed_registry_#` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

# Convert registration_date from timestamp to date
ALTER TABLE `equines` CHANGE `registration_date` `registration_date_timestamp` INT( 11 ) NOT NULL;
ALTER TABLE `equines` ADD `registration_date` DATE NULL AFTER `breed_registry_#`;
UPDATE `equines`
	SET `registration_date` = DATE( FROM_UNIXTIME( `registration_date_timestamp` ) );
ALTER TABLE `equines` DROP `registration_date_timestamp`;
UPDATE equines SET registration_date = NULL WHERE registration_date = '1969-12-31';

# Rename "birthdate" to "foal_date" and convert from timestamp to date
ALTER TABLE `equines` ADD `foal_date` DATE NULL AFTER `equine_sex_id`;
UPDATE `equines`
	SET `foal_date` = DATE( FROM_UNIXTIME( `birthdate` ) );
UPDATE equines SET foal_date = NULL WHERE foal_date = '1969-12-31';
ALTER TABLE `equines` DROP `birthdate`;
