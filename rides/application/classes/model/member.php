<?php

class Model_Member extends ORM implements Acl_Resource_Interface {
	
	public $available_filters = array('active');
	protected $_display_columns = array('id','ERA #','AEF #','active','member_type','first_name','last_name','email','address','city','prov / state','postal_code','phone','birth_date');
	
	public function get_resource_id()
	{
		return 'member';
	}
	
	public function rules()
	{
		return array(
			'first_name' => array(
				array('not_empty'),
			),
			'last_name' => array(
				array('not_empty'),
			),
		);
	}
	
	public function foreign_title()
	{
		return DB::expr('CONCAT(id," - ",first_name," ",last_name )');
	}
	
	public function select_array()
	{
		$arr = array();
		foreach($this->find_all() as $item) {
			$arr[$item->id] = $item->id . ': ' . $item->last_name . ', ' . $item->first_name;
		}
		return $arr;
	}

	public function set_all_inactive()
	{
		return DB::update($this->_table_name)
			->set(array('active' => 0))
			->where('active', '=', 1)
			->execute();
	}

	public function save(Validation $validation = NULL)
	{
		$this->active = Arr::get($_POST, 'active', 0);
		parent::save($validation);
	}

	public function fetch_all($limit = NULL, $offset = NULL, array $filters = array(), array $columns = array())
	{
		$query = $this->_fetch_all_query($limit, $offset, $filters, $columns);
		$query->select(
			'*',
			array('id','ERA #'),
			array('aef_number', 'AEF #'),
			array('phone_home', 'phone'),
			array('state_prov','prov / state'),
			array($this->_select_age_at_first_ride_of_season(), 'season_age'),
			array(DB::select($this->_expr('member_type')), 'member_type')
		);
		$query->where('members.id','!=',1); // missing member for ride data without member
		return $query->execute();
	}

	public function fetch_riders($limit = NULL, $offset = NULL, $filters = array())
	{
		$field = Arr::get($filters, 'sort', $this->tk());
		$order = Arr::get($filters, 'order', 'ASC');

		// Hacky solution because tables are generated in code and not html
		if ($field == "ERA #") {
			$field = 'id';
		}

		$year = (string)Arr::get($filters, 'year', date('Y'));

		// --------------------------------------
		// Sub-Queries (new to increase performance)
		// --------------------------------------
		$reclaim_ytd = DB::select('members.id', DB::expr('SUM(reclaims.miles_completed) as reclaim_ytd'))
			->from('reclaims')
			->join('members')->on('reclaims.member_id','=','members.id')
			//->and_where('reclaims.date','>=',mktime(0, 0, 0, 1, 1, date('Y')))
			->and_where('reclaims.year','>=',$year)
			->group_by('members.id')
			->execute()
			->as_array('id','reclaim_ytd');

		$mileage_ytd = DB::select(array(
				'event_results.member_id', 'id'),
				DB::expr('COALESCE(SUM(event_results.miles), 0) as mileage_ytd')
			)
			->from('event_results')
			->join('rides')->on('rides.id','=','event_results.ride_id')
			->where(DB::expr('YEAR(rides.date)'),'>=', $year)
			->group_by('id');
		$mileage_ytd = $mileage_ytd
			->execute()
			->as_array('id','mileage_ytd');
		
		$lifetime_mileage = DB::select('members.id',DB::expr("SUM(event_results.miles) as lifetime_mileage"))
			->from('event_results')
			->join('members')->on('event_results.member_id','=','members.id') 
			->where('event_results.ride_id', '!=', NULL)
			->group_by('members.id')
			->execute()
			->as_array('id', 'lifetime_mileage');
		
		$lifetime_reclaim = DB::select('members.id', DB::expr("SUM(reclaims.miles_completed) as reclaim_mileage"))
			->from('reclaims')
			->join('members')->on('reclaims.member_id','=','members.id') 
			->group_by('members.id')
			->execute()
			->as_array('id','reclaim_mileage');

		// --------------------------------------
		// Query
		// --------------------------------------
		$query = DB::select_array(array(
				'members.id',
				array(DB::expr('CONCAT(members.last_name, ", ", members.first_name)'), 'name'),
				array('members.state_prov', 'province'),
			))
			->from('members')
			->join('event_results', 'LEFT')->on('event_results.member_id','=','members.id')
			->join('event_types', 'LEFT')->on('event_types.id','=', 'event_results.event_type_id')
			->join('rides', 'LEFT')->on('event_results.ride_id','=', 'rides.id')
			->where('members.id', '!=', 1)
			->group_by('members.id');

		// Apply sorting
		if( ! in_array($field, array('lifetime_mileage', 'YTD_mileage')))
			$query->order_by($field, $order);

		// Apply pagination (commented out bc use manual pagination now)
		//$limit AND $query->limit($limit);
		//$offset AND $query->offset($offset);

		// --------------------------------------
		// Apply Filtering
		// --------------------------------------
		if( ! is_null($filter_letter = Arr::get($filters, 'letter')))
			$query->where(DB::expr('SUBSTRING(members.last_name,1,1)'), '=', $filter_letter);

		if( ! is_null($filter_active = Arr::get($filters, 'active')))
			$filter_active == 1
				? $query->where('active','=',$filter_active)
				: $query->where('active','=',0);

		if( ! is_null($search = Arr::get($filters, 'search'))) {
			$search = $this->get_search_string($search);
			$this->_build_search($query, $this, $search);
		}

		// --------------------------------------
		// Assemble results from separate queries
		// !!!NOTE!!! Cannot order these from table header!!
		// --------------------------------------
		$result = array();
		foreach($query->execute()->as_array() as $row) {
			$mileage = (int)Arr::get($mileage_ytd, $row['id'], 0);
			$reclaim = (int)Arr::get($reclaim_ytd, $row['id'], 0);
			$life_mileage = (int)Arr::get($lifetime_mileage, $row['id'], 0);
			$life_reclaim = (int)Arr::get($lifetime_reclaim, $row['id'], 0);
			$row['YTD_mileage'] = $mileage + $reclaim;
			$row['lifetime_mileage'] = $life_mileage + $life_reclaim;
			$result[] = $row;
		}

		if ($field AND $field === 'lifetime_mileage') {
			usort($result, $order == 'asc'
				? $this->callback_sort_by('lifetime_mileage', 'asc')
				: $this->callback_sort_by('lifetime_mileage', 'desc'));
		}

		if ($field AND $field === 'YTD_mileage') {
			usort($result, $order == 'asc'
				? $this->callback_sort_by('YTD_mileage', 'asc')
				: $this->callback_sort_by('YTD_mileage', 'desc'));
		}

		// manual pagination with array_slice since we can't sort by lifetime_mileage/ytd_mileage via db query
		return array_slice($result, $offset, $limit);
	}

