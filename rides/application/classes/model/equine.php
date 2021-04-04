<?php

class Model_Equine extends ORM implements Acl_Resource_Interface {
	
	public $available_filters = array('active');
	// Checkbox - Modify/Delete - Equine ID - Name - Member ID - Owner Name - Active - Registration Date - Equine Sex - Breed - Breed Registry # - Foal Date - Color.
	protected $_display_columns = array('id','Equine ID','name','member_id','owner_name','active','registration_date','equine_sex_id','breed','breed_registry_#','foal_date','color');
	
	protected $_belongs_to = array(
		'member' => array(),
		'equine_sex' => array(),
	);

	public function fetch_all($limit=NULL, $offset=NULL, array $filters=array(), array $columns=array())
	{
		$query = $this->_fetch_all_query($limit, $offset, $filters, $columns);
		$query->select(
			'*',
			array('id','Equine ID')/*,
			array('equine_sex_id','Equine Sex')*/
		);
		return $query->execute();
	}
		
	public function get_resource_id()
	{
		return 'equine';
	}
	
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
			),
		);
	}
	
	public function foreign_title()
	{
		return DB::expr('CONCAT(id," - ",name)');
	}
	
	public function select_array()
	{
		$arr = array();
		foreach($this->find_all() as $item) {
			$arr[$item->id] = $item->id . ': ' . $item->name;
		}
		return $arr;
	}
	
	public function save(Validation $validation = NULL)
	{
		if($_POST['member_id'] == '') {
			//$this->member_id = NULL;
		}
		$this->active = Arr::get($_POST, 'active', 0);
		parent::save($validation);
	}

	public function fetch_equines($limit = NULL, $offset = NULL, $filters = array())
	{
		// Sub-query to sum miles for Lifetime Mileage
		$select_reclaim_life = DB::select()
			->select(DB::expr('SUM(reclaims.miles_completed)'))
			->from('reclaims')
			->where('reclaims.equine_id','=','equines.id');

		// Sub-query to sum reclaim miles for current year
		$select_reclaim_ytd = DB::select()
			->select(DB::expr('SUM(reclaims.miles_completed)'))
			->from('reclaims')
			->where('reclaims.equine_id','=','equines.id')
			;//->and_where('reclaims.date','>=',mktime(0, 0, 0, 1, 1, date('Y')));
	
		$mileage_ytd = DB::select(DB::expr('SUM(event_results.miles)'))
			->from('event_results')
			->join('rides', 'left')->on('rides.id','=','event_results.ride_id')
			->join('event_types', 'LEFT')->on('event_types.id','=','event_results.event_type_id')
			->where(DB::expr('YEAR(rides.date)'),'>=', date('Y'))
			->and_where('equines.id', '=', DB::expr('event_results.equine_id')); // ARRRGH!!!! This should not need to be wrapped in DB::expr (it quotes it otherwise)!!

		// Name, breed, sex, owner, color, year-to-date mileage, lifetime mileage (event result + reclaimed)
		$query = DB::select(
				'equines.id',
				array('equines.name', 'name'),
				array('equines.breed', 'breed'),
				array('equine_sexes.name', 'sex'),
				array(DB::expr('CONCAT(members.last_name, ", ",members.first_name)'), 'owner'),
				array('equines.color', 'color'),
				array(DB::expr("COALESCE(($select_reclaim_ytd), 0) + COALESCE(($mileage_ytd), 0)"), 'YTD_mileage'),
				array(DB::expr("COALESCE(($select_reclaim_life), 0) + COALESCE(SUM(event_results.miles), 0)"),'lifetime_mileage')
			)
			->from('equines')
			->join('equine_sexes', 'LEFT')->on('equine_sexes.id','=','equines.equine_sex_id')
			->join('members', 'LEFT')->on('members.id','=','equines.member_id')
			->join('event_results', 'LEFT')->on('event_results.equine_id','=','equines.id')
			->join('event_types', 'LEFT')->on('event_types.id','=', 'event_results.event_type_id')
			->join('rides', 'LEFT')->on('event_results.ride_id','=', 'rides.id')
			->group_by('equines.id')
			;

		// sorting
		$field = Arr::get($filters, 'sort', 'name');
		$order = Arr::get($filters, 'order', 'asc');
		$query->order_by($field, $order);

		// pagination
		$limit AND $query->limit($limit);
		$offset AND $query->offset($offset);

		// filtering
		if( ! is_null($filter_letter = Arr::get($filters, 'letter')))
		{
			$query->where(DB::expr('SUBSTRING(equines.name,1,1)'), '=', $filter_letter);
		}

		// search
		if( ! is_null($search = Arr::get($filters, 'search')))
		{
			$search = $this->get_search_string($search);
			$this->_build_search($query, $this, $search);
		}

		return $query->execute();
	}

	public function details()
	{
		$mileage_ytd = DB::select(DB::expr('COALESCE(SUM(event_results.miles), 0) as mileage_ytd'))
			->from('event_results')
			->join('rides')->on('rides.id','=','event_results.ride_id')
			->where(DB::expr('YEAR(rides.date)'),'>=', date('Y'))
			->where('event_results.equine_id', '=', $this->id);

		$mileage = DB::select(DB::expr('COALESCE(SUM(event_results.miles), 0) as lifetime_mileage'))
			->from('event_results')
			->join('rides')->on('rides.id','=','event_results.ride_id')
			->where('event_results.equine_id', '=', $this->id);

		return DB::select_array(array(
				array(DB::expr("CONCAT(members.last_name,', ',members.first_name)"), 'member_name'),
				'equines.owner_name',
				array('equine_sexes.name', 'sex'),
				'equines.foal_date',
				'equines.breed',
				'equines.color',
				array($mileage_ytd, 'YTD_mileage'),
				array($mileage, 'lifetime_mileage')
			))
			->from('equines')
			->join('equine_sexes', 'LEFT')->on('equines.equine_sex_id','=','equine_sexes.id')
			->join('members','LEFT')->on('members.id','=','equines.member_id')
			->where('equines.id','=',$this->id)
			->execute()
			->current();
	}

	public function fetch_ridden_by()
	{
		$query = DB::select_array(array(
				'members.id',
				DB::expr("CONCAT(last_name, ', ', first_name) as name"),
			))
			->distinct(TRUE)
			->from('members')
			->join('event_results')->on('event_results.member_id','=','members.id')
			->where('event_results.equine_id','=',$this->id);

		return $query->execute();
	}

	public function event_results()
	{
		return Model::factory('Event_Result')->fetch_event_results(NULL, NULL, $this);
	}

	public function fetch_top_ranking()
	{
		$result = DB::select_array(array(
				'equines.id',
				'equines.name',
				array(DB::expr("
					ROUND(SUM(IF(points IS NOT NULL, points, 0))
					+ SUM(IF(bc_points IS NOT NULL, bc_points, 0)))
				"), 'total_points'),
			))
			->from('equines')
			->join('event_results')->on('event_results.equine_id','=','equines.id')
			->join('rides')->on('event_results.ride_id','=','rides.id')
			->where('equines.id','!=', 1)
			->where(DB::expr('YEAR(rides.date)'),'<', date('Y'))
			->group_by('event_results.equine_id')
			->order_by('total_points','DESC')
			->execute()
			->as_array();

		$result = array_slice($result, 0, 3);

		return $result;
	}
	
}
