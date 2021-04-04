<?php

class Import_RideResults_Parser implements Iterator {

	const RIDE_DATE = 'C4';
	const RIDE_NAME = 'C5';
	const RIDE_LOCATION = 'C6';
	const RIDE_MANAGER = 'C7';
	const RIDE_SECRETARY = 'C8';
	const RIDE_VETERINARIAN = 'C9';
	const RIDE_DISTANCE = 'C10';

	const EVENT_NUM_STARTERS = 'J4';
	const EVENT_NUM_COMPLETIONS = 'J5';

	const EVENTRESULTS_BC_HORSE_SR = 'J7';
	const EVENTRESULTS_BC_SCORE_SR = 'J8';

	const EVENTRESULTS_BC_HORSE_JR = 'O7';
	const EVENTRESULTS_BC_SCORE_JR = 'O8';

	private $data = array();
	private $current_sheet_index;
	private $current_sheet;
	private $index = 0;

	public function __construct($reader, array $file)
	{
		$this->reader = $reader;
		$this->file = $file;
	}

	public function sheet_name()
	{
		$sheets = $this->sheets();
		return Arr::get($sheets, $this->key());
	}

	public function sheet($num)
	{
		$this->current_sheet_index = $num;
		$this->current_sheet = Arr::get($this->reader->sheets, $num);
		return $this;
	}

	public function sheets()
	{
		$sheets = Arr::pluck($this->reader->boundsheets, 'name');
		return array_filter($sheets, 'is_numeric');
	}

	public function get($key)
	{
		return $this->data[$this->index][$key];
	}

	public function parse()
	{
		foreach($this->sheets() as $num => $name) {
			$this->sheet($num);

			$this->data[$num]['ride']			  = $this->parse_ride();
			$this->data[$num]['event']			  = $this->parse_event();

			$this->data[$num]['results_senior']   = $this->parse_results_by_bounds('Placing', 'Senior', true);
			$this->data[$num]['dnf_senior']       = $this->parse_results_by_bounds('DNF', 'Senior');

			$this->data[$num]['results_junior']   = $this->parse_results_by_bounds('Juniors', 'Junior', true);
			$this->data[$num]['dnf_junior']       = $this->parse_results_by_bounds('DNF', 'Junior', false, 2);
		}

		$this->rewind();
		return $this;
	}

	private function parse_ride()
	{
		if($this->current_sheet_index == 0)
		{
			$date = $this->get_value(self::RIDE_DATE);
			$year = date('Y', strtotime($date));

			return array(
				'date'			=> $date,
				'name'     		=> $year .' '. $this->get_value(self::RIDE_NAME),
				'location' 		=> $this->get_value(self::RIDE_LOCATION),
				'manager'  		=> $this->get_value(self::RIDE_MANAGER),
				'secretary'		=> $this->get_value(self::RIDE_SECRETARY),
				'veterinarian'  => $this->get_value(self::RIDE_VETERINARIAN),
				'distance'      => $this->get_value(self::RIDE_DISTANCE),
			);
		}
	}

	private function parse_event()
	{
		return array(
			'name' => $this->get_value(self::RIDE_DISTANCE) . ' miles',
			// starters and completions can be derived from an SQL query
			//'num_starters' => $this->get_value(self::EVENT_NUM_STARTERS),
			//'num_completions' => $this->get_value(self::EVENT_NUM_COMPLETIONS),
		);
	}

	public static function get_bounds($string)
	{
		$bounds = array();
		$letters = range('A','Z');
		array_unshift($letters, '_');
		$letters = array_flip($letters);
		preg_match('/([A-Z]*)([0-9]*)/', $string, $bounds);
		$bounds = array_slice($bounds, 1);
		return array($bounds[1],$letters[$bounds[0]]);
	}

	private static function get_time($time_str)
	{
		return ! empty($time_str) ? substr($time_str, 0, 8) : NULL;
	}

