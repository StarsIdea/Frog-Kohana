<?php

class Model_Reclaim extends ORM {
	
	protected $_belongs_to = array(
		'equine' => array(),
		'member' => array(),
		'ride' => array(),
	);
	
	public function rules()
	{
		return array(
			'miles_completed' => array(
				array('not_empty'),
			),
			'year' => array(
				array('not_empty'),
			),
		);
	}
	
}