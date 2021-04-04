<?php

class Breadcrumbs {

	public static $breadcrumbs;

	public static $route = 'admin';

	public static $config = array(
		'separator' => '>',
		'min_display' => 1,
	);

	public static function add($title, $url=NULL)
	{
		self::$breadcrumbs[] = array(
			'uri'	=> $url,
			'title'	=> $title,
		);
	}

	public static function count()
	{
		return count(self::$breadcrumbs);
	}

	public static function render()
	{
		return View::factory('breadcrumbs')
			->set('breadcrumbs', self::$breadcrumbs);
	}
	
}