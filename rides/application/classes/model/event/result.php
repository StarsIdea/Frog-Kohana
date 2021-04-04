<?php

class Model_Event_Result extends ORM {
	
	//protected $_display_columns = array(
		//'id',
		//'event',
		//'member',
		//'equine',
		//'placing',
		//'time',
		//'weight',
		//'miles',
		//'points',
		//'bc',
		//'bc_points',
		//'bc_score',
		//'pull',
		//'pull_reason',
		//'comments'
	//);
	
	public $available_filters = array('rider');
	protected $_belongs_to = array(
		'ride' => array(),
		'event_type' => array(),
		'member' => array(),
		'equine' => array(),
	);

	protected $_search = array(
		array('rides', 'name')
	);

	public function table_column_titles()
	{
		return array('id', 'Ride', 'Event Type', 'Rider Name', 'Member ID', 'Equine Name',
		'Equine ID', 'Placing', 'Time', 'Weight', 'Miles', 'Points', 'Vet Score', 'BC', 'BC Points', 'BC Score',
		'Pull', 'Pull Reason', 'Comments', 'Import ID');
	}
	
	public function save(Validation $validation = NULL)
	{
		$this->bc = Arr::get($_POST, 'bc', 0);
		$this->pull = Arr::get($_POST, 'pull', 0);
		parent::save($validation);
	}	
	
	public function fetch_all($limit = NULL, $offset = NULL, array $filters = array(), array $columns = array())
	{
		$query = $this->_fetch_all_query($limit, $offset, $filters, $columns);
		$query->select(
			$this->_table_name.'.id',
			$this->_table_name.'.ride_id',
			$this->_table_name.'.event_type_id',
			$this->_table_name.'.rider_name',
			$this->_table_name.'.member_id',
			$this->_table_name.'.equine_name',
			$this->_table_name.'.equine_id',
			$this->_table_name.'.placing',
			$this->_table_name.'.time',
			$this->_table_name.'.weight',
			$this->_table_name.'.miles',
			$this->_table_name.'.points',
			$this->_table_name.'.vet_score',
			$this->_table_name.'.bc',
			$this->_table_name.'.bc_points',
			$this->_table_name.'.bc_score',
			$this->_table_name.'.pull',
			$this->_table_name.'.pull_reason',
			$this->_table_name.'.comments',
			$this->_table_name.'.import_id'
		);
		$query->join('rides', 'left') // left join important to not exclude any results without a ride
			->on('rides.id','=','event_results.ride_id');
		$query->order_by('rides.name','DESC');
		$query->order_by('event_type_id','ASC');
		$query->order_by(DB::expr('-placing'),'DESC');
		return $query->execute();
	}

	private function prepend_table_name($field_name) {
		return $this->_table_name . '.' . $field_name;
	}

