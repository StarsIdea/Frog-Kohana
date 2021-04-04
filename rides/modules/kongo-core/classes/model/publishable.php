<?php

abstract class Model_Publishable extends ORM {

	public function is_published()
	{
		return $this->published;
	}

	public function publish()
	{
		$this->published = TRUE;
		$this->last_published = time();
		$this->save();
	}

	public function unpublish()
	{
		$this->published = FALSE;
		$this->last_published = time();
		$this->save();
	}

}