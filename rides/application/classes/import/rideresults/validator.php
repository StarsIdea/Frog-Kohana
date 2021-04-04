<?php

class Import_RideResults_Validator {

	public $errors = array();

	public function __construct($parser)
	{
		$this->parser = $parser;
		$this->parser->rewind();
	}

	public function check()
	{
		while ($this->parser->valid())
		{
			$this->ensure_all_members_exist();

			if($this->parser->key() == 0) {
				$this->validate_ride();
			}

			$this->parser->next();
		}

		return empty($this->errors);
	}

	private function ensure_all_members_exist()
	{
		$member_ids = array_unique(Arr::pluck($this->parser->get('results_senior'), 'member_id'));
		$member_ids = array_filter($member_ids, 'is_numeric');

		$member_ids_actual = ORM::factory('member')->where('id', 'IN', $member_ids)->find_all()->as_array('id');
		$member_ids_missing = array_diff($member_ids, array_keys($member_ids_actual));

		if (count($member_ids_missing) > 0) {
			$member_str = implode(', ', $member_ids_missing);
			$sheet_num = $this->parser->sheet_name();
			$this->errors[] = "Sheet \"$sheet_num\": Missing members $member_str";
			return FALSE;
		}

		return TRUE;
	}

	private function validate_ride()
	{
		try {
			return ORM::factory('ride')->values($this->parser->get('ride'))->check();
		}
		catch(Exception $e) {
			$this->errors[$this->parser->key()] = $e->errors('import/ride');
			return FALSE;
		}
	}

}
