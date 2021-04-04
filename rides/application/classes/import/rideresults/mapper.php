<?php

ini_set('memory_limit', '256M');

class Import_RideResults_Mapper {

	public $saved = array();

	public function __construct($parser, array $file)
	{
		$this->parser = $parser;
		$this->file = $file;

		// References
		$this->equines = ORM::factory('equine')->find_all()->as_array('id');
		$this->members = ORM::factory('member')->find_all()->as_array('id');
	}

	public function save()
	{
		$this->parser->rewind();

		try
		{
			DB::query(NULL, 'START TRANSACTION')->execute();

			$this->import = $this->save_import();

			while ($this->parser->valid())
			{
				$ride = $this->parser->get('ride');
				$event = $this->parser->get('event');

				$results_sr = $this->parser->get('results_senior');
				$results_jr = $this->parser->get('results_junior');
				$dnf_sr = $this->parser->get('dnf_senior');
				$dnf_jr = $this->parser->get('dnf_junior');

				if($ride !== NULL) {
					$this->ride = $this->save_ride($ride);
				}

				if( ! empty($results_sr))
				{	
					$this->find_or_save_event_type($event, 'Senior');
					$this->save_results($results_sr, $this->event_types['Senior']);
					$this->save_dnf($dnf_sr, $this->event_types['Senior']);
				}

				if( ! empty($results_jr))
				{
					$this->find_or_save_event_type($event, 'Junior');
					$this->save_results($results_jr, $this->event_types['Junior']);
					$this->save_dnf($dnf_jr, $this->event_types['Junior']);
				}

				$this->parser->next();
			}

			DB::query(NULL, 'COMMIT')->execute();
			Kohana::$log->add(Kohana_Log::ERROR, 'Imported batch '.$this->import->id.' successfully.');

			return TRUE;
		}
		catch(Exception $e) 
		{
			DB::query(NULL, 'ROLLBACK')->execute();

			if(Kohana::$environment == Kohana::TESTING)
			{
				throw $e;	
			}

			if(Kohana::$environment <= Kohana::STAGING)
			{
				Kohana::$log->add(Kohana_Log::ERROR, Kohana_Exception::text($e));
			}

			return FALSE;
		}
	}

	protected function save_import()
	{
		$import_batch_id = DB::select('batch')->from('imports')->order_by('batch','DESC')->limit(1)->execute()->get('batch');

		// Create import batch
		return ORM::factory('import')->values(array(
			'model' => 'event_result',
			'batch' => $import_batch_id + 1,
			'name' => $this->file['name'],
			'type' => $this->file['type'],
			'size' => (int)$this->file['size'],
		))->create();
	}

	protected function save_ride(array $data)
	{
		if(isset($this->ride)) return;
		$values = array_merge($data, array('import_id' => $this->import->id));
		$date = date_parse($values['date']);
		$values['date'] = implode('-', array($date['year'], $date['month'], $date['day']));

		return ORM::factory('ride')->values($values)->create();
	}

	protected function find_or_save_event_type(array $data, $member_type)
	{
		$event_name = $data['name'] . ' - ' . $member_type;
		$this->event_types[$member_type] = ORM::factory('event_type')->where('name','=',$event_name)->find();

		if( ! $this->event_types[$member_type]->loaded()) {
			$this->event_types[$member_type] = ORM::factory('event_type')->values(array('name'=>$event_name))->create();
		}
	}

	protected function save_results(array $rows, Model_Event_Type $event_type)
	{
		$this->saved[] = $event_type->name . ' Results';

		foreach($rows as $row => $values) {
			$values['import_id'] = $this->import->id; // associate data with import
			$values['ride_id'] = $this->ride->id;
			$values['event_type_id'] = $event_type->id;
			$this->_clean_values($values);

			$member_id = $values['member_id'];
			if ($member_id !== NULL && ! isset($this->members[$member_id])) {
				throw new Exception("Member \"$member_id\" not found!");
			}

			DB::insert('event_results', array_keys($values))->values($values)->execute();
		}
	}

	protected function save_dnf(array $rows, Model_Event_Type $event_type)
	{
		if(empty($rows)) return;
		$this->saved[] = $event_type->name . ' DNF';

		foreach($rows as $row => $values) {
			$values['import_id'] = $this->import->id; // associate data with import
			$values['ride_id'] = $this->ride->id;
			$values['event_type_id'] = $event_type->id;
			$this->_clean_values($values);
			DB::insert('event_results', array_keys($values))->values($values)->execute();
		}
	}

	protected function _clean_values( & $values)
	{
		$equine_id = $values['equine_id'];

		// Create new equine and set the id if it doesnt exist
		if($equine_id AND $equine_id != 'N/A' AND ! array_key_exists($equine_id, $this->equines)) {
			$values['equine_id'] = $this->create_equine($values['equine_name']);
		}

		foreach($values as & $value) {
			if($value == 'N/A') $value = NULL;
		}
	}

	protected function create_equine($equine_name)
	{
		try {
			return ORM::factory('equine')
				->values(array('name'=>$equine_name))
				->create()
				->id;
		} catch(ORM_Validation_Exception $e) {
			return $e->errors('model');
		}
	}

}
