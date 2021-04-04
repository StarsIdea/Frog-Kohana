UPDATE  `members` SET  `first_name` =  'No',
`last_name` =  'Number' WHERE  `members`.`id` =1;

UPDATE event_results LEFT JOIN members ON event_results.member_id = members.id 
SET rider_name = CONCAT(members.first_name,' ',members.last_name)  WHERE rider_name IS NULL;