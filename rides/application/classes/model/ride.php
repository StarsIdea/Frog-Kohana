<?php

class Model_Ride extends ORM {
	
	protected $_title_key = 'name';	
	protected $_sorting = array(
		'date' => 'DESC',
		'name' => 'ASC'
	);
	protected $_search = array(
		'name'
	);
	
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
			),
			'date' => array(
				array('Valid::date'),
				array(array($this, 'valid_date')),
				//array('Model_Ride::valid_date')
			)
		);
	}

	// expects date in form of YYYY-MM-DD
	public static function valid_date($date)
	{
		if( ! Valid::date($date)) {
			return strlen($date) == 8  ? true : false;
		}
		return true;
	}

	
	public function foreign_title()
	{
		return 'name';
	}
	
	public function save(Validation $validation = NULL)
	{
		$this->sanctioned = Arr::get($_POST, 'sanctioned', 0);
		parent::save($validation);
	}

	public function fetch_rides($limit = NULL, $offset = NULL, $filters = array())
	{
		$query = DB::select(
				'id',
				'name',
				'date',
				'city',
				'province'
			)
			->from('rides');

		// Apply sorting
		$field = Arr::get($filters, 'sort', 'name');
		$order = Arr::get($filters, 'order', 'desc');
		if(Arr::get($filters, 'sort')) {
			$query->order_by($field, $order);
		} else {
			$query->order_by('date', 'desc');
		}

		// Apply pagination
		$limit AND $query->limit($limit);
		$offset AND $query->offset($offset);

		// Define grouping, ordering, limit and offset
		$query->group_by('rides.id')
			->order_by($field, $order);

		$query->where('name','!=','missing');

		// --------------------------------------
		// Apply Filtering
		// --------------------------------------
		if( ! is_null($filter_letter = Arr::get($filters, 'letter')))
		{
			$query->where(DB::expr('SUBSTRING(rides.name,1,1)'), '=', $filter_letter);
		}

		if( ! is_null($search = Arr::get($filters, 'search')))
		{
			$search_string = $this->get_search_string($search);
			foreach($this->_search as $field_name) {
				$query->where($field_name, 'LIKE', $search_string);
			}
		}

		return $query->execute();
	}
	
	public function fetch_recent($limit)
	{
		return DB::select('id','name','date','city','province')
			->from('rides')
			->order_by('rides.date','DESC')
			->order_by('rides.name','DESC')
			->where('name','!=','missing')
			->limit($limit)
			->execute()
			->as_array();
	}

	public function details()
	{
		$query = DB::select(
				'date',
				'city'
			)
			->from('rides')
			->where('rides.id','=', $this->id);

		return $query
			->execute()
			->current();
	}
	
	public function event_results()
	{
		return Model::factory('Event_Result')->fetch_event_results($this);
	}
	
}
