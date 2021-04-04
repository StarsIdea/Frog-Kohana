<?php

class Model_Federation extends ORM {
		
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
			),
		);
	}
	
}