	protected function callback_sort_by($field, $dir)
	{
		return create_function('$a,$b', 'return (int)$a["'.$field.'"] '.($dir=='asc'?'>':'<').' (int)$b["'.$field.'"] ? 1 : -1;');
	}
	
	public function fetch_top_riders()
	{
		return DB::select_array(array(
				'members.id',
				array($this->_expr('member_name'), 'name'),
				array($this->_expr('total_points'), 'total_points'),
			))
			->from('members')
			->join('event_results')->on('event_results.member_id','=','members.id')
			->group_by('event_results.member_id')
			->order_by('total_points','DESC')
			->limit(3)
			->execute()
			->as_array();
	}
	
	public function fetch_top_ranking($member_type=NULL)
	{
		$members = DB::select_array(array(
				'members.id',
				array($this->_expr('member_name'), 'name'),
				array($this->_expr('total_points'), 'total_points'),
			))
			->from('members')
			->join('event_results')->on('event_results.member_id','=','members.id')
			->join('rides')->on('event_results.ride_id','=','rides.id')
			->where('members.id','!=', 1)
			->where(DB::expr('YEAR(rides.date)'),'<', date('Y'))
			->group_by('event_results.member_id')
			->order_by('total_points','DESC')
			->execute()
			->as_array();

		$ages = $this->member_ages();

		foreach($members as $member)
		{
			if((int)$age = Arr::get($ages, $member['id'])) {
				if($member_type == 'Senior' AND $age > 21)
					$result[] = $member;
				elseif($member_type == 'Junior' AND $age < 16 AND $age > 0)
					$result[] = $member;
				elseif($member_type == 'Youth' AND $age < 21 AND $age >= 16)
					$result[] = $member;
			}

			if( ! $member_type)
				$result[] = $member;
		}

		$result = array_slice($result, 0, 3);

		return $result;
	}

