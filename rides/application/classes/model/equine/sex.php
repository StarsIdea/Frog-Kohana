<?php

class Model_Equine_Sex extends ORM {
		
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
		return DB::expr('SUBSTRING(name, 1, 1)');
	}
	
	public function select_array()
	{
		$arr = array();
		foreach($this->find_all() as $item) {
			$arr[$item->id] = $item->id . ': ' . $item->name;
		}
		return $arr;
	}
	
}