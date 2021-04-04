<?php

class Unittest_Database_TestCase extends Kohana_Unittest_TestCase {

	public function setUp()
	{
		parent::setUp();

		$tables = array(
			'approvals',
			//'awards',
			//'award_members',
			'equines',
			'equine_sexes',
			'event_results',
			'event_types',
			'federations',
			'imports',
			'members',
			'member_types',
			'reclaims',
			'rides'
		);

		foreach ($tables as $table) {
			DB::query(NULL, "TRUNCATE TABLE $table;")->execute();
		}

		DB::query(NULL, 
		"
		CREATE TABLE IF NOT EXISTS `approvals` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `model_name` varchar(64) NOT NULL,
		  `action` varchar(64) NOT NULL,
		  `model_id` int(11) DEFAULT NULL,
		  `original` varchar(255),
		  `modified` varchar(255) NOT NULL,
		  `created_date` datetime NOT NULL,
		  `approved_date` timestamp NULL DEFAULT NULL,
		  `approved_by` int(11) DEFAULT NULL,
		  `rejected_date` datetime DEFAULT NULL,
		  `rejected_by` int(11) DEFAULT NULL,
		  `comment` varchar(255),
		  PRIMARY KEY (`id`),
		  KEY `approved_by` (`approved_by`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;
		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `awards` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `description` varchar(255),
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;
		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `award_member` (
		  `award_id` int(11) NOT NULL DEFAULT '0',
		  `member_id` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`award_id`,`member_id`),
		  KEY `member_id` (`member_id`)
		) ENGINE=Memory DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `equines` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `member_id` int(11) DEFAULT NULL,
		  `owner_name` varchar(64) DEFAULT NULL,
		  `breed_registry_#` varchar(64) DEFAULT NULL,
		  `registration_date` date DEFAULT NULL,
		  `name` varchar(64) NOT NULL,
		  `equine_sex_id` int(11) DEFAULT NULL,
		  `foal_date` date DEFAULT NULL,
		  `breed` varchar(64) DEFAULT NULL,
		  `color` varchar(64) DEFAULT NULL,
		  `active` tinyint(4) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `equine_sex_id` (`equine_sex_id`),
		  KEY `member_id` (`member_id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `equine_sexes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `description` varchar(255),
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `event_results` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `import_id` int(11) NOT NULL DEFAULT '1',
		  `ride_id` int(11) DEFAULT NULL,
		  `event_type_id` int(11) DEFAULT NULL,
		  `rider_name` varchar(64) DEFAULT NULL,
		  `member_id` int(11) DEFAULT NULL,
		  `equine_id` int(11) DEFAULT NULL,
		  `equine_name` varchar(64) DEFAULT NULL,
		  `placing` int(11) DEFAULT NULL,
		  `time` varchar(64) DEFAULT NULL,
		  `weight` varchar(64) DEFAULT NULL,
		  `miles` varchar(64) DEFAULT NULL,
		  `points` varchar(64) DEFAULT NULL,
		  `bc` tinyint(4) DEFAULT NULL COMMENT 'Best Conditioned',
		  `bc_points` int(11) DEFAULT NULL,
		  `bc_score` int(11) DEFAULT NULL,
		  `pull` tinyint(4) DEFAULT NULL,
		  `pull_reason` varchar(64) DEFAULT NULL,
		  `comments` varchar(255),
		  PRIMARY KEY (`id`),
		  KEY `placing` (`placing`),
		  KEY `event_id` (`event_type_id`),
		  KEY `fk_members_member_id_members_id` (`member_id`),
		  KEY `fk_equines_equine_id_equines_id` (`equine_id`),
		  KEY `ride_id` (`ride_id`),
		  KEY `import_id` (`import_id`)
		) ENGINE=Memory DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `event_types` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `federations` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `description` varchar(255),
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `imports` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `batch` int(11) NOT NULL,
		  `model` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
		  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `size` int(11) DEFAULT NULL,
		  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `members` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `active` tinyint(4) DEFAULT NULL,
		  `first_name` varchar(64) NOT NULL,
		  `last_name` varchar(64) NOT NULL,
		  `email` varchar(64) DEFAULT NULL,
		  `address` varchar(64) DEFAULT NULL,
		  `city` varchar(64) DEFAULT NULL,
		  `postal_code` varchar(64) DEFAULT NULL,
		  `state_prov` varchar(64) DEFAULT 'SK',
		  `phone_home` varchar(64) DEFAULT NULL,
		  `phone_alternate` varchar(64) DEFAULT NULL,
		  `fax_number` varchar(64) DEFAULT NULL,
		  `birth_date` date DEFAULT NULL,
		  `aef_number` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `first_name` (`first_name`),
		  KEY `last_name` (`last_name`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `member_types` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `description` varchar(255),
		  `age_start` int(11) DEFAULT NULL,
		  `age_end` int(11) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;

		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `reclaims` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `equine_id` int(11) DEFAULT NULL,
		  `member_id` int(11) DEFAULT NULL,
		  `ride_id` int(11) DEFAULT NULL,
		  `miles_completed` varchar(64) NOT NULL,
		  `comments` varchar(255),
		  `year` varchar(4) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `equine_id` (`equine_id`),
		  KEY `member_id` (`member_id`),
		  KEY `ride_id` (`ride_id`),
		  KEY `miles_completed` (`miles_completed`)
		) ENGINE=Memory DEFAULT CHARSET=latin1;
		")->execute();

		DB::query(NULL,
		"
		CREATE TABLE IF NOT EXISTS `rides` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `import_id` int(11) NOT NULL DEFAULT '1',
		  `name` varchar(64) NOT NULL,
		  `date` date DEFAULT NULL,
		  `city` varchar(64) DEFAULT NULL,
		  `province` varchar(64) DEFAULT NULL,
		  `country` varchar(64) DEFAULT NULL,
		  `manager` varchar(64) DEFAULT NULL,
		  `secretary` varchar(64) DEFAULT NULL,
		  `veterinarian` varchar(64) DEFAULT NULL,
		  `description` varchar(255),
		  `sanctioned` tinyint(4) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `import_id` (`import_id`)
		) ENGINE=Memory  DEFAULT CHARSET=latin1;"
		)->execute();

		//DB::query(NULL, "
		//--
		//-- Constraints for dumped tables
		//--

		//--
		//-- Constraints for table `event_results`
		//--
		//ALTER TABLE `event_results`
		  //ADD CONSTRAINT `event_results_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_10` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_11` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_12` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_13` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_14` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_15` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_16` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_17` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_18` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_19` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_2` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_20` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_21` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_22` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_23` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_24` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_25` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_26` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_27` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_28` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_29` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_3` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_30` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_31` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_32` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_33` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_34` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_35` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_36` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_37` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_38` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_39` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_4` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_40` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_41` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_42` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_43` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_44` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_45` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_46` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_47` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_48` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_49` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_5` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_50` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_51` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_52` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_53` FOREIGN KEY (`equine_id`) REFERENCES `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_6` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_7` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_8` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
		  //ADD CONSTRAINT `event_results_ibfk_9` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

		//--
		//-- Constraints for table `rides`
		//--
		//ALTER TABLE `rides`
		  //ADD CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
		//SET FOREIGN_KEY_CHECKS=1;"
		//)->execute();
	}

}
