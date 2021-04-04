<?php defined('SYSPATH') or die('No direct script access.');

/**
* Purges the application, requires at least one cache configuration group to be
* specified
*
* Available config options:
*
* --cache=cache1[,cache2,cache2...]
*
* Specify the caches to clear, each item in the list is the key of a cache
* config group in config/cache.php
*
* This is a required config option
*
* @author Ryan Padget <rwpadget@gmail.com>
*/
class Minion_Task_Import_RideResults extends Minion_Task {

	/**
	* An array of config options that this task can accept
	*/
	protected $_config = array('file','append','truncate');

	/**
	* Imports the data
	*/
	public function execute(array $config)
	{
		if( ! isset($config['file']))
			return Minion_CLI::write('You must include a file using option --file=path_to_file', 'red');

		if( ! file_exists($config['file']))
			return Minion_CLI::write('There was an error loading the file '.$config['file'], 'red');

		if(array_key_exists('truncate', $config) AND empty($config['truncate']))
			return Minion_CLI::write('--truncate=table1,table2,etc.', 'red');
			
		$count = DB::select('*')->from('event_results')->execute()->count();
		if( ! array_key_exists('append', $config) and ! array_key_exists('truncate', $config) and !empty($config['truncate']) and $count > 0)
			return Minion_CLI::write('Table contains data!  Truncate table or run with option --append to continue.', 'yellow');

		if(array_key_exists('truncate', $config))
		{
			foreach(explode(',', $config['truncate']) as $table)
			{
				if( ! in_array($table, array('event_types','event_results')))
					return Minion_CLI::write('Allowed tables: event_types, event_results.', 'red');
				DB::query(NULL, 'TRUNCATE table `'.$table.'`')->execute();
			}
		}

		$import = new Parser_CSV($config['file']);
		$result = $import->result();
		$unique = $import->unique();

		// File dependency
		$this->import_event_types('/Users/rwpadget/Code/era/application/imports/events.csv');

		// REFERENCE ARRAYS
		$ride_ids = ORM::factory('ride')->find_all()->as_array('name','id');
		$member_ids = ORM::factory('member')->find_all()->as_array('id','id');
		$equine_ids = ORM::factory('equine')->find_all()->as_array('id','id');
		$event_type_ids = ORM::factory('event_type')->find_all()->as_array('name','id');

		$insert = array();
		foreach($result as $row => $item)
		{
			$event_type_str = trim($item['Event']);
			$event_type_id = Arr::get($event_type_ids, $event_type_str);

			// MISSING EVENT TYPES
			// string(18) "150 miles (3 days)"
			// string(23) "50 miles - Senior Day 2"
			// string(18) "25 miles  - Senior"
			// string(16) "50 miles -Senior"
			// string(24) "160 Miles (3 Days) Day 1"
			// string(24) "160 Miles (3 Days) Day 2"
			// string(24) "160 Miles (3 Days) Day 3"

			if( ! $event_type_id) {
				if((int)$event_type_str == 0)
					$event_type_id = 1;
				elseif($event_type_str == "25 miles  - Senior")
					$event_type_id = Arr::get($event_type_ids, "25 miles - Senior");
				elseif($event_type_str == "50 miles -Senior")
					$event_type_id = Arr::get($event_type_ids, "50 miles - Senior");
				else 
					$event_type_id = $this->insert_lookup('event_type', $event_type_str);
			}

			//@todo: do the same thing for event_types as I did for rides!  only import if match
			// and give a warning which spreadsheet rows failed because there WAS an event type
			// but it didn't match any of the existing event types -- yeaaa!!

			$ride_id = (int)Arr::get($ride_ids, trim($item['Ride']));
			if( ! $ride_id)
				$ride_id = $ride_ids['missing'];

			$member_id = Arr::get($member_ids, (int)$item['Rider ID']);
			if($member_id == 0)
				$member_id = 1;

			$equine_id = Arr::get($equine_ids, (int)$item['Horse ID']);
			if($equine_id == 0)
				$equine_id = 1;

			$insert[$row] = array(
				'id'			=> (int)$row + 1,
				'ride_id'		=> (int)$ride_id,
				'event_type_id' => (int)$event_type_id,
				'member_id'		=> $member_id,
				'equine_id'		=> $equine_id,
				'weight'		=> $item['Weight (lbs)'],
				'miles'			=> $item['Miles'],
				'placing'		=> $item['Placing'],
				'time'			=> $item['Time'],
				'points'		=> trim($item['Points'], '.'),
				'bc'			=> $item['BC?'] == 'TRUE',
				'bc_points'		=> $item['BC Points'],
				'bc_score'		=> $item['BC Score'],
				'pull'			=> $item['Pull'] == 'TRUE',
				'pull_reason'	=> $item['Pull Reason'],
				'comments'		=> $item['Comments'],
			);
		}

		try 
		{
			DB::query(NULL, 'START TRANSACTION')->execute();

			foreach($insert as $values)
				DB::insert('event_results', array_keys($values))->values($values)->execute();

			DB::query(NULL, 'COMMIT')->execute();

			DB::query(NULL, 'ALTER TABLE  `event_results` ADD FOREIGN KEY (  `ride_id` ) REFERENCES  `rides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;')->execute();

			DB::query(NULL, 'ALTER TABLE  `event_results` ADD FOREIGN KEY (  `event_type_id` ) REFERENCES  `event_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;')->execute();

			DB::query(NULL, 'ALTER TABLE  `event_results` ADD FOREIGN KEY (  `member_id` ) REFERENCES  `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;')->execute();

			DB::query(NULL, 'ALTER TABLE  `event_results` ADD FOREIGN KEY (  `equine_id` ) REFERENCES  `equines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;')->execute();

			return Minion_CLI::write('Successfully imported Ride Results from file: '.$config['file'], 'green');
		}
		catch(Exception $e) 
		{
			DB::query(NULL, 'ROLLBACK')->execute();
			return Minion_CLI::write('Epic fail! '.$e->getMessage(), 'red');
		}

	}

	protected function insert_into($model, array $results, $include_missing = FALSE)
	{
		foreach($results as $i => $name)
			ORM::factory($table)->values(array('id'=>$i+1,'name'=>$name))->create();

		if($include_missing)
			ORM::factory($table)->values(array('name'=>'missing'))->create();
	}

	protected function insert_lookup($table, $value)
	{
		return ORM::factory($table)->values(array('name'=>$value))->create()->id;
	}

	protected function get_event_id($ride_id, $event_type_id)
	{
		return DB::select('id')
			->from('events')
			->where('ride_id','=',$ride_id)
			->and_where('event_type_id','=',$event_type_id)
			->execute()
			->get('id');
	}

	protected function import_event_types($file_path)
	{
		$events = new Parser_CSV($file_path);
		foreach($events->result() as $row => $item)
		{
			ORM::factory('event_type')->values(array('id'=>$item['ID Number'],'name'=>$item['Event']))->create();
		}
	}

}