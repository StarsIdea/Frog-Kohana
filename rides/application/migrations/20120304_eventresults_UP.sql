ALTER TABLE  `event_results` CHANGE  `placing`  `placing` INT NULL DEFAULT NULL;
UPDATE `event_results` SET placing=NULL where placing=0;