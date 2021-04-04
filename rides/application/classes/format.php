<?php

class Format {
	
	public static function empty_to_null($value)
	{
		return Valid::not_empty($value) ? $value : NULL;
	}
	
}