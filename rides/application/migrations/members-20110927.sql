UPDATE members
SET active = 0
WHERE active IS NULL;

ALTER TABLE `members` DROP COLUMN `birthdate`;