	private function parse_results_by_bounds($value, $member_type, $unset_header=false, $occurance=1)
	{
		$results = array();

		$bounds = $this->get_bounds_from_column_header($value, $occurance);
		//var_dump($value, $member_type, $occurance, $bounds);
		//var_dump($value, $member_type, $occurance, $bounds, $this->current_sheet['cells']);

		if ( ! $bounds) {
			return array(); // could not find the section
		}

		$rows = array_slice($this->current_sheet['cells'], $bounds[0], $bounds[1]);

		if ($unset_header) { array_shift($rows); }

		$miles = $this->get_value(self::RIDE_DISTANCE);
		$bc_horse_bounds = $member_type == 'Senior' ? self::EVENTRESULTS_BC_HORSE_SR : self::EVENTRESULTS_BC_HORSE_JR;
		$bc_score_bounds = $member_type == 'Senior' ? self::EVENTRESULTS_BC_SCORE_SR : self::EVENTRESULTS_BC_SCORE_JR;
		$bc_horse = $this->get_value($bc_horse_bounds);
		$bc_score = $this->get_value($bc_score_bounds);
		
		foreach($rows as $result)
		{
			$horse = Arr::get($result, 4);
			$best_conditioned = ($horse and $horse == $bc_horse);
			$member_id = Arr::get($result, 3);

			$time = self::get_time(Arr::get($result, 6));

			$pull = Arr::get($result, 10);
			$pull_reason = Arr::get($result, 11);

			$obj = array(
				'rider_name' => is_int($member_id) ? NULL : Arr::get($result, 2),
				'member_id' => $member_id, 
				'equine_name' => Arr::get($result, 4),
				'equine_id' => Arr::get($result, 5), 
				'placing' => in_array($value, array('Placing','Juniors')) ? Arr::get($result, 1) : NULL, 
				'time' => $time,
				'weight' => Arr::get($result, 7), 
				'miles' => $miles, 
				'points' => Arr::get($result, 12), 
				'bc' => $best_conditioned ? 1 : 0,
				'bc_points' => Arr::get($result, 13), 
				'bc_score' => $best_conditioned ? $bc_score : NULL, 
				'pull' => empty($pull) ? 0 : 1,
				'pull_reason' => (empty($pull_reason) AND $pull) ? $pull : $pull_reason, 
				// 'comments'
			);

			if($obj['rider_name'] OR $obj['member_id']) {
				$results[] = $obj;
			}
		}

		return $results;
	}

	public static function get_block_indexes(array $array)
	{
		$chunks = array();
		$prev_i = NULL;
		$chunk_end = NULL;
		$keys = array_keys($array);
		$len = count($array);

		foreach($array as $i => $row)
		{
			if ($chunk_end === NULL) {
				$chunk_end = $i;
			}

			if ($prev_i !== NULL AND $i - $prev_i > 1) {
				$chunks[] = array($chunk_end, $prev_i);
				$chunk_end = $i;
			}	
			$prev_i = $i;
		}

		// if last item
		if ($len - 1 == array_search($i, $keys)) {
			$chunks[] = array($chunk_end, $i);
		}

		return $chunks;
	}

	public static function get_offset_and_length(array $array, $search, $occurance = 1)
	{
		$block_indexes = self::get_block_indexes($array);
		$keys = array_keys($array);
		$found = 0;

		foreach ($block_indexes as $startend) {
			list($start, $end) = $startend;
			$first_column_value = Arr::get($array[$start], 1);

			if ($first_column_value == $search) {
				$found += 1;
				if ($found === $occurance) {
					$offset = array_search($start, $keys);
					$length = array_search($end, $keys);
					$length = ($length - $offset);
					return array($offset, $length + 1);
				}
			}
		}
	}

	private function get_bounds_from_column_header($string, $occurance)
	{
		return self::get_offset_and_length($this->current_sheet['cells'], $string, $occurance);
	}

	private function get_value($bounds_string)
	{
		$bounds = self::get_bounds($bounds_string);
		return Arr::get($this->current_sheet['cells'][$bounds[0]], $bounds[1]);
	}

	// Iterator

	public function current()
	{
		return $this->data[$this->index];
	}

	public function key()
	{
		return $this->index;
	}

	public function next()
	{
		$this->index++;
		return $this;
	}

	public function rewind()
	{
		$this->index = 0;
	}

	public function valid()
	{
		return isset($this->data[$this->index]);
	}

}
