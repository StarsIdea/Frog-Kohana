SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;

ALTER TABLE `approvals` ADD `comment` TEXT NULL DEFAULT NULL;

--
-- MEMBERS
--
ALTER TABLE `members` CHANGE `name_first` `first_name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `name_last` `last_name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `members`
  DROP `password`,
  DROP `logins`,
  DROP `last_login`;

ALTER TABLE `members` CHANGE `state_prov` `state_prov` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'SK';
ALTER TABLE `members` CHANGE `email` `email` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;
ALTER TABLE `members` ADD `active` TINYINT NULL DEFAULT NULL AFTER `id` ;
ALTER TABLE `members` ADD `birth_date` DATE NULL DEFAULT NULL AFTER `birthdate` ;
UPDATE members SET birth_date = DATE_ADD(DATE_ADD(FROM_UNIXTIME(0), INTERVAL `birthdate` SECOND), INTERVAL 1 DAY);
ALTER TABLE `members` ADD INDEX ( `first_name` ) ;
ALTER TABLE `members` ADD INDEX ( `last_name` ) ;

UPDATE `members`
INNER JOIN `payments` ON `members`.id = `payments`.member_id
SET active = 1
WHERE date >= UNIX_TIMESTAMP('2011-01-01');
DROP TABLE `payments`;

--
-- INDEXING
--
ALTER TABLE `reclaims` ADD INDEX ( `miles_completed` ) ;
ALTER TABLE `rides` ADD INDEX ( `date` ) ;

--
-- ORM AUTH
--
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

#INSERT INTO `roles` (`id`, `name`, `description`) VALUES(1, 'login', 'Login privileges, granted after account confirmation');
#INSERT INTO `roles` (`id`, `name`, `description`) VALUES(2, 'admin', 'Administrative user, has access to everything.');

CREATE TABLE IF NOT EXISTS `roles_users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`user_id`,`role_id`),
  KEY `fk_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `logins` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_login` int(10) UNSIGNED,
  `member_id` int(11) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `user_agent` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `type` varchar(100) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `roles_users`
  ADD CONSTRAINT `roles_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- DEFAULT USERS
-- admin:mexicomexico
--
INSERT INTO `users` VALUES(1, 'rwpadget@gmail.com', 'admin', '9acf2bd367cc22dba4299d3b68d2072dfa049ed2e4643a90822853709a71485a', 1, 1313547594, NULL);
INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES ('1', '1');
INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES ('1', '2');

#INSERT INTO `users` VALUES(2, 'test@temp.com', 'test', '53a8896f1aaf77726903ab81bd94946b6ec254c2b189d16f1e82f51dc24dd3a9', 1, 1313555055, NULL);
#INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES ('2', '1');

--
-- DEPRECATED TABLES
--
DROP TABLE `members_roles`;


COMMIT;