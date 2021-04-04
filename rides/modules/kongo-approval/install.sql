CREATE TABLE `approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `model_name` varchar(64) NOT NULL,
  `action` varchar(64) NOT NULL,
  `model_id` int(11) DEFAULT NULL,
  `original` text,
  `modified` text NOT NULL,
  `created_date` datetime NOT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `rejected_date` datetime DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;