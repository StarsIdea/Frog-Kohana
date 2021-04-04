<?php

class Model_Event_Type extends ORM {
	
	protected $_title_key = 'name';

	public function foreign_title()
	{
		return 'name';
	}
	
}