	public function details()
	{
		$mileage_ytd = DB::select(DB::expr('COALESCE(SUM(event_results.miles), 0) as mileage_ytd'))
			->from('event_results')
			->join('rides')->on('rides.id','=','event_results.ride_id')
			->where(DB::expr('YEAR(rides.date)'),'>=', date('Y'))
			->where('event_results.member_id', '=', $this->id);

		// Set up basic member details
		$query = DB::select(
				'members.id',
				'members.active',
				'members.last_name',
				'members.first_name',
				'members.city',
				'members.state_prov'
			)
			->from('members')
			->where('members.id','=', $this->id);

		// Add lifetime mileage to query
		$query->select(
			array(DB::expr("COALESCE(({$this->_select_lifetime_reclaim_mileage()}), 0) + COALESCE(SUM(event_results.miles), 0)"), 'lifetime_mileage'),
			array($mileage_ytd, 'YTD_mileage')
			)
			->join('event_results', 'LEFT')->on('event_results.member_id','=','members.id')
			->join('event_types', 'LEFT')->on('event_types.id','=','event_results.event_type_id')
			->and_where('event_results.ride_id','IS NOT',NULL); // ensure completed event

		// Add member type to query
		$query->select(array($this->_select_age_at_first_ride_of_season(), 'season_age'));
		$query->select(array(DB::select($this->_expr('member_type')), 'member_type'));

		// Add active status to query
		//$query->select(array(Model_Member::select_active(), 'active'));

		$result = $query->execute();
		return $result->current();
	}

	public function event_results()
	{
		return Model::factory('event_result')->fetch_event_results(NULL, $this, NULL);
	}

	public function equines_ridden()
	{
		$query = DB::select_array(array(
				'equines.id',
				'equines.name',
				DB::expr('COUNT(equines.name) as times_ridden')
			))
			->distinct(TRUE)
			->from('event_results')
			->where('event_results.member_id','=', $this->id)
			->and_where('event_results.equine_id','IS NOT', NULL)
			->and_where('event_results.equine_id','!=', 1)
			->join('equines', 'LEFT')->on('event_results.equine_id','=','equines.id')
			->order_by('times_ridden', 'DESC')
			->order_by('equines.id', 'ASC')
			->group_by('equines.name');

		return $query->execute();
	}

	protected function member_ages()
	{
		return DB::select('members.id', array($this->_expr('age_from_date'), 'age')) // queries members
			->from('rides')
			->where('rides.date','>=', $this->earliest_ride_date_from_current_year())
			->join('event_results')->on('event_results.ride_id','=','rides.id')
			->join('members')->on('event_results.member_id','=','members.id')
			->order_by('members.id', 'asc')
			->execute()
			->as_array('id','age');
	}
	
	protected function _select_age_at_first_ride_of_season()
	{
		// Get the members age at the first ride of the season
		return DB::select($this->_expr('age_from_date')) // queries members
			->from('rides')
			->where('rides.date','>=', $this->earliest_ride_date_from_current_year())
			->order_by('rides.date', 'asc')
			->limit(1);
	}

	protected function earliest_ride_date_from_current_year()
	{
		return DB::select('rides.date',$this->_expr('ride_year'))
			->from('rides')
			->order_by('ride_year', 'DESC')
			->order_by('date', 'ASC')
			->limit(1)
			->execute()
			->get('date');;
	}
	
	protected function _select_lifetime_reclaim_mileage()
	{
		return DB::select(DB::expr('SUM(reclaims.miles_completed)'))
			->from('reclaims')
			->where('reclaims.member_id','=','members.id');
	}
	
	protected function _expr($type)
	{
		switch($type) {
			case 'total_points':
				return DB::expr("
					ROUND(SUM(IF(points IS NOT NULL, points, 0))
					+ SUM(IF(bc_points IS NOT NULL, bc_points, 0)))
				");
			case 'member_name':
				return DB::expr("CONCAT(members.last_name,', ',members.first_name)");
				
			case 'age_from_date':
				return DB::expr("DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth_date, '%Y') -
					(DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth_date, '00-%m-%d'))");
				
			case 'age_from_timestamp':
				$birthdate = self::_expr('birthdate');
				return DB::expr("DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT($birthdate, '%Y') -
					(DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT($birthdate, '00-%m-%d'))");
				
			case 'birthdate':
				return DB::expr("DATE_ADD(FROM_UNIXTIME(0), INTERVAL `members`.`birthdate` SECOND)");
				
			case 'member_type':
				return DB::expr("
					CASE
						WHEN season_age >= 21 THEN 'Senior'
						WHEN season_age < 21 AND season_age >= 16 THEN 'Youth'
						WHEN season_age < 16  THEN 'Junior'
					END");
				
			case 'ride_date':
				return DB::expr("DATE_ADD(FROM_UNIXTIME(0), INTERVAL `rides`.`date` SECOND)");
				
			case 'ride_year':
				return DB::expr("DATE_FORMAT(FROM_UNIXTIME(rides.date), '%Y') as ride_year");
		}
	}
	
}
