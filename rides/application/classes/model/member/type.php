<?php

class Model_Member_Type extends ORM {
		
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
			),
		);
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