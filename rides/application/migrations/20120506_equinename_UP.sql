UPDATE `event_results` LEFT JOIN `equines` ON `event_results`.equine_id = `equines`.id 
SET equine_name = equines.name WHERE equine_name IS NULL;