	public function fetch_event_results($ride = NULL, $member = NULL, $equine = NULL)
	{
		$query = DB::select();
		
		if($ride) {
			$query->select_array(array(
					array('event_types.name', 'event_type'),
					array('members.id', 'member_#'),
					array('event_results.rider_name', 'member'),
					array('equines.id', 'equine_#'),
					array('event_results.equine_name', 'equine'),
				))
				->where('ride_id','=', $ride->id)
				->order_by('event_type_id','ASC')
				->order_by(DB::expr('-placing'), 'DESC');
		}
		elseif($member) {
			$query->select_array(array(
					array('rides.id', 'ride_id'),
					array('rides.name', 'ride_name'),
					array('rides.date', 'ride_date'),
					array('event_types.name', 'event_type'),
					array('equines.id', 'equine_#'),
					//array('equines.name', 'equine'),
					array('event_results.equine_name', 'equine'),
				))
				->where('event_results.member_id','=',$member->id)
				->and_where('rides.name','IS NOT', NULL);
		}
		elseif($equine) {
			$query->select_array(array(
					array('rides.id', 'ride_id'),	
					array('rides.name', 'ride_name'),
					array('rides.date', 'ride_date'),
					array('event_types.name', 'event_type'),
					array('members.id', 'member_#'),
					array('event_results.rider_name', 'member'),
					//array(DB::expr("CONCAT(last_name, ', ', first_name)"), 'member'),
				))
				->where('event_results.equine_id','=',$equine->id);
		}
		else {
			throw new ERA_Exception('A ride, member, or equine is required.');
		}

		$query->select_array(array(
				'event_results.placing',
				array(DB::expr("IF(event_results.time IS NOT NULL, event_results.time, '')"), 'time'),
			));

		if ($ride) {
			$query->select(array('event_results.weight', 'weight'));
		}

		$query->select_array(array(
				'event_results.miles',
				'event_results.points',
				'event_results.vet_score',
				array(DB::expr("IF(event_results.bc, 'Yes', NULL)"), 'bc'),
				array(DB::expr("IF(event_results.bc_score > 0, event_results.bc_score, NULL)"), 'bc score'),
				array(DB::expr("IF(event_results.bc_points > 0, event_results.bc_points, NULL)"), 'bc points'),
				array($this->_expr('pull_with_reason'), 'pull / reason'),
				'event_results.comments'
			))
			->from('event_results')
			->join('event_types', 'LEFT')->on('event_types.id','=','event_results.event_type_id')
			->join('rides', 'LEFT')->on('rides.id','=','event_results.ride_id')
			->join('members', 'LEFT')->on('members.id','=','event_results.member_id')
			->join('equines', 'LEFT')->on('equines.id','=','event_results.equine_id');

		if(isset($_GET['sort'])){
			$sorting = array($_GET['sort'] => Arr::get($_GET, 'order', 'asc'));
		}
		else{
			$sorting = array(
				'rides.date' => 'desc',
				'rides.name' => 'desc',
			);
		}
		
		foreach($sorting as $field => $order){
			$query->order_by($field, $order);
		}
			
		$query_result = $query->execute();

		if ($ride) {
			$query_result = $query_result->as_array();
			$sorted_results = $this->sort_event_results_by_type($query_result);
			usort($sorted_results, array($this, 'sort_by_placing'));
			return $sorted_results;
		}	

		return $query_result;
	}

	public static function sort_by_placing($a, $b) {
		if ($a['placing'] === null && $b['placing'] !== null) {
			return 1;
		}

		if ($b['placing'] === null && $a['placing'] !== null) {
			return -1;
		}

		if ($a['placing'] == $b['placing']) {
			return 0;
		}
		return ($a['placing'] < $b['placing']) ? -1 : 1;
	}

	public static function sort_event_results_by_type(array $event_results)
	{
		$grouped = array(
			'Senior' => array(),
			'Youth' => array(),
			'Junior' => array(),
			'Intro' => array(),
			'_other' => array(),
		);

		foreach ($event_results as $row) {
			if (strpos($row['event_type'], 'Senior') !== FALSE) {
				$grouped['Senior'][] = $row;
			} else if (strpos($row['event_type'], 'Youth') !== FALSE) {
				$grouped['Youth'][] = $row;
			} else if (strpos($row['event_type'], 'Junior') !== FALSE) {
				$grouped['Junior'][] = $row;
			} else if (strpos($row['event_type'], 'Intro') !== FALSE) {
				$grouped['Intro'][] = $row;
			} else {
				$grouped['_other'][] = $row;
			}
		}

		foreach ($grouped as $i => & $rows) {
			// quick fix to order within group so 25 Senior comes before 50 Senior, etc.
			sort($rows);
		}

		$results = array();
		foreach ($grouped as $rows) {
			$results = array_merge($results, $rows);	
		}

		return $results;
	}

	public function _expr($type)
	{
		switch($type) {	
			case 'pull_with_reason':
				return DB::expr("IF(event_results.pull, CONCAT('Yes / ', IF(event_results.pull_reason IS NOT NULL, event_results.pull_reason, 'None')), '')");
		}
	}
	
}
